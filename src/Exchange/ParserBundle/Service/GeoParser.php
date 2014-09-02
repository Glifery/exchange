<?php

namespace Exchange\ParserBundle\Service;

use Exchange\ParserBundle\GeoPosition\GeoPosition;
use Exchange\ParserBundle\RawData\RawData;
use Umbrellaweb\Bundle\GoogleGeocodingApiBundle\Geolocation\GeocodeManager;

class GeoParser
{
    const ADDRESS_PREFIX = 'Минск, ';
    const REGION_LATLNG = '';

    /** @var GeocodeManager */
    private $geoManager;

    /**
     * @param GeocodeManager $geoManager
     */
    public function __construct(GeocodeManager $geoManager)
    {
        $this->geoManager = $geoManager;
    }

    /**
     * @param RawData $rawData
     * @return GeoPosition|null
     * @throws \HttpRuntimeException
     * @throws \OutOfRangeException
     */
    public function findGeoPosition(RawData $rawData)
    {
        $address = $rawData->getAddress();
        if (!strlen($address)) {
            throw new \OutOfRangeException('Address for \''.$rawData->getOffice().'\' is empty');
        }

        $address = self::ADDRESS_PREFIX.$address;

        $query = array(
            'address' => $address,
            'sensor' => false,
            'language' => 'ru',
            'region' => 'by'
        );
        $geoResponse = $this->geoManager->geocodeAddress($query);

        if (!$geoResponse->isSuccess()) {
            $error = $geoResponse->getErrorMessage();

            throw new \HttpRuntimeException('Error in geocode request: '.$error);
        }

        if ($geoResponse->isSuccess() && !$geoResponse->isOkResponse()) {
            $error = $geoResponse->getStatus();

            throw new \HttpRuntimeException('Error in geocode parsing: '.$error);
        }

        if ($geoResponse->getLongitude() !== null && $geoResponse->getLatitude() !== null) {
            $latitude = $geoResponse->getLatitude();
            $longitude = $geoResponse->getLongitude();

            $geoPosition = new GeoPosition($latitude, $longitude);

            return $geoPosition;
        }

        return null;
    }
}