<?php
declare(strict_types=1);

namespace FileManager\Application\DTO;

/**
 * Immutable data for a managed file that is ready to be streamed over HTTP.
 *
 * Constructed in the Infrastructure layer; consumed by the host app
 * (MediaController) without exposing ORM entities or disk internals.
 */
final class ServableFileData
{
    public function __construct(
        public readonly string $absolutePath,
        public readonly string $mimeType,
    ) {}
}
