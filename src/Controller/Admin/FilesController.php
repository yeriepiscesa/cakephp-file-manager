<?php
declare(strict_types=1);

namespace FileManager\Controller\Admin;

use FileManager\Controller\AppController;
use FileManager\Model\Enum\FileType;
use FileManager\Model\Enum\FileVisibility;

/**
 * Admin/FilesController
 *
 * Manages file upload, listing, editing metadata, sharing, and deletion.
 *
 * Uses friendsofcake/crud for all CRUD actions plus a custom `share` action.
 */
class FilesController extends AppController
{
    protected ?string $defaultTable = 'FileManager.FmFiles';

    /**
     * Treat users with role=admin/superadmin or is_superuser as super admin.
     */
    private function isSuperAdmin(): bool
    {
        $identity = $this->request->getAttribute('identity');
        if ($identity === null) {
            return false;
        }

        $role = (string)($identity->role ?? ($identity->get('role') ?? ''));
        $isSuperuser = (bool)($identity->is_superuser ?? ($identity->get('is_superuser') ?? false));

        return $isSuperuser || in_array($role, ['superadmin', 'admin'], true);
    }

    /**
     * Build upload scope path prefix for Upload behavior.
     */
    private function buildUploadScope(?int $tenantId, ?string $ownerId): string
    {
        $username = $this->resolveUsernameByUserId($ownerId);

        if ($tenantId !== null) {
            return sprintf('tenants/%d/%s', $tenantId, $username);
        }

        return sprintf('sites/%s', $username);
    }

    /**
     * Resolve user id to a path-safe username.
     */
    private function resolveUsernameByUserId(?string $userId): string
    {
        if ($userId === null || $userId === '') {
            return 'anonymous';
        }

        $usersTable = $this->fetchTable('CakeDC/Users.Users');
        $user = $usersTable
            ->find()
            ->select(['id', 'username'])
            ->where(['id' => $userId])
            ->first();

        $username = (string)($user?->username ?? '');
        if ($username === '') {
            return 'anonymous';
        }

        $safe = strtolower(trim((string)preg_replace('/[^a-z0-9._-]+/i', '-', $username), '-'));

        return $safe !== '' ? $safe : 'anonymous';
    }

    /**
     * Build select options for users in a tenant.
     *
     * @param int $tenantId Tenant id in business_users_tenants
     * @return array<string, string>
     */
    private function getTenantUserOptions(int $tenantId): array
    {
        /** @var \BusinessUsers\Model\Table\TenantUsersTable $tenantUsersTable */
        $tenantUsersTable = $this->fetchTable('BusinessUsers.TenantUsers');

        $rows = $tenantUsersTable
            ->find()
            ->select(['user_id'])
            ->where(['business_users_tenant_id' => $tenantId])
            ->contain([
                'Users' => function (\Cake\ORM\Query\SelectQuery $query) {
                    return $query->select(['id', 'first_name', 'last_name', 'username', 'email']);
                },
            ])
            ->all();

        $options = [];
        foreach ($rows as $row) {
            $user = $row->user;
            if ($user === null) {
                continue;
            }

            $name = trim((string)$user->first_name . ' ' . (string)$user->last_name);
            $label = $name !== '' ? $name : (string)($user->username ?? '');
            if ($label === '') {
                $label = (string)($user->email ?? $user->id);
            }
            $suffix = (string)($user->email ?? $user->username ?? $user->id);

            $options[(string)$user->id] = sprintf('%s (%s)', $label, $suffix);
        }

        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }

