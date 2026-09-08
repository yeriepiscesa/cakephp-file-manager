<?php
declare(strict_types=1);

namespace FileManager\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FmFileMetadataTable
 */
class FmFileMetadataTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('fm_file_metadata');
        $this->setEntityClass('FileManager.FmFileMetadata');
        $this->setDisplayField('key');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('FmFiles', [
            'className'  => 'FileManager.FmFiles',
            'foreignKey' => 'file_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('file_id')
            ->requirePresence('file_id', 'create')
            ->notEmptyString('file_id');

        $validator
            ->scalar('key')
            ->maxLength('key', 100)
            ->requirePresence('key', 'create')
            ->notEmptyString('key');

        $validator
            ->scalar('value')
            ->allowEmptyString('value');

        return $validator;
    }
}
