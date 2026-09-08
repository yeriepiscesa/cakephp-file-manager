<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Creates the fm_file_shares table.
 *
 * Controls who can access a file beyond the owner.
 *
 * share_type values:
 *   all       – everyone (equivalent to public)
 *   user      – specific user_id
 *   group     – specific BusinessUsers group_id
 *   tenant    – all members of a BusinessUsers tenant_id
 *
 * When visibility = 'public' on fm_files, no row here is needed;
 * the controller will serve the file without checking this table.
 * When visibility = 'private', access is checked against this table
 * plus owner_id.
 */
class CreateFmFileShares extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fm_file_shares');

        $table->addColumn('file_id', 'uuid', ['null' => false]);
        $table->addColumn('share_type', 'string', ['limit' => 20, 'null' => false]);
        // reference_id is polymorphic: user UUID, group int, or tenant int stored as string
        $table->addColumn('reference_id', 'string', ['limit' => 100, 'null' => true]);
        $table->addColumn('can_download', 'boolean', ['null' => false, 'default' => true]);
        $table->addColumn('expires_at', 'datetime', ['null' => true]);
        $table->addColumn('created', 'datetime', ['null' => false]);
        $table->addColumn('modified', 'datetime', ['null' => false]);

        $table->addIndex(['file_id']);
        $table->addIndex(['share_type', 'reference_id']);

        $table->create();
    }
}
