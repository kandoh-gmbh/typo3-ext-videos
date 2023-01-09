<?php
declare(strict_types=1);

/*
 * This file is part of the "videos" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();


$newSysFileReferenceColumns = [
    'loop' => [
        'exclude' => true,
        'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:loop',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
        ]
    ],
    'show_related_videos' => [
        'exclude' => true,
        'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:show_related_videos',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
        ]
    ],
    'muted' => [
        'exclude' => true,
        'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:muted',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
        ]
    ],
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

ExtensionManagementUtility::addTCAcolumns('sys_file_reference', $newSysFileReferenceColumns);

ExtensionManagementUtility::addFieldsToPalette('sys_file_reference', 'videoOverlayPalette', 'loop,muted,show_related_videos');
