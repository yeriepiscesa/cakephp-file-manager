<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Creates the fm_file_metadata table.
 *
 * Stores arbitrary key-value pairs attached to a file.
 * Examples: "copyright", "license", "photographer", "expiry_date", etc.
 */
class CreateFmFileMetadata extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fm_file_metadata');

        $table->addColumn('file_id', 'uuid', ['null' => false]);
        $table->addColumn('key', 'string', ['limit' => 100, 'null' => false]);
        $table->addColumn('value', 'text', ['null' => true]);
        $table->addColumn('created', 'datetime', ['null' => false]);
        $table->addColumn('modified', 'datetime', ['null' => false]);

        $table->addIndex(['file_id', 'key'], ['unique' => true, 'name' => 'UNIQUE_FILE_META_KEY']);
        $table->addIndex(['file_id']);

        $table->create();
    }
}
