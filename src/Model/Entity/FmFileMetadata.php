<?php
declare(strict_types=1);

namespace FileManager\Model\Entity;

use Cake\ORM\Entity;

/**
 * FmFileMetadata entity.
 *
 * @property int    $id
 * @property string $file_id UUID
 * @property string $key
 * @property string|null $value
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \FileManager\Model\Entity\FmFile $fm_file
 */
class FmFileMetadata extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'file_id' => true,
        'key'     => true,
        'value'   => true,
    ];
}
