<?php
defined('TYPO3_MODE') or die();


call_user_func(
    function ($extKey, $table) {


        $newColumns = [
            'poster' => [
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:poster',
                'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    'poster',
                    [
                        'minitems' => 0,
                        'maxitems' => 1,
                        'appearance' => [
                            'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference',
                            'showAllLocalizationLink' => 1,
                        ],
                        'foreign_match_fields' => [
                            'fieldname' => 'poster',
                            'tablenames' => 'sys_file_metadata',
                            'table_local' => 'sys_file',
                        ],
                        // custom configuration for displaying fields in the overlay/reference table
                        // to use the newsPalette and imageoverlayPalette instead of the basicoverlayPalette
                        'foreign_types' => [
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
								--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette,
								--palette--;;imageoverlayPalette,
								--palette--;;filePalette'
                            ],

                        ]
                    ],
                    $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
                )
            ],

            'tracks' => [
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'label' => 'LLL:EXT:videos/Resources/Private/Language/locallang_be.xlf:tracks',
                'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    'tracks',
                    [
                        'minitems' => 0,
                        'maxitems' => 10,
                        'appearance' => [
                            'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference',
                            'showAllLocalizationLink' => 1,
                        ],
                        'foreign_match_fields' => [
                            'fieldname' => 'tracks',
                            'tablenames' => 'sys_file_metadata',
                            'table_local' => 'sys_file',
                        ],
                        'foreign_types' => [
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                'showitem' => '--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.basicoverlayPalette;basicoverlayPalette,
				                    --palette--;;filePalette,track_language,track_type'
                            ],
                        ]
                    ],
                    'vtt'
                )
            ],

        ];


        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $newColumns);

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes($table, '--linebreak--,poster,tracks',
            TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO, 'after:duration');


    },
    'videos',
    'sys_file_metadata'
);


