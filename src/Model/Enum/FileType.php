<?php
declare(strict_types=1);

namespace FileManager\Model\Enum;

/**
 * Represents the logical type of a managed file.
 */
enum FileType: string
{
    case Image    = 'image';
    case Video    = 'video';
    case Document = 'document';

    public function label(): string
    {
        return match ($this) {
            self::Image    => __('Image'),
            self::Video    => __('Video'),
            self::Document => __('Document'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Image    => 'image',
            self::Video    => 'video-camera',
            self::Document => 'file-text',
        };
    }

    /** Returns allowed MIME type prefixes for this file type. */
    public function mimePrefix(): string
    {
        return match ($this) {
            self::Image    => 'image/',
            self::Video    => 'video/',
            self::Document => 'application/',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string, string> Suitable for Form::control select options. */
    public static function toSelectOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
