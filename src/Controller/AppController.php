<?php
declare(strict_types=1);

namespace FileManager\Controller;

use App\Controller\AppController as BaseController;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

class AppController extends BaseController
{
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $theme = (string)Configure::read('FileManager.theme', 'Uikit');
        $this->viewBuilder()->setTheme($theme);

        $prefix = $this->request->getParam('prefix');
        $action = $this->request->getParam('action');
        if ($prefix === 'Admin') {
            $layout = $theme . '.admin';
            $this->viewBuilder()->setLayout($layout);
        }
    }
}
