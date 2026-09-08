<?php
/**
 * @var \App\View\AppView $this
 * @var \FileManager\Model\Entity\FmFile $file
 */

$this->assign('title', h($file->title));
?>

<div class="uk-grid-medium" uk-grid>

    <!-- Preview -->
    <div class="uk-width-2-3@m">
        <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
            <div class="uk-text-center uk-padding">
                <?php if ($file->type === 'image'): ?>
                    <img src="<?= $this->Url->build($file->getPublicUrlParams()) ?>"
                         alt="<?= h($file->alt_text ?: $file->title) ?>"
                         style="max-width: 100%; max-height: 400px; object-fit: contain;">
                <?php elseif ($file->type === 'video'): ?>
                    <video controls style="max-width: 100%; max-height: 400px;">
                        <source src="<?= $this->Url->build($file->getPublicUrlParams()) ?>"
                                type="<?= h($file->mime_type ?? 'video/mp4') ?>">
                    </video>
                <?php else: ?>
                    <span uk-icon="icon: file-text; ratio: 4" class="uk-text-muted"></span>
                    <p class="uk-text-meta"><?= h($file->filename) ?></p>
                    <?= $this->Html->link(
                        '<span uk-icon="icon: download"></span> ' . __('Download'),
                        $file->getPublicUrlParams(),
                        ['class' => 'uk-button uk-button-primary uk-margin-top', 'escape' => false]
                    ) ?>
                <?php endif; ?>
            </div>

            <?php if ($file->caption): ?>
            <div class="uk-text-center uk-text-meta uk-margin-small-top">
                <?= h($file->caption) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Metadata -->
        <?php if (!empty($file->fm_file_metadata)): ?>
        <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
            <h3 class="uk-card-title"><?= __('Additional Metadata') ?></h3>
            <dl class="uk-description-list uk-description-list-divider">
                <?php foreach ($file->fm_file_metadata as $meta): ?>
                <dt><?= h($meta->key) ?></dt>
                <dd><?= h($meta->value) ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>
        <?php endif; ?>

        <!-- Shares -->
        <?php if (!empty($file->fm_file_shares)): ?>
        <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
            <h3 class="uk-card-title"><?= __('Sharing') ?></h3>
            <table class="uk-table uk-table-small uk-table-divider">
                <thead>
                    <tr>
                        <th><?= __('Type') ?></th>
                        <th><?= __('Reference') ?></th>
                        <th><?= __('Download') ?></th>
                        <th><?= __('Expires') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($file->fm_file_shares as $share): ?>
                    <tr>
                        <td><?= h(ucfirst($share->share_type)) ?></td>
                        <td><?= h($share->reference_id ?? '—') ?></td>
                        <td><?= $share->can_download ? '<span class="uk-text-success" uk-icon="icon: check"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>' ?></td>
                        <td><?= $share->expires_at ? h($share->expires_at) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>

    <!-- Sidebar: info -->
    <div class="uk-width-1-3@m">

        <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
            <h3 class="uk-card-title"><?= __('File Info') ?></h3>
            <dl class="uk-description-list uk-description-list-divider uk-text-small">
                <dt><?= __('Type') ?></dt>
                <dd><span class="uk-label"><?= h(ucfirst($file->type)) ?></span></dd>
                <dt><?= __('Filename') ?></dt>
                <dd><?= h($file->filename) ?></dd>
                <dt><?= __('Extension') ?></dt>
                <dd><?= h(strtoupper($file->extension ?? '—')) ?></dd>
                <dt><?= __('MIME') ?></dt>
                <dd><?= h($file->mime_type ?? '—') ?></dd>
                <dt><?= __('Size') ?></dt>
                <dd><?= $file->getFormattedSize() ?></dd>
                <?php if ($file->width && $file->height): ?>
                <dt><?= __('Dimensions') ?></dt>
                <dd><?= h($file->width) ?> × <?= h($file->height) ?> px</dd>
                <?php endif; ?>
                <?php if ($file->duration): ?>
                <dt><?= __('Duration') ?></dt>
                <dd><?= h($file->duration) ?>s</dd>
                <?php endif; ?>
                <dt><?= __('Disk') ?></dt>
                <dd><?= h($file->disk) ?></dd>
                <dt><?= __('Visibility') ?></dt>
                <dd>
                    <?php if ($file->visibility === 'public'): ?>
                        <span class="uk-text-success"><?= __('Public') ?></span>
                    <?php else: ?>
                        <span class="uk-text-danger"><?= __('Private') ?></span>
                    <?php endif; ?>
                </dd>
                <dt><?= __('Category') ?></dt>
                <dd><?= $file->category ? h($file->category->name) : '—' ?></dd>
                <dt><?= __('Tags') ?></dt>
                <dd>
                    <?php if (!empty($file->tags)): ?>
                        <?php foreach ($file->tags as $tag): ?>
                            <span class="uk-badge"><?= h($tag->name) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </dd>
                <dt><?= __('Uploaded') ?></dt>
                <dd><?= h($file->created) ?></dd>
                <dt><?= __('Modified') ?></dt>
                <dd><?= h($file->modified) ?></dd>
                <dt><?= __('UUID') ?></dt>
                <dd class="uk-text-break uk-text-xsmall"><?= h($file->id) ?></dd>
            </dl>
        </div>

        <div class="uk-card uk-card-default uk-card-body">
            <h3 class="uk-card-title"><?= __('Public URL') ?></h3>
            <input type="text" readonly
                   class="uk-input uk-form-small"
                   value="<?= h($this->Url->build($file->getPublicUrlParams(), ['fullBase' => true])) ?>"
                   onclick="this.select()">
        </div>

        <div class="uk-flex uk-flex-column uk-gap uk-margin-top">
            <?= $this->Html->link(
                '<span uk-icon="icon: file-edit"></span> ' . __('Edit'),
                ['action' => 'edit', $file->id, 'plugin' => 'FileManager', 'prefix' => 'Admin'],
                ['class' => 'uk-button uk-button-primary uk-width-1-1', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<span uk-icon="icon: list"></span> ' . __('Back to List'),
                ['action' => 'index', 'plugin' => 'FileManager', 'prefix' => 'Admin'],
                ['class' => 'uk-button uk-button-default uk-width-1-1', 'escape' => false]
            ) ?>
        </div>
    </div>

</div>
