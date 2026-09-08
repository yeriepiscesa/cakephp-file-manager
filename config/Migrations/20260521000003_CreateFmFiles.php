<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Creates the fm_files table.
 *
 * Central file registry. The physical storage path is managed by
 * League/Flysystem; this table keeps the canonical metadata.
 *
 * Columns:
 *  id            – UUID primary key exposed in public URLs.
 *  category_id   – optional category.
 *  owner_id      – UUID referencing CakeDC/Users users.id.
 *  type          – enum-like string: image | video | document.
 *  title         – human-readable name displayed in UI.
 *  slug          – URL-friendly identifier.
 *  disk          – Flysystem disk/adapter key (e.g. "local", "s3").
 *  path          – file path relative to disk root.
 *  filename      – original uploaded filename.
 *  extension     – lower-cased file extension.
 *  mime_type     – MIME type detected on upload.
 *  size          – file size in bytes.
 *  width         – pixel width (images/videos only, null otherwise).
 *  height        – pixel height (images/videos only, null otherwise).
 *  duration      – duration in seconds (videos only, null otherwise).
 *  visibility    – "public" or "private"; controls controller access.
 *  alt_text      – alt attribute for images (accessibility).
 *  caption       – optional caption shown in media browser.
 *  is_active     – soft-disable without deleting.
 *  created / modified / deleted – standard timestamps.
 */
class CreateFmFiles extends BaseMigration
{
    /**
     * Primary key is a UUID string.
     */
    public bool $autoId = false;

    public function change(): void
    {
        $table = $this->table('fm_files', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', ['null' => false]);
        $table->addColumn('category_id', 'integer', ['null' => true, 'default' => null]);
        $table->addColumn('owner_id', 'uuid', ['null' => true, 'default' => null]);
        $table->addColumn('type', 'string', ['limit' => 20, 'null' => false]);
        $table->addColumn('title', 'string', ['limit' => 255, 'null' => false]);
        $table->addColumn('slug', 'string', ['limit' => 300, 'null' => false]);
        $table->addColumn('disk', 'string', ['limit' => 50, 'null' => false, 'default' => 'local']);
        $table->addColumn('path', 'string', ['limit' => 1000, 'null' => false]);
        $table->addColumn('filename', 'string', ['limit' => 255, 'null' => false]);
        $table->addColumn('extension', 'string', ['limit' => 20, 'null' => true]);
        $table->addColumn('mime_type', 'string', ['limit' => 100, 'null' => true]);
        $table->addColumn('size', 'biginteger', ['null' => true]);
        $table->addColumn('width', 'integer', ['null' => true]);
        $table->addColumn('height', 'integer', ['null' => true]);
        $table->addColumn('duration', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true]);
        $table->addColumn('visibility', 'string', ['limit' => 20, 'null' => false, 'default' => 'public']);
        $table->addColumn('alt_text', 'string', ['limit' => 500, 'null' => true]);
        $table->addColumn('caption', 'text', ['null' => true]);
        $table->addColumn('is_active', 'boolean', ['null' => false, 'default' => true]);
        $table->addColumn('created', 'datetime', ['null' => false]);
        $table->addColumn('modified', 'datetime', ['null' => false]);
        $table->addColumn('deleted', 'datetime', ['null' => true]);

        $table->addIndex(['category_id']);
        $table->addIndex(['owner_id']);
        $table->addIndex(['type']);
        $table->addIndex(['visibility']);
        $table->addIndex(['slug']);
        $table->addIndex(['is_active']);
        $table->addIndex(['deleted']);

        $table->create();
    }
}
