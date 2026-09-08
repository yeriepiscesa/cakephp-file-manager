<?php
declare(strict_types=1);

namespace FileManager\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use FileManager\Model\Enum\FileType;
use FileManager\Model\Enum\FileVisibility;
use Psr\Http\Message\UploadedFileInterface;

/**
 * FmFilesTable
 *
 * Central registry for all managed files. Handles:
 *  - UUID primary key generation
 *  - Slug generation from title
 *  - File upload via josegonzalez/cakephp-upload (Flysystem adapter)
 *  - Soft-delete via `deleted` timestamp
 *  - BelongsToMany tags, HasMany metadata & shares
 *
 * Upload configuration is intentionally generic; specific disk adapters
 * (local, S3, etc.) are configured in app_local.php under `FileManager`.
 */
class FmFilesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('fm_files');
        $this->setEntityClass('FileManager.FmFile');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'filename' => [
                'fields' => [
                    'dir' => 'path',
                    'size' => 'size',
                    'type' => 'mime_type',
                ],
                'path' => 'FileManager{DS}{field-value:upload_scope}{DS}{year}{DS}{month}{DS}',
                'filesystem' => [
                    'root' => ROOT . DS . 'data-files',
                ],
                'keepFilesOnDelete' => false,
            ],
        ]);

        // ---- Associations ------------------------------------------------

        $this->belongsTo('Categories', [
            'className'  => 'FileManager.Categories',
            'foreignKey' => 'category_id',
        ]);

        $this->belongsToMany('Tags', [
            'className'        => 'FileManager.Tags',
            'foreignKey'       => 'file_id',
            'targetForeignKey' => 'tag_id',
            'joinTable'        => 'fm_file_tags',
            'saveStrategy'     => 'replace',
        ]);

        $this->hasMany('FmFileMetadata', [
            'className'  => 'FileManager.FmFileMetadata',
            'foreignKey' => 'file_id',
            'dependent'  => true,
        ]);

        $this->hasMany('FmFileShares', [
            'className'  => 'FileManager.FmFileShares',
            'foreignKey' => 'file_id',
            'dependent'  => true,
        ]);
    }

    // ---- Validation -------------------------------------------------------

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('category_id')
            ->allowEmptyString('category_id');

        $validator
            ->uuid('owner_id')
            ->allowEmptyString('owner_id');

        $validator
            ->scalar('type')
            ->inList('type', FileType::values())
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 300)
            ->allowEmptyString('slug');

        $validator
            ->scalar('disk')
            ->maxLength('disk', 50)
            ->allowEmptyString('disk');

        $validator
            ->notEmptyFile('filename', __('Please choose a file to upload.'), 'create');

        $validator
            ->scalar('visibility')
            ->inList('visibility', FileVisibility::values())
            ->requirePresence('visibility', 'create')
            ->notEmptyString('visibility');

        $validator
            ->scalar('alt_text')
            ->maxLength('alt_text', 500)
            ->allowEmptyString('alt_text');

        $validator
            ->scalar('caption')
            ->allowEmptyString('caption');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        return $validator;
    }

    // ---- Rules ------------------------------------------------------------

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('category_id', 'Categories'), [
            'errorField' => 'category_id',
        ]);

        return $rules;
    }

    // ---- Custom Finders ---------------------------------------------------

    /**
     * Filter by file type.
     *
     * Usage: $this->FmFiles->find('byType', type: 'image')
     */
    public function findByType(SelectQuery $query, string $type): SelectQuery
    {
        return $query->where([$this->aliasField('type') => $type]);
    }

    /**
     * Find only active, non-deleted files.
     */
    public function findActive(SelectQuery $query): SelectQuery
    {
        return $query->where([$this->aliasField('is_active') => true]);
    }

    /**
     * Find public files (visible to all via MediaController).
     */
    public function findPublic(SelectQuery $query): SelectQuery
    {
        return $query->where([$this->aliasField('visibility') => FileVisibility::Public->value]);
    }

    /**
     * Find files owned by a specific user.
     *
     * Usage: $this->FmFiles->find('ownedBy', userId: $userId)
     */
    public function findOwnedBy(SelectQuery $query, string $userId): SelectQuery
    {
        return $query->where([$this->aliasField('owner_id') => $userId]);
    }

    /**
     * Find a single file by UUID and slug for serving.
     * Returns active, non-deleted files only.
     */
    public function findForServe(SelectQuery $query, string $id, string $slug): SelectQuery
    {
        return $query
            ->where([
                $this->aliasField('id')       => $id,
                $this->aliasField('slug')      => $slug,
                $this->aliasField('is_active') => true,
            ]);
    }

    // ---- Lifecycle --------------------------------------------------------

    /**
     * Generates a UUID and slug before saving new records.
     */
    protected function _beforeSave(): void
    {
        // Intentionally left for subclass/event usage
    }

    /**
     * @param \Cake\Event\EventInterface<\Cake\Datasource\EntityInterface> $event
     * @param \FileManager\Model\Entity\FmFile $entity
     * @param \ArrayObject<string, mixed> $options
     */
    public function beforeSave(\Cake\Event\EventInterface $event, \FileManager\Model\Entity\FmFile $entity, \ArrayObject $options): void
    {
        if ($entity->isNew() && empty($entity->id)) {
            $entity->id = Text::uuid();
        }

        // For API usage, controller can pass the authenticated user id in save options.
        $actorId = $options['actor_id'] ?? null;
        if (empty($entity->owner_id) && is_string($actorId) && $actorId !== '') {
            $entity->owner_id = $actorId;
        }

        // Upload behavior path uses {field-value:upload_scope}; provide a safe fallback.
        if ($entity->isDirty('filename') && empty($entity->get('upload_scope'))) {
            $username = $this->resolveUsernameForPath($entity->owner_id);
            $entity->set('upload_scope', sprintf('sites/%s', $username), ['guard' => false]);
        }

        if (empty($entity->disk)) {
            $entity->disk = 'local';
        }

        // Keep extension metadata from the uploaded file when available.
        if ($entity->isDirty('filename') && $entity->filename instanceof UploadedFileInterface) {
            $clientFilename = (string)$entity->filename->getClientFilename();
            $extension = pathinfo($clientFilename, PATHINFO_EXTENSION);
            $entity->extension = $extension !== '' ? strtolower($extension) : null;
        }

        // Auto-generate slug from title if not provided
        if (empty($entity->slug) && !empty($entity->title)) {
            $entity->slug = $this->_buildUniqueSlug($entity->title, $entity->id);
        }
    }

    /**
     * Build a URL-safe slug unique in the table.
     */
    private function _buildUniqueSlug(string $title, string $id): string
    {
        $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title) ?? '', '-'));
        $slug = $base;
        $i    = 1;

        while ($this->exists(['slug' => $slug, 'id !=' => $id])) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    /**
     * Resolve owner id into a path-safe username segment.
     */
    private function resolveUsernameForPath(?string $ownerId): string
    {
        if ($ownerId === null || $ownerId === '') {
            return 'anonymous';
        }

        $usersTable = $this->getTableLocator()->get('CakeDC/Users.Users');
        $user = $usersTable
            ->find()
            ->select(['id', 'username'])
            ->where(['id' => $ownerId])
            ->first();

        $username = (string)($user?->username ?? '');
        if ($username === '') {
            return 'anonymous';
        }

        $safe = strtolower(trim((string)preg_replace('/[^a-z0-9._-]+/i', '-', $username), '-'));

        return $safe !== '' ? $safe : 'anonymous';
    }

    /**
     * Helper used by AccessChecker service.
     * Returns true when the user has been granted access through fm_file_shares.
     *
     * @param string       $fileId
     * @param string       $userId
     * @param list<int>    $groupIds
     * @param list<int>    $tenantIds
     */
    public function hasShareAccess(string $fileId, string $userId, array $groupIds = [], array $tenantIds = []): bool
    {
        /** @var \FileManager\Model\Table\FmFileSharesTable $shares */
        $shares = $this->FmFileShares;

        // Check direct user share
        if ($shares->exists(['file_id' => $fileId, 'share_type' => 'user', 'reference_id' => $userId])) {
            return true;
        }

        // Check "all" share
        if ($shares->exists(['file_id' => $fileId, 'share_type' => 'all'])) {
            return true;
        }

        // Check group memberships
        foreach ($groupIds as $groupId) {
            if ($shares->exists(['file_id' => $fileId, 'share_type' => 'group', 'reference_id' => (string)$groupId])) {
                return true;
            }
        }

        // Check tenant memberships
        foreach ($tenantIds as $tenantId) {
            if ($shares->exists(['file_id' => $fileId, 'share_type' => 'tenant', 'reference_id' => (string)$tenantId])) {
                return true;
            }
        }

        return false;
    }
}
