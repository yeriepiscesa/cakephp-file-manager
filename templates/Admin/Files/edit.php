<?php
/**
 * @var \App\View\AppView $this
 * @var \FileManager\Model\Entity\FmFile $file
 * @var array<string, string> $fileTypes
 * @var array<string, string> $visibilityOptions
 * @var array<int, string> $categories
 * @var array<int, string> $tags
 */

$this->assign('title', __('Edit File: {0}', $file->title));
?>

<div class="uk-width-2-3@m" x-data="{ fileType: '<?= h($file->type) ?>' }">

    <?= $this->Form->create($file, ['class' => 'uk-form-stacked']) ?>

    <div class="uk-grid-medium" uk-grid>

        <!-- Left column -->
        <div class="uk-width-2-3@m">

            <!-- Current file preview -->
            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                <h3 class="uk-card-title"><?= __('Current File') ?></h3>
                <div class="uk-text-center uk-padding-small uk-background-muted">
                    <?php if ($file->type === 'image'): ?>
                        <img src="<?= $this->Url->build($file->getPublicUrlParams()) ?>"
                             alt="<?= h($file->alt_text ?: $file->title) ?>"
                             style="max-height: 200px; max-width: 100%; object-fit: contain;">
                    <?php elseif ($file->type === 'video'): ?>
                        <span uk-icon="icon: video-camera; ratio: 3" class="uk-text-muted"></span>
                    <?php else: ?>
                        <span uk-icon="icon: file-text; ratio: 3" class="uk-text-muted"></span>
                    <?php endif; ?>
                    <p class="uk-text-small uk-text-muted uk-margin-small-top">
                        <?= h($file->filename) ?> &middot; <?= $file->getFormattedSize() ?>
                    </p>
                </div>
            </div>

            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                <h3 class="uk-card-title"><?= __('Details') ?></h3>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('Title') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('title', ['label' => false, 'class' => 'uk-input']) ?>
                    </div>
                </div>

                <div class="uk-margin" x-show="fileType === 'image'">
                    <label class="uk-form-label"><?= __('Alt Text') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('alt_text', ['label' => false, 'class' => 'uk-input']) ?>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('Caption') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('caption', ['label' => false, 'class' => 'uk-textarea', 'rows' => 3]) ?>
                    </div>
                </div>
            </div>

            <!-- Additional Metadata -->
            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom"
                 x-data="metadataEditor(<?= json_encode(array_map(fn($m) => ['key' => $m->key, 'value' => $m->value], $file->fm_file_metadata ?? [])) ?>)">
                <h3 class="uk-card-title"><?= __('Additional Metadata') ?></h3>

                <template x-for="(item, idx) in items" :key="idx">
                    <div class="uk-flex uk-flex-middle uk-grid-small uk-margin-small" uk-grid>
                        <div class="uk-width-1-3">
                            <input type="text"
                                   :name="'fm_file_metadata[' + idx + '][key]'"
                                   x-model="item.key"
                                   placeholder="<?= __('Key') ?>"
                                   class="uk-input uk-form-small">
                        </div>
                        <div class="uk-width-expand">
                            <input type="text"
                                   :name="'fm_file_metadata[' + idx + '][value]'"
                                   x-model="item.value"
                                   placeholder="<?= __('Value') ?>"
                                   class="uk-input uk-form-small">
                        </div>
                        <div class="uk-width-auto">
                            <button type="button" @click="remove(idx)" class="uk-button uk-button-danger uk-button-small" uk-icon="icon: trash; ratio: 0.8"></button>
                        </div>
                    </div>
                </template>

                <button type="button" @click="add()" class="uk-button uk-button-default uk-button-small uk-margin-top">
                    <span uk-icon="icon: plus; ratio: 0.8"></span> <?= __('Add Field') ?>
                </button>
            </div>

            <!-- Sharing -->
            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom"
                 x-data="sharingEditor(<?= json_encode(array_map(fn($s) => ['share_type' => $s->share_type, 'reference_id' => $s->reference_id, 'can_download' => $s->can_download], $file->fm_file_shares ?? [])) ?>)">
                <h3 class="uk-card-title"><?= __('Sharing') ?></h3>

                <template x-for="(share, idx) in shares" :key="idx">
                    <div class="uk-flex uk-flex-middle uk-grid-small uk-margin-small" uk-grid>
                        <div class="uk-width-1-4">
                            <select :name="'fm_file_shares[' + idx + '][share_type]'"
                                    x-model="share.share_type"
                                    class="uk-select uk-form-small">
                                <option value="all"><?= __('Everyone') ?></option>
                                <option value="user"><?= __('User') ?></option>
                                <option value="group"><?= __('Group') ?></option>
                                <option value="tenant"><?= __('Tenant') ?></option>
                            </select>
                        </div>
                        <div class="uk-width-expand">
                            <input type="text"
                                   :name="'fm_file_shares[' + idx + '][reference_id]'"
                                   x-model="share.reference_id"
                                   placeholder="<?= __('UUID / ID (leave blank for Everyone)') ?>"
                                   class="uk-input uk-form-small"
                                   :disabled="share.share_type === 'all'">
                        </div>
                        <div class="uk-width-auto">
                            <label class="uk-text-small">
                                <input type="checkbox"
                                       :name="'fm_file_shares[' + idx + '][can_download]'"
                                       x-model="share.can_download"
                                       value="1"
                                       class="uk-checkbox">
                                <?= __('Download') ?>
                            </label>
                        </div>
                        <div class="uk-width-auto">
                            <button type="button" @click="removeShare(idx)" class="uk-button uk-button-danger uk-button-small" uk-icon="icon: trash; ratio: 0.8"></button>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addShare()" class="uk-button uk-button-default uk-button-small uk-margin-top">
                    <span uk-icon="icon: plus; ratio: 0.8"></span> <?= __('Add Share') ?>
                </button>
            </div>

        </div>

        <!-- Right column -->
        <div class="uk-width-1-3@m">

            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                <h3 class="uk-card-title"><?= __('Organisation') ?></h3>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('Type') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('type', [
                            'type'    => 'select',
                            'options' => $fileTypes,
                            'label'   => false,
                            'class'   => 'uk-select',
                            'x-model' => 'fileType',
                        ]) ?>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('Category') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('category_id', [
                            'type'    => 'select',
                            'options' => ['' => __('— None —')] + $categories,
                            'label'   => false,
                            'class'   => 'uk-select',
                        ]) ?>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('Tags') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('tags._ids', [
                            'type'     => 'select',
                            'options'  => $tags,
                            'label'    => false,
                            'class'    => 'uk-select',
                            'multiple' => 'checkbox',
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                <h3 class="uk-card-title"><?= __('Access') ?></h3>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('Visibility') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('visibility', [
                            'type'    => 'select',
                            'options' => $visibilityOptions,
                            'label'   => false,
                            'class'   => 'uk-select',
                        ]) ?>
                    </div>
                </div>

                <div class="uk-margin">
                    <?= $this->Form->control('is_active', [
                        'type'  => 'checkbox',
                        'label' => __('Active'),
                        'class' => 'uk-checkbox',
                    ]) ?>
                </div>
            </div>

            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                <h3 class="uk-card-title"><?= __('File Info') ?></h3>
                <dl class="uk-description-list uk-text-small">
                    <dt><?= __('ID') ?></dt>
                    <dd class="uk-text-break"><?= h($file->id) ?></dd>
                    <dt><?= __('Filename') ?></dt>
                    <dd><?= h($file->filename) ?></dd>
                    <dt><?= __('MIME') ?></dt>
                    <dd><?= h($file->mime_type ?? '—') ?></dd>
                    <dt><?= __('Disk') ?></dt>
                    <dd><?= h($file->disk) ?></dd>
                    <dt><?= __('Uploaded') ?></dt>
                    <dd><?= h($file->created) ?></dd>
                </dl>
                <a href="<?= $this->Url->build($file->getPublicUrlParams()) ?>"
                   target="_blank" class="uk-button uk-button-default uk-button-small uk-width-1-1">
                    <span uk-icon="icon: link; ratio: 0.8"></span> <?= __('View Public URL') ?>
                </a>
            </div>

        </div>
    </div>

    <?= $this->element('crud_buttons', [
        'type'   => 'form',
        'entity' => $file,
        'options' => ['showDelete' => true],
    ]) ?>

    <?= $this->Form->end() ?>

</div>

<script>
function metadataEditor(initial) {
    return {
        items: initial.length ? initial : [],
        add()   { this.items.push({ key: '', value: '' }); },
        remove(i) { this.items.splice(i, 1); },
    };
}

function sharingEditor(initial) {
    return {
        shares: initial.length ? initial : [],
        addShare()      { this.shares.push({ share_type: 'all', reference_id: '', can_download: true }); },
        removeShare(i)  { this.shares.splice(i, 1); },
    };
}
</script>
