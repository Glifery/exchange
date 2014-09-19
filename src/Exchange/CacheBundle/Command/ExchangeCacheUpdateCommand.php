<?php

namespace Exchange\CacheBundle\Command;

use Exchange\CacheBundle\Service\CacheManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExchangeCacheUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('exchange_cache:update')
            ->setDescription('Update exchange cache from exchange.js')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = $this->getContainer()->get('exchange_cache.cache_manager');

        $output->write('..Cache JS saving...');
        $cacheManager->regenerateCache();
        $output->writeln('done!');
    }
}