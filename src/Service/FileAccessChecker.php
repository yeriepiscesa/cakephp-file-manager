<?php
declare(strict_types=1);

namespace FileManager\Service;

use FileManager\Application\Port\BusinessUserProviderInterface;
use FileManager\Model\Entity\FmFile;
use FileManager\Model\Enum\FileVisibility;
use FileManager\Model\Table\FmFilesTable;

/**
 * FileAccessChecker
 *
 * Determines whether an authenticated (or anonymous) user may access
 * a given file, based on:
 *  1. File visibility (public → always granted)
 *  2. Owner match
 *  3. fm_file_shares rows (all, user, group, tenant)
 */
class FileAccessChecker
{
    public function __construct(
        private readonly FmFilesTable $filesTable,
        private readonly BusinessUserProviderInterface $businessUserProvider,
    ) {}

    /**
     * @param \FileManager\Model\Entity\FmFile $file
     * @param string|null $userId  Null for anonymous users.
     * @return bool
     */
    public function canAccess(FmFile $file, ?string $userId): bool
    {
        // Public files are always accessible
        if ($file->getVisibility() === FileVisibility::Public) {
            return true;
        }

        // Anonymous users cannot access private files
        if ($userId === null) {
            return false;
        }

        // Owner always has access
        if ($file->owner_id === $userId) {
            return true;
        }

        // Resolve group & tenant memberships via BusinessUsers port
        $groups  = $this->businessUserProvider->getGroupIds($userId);
        $tenants = $this->businessUserProvider->getTenantIds($userId);

        return $this->filesTable->hasShareAccess($file->id, $userId, $groups, $tenants);
    }

    /**
     * Check download permission specifically.
     * A user must first pass canAccess(), then have can_download = true on
     * at least one matching share (or be the owner).
     */
    public function canDownload(FmFile $file, ?string $userId): bool
    {
        if (!$this->canAccess($file, $userId)) {
            return false;
        }

        if ($file->getVisibility() === FileVisibility::Public) {
            return true;
        }

        if ($file->owner_id === $userId) {
            return true;
        }

        $groups  = $this->businessUserProvider->getGroupIds($userId ?? '');
        $tenants = $this->businessUserProvider->getTenantIds($userId ?? '');

        /** @var \FileManager\Model\Table\FmFileSharesTable $sharesTable */
        $sharesTable = $this->filesTable->FmFileShares;

        // Build OR conditions for all matching share types
        $orConditions = [
            ['share_type' => 'all'],
            ['share_type' => 'user', 'reference_id' => $userId],
        ];
        foreach ($groups as $gid) {
            $orConditions[] = ['share_type' => 'group', 'reference_id' => (string)$gid];
        }
        foreach ($tenants as $tid) {
            $orConditions[] = ['share_type' => 'tenant', 'reference_id' => (string)$tid];
        }

        return $sharesTable->exists([
            'file_id'      => $file->id,
            'can_download' => true,
            'OR'           => $orConditions,
        ]);
    }
}
