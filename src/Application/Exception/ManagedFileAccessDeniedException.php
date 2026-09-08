<?php
declare(strict_types=1);

namespace FileManager\Application\Exception;

use RuntimeException;

/**
 * Thrown when the current user is not allowed to access a private managed file.
 */
class ManagedFileAccessDeniedException extends RuntimeException
{
}
