<?php

declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Command;

use StudioMitte\TypesenseSearch\Indexer\SitemapIndexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SitemapFetchCommand extends Command
{
    protected function configure()
    {
        $this
            ->addArgument(
                'siteIdentifier',
                InputArgument::REQUIRED,
                'SiteIdentifier',
            )
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Sitemap URL',
            )
            ->addArgument(
                'token',
                InputArgument::OPTIONAL,
                'Token (not yet used)'
            );

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($input->getArgument('siteIdentifier'));

        $sitemapIndexer = GeneralUtility::makeInstance(SitemapIndexer::class);
        $count = $sitemapIndexer->index($site, $input->getArgument('url'), $input->getArgument('token'));
        $io = new SymfonyStyle($input, $output);
        $io->success(sprintf('Indexed %d pages', $count));

        return 0;
    }

}
