<?php
declare(strict_types=1);

namespace FileManager\File\Writer;

use Josegonzalez\Upload\File\Writer\DefaultWriter;
use RuntimeException;

/**
 * Stop the database save when the Upload plugin cannot write the file.
 *
 * DefaultWriter returns false on a failed file write; its behavior then leaves
 * filename/path unset and a database NOT NULL error hides the actual failure.
 */
class StrictWriter extends DefaultWriter
{
    public function write(array $files): array
    {
        $results = parent::write($files);
        if ($results === [] || in_array(false, $results, true)) {
            throw new RuntimeException(
                'File upload failed. Check the PHP upload limits and write access to data-files/FileManager.'
            );
        }

        return $results;
    }
}
