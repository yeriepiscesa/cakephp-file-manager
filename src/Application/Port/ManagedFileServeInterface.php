<?php
declare(strict_types=1);

namespace FileManager\Application\Port;

use FileManager\Application\DTO\ServableFileData;
use FileManager\Application\Exception\ManagedFileAccessDeniedException;
use FileManager\Application\Exception\ManagedFileNotFoundException;

/**
 * Port: contract for resolving a FileManager-managed file for HTTP serving.
 *
 * Consumers (e.g. MediaController) depend ONLY on this interface.
 * Lookup, visibility/share checks, and disk path resolution live in the
 * Infrastructure adapter.
 *
 * @see \FileManager\Infrastructure\Adapter\ManagedFileServeAdapter
 */
interface ManagedFileServeInterface
{
    /**
     * Resolve a managed file by UUID and slug, applying access rules.
     *
     * @param string $id     File UUID.
     * @param string $slug   File slug.
     * @param string|null $userId  Authenticated user UUID, or null for anonymous.
     * @return \FileManager\Application\DTO\ServableFileData
     * @throws ManagedFileNotFoundException
     * @throws ManagedFileAccessDeniedException
     */
    public function resolveForServe(string $id, string $slug, ?string $userId): ServableFileData;
}
