<?php
declare(strict_types=1);

namespace FileManager;

use BusinessUsers\Domain\Repository\TenantUserRepositoryInterface;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Core\Plugin;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\TableRegistry;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use FileManager\Application\Port\BusinessUserProviderInterface;
use FileManager\Application\Port\ManagedFileServeInterface;
use FileManager\Infrastructure\Adapter\BusinessUsersAdapter;
use FileManager\Infrastructure\Adapter\ManagedFileServeAdapter;
use FileManager\Infrastructure\Adapter\NullBusinessUserProvider;
use FileManager\Service\FileAccessChecker;

/**
 * Plugin for FileManager
 *
 * Registers:
 *  - Admin routes under /admin/file-manager/
 *  - BusinessUserProviderInterface (BusinessUsersAdapter when plugin is loaded,
 *    NullBusinessUserProvider otherwise)
 *  - FileAccessChecker service
 *  - ManagedFileServeInterface (used by host MediaController)
 *  - Admin menu adapter
 */
class FileManagerPlugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app): void
    {
        $adapters   = (array)Configure::read('Menu.adminAdapters', []);
        $adapters[] = \FileManager\Menu\AdminMenuAdapter::class;
        Configure::write('Menu.adminAdapters', array_values(array_unique($adapters)));
    }

    public function routes(RouteBuilder $routes): void
    {
        // Admin CRUD routes for Files, Categories, Tags
        $routes->prefix('Admin', function (RouteBuilder $builder) {
            $builder->plugin('FileManager', ['path' => '/file-manager'], function (RouteBuilder $fb) {
                $fb->setRouteClass(DashedRoute::class);

                $fb->resources('Files',    ['prefix' => '']);
                $fb->resources('Categories', ['prefix' => '']);
                $fb->resources('Tags',     ['prefix' => '']);

                $fb->fallbacks(DashedRoute::class);
            });
        });

        parent::routes($routes);
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        return $middlewareQueue;
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        return parent::console($commands);
    }

    public function services(ContainerInterface $container): void
    {
        // Bind the BusinessUserProvider port to the appropriate adapter
        if (Plugin::isLoaded('BusinessUsers')) {
            $container->addShared(BusinessUserProviderInterface::class, function () use ($container) {
                /** @var \BusinessUsers\Domain\Repository\TenantUserRepositoryInterface $tenantUserRepository */
                $tenantUserRepository = $container->get(TenantUserRepositoryInterface::class);

                return new BusinessUsersAdapter($tenantUserRepository);
            });
        } else {
            $container->add(BusinessUserProviderInterface::class, NullBusinessUserProvider::class);
        }

        // FileAccessChecker (internal service used by ManagedFileServeAdapter)
        $container->addShared(FileAccessChecker::class, function () use ($container) {
            /** @var \FileManager\Model\Table\FmFilesTable $filesTable */
            $filesTable = TableRegistry::getTableLocator()->get('FileManager.FmFiles');
            /** @var \FileManager\Application\Port\BusinessUserProviderInterface $businessUserProvider */
            $businessUserProvider = $container->get(BusinessUserProviderInterface::class);

            return new FileAccessChecker($filesTable, $businessUserProvider);
        });

        $container->addShared(ManagedFileServeInterface::class, function () use ($container) {
            /** @var \FileManager\Model\Table\FmFilesTable $filesTable */
            $filesTable = TableRegistry::getTableLocator()->get('FileManager.FmFiles');
            /** @var \FileManager\Service\FileAccessChecker $fileAccessChecker */
            $fileAccessChecker = $container->get(FileAccessChecker::class);

            return new ManagedFileServeAdapter($filesTable, $fileAccessChecker);
        });
    }
}
