<?php
declare(strict_types=1);

namespace FileManager\Model\Entity;

use Cake\ORM\Entity;

/**
 * Category entity.
 *
 * @property int         $id
 * @property int|null    $parent_id
 * @property string      $name
 * @property string      $slug
 * @property string|null $description
 * @property bool        $is_active
 * @property int         $sort_order
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \FileManager\Model\Entity\Category|null   $parent_category
 * @property list<\FileManager\Model\Entity\Category>  $children
 * @property list<\FileManager\Model\Entity\FmFile>    $fm_files
 */
class Category extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'parent_id'   => true,
        'name'        => true,
        'slug'        => true,
        'description' => true,
        'is_active'   => true,
        'sort_order'  => true,
    ];
}
