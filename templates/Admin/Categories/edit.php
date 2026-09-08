<?php
/**
 * @var \App\View\AppView $this
 * @var \FileManager\Model\Entity\Category $category
 * @var array<int, string> $parentCategories
 */

$this->assign('title', __('Edit Category: {0}', $category->name));
?>

<div class="uk-width-1-2@m">
    <?= $this->Form->create($category, ['class' => 'uk-form-stacked']) ?>

        <div class="uk-margin">
            <label class="uk-form-label"><?= __('Name') ?></label>
            <div class="uk-form-controls">
                <?= $this->Form->control('name', ['label' => false, 'class' => 'uk-input', 'required' => true]) ?>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label"><?= __('Parent Category') ?></label>
            <div class="uk-form-controls">
                <?= $this->Form->control('parent_id', [
                    'type'    => 'select',
                    'options' => ['' => __('— None (top level) —')] + $parentCategories,
                    'label'   => false,
                    'class'   => 'uk-select',
                ]) ?>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label"><?= __('Description') ?></label>
            <div class="uk-form-controls">
                <?= $this->Form->control('description', ['label' => false, 'class' => 'uk-textarea', 'rows' => 3]) ?>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label"><?= __('Sort Order') ?></label>
            <div class="uk-form-controls">
                <?= $this->Form->control('sort_order', ['label' => false, 'class' => 'uk-input', 'type' => 'number', 'min' => 0]) ?>
            </div>
        </div>

        <div class="uk-margin">
            <?= $this->Form->control('is_active', [
                'type'  => 'checkbox',
                'label' => __('Active'),
                'class' => 'uk-checkbox',
            ]) ?>
        </div>

        <?= $this->element('crud_buttons', [
            'type'    => 'form',
            'entity'  => $category,
            'options' => ['showDelete' => true],
        ]) ?>

    <?= $this->Form->end() ?>
</div>
