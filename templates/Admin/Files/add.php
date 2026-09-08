<?php
/**
 * @var \App\View\AppView $this
 * @var \FileManager\Model\Entity\FmFile $file
 * @var array<string, string> $fileTypes
 * @var array<string, string> $visibilityOptions
 * @var array<int, string> $categories
 * @var array<int, string> $tags
 * @var bool $isSuperAdmin
 * @var array<int, string> $tenants
 * @var array<string, string> $ownerOptions
 * @var int|null $selectedTenantId
 * @var string|null $selectedOwnerId
 */

$this->assign('title', __('Upload File'));
?>

<?php
$usersByTenantUrl = $this->Url->build([
    'prefix' => 'Admin',
    'plugin' => 'FileManager',
    'controller' => 'Files',
    'action' => 'usersByTenant',
]);
?>

<div class="uk-width-2-3@m" x-data="fileUploadForm({
    isSuperAdmin: <?= !empty($isSuperAdmin) ? 'true' : 'false' ?>,
    usersByTenantUrl: <?= json_encode($usersByTenantUrl) ?>,
    selectedTenantId: <?= json_encode($selectedTenantId ?? null) ?>,
    selectedOwnerId: <?= json_encode($selectedOwnerId ?? null) ?>
})" x-init="init()">

    <?= $this->Form->create($file, ['class' => 'uk-form-stacked', 'type' => 'file', 'enctype' => 'multipart/form-data']) ?>

    <div class="uk-grid-medium" uk-grid>

        <!-- Left column: file + basic info -->
        <div class="uk-width-2-3@m">

            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                <h3 class="uk-card-title"><?= __('File') ?></h3>

                <!-- File drop zone -->
                <div class="uk-margin">
                    <div class="uk-placeholder uk-text-center"
                         @dragover.prevent
                         @drop.prevent="handleDrop($event)">
                        <span uk-icon="icon: cloud-upload; ratio: 2"></span>
                        <p class="uk-text-muted uk-margin-small"><?= __('Drag & drop a file here or') ?></p>
                        <?= $this->Form->control('filename', [
                            'type'  => 'file',
                            'label' => false,
                            'class' => 'uk-input',
                            '@change' => 'handleFileSelect($event)',
                        ]) ?>
                    </div>
                    <!-- Preview -->
                    <div x-show="preview" class="uk-margin-top uk-text-center">
                        <img :src="preview" alt="preview" style="max-height: 200px; max-width: 100%;" x-show="isImage">
                        <p x-show="!isImage" class="uk-text-muted">
                            <span uk-icon="icon: file-text; ratio: 2"></span><br>
                            <span x-text="fileName"></span>
                        </p>
                    </div>
                </div>

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
                    <label class="uk-form-label"><?= __('Title') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('title', ['label' => false, 'class' => 'uk-input']) ?>
                    </div>
                </div>

                <!-- Alt text (images only) -->
                <div class="uk-margin" x-show="fileType === 'image'">
                    <label class="uk-form-label"><?= __('Alt Text') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('alt_text', ['label' => false, 'class' => 'uk-input', 'placeholder' => __('Describe the image for screen readers')]) ?>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('Caption') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('caption', ['label' => false, 'class' => 'uk-textarea', 'rows' => 3]) ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right column: meta -->
        <div class="uk-width-1-3@m">

            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                <h3 class="uk-card-title"><?= __('Organisation') ?></h3>

                <?php if (!empty($isSuperAdmin)): ?>
                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('Tenant') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('business_users_tenant_id', [
                            'type' => 'select',
                            'options' => ['' => __('— Site (no tenant) —')] + $tenants,
                            'value' => $selectedTenantId,
                            'label' => false,
                            'class' => 'uk-select',
                            '@change' => 'handleTenantChange($event)',
                        ]) ?>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= __('User') ?></label>
                    <div class="uk-form-controls">
                        <?= $this->Form->control('owner_id', [
                            'type' => 'select',
                            'options' => ['' => __('— Select User —')] + $ownerOptions,
                            'value' => $selectedOwnerId ?? $file->owner_id,
                            'label' => false,
                            'class' => 'uk-select',
                        ]) ?>
                    </div>
                </div>
                <?php endif; ?>

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
                            'type'    => 'select',
                            'options' => $tags,
                            'label'   => false,
                            'class'   => 'uk-select',
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

        </div>
    </div>

    <?= $this->element('crud_buttons', [
        'type'   => 'form',
        'entity' => $file,
        'options' => ['showDelete' => false],
    ]) ?>

    <?= $this->Form->end() ?>

</div>

<script>
function fileUploadForm(config) {
    return {
        preview:  null,
        fileName: '',
        isImage:  false,
        fileType: '<?= h($file->type ?? 'document') ?>',
        isSuperAdmin: Boolean(config?.isSuperAdmin),
        usersByTenantUrl: config?.usersByTenantUrl || '',
        selectedOwnerId: config?.selectedOwnerId || '',

        init() {
            if (!this.isSuperAdmin) {
                return;
            }

            const selectedTenantId = config?.selectedTenantId;
            if (selectedTenantId) {
                this.fetchUsersByTenant(String(selectedTenantId));
            }
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.fileName = file.name;
            this.isImage  = file.type.startsWith('image/');
            if (this.isImage) {
                const reader = new FileReader();
                reader.onload = (ev) => { this.preview = ev.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.preview = true; // trigger x-show for non-image
            }
        },

        handleDrop(e) {
            const dt    = e.dataTransfer;
            const input = document.querySelector('input[type="file"]');
            if (input && dt.files.length) {
                // Assign dropped files to the input
                const dT = new DataTransfer();
                dT.items.add(dt.files[0]);
                input.files = dT.files;
                input.dispatchEvent(new Event('change'));
            }
        },

        async handleTenantChange(e) {
            const tenantId = e.target.value || '';
            await this.fetchUsersByTenant(tenantId);
        },

        async fetchUsersByTenant(tenantId) {
            const ownerSelect = document.querySelector('select[name="owner_id"]');
            if (!ownerSelect) {
                return;
            }

            ownerSelect.innerHTML = '';
            ownerSelect.appendChild(new Option('<?= h(__('— Select User —')) ?>', ''));

            if (!tenantId) {
                return;
            }

            const url = `${this.usersByTenantUrl}?tenant_id=${encodeURIComponent(tenantId)}`;
            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                if (!payload || !payload.success || !Array.isArray(payload.users)) {
                    return;
                }

                for (const user of payload.users) {
                    ownerSelect.appendChild(new Option(user.label, user.id));
                }

                if (this.selectedOwnerId) {
                    ownerSelect.value = this.selectedOwnerId;
                }
            } catch (error) {
                // Keep form usable even when ajax request fails.
            }
        },
    };
}
</script>
