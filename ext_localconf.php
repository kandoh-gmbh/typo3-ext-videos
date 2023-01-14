<?php

use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Videos\Resource\Rendering\VideoTagRenderer;

defined('TYPO3') or die();


$rendererRegistry = GeneralUtility::makeInstance(RendererRegistry::class);
$rendererRegistry->registerRendererClass(VideoTagRenderer::class);


