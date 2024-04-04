<?php

use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Videos\Backend\Form\Container\FileReferenceContainer;
use WapplerSystems\Videos\Resource\Rendering\VideoTagRenderer;


$rendererRegistry = GeneralUtility::makeInstance(RendererRegistry::class);
$rendererRegistry->registerRendererClass(VideoTagRenderer::class);


$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1712229824] = [
    'nodeName' => FileReferenceContainer::NODE_TYPE_IDENTIFIER,
    'priority' => '70',
    'class' => FileReferenceContainer::class,
];
