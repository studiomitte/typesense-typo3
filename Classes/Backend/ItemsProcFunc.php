<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Backend;

use StudioMitte\TypesenseSearch\Api\Client;
use StudioMitte\TypesenseSearch\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemsProcFunc
{
    protected readonly ConfigurationManager $configurationManager;

    public function __construct()
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
    }

    public function getAllProfilesOfSite(array &$config): void
    {
        $site = $this->getSite($config);

        $configurations = $this->configurationManager->getAllBySite($site);
        foreach ($configurations as $configuration) {
            $additionalProfile = [
                $configuration->profile->getFullLabel(),
                $configuration->profile->identifier,
            ];
            $config['items'][] = $additionalProfile;
        }
    }

    public function getQueryByFieldsOfCollection(array &$config): void
    {
        $collection = $this->getCollection($config);
        if (!$collection) {
            return;
        }

        $fields = array_filter($collection['fields'], static function ($facet) {
            return ($facet['index'] === true && !in_array($facet['name'], ['pid', 'url', 'embedding', '_geoloc', 'sitelanguage', 'languageid', 'site', 'uid', 'site'], true) && !str_starts_with($facet['name'], '.*'));
        });
        foreach ($fields as $field) {
            $config['items'][] = [
                sprintf('%s [%s]', $field['name'], $field['type']),
                $field['name'],
            ];
        }
    }

    public function getAllFacetFieldsOfCollection(array &$config): void
    {
        $collection = $this->getCollection($config);
        if (!$collection) {
            return;
        }

        $fields = array_filter($collection['fields'], static function ($facet) {
            return ($facet['facet'] === true && !str_starts_with($facet['name'], '.*'));
        });
        foreach ($fields as $field) {
            $config['items'][] = [
                sprintf('%s [%s]', $field['name'], $field['type']),
                $field['name'],
            ];
        }
    }

    private function getSite(array $config): Site
    {
        /** @var Site $site */
        $site = $config['site'] ?? null;
        if (!$site) {
            // todo error handling: flash message, logging
            die('no site!');
        }
        return $site;
    }

    private function getProfile(array $config): ?string
    {
        $selectedProfile = null;
        if (isset($config['row']['settings.profile'])) {
            if (is_array($config['row']['settings.profile'])) {
                $selectedProfile = $config['row']['settings.profile'][0] ?? '';
            } else {
                $selectedProfile = $config['row']['settings.profile'] ?? '';
            }
        }
        return $selectedProfile;
    }

    private function getCollection(array $config): array
    {
        $site = $this->getSite($config);
        $selectedProfile = $this->getProfile($config);

        if (!$site || !$selectedProfile) {
            // todo flash msg
            return [];
        }

        $configuration = $this->configurationManager->getBySite($site, $selectedProfile);

        $client = (new Client())->get($site);
        $collection = $client->collections[$configuration->profile->collection]->retrieve();
        return $collection;
    }
}
