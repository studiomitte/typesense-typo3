<?php

declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Command;

use StudioMitte\TypesenseSearch\Api\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BasicSetupCommand extends Command
{
    protected function configure()
    {
        $this
            ->addArgument(
                'siteIdentifier',
                InputArgument::REQUIRED,
                'SiteIdentifier',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($input->getArgument('siteIdentifier'));
        $client = new Client();
        $typesense = $client->get($site);
        $this->addGlobalGollection($typesense);
        return 0;
    }

    private function addGlobalGollection(\Typesense\Client $client)
    {
        try {
            $client->collections['global']->delete();
        } catch (\Exception $e) {
            // Ignore
        }
        $schema = [
            'name' => 'global',
            'fields' => [
                // core fields
                [
                    'name' => 'uid',
                    'type' => 'int64',
                ],
                [
                    'name' => 'pid',
                    'type' => 'int64',
                ],
                [
                    'name' => 'site',
                    'type' => 'string',
                    'facet' => true,
                ],
                [
                    'name' => 'sitelanguage',
                    'type' => 'string',
                    'facet' => true,
                ],
                [
                    'name' => 'languageid',
                    'type' => 'int32',
                    'facet' => true,
                    'optional' => true,
                ],
                [
                    'name' => 'table',
                    'type' => 'string',
                    'facet' => true,
                ],
                // content
                [
                    'name' => 'title',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'url',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'teaser',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'content',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'content_full',
                    'type' => 'string',
                    'optional' => true,
                    'index' => false,
                ],
                // basic helpful
                [
                    'name' => 'tree.lvl0',
                    'type' => 'string',
                    'optional' => true,
                    'facet' => true,
                ],
                [
                    'name' => 'tree.lvl1',
                    'type' => 'string',
                    'optional' => true,
                    'facet' => true,
                ],
                [
                    'name' => 'tree.lvl2',
                    'type' => 'string',
                    'optional' => true,
                    'facet' => true,
                ],
                [
                    'name' => 'tree.lvl3',
                    'type' => 'string',
                    'optional' => true,
                    'facet' => true,
                ],
                // dynamic fields
                [
                    'name' => '.*_string_facet',
                    'type' => 'string',
                    'optional' => true,
                    'facet' => true,
                ],
                [
                    'name' => '.*_string',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => '.*_int_facet',
                    'type' => 'int64',
                    'optional' => true,
                    'facet' => true,
                ],
                [
                    'name' => '.*_int',
                    'type' => 'int64',
                    'optional' => true,
                ],
                [
                    'name' => '.*_bool_facet',
                    'type' => 'bool',
                    'optional' => true,
                    'facet' => true,
                ],
                [
                    'name' => '.*_bool',
                    'type' => 'bool',
                    'optional' => true,
                ],
                // embedding
                [
                    'name' => 'embedding',
                    'type' => 'float[]',
                    'embed' => [
                        'from' => [
                            'title',
                            'teaser',
                            'content',
                        ],
                        'model_config' => [
                            'model_name' => 'ts/e5-small',
                        ],
                    ],
                ],
                [
                    'name' => '_geoloc',
                    'type' => 'geopoint',
                    'optional' => true,
                    'facet' => true,
                ],
            ],
//            'default_sorting_field' => 'num_employees',
        ];

        $response = $client->collections->create($schema);
//        $response = $client->collections['products']->update($schema);
//        print_R($response);
    }
}
