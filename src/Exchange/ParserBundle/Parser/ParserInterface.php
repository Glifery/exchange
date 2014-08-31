<?php

namespace Exchange\ParserBundle\Parser;

interface ParserInterface
{
    public function parseCurrencies(array $currencyMap);
    public function getNextRowData();
} 