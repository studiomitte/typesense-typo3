<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Api;

use StudioMitte\TypesenseSearch\Configuration\ConfigurationManager;
use Symfony\Component\HttpClient\HttplugClient;
use TYPO3\CMS\Core\Site\Entity\Site;

class Client
{


    public function get(Site $site): \Typesense\Client
    {
        $configurationManager = new ConfigurationManager();
        $authentication = $configurationManager->getAuthentication($site);
        return new \Typesense\Client(
            [
                'api_key' => $authentication->apiKeyWrite,
                'nodes' => [
                    [
                        'host' => $authentication->host,
                        'port' => $authentication->port,
                        'protocol' => $authentication->protocol,
                    ],
                ],
                'client' => new HttplugClient(),
            ]
        );
    }

}
