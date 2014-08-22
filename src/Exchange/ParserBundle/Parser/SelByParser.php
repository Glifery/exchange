<?php

namespace Exchange\ParserBundle\Parser;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SelByParser implements ParserInterface
{
    const TARGET_URL = 'http://select.by/kurs/';

    const EXPAND_CLASS = 'expand-child';

    private function getCurrencyMap()
    {
        return array(
            array(
                "CODE" => "USD_H",
                "NAME" => "Продажа USD",
                "SORT" => "MIN",
                "INDEX" => 2,
                "STEP" => 10,
                "DEFAULT" => true
            ),
            array(
                "CODE" => "EUR_H",
                "NAME" => "Продажа EUR",
                "SORT" => "MIN",
                "INDEX" => 4,
                "STEP" => 10,
            ),
            array(
                "CODE" => "RUB_H",
                "NAME" => "Продажа RUB",
                "SORT" => "MIN",
                "INDEX" => 6,
                "STEP" => 1,
            ),
            array(
                "CODE" => "USD_L",
                "NAME" => "Покупка USD",
                "SORT" => "MAX",
                "INDEX" => 1,
                "STEP" => 10,
            ),
            array(
                "CODE" => "EUR_L",
                "NAME" => "Покупка EUR",
                "SORT" => "MAX",
                "INDEX" => 3,
                "STEP" => 10,
            ),
            array(
                "CODE" => "RUB_L",
                "NAME" => "Покупка RUB",
                "SORT" => "MAX",
                "INDEX" => 5,
                "STEP" => 1,
            ),
        );
    }

    private function getTargetHtml($url)
    {
        $html = file_get_contents($url);

        if (!strlen($html)) {
            throw new NotFoundHttpException();
        }

        return $html;
    }

    private function handleTableCell(Crawler $row)
    {
        $row = $row;

        $arCols = $row->find("td");
        preg_match("/- ([^,]*),.*/", $arCols->eq(0)->text(), $match);
        $address = $arCols->eq(0)->find("a")->text();
        $addressUrl = str_replace(" ", "+", "Минск, ".$address);
        $addressJSON = file_get_contents("http://geocode-maps.yandex.ru/1.x/?format=json&results=1&geocode={$addressUrl}");
        $addressArray = json_decode($addressJSON, true);
        $rates = array();
        foreach($arCurrency as $pos=>$cur)
        {
            $rates[$cur["CODE"]] = (int)trim($arCols->eq($cur["INDEX"])->text());
            if(!isset($arCurrency[$pos]["MIN"]) || ($rates[$cur["CODE"]] < $arCurrency[$pos]["MIN"])) $arCurrency[$pos]["MIN"] = $rates[$cur["CODE"]];
            if(!isset($arCurrency[$pos]["MAX"]) || ($rates[$cur["CODE"]] > $arCurrency[$pos]["MAX"])) $arCurrency[$pos]["MAX"] = $rates[$cur["CODE"]];
        }

        $arResult["BANKS"][$rowParent]["SUB"][] = array(
            "NAME" => $match[1],
            "ADDRESS" => $address,
            "JSON" => $addressJSON,
            "POS" => explode(" ", $addressArray["response"]["GeoObjectCollection"]["featureMember"][0]["GeoObject"]["Point"]["pos"]),
            "RATES" => $rates
        );
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

    public function parse()
    {
        $html = $this->getTargetHtml(self::TARGET_URL);

        $handleTableCell = function(Crawler $cell, $i)
        {
            if ($i) {
                $this->handleTableCell($cell);
            } else {
                $office = $this->getOffice($cell);
                $address = $this->getAddress($cell);
            }
        };

        $handleTableRow = function(Crawler $row) use ($handleTableCell)
        {
            $class = $row->eq(0)->attr('class');

            if ($class == self::EXPAND_CLASS) {
                $row
                    ->filter('td')
                    ->each($handleTableCell)
                ;
            } else {
                $ee = 2;
            }

            return $row->text();
        };

        $document = new Crawler($html);
        $document
            ->filter('#curr_table tbody tr')
            ->each($handleTableRow)
        ;
    }

    public function getNextRowData()
    {
        // TODO: Implement getNextRowData() method.
    }
}