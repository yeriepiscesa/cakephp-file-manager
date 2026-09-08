<?php
declare(strict_types=1);

namespace FileManager\Application\Exception;

use RuntimeException;

/**
 * Thrown when a managed file cannot be resolved for serving
 * (missing database record or missing physical file).
 */
class ManagedFileNotFoundException extends RuntimeException
{
}
