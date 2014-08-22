<?php

namespace Exchange\ParserBundle\Service;

use Exchange\ParserBundle\Parser\ParserInterface;

class ExchangeRateParser
{
    /** @var ParserInterface */
    private $parser;

    /**
     * @param ParserInterface $parser
     */
    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function parse()
    {
        $this->parser->parse();

        $rowData = $this->parser->getNextRowData();
    }
}