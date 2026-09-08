<?php
declare(strict_types=1);

namespace FileManager\Application\Port;

/**
 * Port: contract for retrieving a user's group and tenant memberships.
 *
 * FileManager depends only on this interface. The concrete implementation
 * (BusinessUsersAdapter) lives in FileManager\Infrastructure\Adapter and
 * delegates to BusinessUsers domain repositories.
 *
 * Applications that do NOT use the BusinessUsers plugin can provide a
 * NullBusinessUserProvider that returns empty arrays.
 */
interface BusinessUserProviderInterface
{
    /**
     * Returns the IDs of all BusinessUsers groups the user belongs to.
     *
     * @param string $userId  CakeDC/Users user UUID.
     * @return list<int>
     */
    public function getGroupIds(string $userId): array;

    /**
     * Returns the IDs of all BusinessUsers tenants the user belongs to.
     *
     * @param string $userId
     * @return list<int>
     */
    public function getTenantIds(string $userId): array;
}
