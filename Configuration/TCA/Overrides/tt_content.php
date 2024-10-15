<?php


$contentTypeName = 'Search';

$lowerCaseName = \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($contentTypeName);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'TypesenseSearch',
    $contentTypeName,
    $contentTypeName,
//    null,
//    'products'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['typesensesearch_search'] = 'recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['typesensesearch_search'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'typesensesearch_search',
    'FILE:EXT:typesense_search/Configuration/Flexforms/flexforms_typesense.xml'
);
