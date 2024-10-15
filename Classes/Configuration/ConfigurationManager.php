<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Configuration;

use TYPO3\CMS\Core\Site\Entity\Site;

readonly class ConfigurationManager
{

    public function getAuthentication(Site $site): Dto\Authentication
    {
        $siteSettings = $site->getSettings()->getAll()['typesense'] ?? [];
        return new Dto\Authentication($siteSettings['authentication']);
    }

    public function getBySite(Site $site, string $profileIdentifier): Dto\Configuration
    {
        $siteSettings = $site->getSettings()->getAll()['typesense'] ?? [];


        if (!isset($siteSettings['profiles'][$profileIdentifier])) {
            throw new \Exception(sprintf('Profile %s not found in site %s', $profileIdentifier, $site->getIdentifier()));
        }
        $authentication = new Dto\Authentication($siteSettings['authentication']);
        $profile = new Dto\Profile($profileIdentifier, $siteSettings['profiles'][$profileIdentifier]);

        return new Dto\Configuration($authentication, $profile);
    }

    /**
     * @return Dto\Configuration[]
     */
    public function getAllBySite(Site $site): array
    {
        $collection = [];
        $siteSettings = $site->getSettings()->getAll()['typesense'] ?? [];

        if (!is_array($siteSettings['profiles'] ?? null)) {
            throw new \RuntimeException(sprintf('Profiles not found in site %s', $site->getIdentifier()));
        }
        foreach ($siteSettings['profiles'] as $profileIdentifier => $profileSettings) {
            $authentication = new Dto\Authentication($siteSettings['authentication']);
            $profile = new Dto\Profile($profileIdentifier, $profileSettings);
            $collection[] = new Dto\Configuration($authentication, $profile);
        }

        return $collection;
    }

}
