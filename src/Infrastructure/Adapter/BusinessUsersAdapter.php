<?php
declare(strict_types=1);

namespace FileManager\Infrastructure\Adapter;

use BusinessUsers\Domain\Repository\TenantUserRepositoryInterface;
use FileManager\Application\Port\BusinessUserProviderInterface;

/**
 * BusinessUsersAdapter
 *
 * Implements the FileManager port by delegating to the BusinessUsers
 * domain repository. This class is the only place in FileManager that
 * directly references BusinessUsers internals; all other FileManager
 * code depends on BusinessUserProviderInterface.
 *
 * Wired automatically by BusinessUserProviderBinding when BusinessUsers is loaded.
 * Override in Application::services() if a custom adapter is required.
 */
class BusinessUsersAdapter implements BusinessUserProviderInterface
{
    public function __construct(
        private readonly TenantUserRepositoryInterface $tenantUserRepository,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getGroupIds(string $userId): array
    {
        $memberships = $this->tenantUserRepository->findByUserId($userId);

        $ids = [];
        foreach ($memberships as $membership) {
            foreach ($membership->groups as $group) {
                $ids[] = $group->id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * {@inheritDoc}
     */
    public function getTenantIds(string $userId): array
    {
        $memberships = $this->tenantUserRepository->findByUserId($userId);

        $ids = [];
        foreach ($memberships as $membership) {
            $ids[] = $membership->tenant->id;
        }

        return array_values(array_unique($ids));
    }
}
