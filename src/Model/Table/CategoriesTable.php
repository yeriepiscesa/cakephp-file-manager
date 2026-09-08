<?php
declare(strict_types=1);

namespace FileManager\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CategoriesTable
 *
 * Manages hierarchical file categories. Supports single-level nesting
 * (parent_id self-join) for simple tree structures.
 */
class CategoriesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('fm_categories');
        $this->setEntityClass('FileManager.Category');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tools.Slugged', [
            'label' => 'name',
            'field' => 'slug',
        ]);

        $this->belongsTo('ParentCategories', [
            'className'  => 'FileManager.Categories',
            'foreignKey' => 'parent_id',
        ]);

        $this->hasMany('ChildCategories', [
            'className'  => 'FileManager.Categories',
            'foreignKey' => 'parent_id',
        ]);

        $this->hasMany('FmFiles', [
            'className'  => 'FileManager.FmFiles',
            'foreignKey' => 'category_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 150)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 180)
            ->allowEmptyString('slug');

        $validator
            ->allowEmptyString('description');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        $validator
            ->integer('sort_order')
            ->allowEmptyString('sort_order');

        return $validator;
    }
}
