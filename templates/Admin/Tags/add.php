<?php
/**
 * @var \App\View\AppView $this
 * @var \FileManager\Model\Entity\Tag $tag
 */

$this->assign('title', __('New Tag'));
?>

<div class="uk-width-1-3@m">
    <?= $this->Form->create($tag, ['class' => 'uk-form-stacked']) ?>

        <div class="uk-margin">
            <label class="uk-form-label"><?= __('Name') ?></label>
            <div class="uk-form-controls">
                <?= $this->Form->control('name', ['label' => false, 'class' => 'uk-input', 'required' => true]) ?>
            </div>
        </div>

        <?= $this->element('crud_buttons', [
            'type'    => 'form',
            'entity'  => $tag,
            'options' => ['showDelete' => false],
        ]) ?>

    <?= $this->Form->end() ?>
</div>
