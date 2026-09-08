<?php
declare(strict_types=1);

namespace FileManager\Controller\Admin;

use FileManager\Controller\AppController;

/**
 * Admin/CategoriesController
 */
class CategoriesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index'  => ['className' => 'Crud.Index'],
                'view'   => ['className' => 'Crud.View'],
                'add'    => ['className' => 'Crud.Add'],
                'edit'   => ['className' => 'Crud.Edit'],
                'delete' => ['className' => 'Crud.Delete'],
            ],
        ]);
    }

    public function index(): void
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\EventInterface $event) {
            $event->getSubject()->query
                ->contain(['ParentCategories'])
                ->orderByAsc('Categories.sort_order')
                ->orderByAsc('Categories.name');
        });

        $this->Crud->execute();
    }

    public function add(): void
    {
        $this->_setFormViewVars();
        $this->Crud->execute();
    }

    public function edit(int $id): void
    {
        $this->_setFormViewVars();
        $this->Crud->execute();
    }

    public function delete(int $id): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->Crud->execute();
    }

    private function _setFormViewVars(): void
    {
        $this->set('parentCategories', $this->fetchTable('FileManager.Categories')
            ->find('list')
            ->where(['is_active' => true])
            ->orderByAsc('name')
            ->toArray());
    }
}
