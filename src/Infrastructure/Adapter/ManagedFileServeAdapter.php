<?php
declare(strict_types=1);

namespace FileManager\Infrastructure\Adapter;

use Cake\Core\Configure;
use FileManager\Application\DTO\ServableFileData;
use FileManager\Application\Exception\ManagedFileAccessDeniedException;
use FileManager\Application\Exception\ManagedFileNotFoundException;
use FileManager\Application\Port\ManagedFileServeInterface;
use FileManager\Model\Entity\FmFile;
use FileManager\Model\Table\FmFilesTable;
use FileManager\Service\FileAccessChecker;

/**
 * Resolves FileManager-managed files for HTTP serving.
 *
 * This is the only place that MediaController-facing serving logic
 * touches FmFilesTable, FileAccessChecker, and disk path resolution.
 */
class ManagedFileServeAdapter implements ManagedFileServeInterface
{
    public function __construct(
        private readonly FmFilesTable $filesTable,
        private readonly FileAccessChecker $fileAccessChecker,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function resolveForServe(string $id, string $slug, ?string $userId): ServableFileData
    {
        /** @var \FileManager\Model\Entity\FmFile|null $file */
        $file = $this->filesTable->find('forServe', id: $id, slug: $slug)->first();

        if ($file === null) {
            throw new ManagedFileNotFoundException('File not found.');
        }

        if (!$this->fileAccessChecker->canAccess($file, $userId)) {
            throw new ManagedFileAccessDeniedException('You do not have access to this file.');
        }

        $filePath = $this->resolveLocalPath($file);

        if (!file_exists($filePath) || !is_file($filePath)) {
            throw new ManagedFileNotFoundException('Physical file not found on disk.');
        }

        $mimeType = $file->mime_type ?? (mime_content_type($filePath) ?: 'application/octet-stream');

        return new ServableFileData(
            absolutePath: $filePath,
            mimeType: $mimeType,
        );
    }

    /**
     * Resolve the absolute filesystem path for a local-disk file.
     * The `path` column is stored relative to the configured disk root.
     *
     * Default disk root: ROOT/data-files/
     */
    private function resolveLocalPath(FmFile $file): string
    {
        $diskRoot = Configure::read(
            'FileManager.diskRoot',
            ROOT . DS . 'data-files'
        );

        $relativePath = ltrim(str_replace('/', DS, (string)$file->path), DS);
        if (
            $relativePath !== ''
            && $file->filename !== null
            && $file->filename !== ''
            && !str_ends_with($relativePath, str_replace('/', DS, $file->filename))
        ) {
            $relativePath = rtrim($relativePath, DS) . DS . ltrim(str_replace('/', DS, $file->filename), DS);
        }

        return $diskRoot . DS . $relativePath;
    }
}
