<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Videos',
    'description' => 'Extends video file properties and provides a player for playlists, cue points and subtitles',
    'author' => 'Sven Wappler',
    'author_email' => 'typo3@wappler.systems',
    'category' => 'misc',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '12.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'filemetadata' => ''
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];

