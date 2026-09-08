<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\FileManager\Model\Entity\Tag> $tags
 */

$this->element('Uikit.page_header', [
    'title'   => __('File Tags'),
    'actions' => [
        [
            'label' => __('New Tag'),
            'url'   => ['action' => 'add'],
            'class' => 'uk-button uk-button-primary',
        ],
    ],
]);
?>

<?= $this->element('Uikit.table_controls') ?>

<div class="uk-overflow-auto">
    <table class="uk-table uk-table-small uk-table-striped uk-table-hover">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th><?= $this->Sort->column('name', __('Name')) ?></th>
                <th><?= $this->Sort->column('slug', __('Slug')) ?></th>
                <th><?= $this->Sort->column('created', __('Created')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!count($tags)): ?>
            <tr>
                <td colspan="4" class="uk-text-center uk-text-muted uk-text-bold">
                    <?= __('No tags found.') ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php foreach ($tags as $tag): ?>
            <tr>
                <td class="uk-table-shrink uk-text-nowrap">
                    <?= $this->element('Uikit.list_action_buttons', ['entity' => $tag]) ?>
                </td>
                <td><span class="uk-badge"><?= h($tag->name) ?></span></td>
                <td class="uk-text-small uk-text-muted"><?= h($tag->slug) ?></td>
                <td class="uk-text-small"><?= h($tag->created) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->element('Uikit.table_paginator') ?>
