<?php
declare(strict_types=1);

namespace FileManager\Model\Enum;

/**
 * Determines the target audience of a file share record.
 */
enum ShareType: string
{
    /** All authenticated users – or all users if file is public. */
    case All    = 'all';
    /** A specific platform user (by UUID). */
    case User   = 'user';
    /** All members of a BusinessUsers group. */
    case Group  = 'group';
    /** All members of a BusinessUsers tenant. */
    case Tenant = 'tenant';

    public function label(): string
    {
        return match ($this) {
            self::All    => __('Everyone'),
            self::User   => __('Specific User'),
            self::Group  => __('Group'),
            self::Tenant => __('Tenant'),
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
