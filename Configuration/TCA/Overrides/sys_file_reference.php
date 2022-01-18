<?php
defined('TYPO3_MODE') or die();


$newSysFileReferenceColumns = [
    'track_language' => [
        'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:track_language',
        'config' => [
            'type' => 'language',
        ]
    ],
    'track_type' => [
        'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:track_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:track_type.subtitles', 'subtitles'],
                ['LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:track_type.captions', 'captions'],
                ['LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:track_type.descriptions', 'descriptions'],
                ['LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:track_type.chapters', 'chapters'],
                ['LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:track_type.metadata', 'metadata']
            ],
            'default' => 'subtitles',
        ]
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_reference', $newSysFileReferenceColumns);

