<?php
declare(strict_types=1);

/*
 * This file is part of the "videos" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Videos\Resource\Rendering;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class VideoTagRenderer
 */
class VideoTagRenderer extends \TYPO3\CMS\Core\Resource\Rendering\VideoTagRenderer
{
    /**
     * Mime types that can be used in the HTML Video tag
     *
     * @var array
     * @phpstan-var string[]
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
    #[\Override]
    public function getPriority()
    {
        return 100;
    }


    /**
     * Render for given File(Reference) HTML output
     *
     * @param FileInterface $file
     * @param int|string $width TYPO3 known format; examples: 220, 200m or 200c
     * @param int|string $height TYPO3 known format; examples: 220, 200m or 200c
     * @param array $options controls = TRUE/FALSE (default TRUE), autoplay = TRUE/FALSE (default FALSE), loop = TRUE/FALSE (default FALSE)
     * @return string
     */
    #[\Override]
    public function render(FileInterface $file, $width, $height, array $options = [])
    {
        if (
            ($request = $GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($request)->isBackend()
        ) {
            return parent::render($file, $width, $height, $options);
        }

        $attributes = [];

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

        $showControlsGlobal = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('videos', 'controls');

        $showControls = ($showControlsGlobal === '1');
        if (isset($options['controls'])) {
            $showControls = (int)$options['controls'] === 1;
        }
        if ($showControls) {
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

        $poster = $options['poster'] ?? false;
        if (! is_string($poster)
            && $file instanceof FileReference
            && $file->getOriginalFile()->getProperty('poster')
        ) {
            $fileMetadata = $file->getOriginalFile()->getMetaData();
            $fileObjects = $this->getFileRepository()->findByRelation(
                'sys_file_metadata',
                'poster',
                (int)$fileMetadata['uid']
            );

            $posterFile = $fileObjects[0] ?? null;
            if ($posterFile instanceof FileReference) {
                $poster = $posterFile->getPublicUrl();
            }
        }

        if (is_string($poster) && ! empty($poster)) {
            $attributes['poster'] = 'poster="'. $options['poster'] .'"';
        }


        $tracks = '';
        if (
            $file instanceof FileReference
            && $file->getOriginalFile()->getProperty('tracks')
        ) {
            $site = $this->getSiteFinder()->getSiteByPageId($file->getProperty('pid') ?: $GLOBALS['TSFE']->id);
            $defaultLanguage = $site->getDefaultLanguage();

            $fileMetadata = $file->getOriginalFile()->getMetaData();
            $fileObjects = $this->getFileRepository()->findByRelation(
                'sys_file_metadata',
                'tracks',
                (int)$fileMetadata['uid']
            );

            foreach ($fileObjects as $fileObject) {
                $trackLanguage = (int)$fileObject->getProperty('track_language');
                $trackType = (string)$fileObject->getProperty('track_type') ?: 'subtitles';
                $languageTitle = LocalizationUtility::translate('language.default', 'videos');

                $isoCode = $defaultLanguage->getLocale()->getLanguageCode();

                if ($trackLanguage > -1) {
                    try {
                        $language = $site->getLanguageById($trackLanguage);
                        $languageTitle = $language->getTitle();
                        $isoCode = $language->getLocale()->getLanguageCode();
                    } catch (\InvalidArgumentException) {
                        // silent fail
                    }
                }

                if (! empty($fileObject->getPublicUrl())) {
                    $tracks .= sprintf(
                        '<track label="%s" kind="%s" srclang="%s" src="%s">',
                        $languageTitle,
                        $trackType,
                        $isoCode,
                        (string)$fileObject->getPublicUrl()
                    );
                }
            }
        }

        /* TODO: make it configurable */
        $attributes[] = 'oncontextmenu="return false;"';

        foreach (['class', 'dir', 'id', 'lang', 'style', 'title', 'accesskey', 'tabindex', 'onclick', 'controlsList', 'preload'] as $key) {
            if (!empty($options[$key])) {
                $attributes[] = $key . '="' . htmlspecialchars($options[$key]) . '"';
            }
        }

        if (strpos($options['class'] ?? '', 'no-videojs') === false) {
            $attributes[] = 'data-setup="{}"';
        }

        // Clean up duplicate attributes
        $attributes = array_unique($attributes);

        return sprintf(
            '<video%s><source src="%s" type="%s">%s</video>',
            empty($attributes) ? '' : ' ' . implode(' ', $attributes),
            htmlspecialchars((string)$file->getPublicUrl()),
            $file->getMimeType(),
            $tracks
        );
    }

    protected function getFileRepository(): FileRepository
    {
        return GeneralUtility::makeInstance(FileRepository::class);
    }

    protected function getSiteFinder(): SiteFinder
    {
        return GeneralUtility::makeInstance(SiteFinder::class);
    }
}
