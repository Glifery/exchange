<?php

namespace Exchange\ParserBundle\Command;

use Exchange\DomainBundle\Entity\Office;
use Exchange\DomainBundle\Service\OfficeBag;
use Exchange\ParserBundle\RawData\RawData;
use Exchange\ParserBundle\Service\ExchangeRateParser;
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

        /** @var OfficeBag $officeBag */
        $officeBag = $this->getContainer()->get('exchange_domain.office_bag');
        $officeBag->fillCache();

        $rawDataSet = $exchangeRateParser->parse();
        foreach ($rawDataSet as $rawData) {
            /** @var RawData $rawData */
            $address = $rawData->getAddress();
            $criteria = array('address' => $address);

            if (!($office = $officeBag->findEntity($criteria))) {
                $office = new Office();
                $office->setAddress($address);

                $officeBag->addEntity($criteria, $office);
            }
        }

        $officeBag->flush();
    }
}