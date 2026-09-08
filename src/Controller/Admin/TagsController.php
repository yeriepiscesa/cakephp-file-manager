<?php
declare(strict_types=1);

namespace FileManager\Controller\Admin;

use FileManager\Controller\AppController;

/**
 * Admin/TagsController
 */
class TagsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index'  => ['className' => 'Crud.Index'],
                'add'    => ['className' => 'Crud.Add'],
                'edit'   => ['className' => 'Crud.Edit'],
                'delete' => ['className' => 'Crud.Delete'],
            ],
        ]);
    }

    public function index(): void
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\EventInterface $event) {
            $event->getSubject()->query->orderByAsc('Tags.name');
        });

        $this->Crud->execute();
    }

    public function add(): void
    {
        $this->Crud->execute();
    }

    public function edit(int $id): void
    {
        $this->Crud->execute();
    }

    public function delete(int $id): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->Crud->execute();
    }
}
