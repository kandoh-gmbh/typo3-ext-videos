<?php
declare(strict_types=1);

/*
 * This file is part of the "videos" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addPlugin(
    [
        'Video Playlist',
        'videos_playlist',
        'EXT:core/Resources/Public/Icons/T3Icons/mimetypes/mimetypes-x-content-multimedia.svg'
    ],
    'CType',
    'videos'
);

$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['videos_playlist'] = 'mimetypes-x-content-videos_playlist';


$GLOBALS['TCA']['tt_content']['types']['videos_playlist'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
            assets,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ',
    'columnsOverrides' => [
        'assets' => [
            'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:tt_content.videos',
            'config' => [
                'filter' => [
                    0 => [
                        'userFunc' => FileExtensionFilter::class . '->filterInlineChildren',
                        'parameters' => [
                            'allowedFileExtensions' => 'mp4',
                        ]
                    ]
                ],
                'overrideChildTca' => [
                    'columns' => [
                        'uid_local' => [
                            'config' => [
                                'appearance' => [
                                    'elementBrowserAllowed' => 'mp4',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

