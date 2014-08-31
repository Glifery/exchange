<?php

namespace Exchange\ParserBundle\Parser;

use Exchange\ParserBundle\RawData\RawData;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SelByParser implements ParserInterface
{
    const TARGET_URL = 'http://select.by/kurs/';

    const EXPAND_CLASS = 'expand-child';

    /** @var array */
    private $currencyMap;

    /** @var string */
    private $bankNameIterator;

    /** @var array */
    private $currencies = array();

    private function getDirectionByIndex($index)
    {
        foreach ($this->currencyMap as $currency)
        {
            if ($currency['INDEX'] == $index) {
                return $currency['CODE'];
            }
        }

        return null;
    }

    private function getTargetHtml($url)
    {
        $html = file_get_contents($url);

        if (!strlen($html)) {
            throw new NotFoundHttpException();
        }

        return $html;
    }

    private function handleCurrencyCell(Crawler $row, $position)
    {
        $arCurrency = $this->getCurrencyMap();

        $value = $row->text();

        $arCols = $row->filter("td");
        preg_match("/- ([^,]*),.*/", $arCols->eq(0)->text(), $match);
//        $address = $arCols->eq(0)->filter("a")->text();
//        $addressUrl = str_replace(" ", "+", "Минск, ".$address);
//        $addressJSON = file_get_contents("http://geocode-maps.yandex.ru/1.x/?format=json&results=1&geocode={$addressUrl}");
//        $addressArray = json_decode($addressJSON, true);
        $rates = array();
        foreach($arCurrency as $pos=>$cur)
        {
            $rates[$cur["CODE"]] = (int)trim($arCols->eq($cur["INDEX"])->text());
            if(!isset($arCurrency[$pos]["MIN"]) || ($rates[$cur["CODE"]] < $arCurrency[$pos]["MIN"])) $arCurrency[$pos]["MIN"] = $rates[$cur["CODE"]];
            if(!isset($arCurrency[$pos]["MAX"]) || ($rates[$cur["CODE"]] > $arCurrency[$pos]["MAX"])) $arCurrency[$pos]["MAX"] = $rates[$cur["CODE"]];
        }

//        $arResult["BANKS"][$rowParent]["SUB"][] = array(
//            "NAME" => $match[1],
//            "ADDRESS" => $address,
//            "JSON" => $addressJSON,
//            "POS" => explode(" ", $addressArray["response"]["GeoObjectCollection"]["featureMember"][0]["GeoObject"]["Point"]["pos"]),
//            "RATES" => $rates
//        );
    }

    private function getAddress(Crawler $cell)
    {
        $address = $cell->eq(0)->filter('a')->text();

        return $address;
    }

    private function getOffice(Crawler $cell)
    {
        $officeAddress = $cell->eq(0)->text();

        preg_match("/- ([^,]*),.*/", $officeAddress, $match);
        $office = $match[1];

        return $office;
    }

    private function generateRowDataSet($officeCurrencies)
    {
        $officeInfo = array_shift($officeCurrencies);
        if (!isset($officeInfo['office']) || !isset($officeInfo['address'])) {
            throw new \LogicException('There is no office info in first element of officeCurrencies array');
        }

        $currencies = array();
        foreach ($officeCurrencies as $currencyArray) {
            if (!is_array($currencyArray)) {
                continue;
            }

            $currency = new RawData();
            $currency->setBank($this->bankNameIterator);
            $currency->setAddress($officeInfo['address']);
            $currency->setOffice($officeInfo['office']);

            foreach ($currencyArray as $direction => $exchangeRate) {
                $currency->setDirection($direction);
                $currency->setExchangeRate($exchangeRate);
            }

            $currencies[] = $currency;
        }

        return $currencies;
    }

    public function parseCurrencies(array $currencyMap)
    {
        $this->currencyMap = $currencyMap;

        $html = $this->getTargetHtml(self::TARGET_URL);

        $handleTableCell = function(Crawler $cell, $i)
        {
            if ($i) {
                $direction = $this->getDirectionByIndex($i);
                if (!$direction) {
                    return null;
                }

                $value = $cell->text();

                return array(
                    $direction => $value,
                );
            } else {
                $office = $this->getOffice($cell);
                $address = $this->getAddress($cell);

                return array(
                    'office' => $office,
                    'address' => $address
                );
            }
        };

        $handleTableRow = function(Crawler $row) use ($handleTableCell)
        {
            $class = $row->eq(0)->attr('class');

            if ($class == self::EXPAND_CLASS) {
                $officeCurrencies = $row
                    ->filter('td')
                    ->each($handleTableCell)
                ;

                $currencies = $this->generateRowDataSet($officeCurrencies);
                $this->currencies = array_merge($this->currencies, $currencies);
            } else {
                $this->bankNameIterator = $row->eq(0)->filter('td')->eq(1)->filter('a')->text();
            }
        };

        $document = new Crawler($html);
        $document
            ->filter('#curr_table tbody tr')
            ->each($handleTableRow)
        ;
    }

    public function getNextRowData()
    {
        if (!is_array($this->currencies) || !count($this->currencies)) {
            return null;
        }

        return array_shift($this->currencies);
    }
}