    /**
     * Build select options for global site users.
     *
     * @return array<string, string>
     */
    private function getSiteUserOptions(): array
    {
        $users = $this->fetchTable('CakeDC/Users.Users')
            ->find('all')
            ->select(['id', 'first_name', 'last_name', 'username', 'email'])
            ->orderByAsc('username')
            ->all();

        $options = [];
        foreach ($users as $user) {
            $name = trim((string)$user->first_name . ' ' . (string)$user->last_name);
            $label = $name !== '' ? $name : (string)($user->username ?? '');
            if ($label === '') {
                $label = (string)($user->email ?? $user->id);
            }
            $suffix = (string)($user->email ?? $user->username ?? $user->id);
            $options[(string)$user->id] = sprintf('%s (%s)', $label, $suffix);
        }

        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index'  => ['className' => 'Crud.Index'],
                'view'   => ['className' => 'Crud.View'],
                'add'    => ['className' => 'Crud.Add'],
                'edit'   => ['className' => 'Crud.Edit'],
                'delete' => ['className' => 'Crud.Delete'],
            ],
        ]);

        $this->loadComponent('Search.Search', [
            'actions' => ['index'],
        ]);
    }

    /**
     * Index – grid & list view with search/filter.
     */
    public function index(): void
    {
        $this->Crud->on('beforeFind', function (\Cake\Event\EventInterface $event) {
            /** @var \Cake\ORM\Query\SelectQuery $query */
            $query = $event->getSubject()->query;
            $query
                ->contain(['Categories', 'Tags'])
                ->orderByDesc('FmFiles.created');

            // Apply search filters from query string
            $type       = $this->request->getQuery('type');
            $categoryId = $this->request->getQuery('category_id');
            $search     = $this->request->getQuery('search');

            if ($type && in_array($type, FileType::values(), true)) {
                $query->where(['FmFiles.type' => $type]);
            }
            if ($categoryId) {
                $query->where(['FmFiles.category_id' => (int)$categoryId]);
            }
            if ($search) {
                $query->where(['FmFiles.title LIKE' => '%' . $search . '%']);
            }
        });

        $this->Crud->on('beforePaginate', function (\Cake\Event\EventInterface $event) {
            $event->getSubject()->query->contain(['Categories', 'Tags']);
        });

        // Pass filter options to view (must be set before execute() renders)
        $this->set('fileTypes', FileType::toSelectOptions());
        $this->set('categories', $this->fetchTable('FileManager.Categories')
            ->find('list')
            ->where(['is_active' => true])
            ->orderByAsc('name')
            ->toArray());
        $this->set('viewMode', $this->request->getQuery('view', 'grid'));

        $this->Crud->execute();
    }

    /**
     * View file detail.
     */
    public function view(string $id): void
    {
        $this->Crud->on('beforeFind', function (\Cake\Event\EventInterface $event) {
            $event->getSubject()->query->contain([
                'Categories',
                'Tags',
                'FmFileMetadata',
                'FmFileShares',
            ]);
        });

        $this->Crud->execute();
    }

    /**
     * Add / upload new file.
     */
    public function add(): void
    {
        $isSuperAdmin = $this->isSuperAdmin();

        $this->Crud->on('beforeSave', function (\Cake\Event\EventInterface $event) {
            /** @var \FileManager\Model\Entity\FmFile $entity */
            $entity = $event->getSubject()->entity;

            $identity = $this->request->getAttribute('identity');
            $loggedInUserId = $identity?->getIdentifier();

            $tenantId = null;
            $selectedOwnerId = null;
            if ($this->isSuperAdmin()) {
                $tenantIdData = $this->request->getData('business_users_tenant_id');
                if ($tenantIdData !== null && $tenantIdData !== '') {
                    $tenantId = (int)$tenantIdData;
                }

                $ownerData = $this->request->getData('owner_id');
                if (is_string($ownerData) && $ownerData !== '') {
                    $selectedOwnerId = $ownerData;
                }
            }

            $entity->owner_id = $selectedOwnerId ?? $loggedInUserId;
            $entity->set(
                'upload_scope',
                $this->buildUploadScope($tenantId, $entity->owner_id),
                ['guard' => false]
            );
        });

        $this->Crud->on('afterSave', function (\Cake\Event\EventInterface $event) {
            if ($event->getSubject()->success) {
                $this->Flash->success(__('File uploaded successfully.'));
            }
        });

        $this->_setFormViewVars($isSuperAdmin);
        $this->Crud->execute();
    }

    /**
     * Edit file metadata, tags, category, sharing.
     */
    public function edit(string $id): void
    {
        $this->Crud->on('beforeFind', function (\Cake\Event\EventInterface $event) {
            $event->getSubject()->query->contain([
                'Categories',
                'Tags',
                'FmFileMetadata',
                'FmFileShares',
            ]);
        });

        $this->Crud->on('afterSave', function (\Cake\Event\EventInterface $event) {
            if ($event->getSubject()->success) {
                $this->Flash->success(__('File updated successfully.'));
            }
        });

        $this->_setFormViewVars(false);
        $this->Crud->execute();
    }

    /**
     * AJAX endpoint: users for a tenant.
     */
    public function usersByTenant(): \Cake\Http\Response
    {
        $this->request->allowMethod(['get']);

        if (!$this->isSuperAdmin()) {
            return $this->response
                ->withType('application/json')
                ->withStringBody((string)json_encode(['success' => false, 'users' => []]));
        }

        $tenantId = (int)$this->request->getQuery('tenant_id', 0);
        $options = $tenantId > 0
            ? $this->getTenantUserOptions($tenantId)
            : $this->getSiteUserOptions();
        $users = [];
        foreach ($options as $id => $label) {
            $users[] = ['id' => $id, 'label' => $label];
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode(['success' => true, 'users' => $users]));
    }

    /**
     * Delete (soft-delete via Trash behavior).
     */
    public function delete(string $id): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->Crud->execute();
    }

    // ---- Helpers ----------------------------------------------------------

    private function _setFormViewVars(bool $isSuperAdmin): void
    {
        $this->set('fileTypes', FileType::toSelectOptions());
        $this->set('visibilityOptions', FileVisibility::toSelectOptions());
        $this->set('categories', $this->fetchTable('FileManager.Categories')
            ->find('list')
            ->where(['is_active' => true])
            ->orderByAsc('name')
            ->toArray());
        $this->set('tags', $this->fetchTable('FileManager.Tags')
            ->find('list')
            ->orderByAsc('name')
            ->toArray());

        $this->set('isSuperAdmin', $isSuperAdmin);
        $this->set('tenants', []);
        $this->set('ownerOptions', []);

        if (!$isSuperAdmin) {
            return;
        }

        $selectedTenantId = $this->request->getData('business_users_tenant_id');
        $selectedOwnerId = $this->request->getData('owner_id');
        $tenantId = null;
        if ($selectedTenantId !== null && $selectedTenantId !== '') {
            $tenantId = (int)$selectedTenantId;
        }

        $tenants = $this->fetchTable('BusinessUsers.Tenants')
            ->find('list', ['keyField' => 'id', 'valueField' => 'name'])
            ->orderByAsc('name')
            ->toArray();

        $ownerOptions = $tenantId !== null && $tenantId > 0
            ? $this->getTenantUserOptions($tenantId)
            : $this->getSiteUserOptions();

        $this->set('tenants', $tenants);
        $this->set('ownerOptions', $ownerOptions);
        $this->set('selectedTenantId', $tenantId);
        $this->set('selectedOwnerId', is_string($selectedOwnerId) ? $selectedOwnerId : null);
    }
}
