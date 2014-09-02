<?php

namespace Exchange\ParserBundle\Command;

use Exchange\DomainBundle\Entity\Bank;
use Exchange\DomainBundle\Entity\Office;
use Exchange\DomainBundle\Service\BankBag;
use Exchange\DomainBundle\Service\OfficeBag;
use Exchange\ParserBundle\RawData\RawData;
use Exchange\ParserBundle\Service\ExchangeRateParser;
use Exchange\ParserBundle\Service\GeoParser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExchangeRateParseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('exchange_parser:exchange_rate')
            ->setDescription('Get Exchange Rates')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Launch exchange rate parser');

        /** @var ExchangeRateParser $exchangeRateParser */
        $exchangeRateParser = $this->getContainer()->get('exchange_parser.exchange_rate_parser');
        /** @var GeoParser $geoParser */
        $geoParser = $this->getContainer()->get('exchange_parser.geo_parser');

        /** @var BankBag $bankBag */
        $bankBag = $this->getContainer()->get('exchange_domain.bank_bag');
        $bankBag->fillCache();

        /** @var OfficeBag $officeBag */
        $officeBag = $this->getContainer()->get('exchange_domain.office_bag');
        $officeBag->fillCache();

        $rawDataSet = $exchangeRateParser->parse();
        foreach ($rawDataSet as $rawData) {
            /** @var RawData $rawData */
            $address = $rawData->getAddress();
            $bankName = $rawData->getBank();

            $bankCriteria = array('title' => $bankName);
            if (!($bank = $bankBag->findEntity($bankCriteria))) {
                $bank = new Bank();
                $bank->setTitle($bankName);

                $bankBag->addEntity($bankCriteria, $bank);
            }

            $officeCriteria = array('address' => $address);
            if (!($office = $officeBag->findEntity($officeCriteria))) {
                $office = new Office();
                $office->setTitle($rawData->getOffice());
                $office->setBank($bank);
                $office->setAddress($address);

//                if ($geoPosition = $geoParser->findGeoPosition($rawData)) {
//                    $office->setLatitude($geoPosition->getLatitude());
//                    $office->setLongitude($geoPosition->getLongitude());
//                }

                $officeBag->addEntity($officeCriteria, $office);
            }
        }

        $officeBag->flush();
    }
}