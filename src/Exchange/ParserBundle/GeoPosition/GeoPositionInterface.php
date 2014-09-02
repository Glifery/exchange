<?php

namespace Exchange\ParserBundle\GeoPosition;

interface GeoPositionInterface
{
    public function getLatitude();
    public function getLongitude();
}