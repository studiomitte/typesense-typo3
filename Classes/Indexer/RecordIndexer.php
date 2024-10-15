<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Indexer;

use StudioMitte\TypesenseSearch\Api\Client;
use StudioMitte\TypesenseSearch\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class RecordIndexer
{
    private SiteInterface $site;
    protected ConfigurationManager $configurationManager;


    public function __construct(
        private string $siteIdentifier,
        protected string $collection,
    )
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
    }

    public function index(array $documents, string $deleteConstraint = ''): int
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($this->siteIdentifier);

        $client = new Client();
        $client = $client->get($site);

        if ($deleteConstraint) {
            $client->collections[$this->collection]->documents->delete(['filter_by' => $deleteConstraint]);
        }

        if (empty($documents)) {
            return 0;
        }
        $response = $client->collections[$this->collection]->documents->import($documents, ['action' => 'upsert']);
        return count($documents);
    }

    private function transformRecordsToDocuments($raw)
    {
        $documents = [];
        $data = $this->getData();
        foreach ($data as $row) {
            $document = [];
            foreach ($this->fieldMapping as $field => $mapping) {
                $document[$field] = $row[$mapping];
            }
            $documents[] = $document;
        }

        return $documents;
    }

}
