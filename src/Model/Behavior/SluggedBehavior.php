<?php
declare(strict_types=1);

namespace FileManager\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\Utility\Text;

/**
 * Generates unique slugs for FileManager categories and tags.
 */
class SluggedBehavior extends Behavior
{
    protected array $_defaultConfig = [
        'source' => 'name',
        'field' => 'slug',
        'maxLength' => 180,
    ];

    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $field = (string)$this->getConfig('field');
        if (trim((string)$entity->get($field)) !== '') {
            return;
        }

        $name = trim((string)$entity->get((string)$this->getConfig('source')));
        if ($name === '') {
            return;
        }

        $limit = (int)$this->getConfig('maxLength');
        $base = strtolower(trim(Text::slug($name), '-'));
        $base = trim(mb_substr($base !== '' ? $base : 'item', 0, $limit), '-');
        $table = $this->table();
        $primaryKey = $table->getPrimaryKey();
        $id = $entity->get($primaryKey);
        $slug = $base;
        $suffix = 1;

        while (true) {
            $conditions = [$field => $slug];
            if ($id !== null) {
                $conditions[$primaryKey . ' !='] = $id;
            }
            if (!$table->exists($conditions)) {
                break;
            }

            $end = '-' . $suffix++;
            $slug = rtrim(mb_substr($base, 0, $limit - strlen($end)), '-') . $end;
        }

        $entity->set($field, $slug);
    }
}
