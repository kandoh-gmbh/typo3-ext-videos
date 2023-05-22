<?php
declare(strict_types=1);

/*
 * This file is part of the "videos" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;


call_user_func(
    function ($extKey, $table) {

        $newColumns = [
            'poster' => [
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:poster',
                'config' => [
                    'type' => 'file',
                    'maxitems' => 1,
                    'allowed' => 'common-image-types',

                    // custom configuration for displaying fields in the overlay/reference table
                    // to use the newsPalette and imageoverlayPalette instead of the basicoverlayPalette
                    'overrideChildTca' => [
                        'types' => [
                            File::FILETYPE_IMAGE => [
                                'showitem' => '
                                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette,
                                        --palette--;;imageoverlayPalette,
                                        --palette--;;filePalette'
                            ],
                        ],
                    ]
                ],
            ],
            'tracks' => [
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:tracks',
                'config' => [
                    'type' => 'file',
                    'maxitems' => 99,
                    'allowed' => 'vtt',
                    'overrideChildTca' => [
                        'types' => [
                            File::FILETYPE_TEXT => [
                                'showitem' => '--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.basicoverlayPalette;basicoverlayPalette,
                                    --palette--;;filePalette,track_language,track_type'
                            ],
                        ],
                    ]
                ],
            ],
            'aspect_ratio' => [
                'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:aspectRatio',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['', ''],
                        ['1:1', '1:1'],
                        ['4:3', '4:3'],
                        ['16:9', '16:9'],
                        ['21:9', '21:9'],
                    ],
                    'size' => 1,
                    'minitems' => 0,
                    'maxitems' => 1
                ]
            ],

        ];


        ExtensionManagementUtility::addTCAcolumns($table, $newColumns);

        ExtensionManagementUtility::addToAllTCAtypes($table, '--linebreak--,poster,tracks,aspect_ratio',
            (string)AbstractFile::FILETYPE_VIDEO, 'after:duration');


    },
    'videos',
    'sys_file_metadata'
);


