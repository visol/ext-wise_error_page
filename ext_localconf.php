<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Visol.wise_error_page',
    'Pi1',
    array(
        'ContentRenderer' => 'render',
    ),
    // non-cacheable actions
    array(
        'ContentRenderer' => 'render',
    )
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'wise_error_page',
    'setup',
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:wise_error_page/Configuration/TypoScript/setup.txt">'
);
