# FileManager Plugin

Plugin CakePHP 5 untuk mengelola file (gambar, video, dokumen) dengan kategori, tag, metadata, ownership, dan sharing — menggunakan **josegonzalez/cakephp-upload** (Flysystem) dan terintegrasi opsional dengan plugin **BusinessUsers**.

Package Composer: `yeriepiscesa/cakephp-file-manager`

---

## Daftar Isi

1. [Instalasi](#instalasi)
2. [Konfigurasi](#konfigurasi)
3. [Migration](#migration)
4. [Host wiring (MediaController)](#host-wiring-mediacontroller)
5. [Fitur](#fitur)
6. [Arsitektur](#arsitektur)
7. [Skema Database](#skema-database)
8. [Alur Upload](#alur-upload)
9. [Serving File (MediaController)](#serving-file-mediacontroller)
10. [Access Control](#access-control)
11. [Integrasi BusinessUsers](#integrasi-businessusers)
12. [Admin UI](#admin-ui)
13. [Mengonsumsi Plugin (CMS / LMS)](#mengonsumsi-plugin-cms--lms)
14. [Extending](#extending)

---

## Instalasi

### 1. Composer

Pastikan host application sudah memiliki `cakephp/plugin-installer`.

```bash
composer require yeriepiscesa/cakephp-file-manager
```

Atau via repository GitHub:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/yeriepiscesa/cakephp-file-manager"
        }
    ],
    "require": {
        "yeriepiscesa/cakephp-file-manager": "dev-main"
    }
}
```

Dependency Composer plugin ini sudah mencakup: `cakedc/users`, `friendsofcake/crud`, `friendsofcake/search`, `josegonzalez/cakephp-upload`, `cakephp/migrations`. Slug kategori dan tag dibuat oleh behavior internal `FileManager.Slugged`; plugin `Tools` tidak diperlukan.

Disarankan tambahan:

```bash
composer require yeriepiscesa/cakephp-uikit yeriepiscesa/cakephp-business-users
```

| Plugin | Wajib | Keterangan |
|---|---|---|
| `yeriepiscesa/cakephp-uikit` | Disarankan | Layout admin (Alpine.js, UIkit) |
| `yeriepiscesa/cakephp-business-users` | Opsional | Sharing berbasis group/tenant |

### 2. Load plugin

**Jangan** mendaftarkan plugin yang sama dua kali. Beberapa dependensi di-load otomatis oleh plugin lain — terutama `CakeDC/Users` yang sudah di-bootstrap oleh BusinessUsers.

#### Siapa load apa

| Plugin | Daftar di `config/plugins.php`? | Keterangan |
|---|---|---|
| `CakeDC/Users` | **Tergantung skenario** (lihat di bawah) | Auto-load oleh BusinessUsers |
| `Crud`, `Search`, `Josegonzalez/Upload`, `Migrations` | **Ya** | Host wajib daftar |
| `CrudConnect`, `AuditStash` | **Ya** | Wajib jika BusinessUsers dipakai |
| `Uikit` | **Ya** (disarankan) | Layout admin |
| `BusinessUsers` | **Ya**, jika butuh group/tenant sharing | Mem-load `CakeDC/Users` otomatis |
| `FileManager` | **Ya** | Load **setelah** semua dependensinya |

#### Skenario A — dengan BusinessUsers (disarankan)

Pakai konfigurasi ini jika project sudah memakai BusinessUsers untuk auth/multi-tenant. **Jangan** tambahkan `'CakeDC/Users' => []` — BusinessUsers mem-load-nya di `BusinessUsersPlugin::bootstrap()`.

```php
return [
    'Migrations' => ['onlyCli' => true],
    'Crud' => [],
    'Search' => [],
    'Josegonzalez/Upload' => [],
    'AuditStash' => [],
    'CrudConnect' => [],
    'Uikit' => [],
    'BusinessUsers' => [],   // mem-load CakeDC/Users otomatis
    'FileManager' => [],     // setelah BusinessUsers
];
```

`FileManagerPlugin::services()` mendaftarkan `BusinessUserProviderBinding`, yang mendeteksi BusinessUsers saat **service pertama kali di-resolve** (bukan saat registrasi DI) via `Plugin::isLoaded('BusinessUsers')` + ketersediaan `TenantUserRepositoryInterface`.

#### Skenario B — FileManager saja (tanpa BusinessUsers)

Jika BusinessUsers **tidak** dipakai, daftarkan `CakeDC/Users` secara eksplisit karena tidak ada plugin lain yang mem-load-nya:

```php
return [
    'Migrations' => ['onlyCli' => true],
    'Crud' => [],
    'Search' => [],
    'Josegonzalez/Upload' => [],
    'CakeDC/Users' => [],
    'Uikit' => [],
    'FileManager' => [],
];
```

Tanpa BusinessUsers, FileManager memakai `NullBusinessUserProvider` — fitur share `group` dan `tenant` tidak aktif; share `user` dan `all` tetap berfungsi.

#### Urutan load

1. `Crud`, `Search`, `Josegonzalez/Upload`
2. `AuditStash`, `CrudConnect` (jika BusinessUsers dipakai)
3. `Uikit`
4. `BusinessUsers` (jika dipakai — sebelum FileManager)
5. `FileManager`

#### Anti-pattern (hindari)

```php
// ❌ JANGAN — CakeDC/Users ter-load dua kali
'CakeDC/Users' => [],
'BusinessUsers' => [],
'FileManager' => [],
```

Gejala umum jika terjadi bentrok: error bootstrap plugin, konfigurasi `Users.config` tidak terbaca, atau route auth ganda.

### 3. Prasyarat host

| Prasyarat | Keterangan |
|---|---|
| UUID di `users.id` | Wajib — kolom `owner_id` memakai UUID CakeDC/Users |
| Base `AppController` | Wajib — `FileManager\Controller\AppController` extends controller host |
| `MediaController` + route `/media` | Wajib — serving file managed (lihat [Host wiring](#host-wiring-mediacontroller)) |
| Direktori storage | Wajib — buat folder fisik untuk upload lokal |

### 4. Direktori storage

```bash
mkdir -p data-files/FileManager
chmod 775 data-files/FileManager
```

Path default dapat diubah lewat `FileManager.diskRoot` di konfigurasi.

---

## Konfigurasi

Tambahkan ke `config/app.php` atau `config/app_local.php`:

```php
'FileManager' => [
    // Flysystem disk key (untuk pengembangan S3/adapters lain)
    'defaultDisk' => 'local',

    // Path absolut root storage lokal (dipakai ManagedFileServeAdapter)
    'diskRoot' => ROOT . DS . 'data-files',

    // Nama plugin theme untuk layout admin (default: Uikit)
    'theme' => env('FILE_MANAGER_THEME', 'Uikit'),
],
```

Upload fisik dikonfigurasi di `FmFilesTable` via behavior `Josegonzalez/Upload.Upload` dengan root `ROOT/data-files` dan subfolder `FileManager/`. `FileManager.diskRoot` untuk serving harus menunjuk ke root yang sama, agar path yang tersimpan tidak berulang. Pastikan proses PHP dapat menulis ke `data-files/FileManager/`. Jika upload gagal, penyimpanan record dihentikan dan error write ditampilkan alih-alih error SQL kolom `path`.

---

## Migration

Jalankan migration plugin:

```bash
bin/cake migrations migrate --plugin FileManager
```

Cek status:

```bash
bin/cake migrations status --plugin FileManager
```

Rollback satu step bila diperlukan:

```bash
bin/cake migrations rollback --plugin FileManager
```

Verifikasi route admin:

```bash
bin/cake routes check /admin/file-manager/files
```

---

## Host wiring (MediaController)

File managed **tidak** diakses langsung dari disk. Host application harus menyediakan controller dan route HTTP.

### 1. Controller

Buat `src/Controller/MediaController.php` di host application:

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use FileManager\Application\Exception\ManagedFileAccessDeniedException;
use FileManager\Application\Exception\ManagedFileNotFoundException;
use FileManager\Application\Port\ManagedFileServeInterface;

class MediaController extends AppController
{
    public function __construct(
        \Cake\Http\ServerRequest $request,
        private readonly ManagedFileServeInterface $managedFileServe,
    ) {
        parent::__construct($request);
    }

    public function serveManaged(string $id, string $slug): Response
    {
        $identity   = $this->request->getAttribute('identity');
        $identifier = $identity?->getIdentifier();
        $userId     = $identifier !== null ? (string)$identifier : null;

        try {
            $file = $this->managedFileServe->resolveForServe($id, $slug, $userId);
        } catch (ManagedFileNotFoundException $e) {
            throw new NotFoundException(__($e->getMessage()));
        } catch (ManagedFileAccessDeniedException $e) {
            throw new ForbiddenException(__($e->getMessage()));
        }

        return $this->response
            ->withFile($file->absolutePath)
            ->withType($file->mimeType)
            ->withCache('-1 minute', '+1 year');
    }
}
```

### 2. Dependency injection

Daftarkan controller di `Application::services()` host:

```php
use App\Controller\MediaController;
use Cake\Http\ServerRequest;
use FileManager\Application\Port\ManagedFileServeInterface;

$container->add(MediaController::class)
    ->addArgument(ServerRequest::class)
    ->addArgument(ManagedFileServeInterface::class);
```

`ManagedFileServeInterface` sudah di-wire otomatis oleh `FileManagerPlugin::services()`.

### 3. Route

Tambahkan ke `config/routes.php`:

```php
$routes->scope('/media', function (\Cake\Routing\RouteBuilder $builder): void {
    $uuidPattern = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

    $builder->connect(
        '/{id}/{slug}',
        ['controller' => 'Media', 'action' => 'serveManaged'],
        ['pass' => ['id', 'slug'], 'id' => $uuidPattern]
    );
});
```

URL publik: `GET /media/{uuid}/{slug}`

---

## Fitur

| Feature | Details |
|---|---|
| File types | `image`, `video`, `document` |
| Categories | Hierarchical (parent/child), sortable |
| Tags | Many-to-many, auto-slug |
| Metadata | Arbitrary key-value pairs per file |
| Ownership | Each file has an `owner_id` (CakeDC/Users UUID) |
| Sharing | `public`, `all`, `user`, `group` (BusinessUsers), `tenant` (BusinessUsers) |
| Views | Grid & list toggle (Alpine.js) |
| Serving | Controlled via host `MediaController` + `ManagedFileServeInterface` |
| Soft-delete | Kolom `deleted` + flag `is_active` |
| Flysystem | Upload via `josegonzalez/cakephp-upload`; kolom `disk` + `path` |

---

## Arsitektur

```
plugins/FileManager/
├── config/
│   └── Migrations/          ← 6 migration files (fm_categories, fm_tags, fm_files, …)
└── src/
    ├── Application/
    │   ├── DTO/
    │   │   └── ServableFileData.php                ← path + mime for HTTP serving
    │   ├── Exception/
    │   │   ├── ManagedFileNotFoundException.php
    │   │   └── ManagedFileAccessDeniedException.php
    │   └── Port/
    │       ├── BusinessUserProviderInterface.php   ← port (interface only)
    │       └── ManagedFileServeInterface.php       ← port used by host MediaController
    ├── Controller/
    │   ├── AppController.php                       ← sets Uikit theme/layout
    │   └── Admin/
    │       ├── FilesController.php
    │       ├── CategoriesController.php
    │       └── TagsController.php
    ├── FileManagerPlugin.php                       ← routes, DI wiring, menu registration
    ├── Infrastructure/
    │   └── Adapter/
    │       ├── BusinessUsersAdapter.php             ← concrete adapter (uses BusinessUsers)
    │       ├── ManagedFileServeAdapter.php          ← lookup, access check, disk path
    │       └── NullBusinessUserProvider.php         ← fallback for apps without BusinessUsers
    ├── Menu/
    │   └── AdminMenuAdapter.php
    ├── Model/
    │   ├── Entity/  (Category, Tag, FmFile, FmFileMetadata, FmFileShare)
    │   ├── Enum/    (FileType, FileVisibility, ShareType)
    │   └── Table/   (CategoriesTable, TagsTable, FmFilesTable, …)
    └── Service/
        └── FileAccessChecker.php                   ← decides who may access a file

src/Controller/MediaController.php  ← HTTP layer di host app; panggil ManagedFileServeInterface
```

---

## Skema Database

| Table | Purpose |
|---|---|
| `fm_categories` | Hierarchical categories |
| `fm_tags` | Tags |
| `fm_files` | File registry (UUID PK) |
| `fm_file_tags` | files ↔ tags pivot |
| `fm_file_metadata` | Key-value metadata per file |
| `fm_file_shares` | Sharing rules (user / group / tenant / all) |

### Key columns on `fm_files`

| Column | Type | Notes |
|---|---|---|
| `id` | UUID | Exposed in public URLs |
| `type` | string | `image` / `video` / `document` |
| `disk` | string | Flysystem adapter key (`local`, `s3`, …) |
| `path` | string | Path relative to the disk root |
| `visibility` | string | `public` (anyone) or `private` (access-checked) |
| `owner_id` | UUID | CakeDC/Users `users.id` |
| `slug` | string | URL-friendly; auto-generated from `title` |

---

## Alur Upload

1. User submit form **Add File** (`Admin/Files/add`).
2. `FilesController::add()` menerima POST.
3. `FmFilesTable::beforeSave()` generate UUID dan slug unik.
4. Behavior `Josegonzalez/Upload.Upload` di `FmFilesTable` menyimpan file fisik ke `data-files/` dan menulis `path`, `filename`, `mime_type`, `size`.
5. Controller set `owner_id` dari identity user yang login.
6. Tag disimpan via `BelongsToMany` dengan `saveStrategy = replace`.

Untuk adapter storage custom (S3, dll.), lihat [Extending](#extending).

---

## Serving File (MediaController)

Files are never accessed directly from disk. They are always routed through:

```
GET /media/{uuid}/{slug}
```

Example:

```
GET /media/550e8400-e29b-41d4-a716-446655440000/my-product-photo
```

`MediaController::serveManaged()` calls `ManagedFileServeInterface::resolveForServe()` and streams the result. The adapter (`ManagedFileServeAdapter`) owns the implementation:

1. Looks up the file by `id` + `slug` in `fm_files`.
2. If `visibility = public` → allowed immediately.
3. If `visibility = private` → calls `FileAccessChecker::canAccess()`.
4. Resolves the physical path from `FileManager.diskRoot` + `file.path`.
5. Returns `ServableFileData` (`absolutePath`, `mimeType`). Missing records/files raise `ManagedFileNotFoundException`; denied access raises `ManagedFileAccessDeniedException`.

The host controller maps those exceptions to HTTP 404/403 and returns a `withFile()` response with cache headers.

The legacy `/media/{model}/**` route still works for josegonzalez/cakephp-upload files (e.g. airline logos).

---

## Access Control

`FileAccessChecker` decides if a user can see a private file:

| Priority | Check |
|---|---|
| 1 | `visibility = public` → ✅ always allowed |
| 2 | Anonymous user → ❌ denied |
| 3 | `owner_id = userId` → ✅ allowed |
| 4 | Share row `share_type = all` → ✅ allowed |
| 5 | Share row `share_type = user`, `reference_id = userId` → ✅ |
| 6 | Share row `share_type = group`, `reference_id ∈ userGroupIds` → ✅ |
| 7 | Share row `share_type = tenant`, `reference_id ∈ userTenantIds` → ✅ |
| — | Otherwise → ❌ 403 Forbidden |

Download permission follows the same chain but additionally checks `can_download = true` on the matching share row.

---

## Integrasi BusinessUsers

FileManager tidak memuat BusinessUsers sendiri — host application yang mendaftarkan keduanya di `config/plugins.php`. Lihat [Load plugin](#2-load-plugin) untuk skenario A/B dan aturan urutan load.

The plugin exposes a **port** and ships two **adapters**:

### Port

```php
// FileManager\Application\Port\BusinessUserProviderInterface
public function getGroupIds(string $userId): array;   // list<int>
public function getTenantIds(string $userId): array;  // list<int>
```

### Adapters

| Class | When to use |
|---|---|
| `BusinessUsersAdapter` | BusinessUsers plugin is loaded (default wiring in `FileManagerPlugin::services()`) |
| `NullBusinessUserProvider` | BusinessUsers not available; group/tenant shares always return false |

### DI Wiring (automatic)

`BusinessUserProviderBinding` (dipanggil dari `FileManagerPlugin::services()`) memilih adapter saat container **resolve** service, bukan saat registrasi:

| Kondisi | Adapter |
|---|---|
| `Plugin::isLoaded('BusinessUsers')` **dan** `TenantUserRepositoryInterface` tersedia | `BusinessUsersAdapter` |
| Selain itu | `NullBusinessUserProvider` |

Deteksi lazy ini aman meski urutan entri di `config/plugins.php` tidak ideal — selama BusinessUsers terdaftar dan bootstrap sebelum request pertama, share group/tenant tetap aktif.

To override, re-bind in `Application::services()` after `parent::pluginBootstrap()`:

```php
$container->add(
    BusinessUserProviderInterface::class,
    MyCustomBusinessUserProvider::class
);
```

---

## Admin UI

Routes:

| URL | Controller | Description |
|---|---|---|
| `/admin/file-manager/files` | `Admin\FilesController::index` | Grid/list view with filters |
| `/admin/file-manager/files/add` | `Admin\FilesController::add` | Upload form with drag-drop |
| `/admin/file-manager/files/{id}/edit` | `Admin\FilesController::edit` | Edit metadata, tags, shares |
| `/admin/file-manager/files/{id}` | `Admin\FilesController::view` | Detail view |
| `/admin/file-manager/categories` | `Admin\CategoriesController` | Category CRUD |
| `/admin/file-manager/tags` | `Admin\TagsController` | Tag CRUD |

Templates live in `plugins/FileManager/templates/Admin/`. They use the `Uikit` theme and follow the same conventions as other admin modules.

### Grid ↔ List Toggle

The index template uses Alpine.js to toggle between views in-browser (no page reload):

```html
<div x-data="{ viewMode: 'grid' }">
    <button @click="viewMode = 'grid'">Grid</button>
    <button @click="viewMode = 'list'">List</button>
    <div x-show="viewMode === 'grid'">…grid cards…</div>
    <div x-show="viewMode === 'list'">…table rows…</div>
</div>
```

### Metadata Editor (edit template)

The edit template uses Alpine.js `metadataEditor()` to add/remove key-value rows dynamically. Fields are named `fm_file_metadata[{idx}][key]` / `[value]` so CakePHP's `patchEntity()` can hydrate them through the `hasMany` association.

### Share Editor (edit template)

`sharingEditor()` similarly manages `fm_file_shares[{idx}][share_type]`, `[reference_id]`, `[can_download]`.

---

## Mengonsumsi Plugin (CMS / LMS)

### Embed a file picker in a form

```php
// In your controller
$files = $this->fetchTable('FileManager.FmFiles')
    ->find('active')
    ->find('byType', type: 'image')
    ->find('list')
    ->toArray();

$this->set('mediaFiles', $files);
```

### Render a managed image in a template

```php
<?= $this->Html->image(
    $this->Url->build($file->getPublicUrlParams()),
    ['alt' => h($file->alt_text)]
) ?>
```

Or use the entity helper:

```php
$url = $this->Url->build($file->getPublicUrlParams());
```

### Check access programmatically

```php
// Inject or fetch the service
/** @var \FileManager\Service\FileAccessChecker $checker */
$checker = $this->getContainer()->get(FileAccessChecker::class);

$userId = $this->request->getAttribute('identity')?->getIdentifier();
if (!$checker->canAccess($file, $userId)) {
    throw new ForbiddenException();
}
```

---

## Extending

### Override upload behavior

Upload default sudah dikonfigurasi di `FmFilesTable`. Untuk mengganti path, adapter, atau callback, extend table di host app atau gunakan event listener:

```php
// Contoh: override path upload di Application::bootstrap()
\Cake\Event\EventManager::instance()->on(
    'Model.initialize',
    function (\Cake\Event\EventInterface $event) {
        $table = $event->getSubject();
        if ($table->getAlias() !== 'FmFiles') {
            return;
        }

        // Hapus behavior default lalu tambahkan konfigurasi custom
        $table->removeBehavior('Upload');
        $table->addBehavior('Josegonzalez/Upload.Upload', [
            'filename' => [
                'path' => 'custom/{year}/{month}/',
                'filesystem' => ['root' => ROOT . DS . 'data-files' . DS . 'FileManager'],
                // ...
            ],
        ]);
    }
);
```

### Menambah adapter Flysystem (mis. S3)

1. Install `league/flysystem-aws-s3-v3`.
2. Add an adapter key to your Flysystem configuration.
3. Set `FileManager.defaultDisk = 's3'` in `app_local.php`.
4. Override `MediaController::_resolveLocalPath()` (or override the whole `_buildFileResponse()`) to stream from the Flysystem adapter instead of a local `withFile()` call.

### Custom access logic

Replace the `BusinessUserProviderInterface` binding in `Application::services()`:

```php
use FileManager\Application\Port\BusinessUserProviderInterface;

$container->add(BusinessUserProviderInterface::class, MyProvider::class)
    ->addArgument(MyDependency::class);
```

---

## Referensi

- Plugin entry point: `src/FileManagerPlugin.php`
- Migrations: `config/Migrations/`
- Package Composer: `yeriepiscesa/cakephp-file-manager`
- Plugin terkait: `yeriepiscesa/cakephp-uikit`, `yeriepiscesa/cakephp-business-users`
