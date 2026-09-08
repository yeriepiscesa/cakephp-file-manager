<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Creates the fm_tags table.
 *
 * Tags are free-form labels that can be attached to many files.
 */
class CreateFmTags extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fm_tags');

        $table->addColumn('name', 'string', ['limit' => 100, 'null' => false]);
        $table->addColumn('slug', 'string', ['limit' => 120, 'null' => false]);
        $table->addColumn('created', 'datetime', ['null' => false]);
        $table->addColumn('modified', 'datetime', ['null' => false]);

        $table->addIndex(['slug'], ['unique' => true]);

        $table->create();
    }
}
