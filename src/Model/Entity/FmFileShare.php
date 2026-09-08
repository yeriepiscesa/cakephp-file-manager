<?php
declare(strict_types=1);

namespace FileManager\Model\Entity;

use Cake\ORM\Entity;
use FileManager\Model\Enum\ShareType;

/**
 * FmFileShare entity.
 *
 * @property int         $id
 * @property string      $file_id       UUID
 * @property string      $share_type    all|user|group|tenant
 * @property string|null $reference_id  User UUID, group ID, or tenant ID
 * @property bool        $can_download
 * @property \Cake\I18n\DateTime|null $expires_at
 * @property \Cake\I18n\DateTime      $created
 * @property \Cake\I18n\DateTime      $modified
 *
 * @property \FileManager\Model\Entity\FmFile $fm_file
 */
class FmFileShare extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'file_id'      => true,
        'share_type'   => true,
        'reference_id' => true,
        'can_download' => true,
        'expires_at'   => true,
    ];

    public function getShareType(): ?ShareType
    {
        return isset($this->share_type) ? ShareType::tryFrom($this->share_type) : null;
    }

    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }
}
