<?php

namespace Exchange\ParserBundle\Command;

use Exchange\DomainBundle\Entity\Bank;
use Exchange\DomainBundle\Entity\ExchangeRate;
use Exchange\DomainBundle\Entity\Office;
use Exchange\DomainBundle\Service\BankBag;
use Exchange\DomainBundle\Service\ExchangeRateBag;
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
            ->setName('exchange:parser')
            ->setDescription('Get Exchange Rates')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('EXCHANGE RATES PARSER');

        /** @var ExchangeRateParser $exchangeRateParser */
        $exchangeRateParser = $this->getContainer()->get('exchange_parser.exchange_rate_parser');
        /** @var GeoParser $geoParser */
        $geoParser = $this->getContainer()->get('exchange_parser.geo_parser');

        /** @var BankBag $bankBag */
        $bankBag = $this->getContainer()->get('exchange_domain.bank_bag');
        /** @var OfficeBag $officeBag */
        $officeBag = $this->getContainer()->get('exchange_domain.office_bag');
        /** @var ExchangeRateBag $exchangeRateBag */
        $exchangeRateBag = $this->getContainer()->get('exchange_domain.exchange_rate_bag');

        $output->writeln('..Cache filling:');

        $output->write('....get banks...');
        $bankBag->fillCache();
        $output->writeln('done!');

        $output->write('....get offices...');
        $officeBag->fillCache();
        $output->writeln('done!');

        $output->write('....get exchange rates...');
        $exchangeRateBag->fillCache();
        $output->writeln('done!');

        $output->write('..Parsing...');
        $rawDataSet = $exchangeRateParser->parse();
        $output->writeln('found '.count($rawDataSet).' positions!');

        $output->writeln('..Saving...');
        $newBanksAmount = 0;
        $newOfficesAmount = 0;
        $newExchangeRatesAmount = 0;

        foreach ($rawDataSet as $rawData) {
            /** @var RawData $rawData */
            $address = $rawData->getAddress();
            $bankTitle = $rawData->getBank();
            $direction = $rawData->getDirection();
            $longitude = $rawData->getLongitude();
            $latitude = $rawData->getLatitude();

            $bankCriteria = array('title' => $bankTitle);
            if (!($bank = $bankBag->findEntity($bankCriteria))) {
                $bank = new Bank();
                $bank->setTitle($bankTitle);

                $bankBag->addEntity($bankCriteria, $bank);

                $output->writeln('....new bank: \''.$bankTitle.'\'');
                $newBanksAmount++;
            }

            $officeCriteria = array(
                'bank' => $bank,
                'title' => $rawData->getOffice(),
                'address' => $address
            );
            if (!($office = $officeBag->findEntity($officeCriteria))) {
                $office = new Office();
                $office->setTitle($rawData->getOffice());
                $office->setBank($bank);
                $office->setAddress($address);

                if ($longitude && $latitude) {
                    $office->setLatitude($longitude);
                    $office->setLongitude($latitude);
                } else {
                    if ($geoPosition = $geoParser->findGeoPosition($rawData)) {
                        $office->setLatitude($geoPosition->getLatitude());
                        $office->setLongitude($geoPosition->getLongitude());
                    }
                }

                $officeBag->addEntity($officeCriteria, $office);

                $output->writeln('....new office: \''.$address.'\' ('.$bankTitle.')');
                $newOfficesAmount++;
            }

            $exchangeRateCriteria = array(
                'office' => $office,
                'direction' => $direction
            );
            if (!($exchangeRate = $exchangeRateBag->findEntity($exchangeRateCriteria))) {
                $exchangeRate = new ExchangeRate();
                $exchangeRate->setDirection($direction);
                $exchangeRate->setOffice($office);

                $exchangeRateBag->addEntity($exchangeRateCriteria, $exchangeRate);

                $newExchangeRatesAmount++;
            }

            $exchangeRate->setValue($rawData->getExchangeRate());
            $exchangeRate->setUpdatedAt(new \DateTime());
        }
        $output->writeln('..Saving results:');
        $output->writeln('....'.$newBanksAmount.' banks created');
        $output->writeln('....'.$newOfficesAmount.' offices created');
        $output->writeln('....'.$newExchangeRatesAmount.' exchange rates created');

        $output->write('..Flushing...');
        $officeBag->flush();
        $output->writeln('done!');

        $cacheUpdater = $this->getApplication()->find('exchange:cache');
        $cacheUpdater->run($input, $output);

        $output->writeln('SUCCESSFUL FINISH');
    }
}