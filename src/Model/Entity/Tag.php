<?php
declare(strict_types=1);

namespace FileManager\Model\Entity;

use Cake\ORM\Entity;

/**
 * Tag entity.
 *
 * @property int    $id
 * @property string $name
 * @property string $slug
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property list<\FileManager\Model\Entity\FmFile> $_joinData
 */
class Tag extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
    ];
}
