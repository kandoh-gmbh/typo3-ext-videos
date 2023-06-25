<?php
declare(strict_types=1);

/*
 * This file is part of the "videos" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Videos\Resource\Rendering;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class VideoTagRenderer
 */
class VideoTagRenderer implements FileRendererInterface
{
    /**
     * Mime types that can be used in the HTML Video tag
     *
     * @var array
     */
    protected $possibleMimeTypes = ['video/mp4', 'video/webm', 'video/ogg', 'application/ogg'];

    /**
     * Returns the priority of the renderer
     * This way it is possible to define/overrule a renderer
     * for a specific file type/context.
     * For example create a video renderer for a certain storage/driver type.
     * Should be between 1 and 100, 100 is more important than 1
     *
     * @return int
     */
    public function getPriority()
    {
        return 100;
    }

    /**
     * Check if given File(Reference) can be rendered
     *
     * @param FileInterface $file File or FileReference to render
     * @return bool
     */
    public function canRender(FileInterface $file)
    {
        return in_array($file->getMimeType(), $this->possibleMimeTypes, true);
    }

    /**
     * Render for given File(Reference) HTML output
     *
     * @param FileInterface $file
     * @param int|string $width TYPO3 known format; examples: 220, 200m or 200c
     * @param int|string $height TYPO3 known format; examples: 220, 200m or 200c
     * @param array $options controls = TRUE/FALSE (default TRUE), autoplay = TRUE/FALSE (default FALSE), loop = TRUE/FALSE (default FALSE)
     * @param bool $usedPathsRelativeToCurrentScript See $file->getPublicUrl()
     * @return string
     */
    public function render(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false)
    {
        $attributes = [];

        $showControls = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('videos', 'controls');

        // If autoplay isn't set manually check if $file is a FileReference take autoplay from there
        if ($file instanceof FileReference) {
            $autoplay = $file->getProperty('autoplay');
            if ($autoplay) {
                $attributes['autoplay'] = 'autoplay';
                $attributes['playsinline'] = 'playsinline';
            }
            $muted = $file->getProperty('muted');
            if ($muted) {
                $attributes['muted'] = 'muted';
            }
            $loop = $file->getProperty('loop');
            if ($loop) {
                $attributes['loop'] = 'loop';
            }
        }


        if ((int)$width > 0) {
            $attributes[] = 'width="' . (int)$width . '"';
        }
        if ((int)$height > 0) {
            $attributes[] = 'height="' . (int)$height . '"';
        }
        if (($options['controls'] ?? false) || $showControls === '1') {
            $attributes['controls'] = 'controls';
        }
        if ($options['autoplay'] ?? false) {
            $attributes['autoplay'] = 'autoplay';
            $attributes['playsinline'] = 'playsinline';
        }
        if (($options['muted'] ?? false) || (($attributes['autoplay'] ?? '') === 'autoplay')) {
            $attributes['muted'] = 'muted';
        }
        if ($options['loop'] ?? false) {
            $attributes['loop'] = 'loop';
        }
        if ($options['playsinline'] ?? false) {
            $attributes['playsinline'] = 'playsinline';
        }
        if ($options['poster'] ?? false) {
            $attributes['poster'] = 'poster="'.$options['poster'].'"';
        }

        if ($file instanceof FileReference) {
            $file = $file->getOriginalFile();
        }
        if ($file->getProperty('poster')) {
            /** @var FileRepository $fileRepository */
            $fileRepository = GeneralUtility::makeInstance(FileRepository::class);

            $fileObjects = $fileRepository->findByRelation('sys_file_metadata', 'poster', $file->getMetaData()['uid']);

            if (isset($fileObjects[0])) {
                /** @var FileReference $posterFile */
                $posterFile = $fileObjects[0];
                $attributes['poster'] = 'poster="' . $posterFile->getPublicUrl() . '"';
            }

        }

        /* TODO: make it configurable */
        $attributes[] = 'oncontextmenu="return false;"';

        $tracks = '';
        if ($file->getProperty('tracks')) {

            /** @var FileRepository $fileRepository */
            $fileRepository = GeneralUtility::makeInstance(FileRepository::class);

            $fileObjects = $fileRepository->findByRelation('sys_file_metadata', 'tracks', $file->getMetaData()['uid']);

            /** @var FileReference $fileObject */
            foreach ($fileObjects as $key => $fileObject) {

                $trackLanguage = $fileObject->getProperty('track_language');
                $trackType = $fileObject->getProperty('track_type');
                $languageTitle = LocalizationUtility::translate('language.default', 'videos');

                $defaultLanguage = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($GLOBALS['TSFE']->id)->getDefaultLanguage();

                $isoCode = $defaultLanguage->getTwoLetterIsoCode();

                if ($trackLanguage > -1) {
                    $language = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($GLOBALS['TSFE']->id)->getLanguageById($trackLanguage);
                    if ($language) {
                        $languageTitle = $language->getTitle();
                        $isoCode = $language->getTwoLetterIsoCode();
                    }
                }

                $tracks .= '<track label="'.$languageTitle.'" kind="'.($trackType ?: 'subtitles').'" srclang="'.$isoCode.'" src="' . $fileObject->getPublicUrl() . '">';
            }
        }


        foreach (['class', 'dir', 'id', 'lang', 'style', 'title', 'accesskey', 'tabindex', 'onclick'] as $key) {
            if (!empty($options[$key])) {
                $attributes[] = $key . '="' . htmlspecialchars($options[$key]) . '"';
            }
        }
        $attributes[] = 'data-setup="{}"';

        return sprintf(
            '<video%s><source src="%s" type="%s">%s</video>',
            empty($attributes) ? '' : ' ' . implode(' ', $attributes),
            htmlspecialchars($file->getPublicUrl($usedPathsRelativeToCurrentScript)),
            $file->getMimeType(),
            $tracks
        );
    }
}
