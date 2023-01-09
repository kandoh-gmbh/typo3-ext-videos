<?php
declare(strict_types=1);

/*
 * This file is part of the "videos" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Videos\DataProcessing;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;


class JsonPlaylistProcessor implements DataProcessorInterface
{
    /**
     * The content object renderer
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $contentObjectRenderer;

    /**
     * The processor configuration
     *
     * @var array
     */
    protected $processorConfiguration;

    /**
     * The (filtered) media files to be used in the gallery
     *
     * @var FileInterface[]
     */
    protected $fileObjects = [];

    /**
     *
     * @var array
     */
    protected $jsonData = [];

    /**
     * Process data for a gallery, for instance the CType "textmedia"
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     * @throws ContentRenderingException
     */
    public function process(
        ContentObjectRenderer $cObj,
        array                 $contentObjectConfiguration,
        array                 $processorConfiguration,
        array                 $processedData
    )
    {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        $this->contentObjectRenderer = $cObj;
        $this->processorConfiguration = $processorConfiguration;

        $filesProcessedDataKey = (string)$cObj->stdWrapValue(
            'filesProcessedDataKey',
            $processorConfiguration,
            'files'
        );
        if (isset($processedData[$filesProcessedDataKey]) && is_array($processedData[$filesProcessedDataKey])) {
            $this->fileObjects = $processedData[$filesProcessedDataKey];
        } else {
            throw new ContentRenderingException('No files found for key ' . $filesProcessedDataKey . ' in $processedData.', 1436809789);
        }


        $this->prepareJsonData();

        $targetFieldName = (string)$cObj->stdWrapValue(
            'as',
            $processorConfiguration,
            'videos'
        );

        $processedData[$targetFieldName] = json_encode($this->jsonData);

        return $processedData;
    }

    /**
     * Get configuration value from processorConfiguration
     * with when $dataArrayKey fallback to value from cObj->data array
     *
     * @param string $key
     * @param string|null $dataArrayKey
     * @return string
     */
    protected function getConfigurationValue($key, $dataArrayKey = null)
    {
        $defaultValue = '';
        if ($dataArrayKey && isset($this->contentObjectRenderer->data[$dataArrayKey])) {
            $defaultValue = $this->contentObjectRenderer->data[$dataArrayKey];
        }
        return $this->contentObjectRenderer->stdWrapValue(
            $key,
            $this->processorConfiguration,
            $defaultValue
        );
    }


    /**
     */
    protected function prepareJsonData()
    {
        /** @var FileReference $file */
        foreach ($this->fileObjects as $file) {


            $video = [
                'name' => $file->getTitle(),
                'description' => $file->getDescription(),
                'sources' => [
                    [
                        'src' => $file->getPublicUrl(),
                        'type' => $file->getMimeType()
                    ]
                ],
                'thumbnail' => [],
                'textTracks' => [],
            ];

            if ($file->getOriginalFile()->getProperty('poster')) {
                /** @var FileRepository $fileRepository */
                $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
                $fileObjects = $fileRepository->findByRelation('sys_file_metadata', 'poster', $file->getOriginalFile()->getMetaData()->get()['uid']);

                if (isset($fileObjects[0])) {
                    /** @var FileReference $posterFile */
                    $posterFile = $fileObjects[0];
                    $video['thumbnail'] = $posterFile->getPublicUrl();

                    /*
                     * $video['thumbnail'][] = [
                        'src' => $posterFile->getPublicUrl(),
                        'type' => $posterFile->getMimeType()
                    ];
                     */
                }
            }


            if ($file->getOriginalFile()->getProperty('tracks')) {

                /** @var FileRepository $fileRepository */
                $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
                $fileObjects = $fileRepository->findByRelation('sys_file_metadata', 'tracks', $file->getOriginalFile()->getMetaData()->get()['uid']);

                /** @var FileReference $fileObject */
                foreach ($fileObjects as $key => $fileObject) {

                    $trackLanguage = $fileObject->getProperty('track_language');
                    $trackType = $fileObject->getProperty('track_type');
                    $languageTitle = LocalizationUtility::translate('language.default', 'videos');
                    $isoCode = $GLOBALS['TSFE']->config['config']['sys_language_isocode_default'];
                    $default = false;

                    if ($trackType === 'chapters') $default = true;

                    if ($trackLanguage > 0) {
                        /** @var QueryBuilder $queryBuilder */
                        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');
                        $languageRecord = $queryBuilder
                            ->select('*')
                            ->from('sys_language')
                            ->where(
                                $queryBuilder->expr()->eq(
                                    'uid',
                                    $queryBuilder->createNamedParameter($trackLanguage, \PDO::PARAM_INT)
                                )
                            )
                            ->execute()
                            ->fetch();

                        if ($languageRecord) {
                            $languageTitle = $languageRecord['title'];
                            $isoCode = $languageRecord['language_isocode'];
                        }
                    }

                    $video['textTracks'][] = [
                        'label' => $languageTitle,
                        'kind' => $trackType ?: 'subtitles',
                        'language' => $isoCode,
                        'src' => $fileObject->getPublicUrl(),
                        'default' => $default,
                    ];

                }
            }

            $this->jsonData[] = $video;
        }
    }
}
