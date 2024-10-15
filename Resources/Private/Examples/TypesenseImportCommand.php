<?php

declare(strict_types=1);

namespace Vendor\Extension\Command;

use StudioMitte\TypesenseSearch\Indexer\RecordIndexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TypesenseImportCommand extends Command
{

    private const TABLE = 'tx_news_domain_model_news';
    private const SITE = 'main';
    private const SITE_LANGUAGE = 'de';
    private const COLLECTION = 'global';


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success(sprintf('Imported %d news', $this->importNews()));
        return 0;
    }

    protected function importNews(): int
    {
        $site = self::SITE;
        $collection = self::COLLECTION;
        $recordIndexer = GeneralUtility::makeInstance(RecordIndexer::class, $site, $collection);

        $documents = [];
        foreach ($this->getNews() as $row) {
            $item = [
                'table' => self::TABLE,
                'id' => self::TABLE . $row['uid'],
                'site' => $site,
                'sitelanguage' => self::SITE_LANGUAGE,
                'uid' => $row['uid'],
                'pid' => $row['pid'],
                'title' => $row['title'],
                'url' => '',
                'content' => strip_tags($row['bodytext']),
                'teaser' => $row['teaser'],
                'topnews_bool_facet' => $row['istopnews'],
            ];
            $documents[] = $item;
        }
        return $recordIndexer->index($documents, 'table:' . self::TABLE);
    }

    private function getNews(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE)->createQueryBuilder();
        return
            $queryBuilder
                ->select('*')
                ->from(self::TABLE)
                ->where(
                    $queryBuilder->expr()->eq('sys_language_uid', 0),
                )
                ->executeQuery()->fetchAllAssociative();
    }


}
