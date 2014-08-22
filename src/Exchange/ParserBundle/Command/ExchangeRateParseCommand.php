<?php

namespace Exchange\ParserBundle\Command;

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

        $exchangeRateParser->parse();
    }
}