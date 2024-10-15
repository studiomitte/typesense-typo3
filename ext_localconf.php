<?php


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TypesenseSearch',
    'Search',
    [
        \StudioMitte\TypesenseSearch\Controller\SearchController::class => 'index,search',
    ],
    [
        \StudioMitte\TypesenseSearch\Controller\SearchController::class => 'index,search',
    ],
);
