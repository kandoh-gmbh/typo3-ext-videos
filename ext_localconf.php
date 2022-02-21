<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Videos\Resource\Rendering\VideoTagRenderer;

defined('TYPO3') or die();


$rendererRegistry = GeneralUtility::makeInstance(RendererRegistry::class);
$rendererRegistry->registerRendererClass(VideoTagRenderer::class);

if (TYPO3_MODE === 'BE') {

    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    $iconRegistry->registerIcon(
        'mimetypes-x-content-videos_playlist',
        SvgIconProvider::class,
        ['source' => 'EXT:core/Resources/Public/Icons/T3Icons/mimetypes/mimetypes-x-content-multimedia.svg']
    );

}


