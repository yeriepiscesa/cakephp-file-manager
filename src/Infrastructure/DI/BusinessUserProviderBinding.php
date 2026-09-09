<?php
declare(strict_types=1);

namespace FileManager\Infrastructure\DI;

use BusinessUsers\Domain\Repository\TenantUserRepositoryInterface;
use Cake\Core\ContainerInterface;
use Cake\Core\Plugin;
use FileManager\Application\Port\BusinessUserProviderInterface;
use FileManager\Infrastructure\Adapter\BusinessUsersAdapter;
use FileManager\Infrastructure\Adapter\NullBusinessUserProvider;

/**
 * Wires BusinessUserProviderInterface to the correct adapter at container resolve time.
 *
 * Detection runs when the service is first resolved (not when FileManager registers
 * services), so BusinessUsers may bootstrap after FileManager in config/plugins.php.
 */
final class BusinessUserProviderBinding
{
    public static function register(ContainerInterface $container): void
    {
        $container->addShared(BusinessUserProviderInterface::class, function () use ($container): BusinessUserProviderInterface {
            return self::resolve($container);
        });
    }

    public static function usesBusinessUsersAdapter(): bool
    {
        return Plugin::isLoaded('BusinessUsers')
            && interface_exists(TenantUserRepositoryInterface::class);
    }

    private static function resolve(ContainerInterface $container): BusinessUserProviderInterface
    {
        if (!self::usesBusinessUsersAdapter()) {
            return new NullBusinessUserProvider();
        }

        /** @var \BusinessUsers\Domain\Repository\TenantUserRepositoryInterface $tenantUserRepository */
        $tenantUserRepository = $container->get(TenantUserRepositoryInterface::class);

        return new BusinessUsersAdapter($tenantUserRepository);
    }
}
