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
 * Register this in the container when the BusinessUsers plugin is active:
 *
 * ```php
 * // In FileManagerPlugin::services() or Application::services():
 * $container->add(
 *     BusinessUserProviderInterface::class,
 *     BusinessUsersAdapter::class
 * )->addArgument(TenantUserRepositoryInterface::class);
 * ```
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
