<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Creates the fm_categories table.
 *
 * Categories allow files to be grouped into logical collections
 * (e.g. "Product Images", "Marketing Videos", "Legal Documents").
 */
class CreateFmCategories extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fm_categories');

        $table->addColumn('parent_id', 'integer', ['null' => true, 'default' => null]);
        $table->addColumn('name', 'string', ['limit' => 150, 'null' => false]);
        $table->addColumn('slug', 'string', ['limit' => 180, 'null' => false]);
        $table->addColumn('description', 'text', ['null' => true]);
        $table->addColumn('is_active', 'boolean', ['null' => false, 'default' => true]);
        $table->addColumn('sort_order', 'integer', ['null' => false, 'default' => 0]);
        $table->addColumn('created', 'datetime', ['null' => false]);
        $table->addColumn('modified', 'datetime', ['null' => false]);

        $table->addIndex(['slug'], ['unique' => true]);
        $table->addIndex(['parent_id']);

        $table->create();
    }
}
