<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Creates the fm_file_tags pivot table.
 */
class CreateFmFileTags extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fm_file_tags', ['id' => false, 'primary_key' => ['file_id', 'tag_id']]);

        $table->addColumn('file_id', 'uuid', ['null' => false]);
        $table->addColumn('tag_id', 'integer', ['null' => false]);

        $table->addIndex(['file_id']);
        $table->addIndex(['tag_id']);

        $table->create();
    }
}
