<?php
declare(strict_types=1);

namespace FileManager\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TagsTable
 */
class TagsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('fm_tags');
        $this->setEntityClass('FileManager.Tag');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('FileManager.Slugged', [
            'source' => 'name',
            'field' => 'slug',
            'maxLength' => 120,
        ]);

        $this->belongsToMany('FmFiles', [
            'className'        => 'FileManager.FmFiles',
            'foreignKey'       => 'tag_id',
            'targetForeignKey' => 'file_id',
            'joinTable'        => 'fm_file_tags',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 120)
            ->allowEmptyString('slug');

        return $validator;
    }
}
