<?php
declare(strict_types=1);

namespace FileManager\Menu;

/**
 * Admin menu adapter for the FileManager plugin.
 *
 * Registered in FileManagerPlugin::bootstrap() so the host application's
 * menu builder picks it up automatically.
 */
class AdminMenuAdapter
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAdminMenuItems(): array
    {
        return [
            [
                'id'       => 'file_manager',
                'label'    => __('File Manager'),
                'route'    => '#',
                'icon'     => 'folder',
                'children' => [
                    [
                        'id'    => 'file_manager_files',
                        'label' => __('Files'),
                        'route' => [
                            'plugin'     => 'FileManager',
                            'prefix'     => 'Admin',
                            'controller' => 'Files',
                            'action'     => 'index',
                        ],
                        'auth' => ['plugin' => 'FileManager', 'controller' => 'Files'],
                    ],
                    [
                        'id'    => 'file_manager_categories',
                        'label' => __('Categories'),
                        'route' => [
                            'plugin'     => 'FileManager',
                            'prefix'     => 'Admin',
                            'controller' => 'Categories',
                            'action'     => 'index',
                        ],
                        'auth' => ['plugin' => 'FileManager', 'controller' => 'Categories'],
                    ],
                    [
                        'id'    => 'file_manager_tags',
                        'label' => __('Tags'),
                        'route' => [
                            'plugin'     => 'FileManager',
                            'prefix'     => 'Admin',
                            'controller' => 'Tags',
                            'action'     => 'index',
                        ],
                        'auth' => ['plugin' => 'FileManager', 'controller' => 'Tags'],
                    ],
                ],
            ],
        ];
    }
}
