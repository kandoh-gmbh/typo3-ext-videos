<?php

declare(strict_types=1);

/*
 * This file is part of the "videos" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Videos\Backend\Form\Container;

use TYPO3\CMS\Backend\Form\Event\ModifyFileReferenceControlsEvent;
use TYPO3\CMS\Backend\Form\Event\ModifyFileReferenceEnabledControlsEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class FileReferenceContainer extends \TYPO3\CMS\Backend\Form\Container\FileReferenceContainer {


    protected function renderFileReferenceHeaderControl(): string
    {
        $controls = [];
        $databaseRow = $this->data['databaseRow'];
        $databaseRow += [
            'uid' => 0,
        ];
        $parentConfig = $this->data['inlineParentConfig'];
        $languageService = $this->getLanguageService();
        $backendUser = $this->getBackendUserAuthentication();
        $isNewItem = str_starts_with((string)$databaseRow['uid'], 'NEW');
        $fileReferenceTableTca = $GLOBALS['TCA']['sys_file_reference'];
        $calcPerms = new Permission(
            $backendUser->calcPerms(BackendUtility::readPageAccess(
                (int)($this->data['parentPageRow']['uid'] ?? 0),
                $backendUser->getPagePermsClause(Permission::PAGE_SHOW)
            ))
        );
        $event = $this->eventDispatcher->dispatch(
            new ModifyFileReferenceEnabledControlsEvent($this->data, $databaseRow)
        );
        if ($this->data['isInlineDefaultLanguageRecordInLocalizedParentContext']) {
            $controls['localize'] = $this->iconFactory
                ->getIcon('actions-edit-localize-status-low', Icon::SIZE_SMALL)
                ->setTitle($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_misc.xlf:localize.isLocalizable'))
                ->render();
        }
        if ($event->isControlEnabled('info')) {
            if ($isNewItem) {
                $controls['info'] = '
                    <span class="btn btn-default disabled">
                        ' . $this->iconFactory->getIcon('empty-empty', Icon::SIZE_SMALL)->render() . '
                    </span>';
            } else {
                $controls['info'] = '
                    <button type="button" class="btn btn-default" data-action="infowindow" data-info-table="' . htmlspecialchars('_FILE') . '" data-info-uid="' . (int)$databaseRow['uid_local'][0]['uid'] . '" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:showInfo')) . '">
                        ' . $this->iconFactory->getIcon('actions-document-info', Icon::SIZE_SMALL)->render() . '
                    </button>';
            }
        }
        // If the table is NOT a read-only table, then show these links:
        if (!($parentConfig['readOnly'] ?? false)
            && !($fileReferenceTableTca['ctrl']['readOnly'] ?? false)
            && !($this->data['isInlineDefaultLanguageRecordInLocalizedParentContext'] ?? false)
        ) {
            if ($event->isControlEnabled('sort')) {
                $icon = 'actions-move-up';
                $class = '';
                if ((int)$parentConfig['inline']['first'] === (int)$databaseRow['uid']) {
                    $class = ' disabled';
                    $icon = 'empty-empty';
                }
                $controls['sort.up'] = '
                    <button type="button" class="btn btn-default' . $class . '" data-action="sort" data-direction="up" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:moveUp')) . '">
                        ' . $this->iconFactory->getIcon($icon, Icon::SIZE_SMALL)->render() . '
                    </button>';

                $icon = 'actions-move-down';
                $class = '';
                if ((int)$parentConfig['inline']['last'] === (int)$databaseRow['uid']) {
                    $class = ' disabled';
                    $icon = 'empty-empty';
                }
                $controls['sort.down'] = '
                    <button type="button" class="btn btn-default' . $class . '" data-action="sort" data-direction="down" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:moveDown')) . '">
                        ' . $this->iconFactory->getIcon($icon, Icon::SIZE_SMALL)->render() . '
                    </button>';
            }
            if (!$isNewItem
                && ($languageField = ($GLOBALS['TCA']['sys_file_metadata']['ctrl']['languageField'] ?? false))
                && $backendUser->check('tables_modify', 'sys_file_metadata')
                && $event->isControlEnabled('edit')
            ) {
                $languageId = (int)(is_array($databaseRow[$languageField] ?? null)
                    ? ($databaseRow[$languageField][0] ?? 0)
                    : ($databaseRow[$languageField] ?? 0));
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('sys_file_metadata');
                $metadataRecord = $queryBuilder
                    ->select('uid')
                    ->from('sys_file_metadata')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'file',
                            $queryBuilder->createNamedParameter((int)$databaseRow['uid_local'][0]['uid'], Connection::PARAM_INT)
                        ),
                        $queryBuilder->expr()->eq(
                            $languageField,
                            $queryBuilder->createNamedParameter($languageId, Connection::PARAM_INT)
                        )
                    )
                    ->setMaxResults(1)
                    ->executeQuery()
                    ->fetchAssociative();
                if (!empty($metadataRecord)) {
                    $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
                    $url = (string)$uriBuilder->buildUriFromRoute('record_edit', [
                        'edit[sys_file_metadata][' . (int)$metadataRecord['uid'] . ']' => 'edit',
                        'returnUrl' => $this->data['returnUrl'],
                    ]);
                    $controls['edit'] = '
                        <a class="btn btn-default" href="' . htmlspecialchars($url) . '" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.editMetadata')) . '">
                            ' . $this->iconFactory->getIcon('actions-open', Icon::SIZE_SMALL)->render() . '
                        </a>';
                }
            }
            // PATCH
            if ($event->isControlEnabled('delete') && ($calcPerms->editContentPermissionIsGranted() || ($this->data['tableName'] === 'sys_file_reference' && $this->data['inlineParentTableName'] === 'sys_file_metadata'))) {
                $recordInfo = $this->data['databaseRow']['uid_local'][0]['title'] ?? $this->data['recordTitle'] ?? '';
                if ($this->getBackendUserAuthentication()->shallDisplayDebugInformation()) {
                    $recordInfo .= ' [' . $this->data['tableName'] . ':' . $this->data['vanillaUid'] . ']';
                }
                $controls['delete'] = '
                    <button type="button" class="btn btn-default t3js-editform-delete-file-reference" data-record-info="' . htmlspecialchars(trim($recordInfo)) . '" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:delete')) . '">
                        ' . $this->iconFactory->getIcon('actions-edit-delete', Icon::SIZE_SMALL)->render() . '
                    </button>';
            }
            if (($hiddenField = (string)($fileReferenceTableTca['ctrl']['enablecolumns']['disabled'] ?? '')) !== ''
                && ($fileReferenceTableTca['columns'][$hiddenField] ?? false)
                && $event->isControlEnabled('hide')
                && (
                    !($fileReferenceTableTca['columns'][$hiddenField]['exclude'] ?? false)
                    || $backendUser->check('non_exclude_fields', 'sys_file_reference' . ':' . $hiddenField)
                )
            ) {
                if ($databaseRow[$hiddenField] ?? false) {
                    $controls['hide'] = '
                        <button type="button" class="btn btn-default t3js-toggle-visibility-button" data-hidden-field="' . htmlspecialchars($hiddenField) . '" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:unHide')) . '">
                            ' . $this->iconFactory->getIcon('actions-edit-unhide', Icon::SIZE_SMALL)->render() . '
                        </button>';
                } else {
                    $controls['hide'] = '
                        <button type="button" class="btn btn-default t3js-toggle-visibility-button" data-hidden-field="' . htmlspecialchars($hiddenField) . '" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:hide')) . '">
                            ' . $this->iconFactory->getIcon('actions-edit-hide', Icon::SIZE_SMALL)->render() . '
                        </button>';
                }
            }
            if (($parentConfig['appearance']['useSortable'] ?? false) && $event->isControlEnabled('dragdrop')) {
                $controls['dragdrop'] = '
                    <span class="btn btn-default sortableHandle" data-id="' . (int)$databaseRow['uid'] . '" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.move')) . '">
                        ' . $this->iconFactory->getIcon('actions-move-move', Icon::SIZE_SMALL)->render() . '
                    </span>';
            }
        } elseif (($this->data['isInlineDefaultLanguageRecordInLocalizedParentContext'] ?? false)
            && MathUtility::canBeInterpretedAsInteger($this->data['inlineParentUid'])
            && $event->isControlEnabled('localize')
        ) {
            $controls['localize'] = '
                <button type="button" class="btn btn-default t3js-synchronizelocalize-button" data-type="' . htmlspecialchars((string)$databaseRow['uid']) . '" title="' . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_misc.xlf:localize')) . '">
                    ' . $this->iconFactory->getIcon('actions-document-localize', Icon::SIZE_SMALL)->render() . '
                </button>';
        }
        if ($lockInfo = BackendUtility::isRecordLocked('sys_file_reference', $databaseRow['uid'])) {
            $controls['locked'] = '
				<button type="button" class="btn btn-default" title="' . htmlspecialchars($lockInfo['msg']) . '">
					' . $this->iconFactory->getIcon('status-user-backend', Icon::SIZE_SMALL, 'overlay-edit')->render() . '
				</button>';
        }

        // Get modified controls. This means their markup was modified, new controls were added or controls got removed.
        $controls = $this->eventDispatcher->dispatch(
            new ModifyFileReferenceControlsEvent($controls, $this->data, $databaseRow)
        )->getControls();

        $out = '';
        if (($controls['edit'] ?? false) || ($controls['hide'] ?? false) || ($controls['delete'] ?? false)) {
            $out .= '
                <div class="btn-group btn-group-sm" role="group">
                    ' . ($controls['edit'] ?? '') . ($controls['hide'] ?? '') . ($controls['delete'] ?? '') . '
                </div>';
            unset($controls['edit'], $controls['hide'], $controls['delete']);
        }
        if (($controls['info'] ?? false) || ($controls['new'] ?? false) || ($controls['sort.up'] ?? false) || ($controls['sort.down'] ?? false) || ($controls['dragdrop'] ?? false)) {
            $out .= '
                <div class="btn-group btn-group-sm" role="group">
                    ' . ($controls['info'] ?? '') . ($controls['new'] ?? '') . ($controls['sort.up'] ?? '') . ($controls['sort.down'] ?? '') . ($controls['dragdrop'] ?? '') . '
                </div>';
            unset($controls['info'], $controls['new'], $controls['sort.up'], $controls['sort.down'], $controls['dragdrop']);
        }
        if ($controls['localize'] ?? false) {
            $out .= '<div class="btn-group btn-group-sm" role="group">' . $controls['localize'] . '</div>';
            unset($controls['localize']);
        }
        if ($controls !== [] && ($remainingControls = trim(implode('', $controls))) !== '') {
            $out .= '<div class="btn-group btn-group-sm" role="group">' . $remainingControls . '</div>';
        }
        return $out;
    }



}
