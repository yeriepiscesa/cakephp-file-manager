<?php
declare(strict_types=1);

namespace FileManager\Model\Entity;

use Cake\ORM\Entity;
use FileManager\Model\Enum\FileType;
use FileManager\Model\Enum\FileVisibility;

/**
 * FmFile entity.
 *
 * @property string      $id           UUID
 * @property int|null    $category_id
 * @property string|null $owner_id     UUID of the owning user
 * @property string      $type         image|video|document
 * @property string      $title
 * @property string      $slug
 * @property string      $disk         Flysystem adapter key
 * @property string      $path         Path relative to disk root
 * @property string      $filename     Original filename
 * @property string|null $extension
 * @property string|null $mime_type
 * @property int|null    $size         Bytes
 * @property int|null    $width
 * @property int|null    $height
 * @property float|null  $duration     Seconds (video only)
 * @property string      $visibility   public|private
 * @property string|null $alt_text
 * @property string|null $caption
 * @property bool        $is_active
 * @property \Cake\I18n\DateTime      $created
 * @property \Cake\I18n\DateTime      $modified
 * @property \Cake\I18n\DateTime|null $deleted
 *
 * @property \FileManager\Model\Entity\Category|null        $category
 * @property list<\FileManager\Model\Entity\Tag>            $tags
 * @property list<\FileManager\Model\Entity\FmFileMetadata> $fm_file_metadata
 * @property list<\FileManager\Model\Entity\FmFileShare>    $fm_file_shares
 */
class FmFile extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'category_id' => true,
        'owner_id'    => true,
        'type'        => true,
        'title'       => true,
        'slug'        => true,
        'disk'        => true,
        'path'        => true,
        'filename'    => true,
        'extension'   => true,
        'mime_type'   => true,
        'size'        => true,
        'width'       => true,
        'height'      => true,
        'duration'    => true,
        'visibility'  => true,
        'alt_text'    => true,
        'caption'     => true,
        'is_active'   => true,
        // associations
        'category'         => true,
        'tags'             => true,
        'fm_file_metadata' => true,
        'fm_file_shares'   => true,
    ];

    /**
     * Typed access to the file type enum.
     */
    public function getFileType(): ?FileType
    {
        return isset($this->type) ? FileType::tryFrom($this->type) : null;
    }

    /**
     * Typed access to the visibility enum.
     */
    public function getVisibility(): FileVisibility
    {
        return FileVisibility::tryFrom($this->visibility ?? '') ?? FileVisibility::Public;
    }

    /**
     * Returns a human-readable file size string.
     */
    public function getFormattedSize(): string
    {
        if ($this->size === null) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i     = 0;
        $size  = (float)$this->size;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Build the public URL path for this file.
     * Route: /media/{id}/{slug}
     *
     * @return array<string, mixed> CakePHP URL array.
     */
    public function getPublicUrlParams(): array
    {
        return [
            'plugin'     => false,
            'prefix'     => false,
            'controller' => 'Media',
            'action'     => 'serveManaged',
            'id'         => $this->id,
            'slug'       => $this->slug,
        ];
    }
}
