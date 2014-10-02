<?php

namespace Exchange\ParserBundle\Parser;

use Exchange\DomainBundle\Model\Currency;
use Exchange\ParserBundle\RawData\RawData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;

class TutByParser implements ParserInterface
{
    const SELECTOR_TABLE = '.inner .m-tbl tr';

    /** @var array */
    private $parameters;

    /** @var array */
    private $currencies;

    /** @var array */
    private $rawDataSet;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->loadParameters($container);
    }

    /**
     * @param ContainerInterface $container
     * @throws \Exception
     */
    private function loadParameters(ContainerInterface $container)
    {
        $parameters = $container->getParameter('tut_by');
        if (!is_array($parameters) || !count($parameters)) {
            throw new \Exception('Can\'t get tut.by parameters');
        }

        if (!array_key_exists('currency', $parameters) || !is_array($parameters['currency']) || !count($parameters['currency'])) {
            throw new \Exception('Can\'t get tut.by currencies');
        }

        foreach ($parameters['currency'] as $code => $currencyArray) {
            $currencyArray['code'] = $code;

            if (!$currency = Currency::createFromArray($currencyArray)) {
                throw new \Exception('Can\'t create Currency object from "'.$currencyArray.'"');
            }

            $this->currencies[] = $currency;
        }

        unset($parameters['currency']);
        $this->parameters = $parameters;
    }

    /**
     * @param Currency $currency
     * @return mixed
     * @throws \Exception
     */
    private function generateCurrencyLink(Currency $currency)
    {
            $link = str_replace('#slug#', $currency->getSlug(), $this->parameters['template']['link']);
            if (!strlen($link)) {
                throw new \Exception('Can\'t generate link for currency "'.$currency->getCode().'"');
            }

            return $link;
    }

    private function getLinkHtml($link)
    {
        $html = file_get_contents($link);

        if (!strlen($html)) {
            throw new NotFoundHttpException('Can\'t get content of url "'.$link.'"');
        }

        return $html;
    }

    /**
     * @param string $link
     * @return array
     */
    private function parseLinkRecursive($link)
    {
        $html = $this->getLinkHtml($link);
        $document = new Crawler($html);

        $rowsDataArray = $this->parseRatesOnPage($document);

        if ($nextLink = $this->checkNextLink($document)) {
            unset($document);

            $nextRowsDataArray = $this->parseLinkRecursive($nextLink);
            $rowsDataArray = array_merge($rowsDataArray, $nextRowsDataArray);
        } else {
            unset($document);
        }


        return $rowsDataArray;
    }

    /**
     * @param Crawler $document
     * @internal param $html
     * @internal param string $link
     * @return array
     */
    private function parseRatesOnPage(Crawler $document)
    {
        $rowsDataArray = $document
            ->filter(self::SELECTOR_TABLE)
            ->reduce($this->exceptFirstRow())
            ->each($this->parseElementsInRow())
        ;

        if (!is_array($rowsDataArray)) {
            return array();
        }

        return $rowsDataArray;
    }

    /**
     * @param Crawler $document
     * @return string|null
     */
    private function checkNextLink(Crawler $document)
    {
        if ($nextPageButton = $document->filter('.inner .b-pagination-list .p-next a')) {
            if ($nextPageButton->count()) {
                $nextLink = $nextPageButton->attr('href');

                return $nextLink;
            }
        }

        return null;
    }

    private function parseElementsInRow()
    {
        return function(Crawler $row)
        {
            $bankTitle = $row->filter('td')->eq(0)->filter('a')->text();
            $officeTitle = $row->filter('td')->eq(0)->filter('.addr')->text();
            $exchangeLow = $row->filter('td')->eq(1)->text();
            $exchangeHight = $row->filter('td')->eq(2)->text();
            $officeAddress = $row->filter('td.addr a')->text();
            $officeLink = $row->filter('td.addr a')->attr('href');

            $coordinates = $this->makeCoordinatesFromLink($officeLink);

            $rowArray = array(
                'bankTitle'     => $bankTitle,
                'officeTitle'   => $officeTitle,
                'exchangeLow'   => $this->makeValueFrom($exchangeLow),
                'exchangeHight' => $this->makeValueFrom($exchangeHight),
                'officeAddress' => $officeAddress,
                'officeLink'    => $officeLink,
                'longitude'     => $coordinates['longitude'],
                'latitude'      => $coordinates['latitude']
            );

            return $rowArray;
        };
    }

    private function exceptFirstRow()
    {
        return function(Crawler $row, $i)
        {
            return ($i != 0);
        };
    }

    private function makeValueFrom($string)
    {
        $string = str_replace(' ', '', $string);

        return (int)$string;
    }

    /**
     * @param $link
     * @return array|null
     */
    private function makeCoordinatesFromLink($link)
    {
        $linkInfo = parse_url($link);
        if (isset($linkInfo['query']) && strlen($linkInfo['query'])) {
            $urlParams = array();
            parse_str($linkInfo['query'], $urlParams);

            if (isset($urlParams['x']) && isset($urlParams['y'])) {
                $coordinates = array(
                    'latitude' => (float)$urlParams['x'],
                    'longitude'  => (float)$urlParams['y'],
                );

                return $coordinates;
            }
        }

        return null;
    }

    /**
     * @param Currency $currency
     * @param array $rowsData
     * @return array
     */
    private function resolveRowsToRawData(Currency $currency, array $rowsData)
    {
        $rawDataSet = array();

        foreach ($rowsData as $row) {
            $rawData = new RawData();
            $rawData->setAddress($row['officeAddress']);
            $rawData->setBank($row['bankTitle']);
            $rawData->setDirection($currency->getCode().'_L');
            $rawData->setExchangeRate($row['exchangeLow']);
            $rawData->setOffice($row['officeTitle']);
            $rawData->setLongitude($row['longitude']);
            $rawData->setLatitude($row['latitude']);
            $rawDataSet[] = $rawData;

            $rawData = new RawData();
            $rawData->setAddress($row['officeAddress']);
            $rawData->setBank($row['bankTitle']);
            $rawData->setDirection($currency->getCode().'_H');
            $rawData->setExchangeRate($row['exchangeHight']);
            $rawData->setOffice($row['officeTitle']);
            $rawData->setLongitude($row['longitude']);
            $rawData->setLatitude($row['latitude']);
            $rawDataSet[] = $rawData;
        }

        return $rawDataSet;
    }

    public function parseCurrencies(array $currencyMap)
    {
        $this->rawDataSet = array();

        foreach ($this->currencies as $currency) {
            $firstLink = $this->generateCurrencyLink($currency);

            $rowsDataArray = $this->parseLinkRecursive($firstLink);

            $rawDataSet = $this->resolveRowsToRawData($currency, $rowsDataArray);

            $this->rawDataSet = array_merge($this->rawDataSet, $rawDataSet);
        }
    }

    public function getNextRowData()
    {
        return array_shift($this->rawDataSet);
    }
}