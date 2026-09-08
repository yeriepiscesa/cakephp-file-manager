<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\FileManager\Model\Entity\Category> $categories
 */

$this->element('Uikit.page_header', [
    'title'   => __('File Categories'),
    'actions' => [
        [
            'label' => __('New Category'),
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
                <th><?= __('Parent') ?></th>
                <th><?= $this->Sort->column('sort_order', __('Order')) ?></th>
                <th><?= $this->Sort->column('is_active', __('Active')) ?></th>
                <th><?= $this->Sort->column('created', __('Created')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!count($categories)): ?>
            <tr>
                <td colspan="6" class="uk-text-center uk-text-muted uk-text-bold">
                    <?= __('No categories found.') ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td class="uk-table-shrink uk-text-nowrap">
                    <?= $this->element('Uikit.list_action_buttons', ['entity' => $category]) ?>
                </td>
                <td><?= h($category->name) ?></td>
                <td><?= $category->parent_category ? h($category->parent_category->name) : '—' ?></td>
                <td><?= h($category->sort_order) ?></td>
                <td>
                    <?= $category->is_active
                        ? '<span class="uk-text-success" uk-icon="icon: check"></span>'
                        : '<span class="uk-text-muted" uk-icon="icon: close"></span>' ?>
                </td>
                <td class="uk-text-small"><?= h($category->created) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->element('Uikit.table_paginator') ?>
