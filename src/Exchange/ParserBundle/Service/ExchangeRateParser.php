<?php

namespace Exchange\ParserBundle\Service;

use Exchange\ParserBundle\Parser\ParserInterface;

class ExchangeRateParser
{
    /** @var ParserInterface */
    private $parser;

    private $currencyMap = array(
        array(
            "DIRECTION" => "USD_H",
            "NAME" => "Продажа USD",
            "SORT" => "MIN",
            "INDEX" => 2,
            "STEP" => 10,
            "DEFAULT" => true
        ),
        array(
            "DIRECTION" => "EUR_H",
            "NAME" => "Продажа EUR",
            "SORT" => "MIN",
            "INDEX" => 4,
            "STEP" => 10,
        ),
        array(
            "DIRECTION" => "RUB_H",
            "NAME" => "Продажа RUB",
            "SORT" => "MIN",
            "INDEX" => 6,
            "STEP" => 1,
        ),
        array(
            "DIRECTION" => "USD_L",
            "NAME" => "Покупка USD",
            "SORT" => "MAX",
            "INDEX" => 1,
            "STEP" => 10,
        ),
        array(
            "DIRECTION" => "EUR_L",
            "NAME" => "Покупка EUR",
            "SORT" => "MAX",
            "INDEX" => 3,
            "STEP" => 10,
        ),
        array(
            "DIRECTION" => "RUB_L",
            "NAME" => "Покупка RUB",
            "SORT" => "MAX",
            "INDEX" => 5,
            "STEP" => 1,
        ),
    );

    /**
     * @param ParserInterface $parser
     */
    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function parse()
    {
        $this->parser->parseCurrencies($this->currencyMap);

        $rawDataSet = array();
        while ($rawData = $this->parser->getNextRowData()) {
            $rawDataSet[] = $rawData;
        }

        return $rawDataSet;
    }
}