<?php

return [
    'frontend' => [
        'typo3/extension-typesearch/proxy' => [
            'target' => \StudioMitte\TypesenseSearch\Middleware\Proxy::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-redirects/redirecthandler'
            ]
        ],
        'typo3/extension-typesearch/pagedata' => [
            'target' => \StudioMitte\TypesenseSearch\Middleware\PageData::class,
            'after' => [
                'typo3/cms-frontend/output-compression',
            ],
        ],
    ],
];
