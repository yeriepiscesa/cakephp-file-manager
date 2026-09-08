<?php
declare(strict_types=1);

namespace FileManager\Infrastructure\Adapter;

use FileManager\Application\Port\BusinessUserProviderInterface;

/**
 * NullBusinessUserProvider
 *
 * Fallback adapter for applications that do NOT use the BusinessUsers
 * plugin. Always returns empty arrays so that share checks based on
 * group/tenant always evaluate to false (only owner and explicit user
 * shares will work).
 *
 * Register this in the container when BusinessUsers is NOT loaded:
 *
 * ```php
 * $container->add(BusinessUserProviderInterface::class, NullBusinessUserProvider::class);
 * ```
 */
class NullBusinessUserProvider implements BusinessUserProviderInterface
{
    public function getGroupIds(string $userId): array
    {
        return [];
    }

    public function getTenantIds(string $userId): array
    {
        return [];
    }
}
