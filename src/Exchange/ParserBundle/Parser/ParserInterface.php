<?php

namespace Exchange\ParserBundle\Parser;

interface ParserInterface
{
    public function parse();
    public function getNextRowData();
} 