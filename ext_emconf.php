<?php

$EM_CONF['videos'] = [
    'title' => 'Videos',
    'description' => 'Extends video file properties and provides a player for playlists, cue points and subtitles',
    'author' => 'Sven Wappler',
    'author_email' => 'typo3@wappler.systems',
    'category' => 'misc',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '13.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.4.99',
            'filemetadata' => '13.0.0'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];

