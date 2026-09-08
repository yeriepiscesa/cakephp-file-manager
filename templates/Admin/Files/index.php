<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\FileManager\Model\Entity\FmFile> $files
 * @var array<string, string> $fileTypes
 * @var array<int, string> $categories
 * @var string $viewMode 'grid'|'list'
 */

$this->element('Uikit.page_header', [
    'title'   => __('File Manager'),
    'actions' => [
        [
            'label' => __('Upload File'),
            'url'   => ['action' => 'add'],
            'class' => 'uk-button uk-button-primary',
        ],
    ],
]);
?>

<!-- Filters & View Toggle -->
<div class="uk-margin-bottom">
    <?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query']]) ?>
    <div class="uk-flex uk-flex-wrap uk-flex-middle uk-grid-small" uk-grid>

        <div class="uk-width-auto">
            <?= $this->Form->control('type', [
                'type'    => 'select',
                'options' => ['' => __('All Types')] + $fileTypes,
                'label'   => false,
                'class'   => 'uk-select',
                'onchange' => 'this.form.submit()',
            ]) ?>
        </div>

        <div class="uk-width-auto">
            <?= $this->Form->control('category_id', [
                'type'    => 'select',
                'options' => ['' => __('All Categories')] + $categories,
                'label'   => false,
                'class'   => 'uk-select',
                'onchange' => 'this.form.submit()',
            ]) ?>
        </div>

        <div class="uk-width-auto">
            <?= $this->Form->control('search', [
                'type'        => 'text',
                'placeholder' => __('Search files…'),
                'label'       => false,
                'class'       => 'uk-input',
                'style'       => 'width: 220px;',
            ]) ?>
        </div>

        <div class="uk-width-auto">
            <?= $this->Form->submit(__('Filter'), ['class' => 'uk-button uk-button-default']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<!-- View Mode Toggle (Alpine.js) -->
<div x-data="{ viewMode: '<?= h($viewMode) ?>' }" class="uk-margin-bottom">

    <div class="uk-flex uk-flex-between uk-flex-middle">
        <div class="uk-text-muted uk-text-small">
            <?= __('Showing {0} files', count($files)) ?>
        </div>
        <div class="uk-button-group">
            <button @click="viewMode = 'grid'"
                    :class="{ 'uk-button-primary': viewMode === 'grid', 'uk-button-default': viewMode !== 'grid' }"
                    class="uk-button uk-button-small" title="<?= __('Grid View') ?>" uk-tooltip>
                <span uk-icon="icon: grid; ratio: 0.8"></span>
            </button>
            <button @click="viewMode = 'list'"
                    :class="{ 'uk-button-primary': viewMode === 'list', 'uk-button-default': viewMode !== 'list' }"
                    class="uk-button uk-button-small" title="<?= __('List View') ?>" uk-tooltip>
                <span uk-icon="icon: list; ratio: 0.8"></span>
            </button>
        </div>
    </div>

    <!-- ── GRID VIEW ───────────────────────────────────────────────── -->
    <div x-show="viewMode === 'grid'" class="uk-margin-top">
        <?php if (!count($files)): ?>
            <div class="uk-text-center uk-text-muted uk-padding">
                <?= __('No files found.') ?>
            </div>
        <?php else: ?>
        <div class="uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-4@l" uk-grid>
            <?php foreach ($files as $file): ?>
            <div>
                <div class="uk-card uk-card-default uk-card-hover">
                    <!-- Thumbnail -->
                    <div class="uk-card-media-top uk-text-center uk-background-muted" style="height: 140px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <?php if ($file->type === 'image'): ?>
                            <img src="<?= $this->Url->build($file->getPublicUrlParams()) ?>"
                                 alt="<?= h($file->alt_text ?: $file->title) ?>"
                                 style="max-height: 140px; max-width: 100%; object-fit: cover;">
                        <?php elseif ($file->type === 'video'): ?>
                            <span uk-icon="icon: video-camera; ratio: 3" class="uk-text-muted"></span>
                        <?php else: ?>
                            <span uk-icon="icon: file-text; ratio: 3" class="uk-text-muted"></span>
                        <?php endif; ?>
                    </div>

                    <div class="uk-card-body uk-padding-small">
                        <p class="uk-card-title uk-text-small uk-text-truncate uk-margin-remove" title="<?= h($file->title) ?>">
                            <?= h($file->title) ?>
                        </p>
                        <p class="uk-text-meta uk-text-xsmall uk-margin-remove">
                            <?= h(strtoupper($file->extension ?? $file->type)) ?> &middot; <?= $file->getFormattedSize() ?>
                        </p>
                    </div>

                    <div class="uk-card-footer uk-padding-small uk-flex uk-flex-right uk-gap">
                        <?= $this->Html->link(
                            '<span uk-icon="icon: eye; ratio: 0.8"></span>',
                            ['action' => 'view', $file->id, 'plugin' => 'FileManager', 'prefix' => 'Admin'],
                            ['escape' => false, 'class' => 'uk-icon-link', 'title' => __('View'), 'uk-tooltip' => '']
                        ) ?>
                        <?= $this->Html->link(
                            '<span uk-icon="icon: file-edit; ratio: 0.8"></span>',
                            ['action' => 'edit', $file->id, 'plugin' => 'FileManager', 'prefix' => 'Admin'],
                            ['escape' => false, 'class' => 'uk-icon-link', 'title' => __('Edit'), 'uk-tooltip' => '']
                        ) ?>
                        <a href="#" class="uk-icon-link" uk-icon="icon: trash; ratio: 0.8"
                           title="<?= __('Delete') ?>" uk-tooltip
                           onclick="UIkit.modal.confirm('<?= __('Delete this file?') ?>').then(function() {
                               document.getElementById('del-<?= $file->id ?>').submit();
                           }); return false;"></a>
                        <?= $this->Form->create(null, [
                            'url'   => ['action' => 'delete', $file->id, 'plugin' => 'FileManager', 'prefix' => 'Admin'],
                            'id'    => 'del-' . $file->id,
                            'style' => 'display:none',
                        ]) ?>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── LIST VIEW ───────────────────────────────────────────────── -->
    <div x-show="viewMode === 'list'" class="uk-margin-top">
        <div class="uk-overflow-auto">
            <table class="uk-table uk-table-small uk-table-striped uk-table-hover">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th><?= $this->Sort->column('title', __('Title')) ?></th>
                        <th><?= $this->Sort->column('type', __('Type')) ?></th>
                        <th><?= __('Category') ?></th>
                        <th><?= __('Tags') ?></th>
                        <th><?= $this->Sort->column('size', __('Size')) ?></th>
                        <th><?= $this->Sort->column('visibility', __('Visibility')) ?></th>
                        <th><?= $this->Sort->column('created', __('Uploaded')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!count($files)): ?>
                    <tr>
                        <td colspan="8" class="uk-text-center uk-text-muted uk-text-bold">
                            <?= __('No files found.') ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td class="uk-table-shrink uk-text-nowrap">
                            <?= $this->element('Uikit.list_action_buttons', ['entity' => $file]) ?>
                        </td>
                        <td class="uk-text-nowrap">
                            <?= $this->Html->link(h($file->title), ['action' => 'view', $file->id, 'plugin' => 'FileManager', 'prefix' => 'Admin']) ?>
                        </td>
                        <td>
                            <span class="uk-label uk-label-<?= $file->type === 'image' ? 'success' : ($file->type === 'video' ? 'warning' : '') ?>">
                                <?= h(ucfirst($file->type)) ?>
                            </span>
                        </td>
                        <td><?= $file->category ? h($file->category->name) : '<span class="uk-text-muted">—</span>' ?></td>
                        <td>
                            <?php foreach ($file->tags as $tag): ?>
                                <span class="uk-badge"><?= h($tag->name) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="uk-text-nowrap"><?= $file->getFormattedSize() ?></td>
                        <td>
                            <?php if ($file->visibility === 'public'): ?>
                                <span class="uk-text-success" uk-icon="icon: world; ratio: 0.8" title="<?= __('Public') ?>" uk-tooltip></span>
                            <?php else: ?>
                                <span class="uk-text-danger" uk-icon="icon: lock; ratio: 0.8" title="<?= __('Private') ?>" uk-tooltip></span>
                            <?php endif; ?>
                        </td>
                        <td class="uk-text-nowrap uk-text-small"><?= h($file->created) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /x-data -->

<?= $this->element('Uikit.table_paginator') ?>
