<?php
declare(strict_types=1);

namespace FileManager\Model\Enum;

/**
 * Controls who can access the file via the public media controller.
 */
enum FileVisibility: string
{
    /** Accessible by anyone who knows the URL. */
    case Public  = 'public';
    /** Access restricted to owner + explicit shares. */
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Public  => __('Public'),
            self::Private => __('Private'),
        };
    }

    /** @return array<string, string> */
    public static function toSelectOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
