<?php
declare(strict_types=1);

namespace FileManager\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use FileManager\Model\Enum\ShareType;

/**
 * FmFileSharesTable
 */
class FmFileSharesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('fm_file_shares');
        $this->setEntityClass('FileManager.FmFileShare');
        $this->setDisplayField('share_type');
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
            ->scalar('share_type')
            ->inList('share_type', ShareType::values())
            ->requirePresence('share_type', 'create')
            ->notEmptyString('share_type');

        $validator
            ->scalar('reference_id')
            ->maxLength('reference_id', 100)
            ->allowEmptyString('reference_id');

        $validator
            ->boolean('can_download')
            ->notEmptyString('can_download');

        $validator
            ->dateTime('expires_at')
            ->allowEmptyDateTime('expires_at');

        return $validator;
    }
}
