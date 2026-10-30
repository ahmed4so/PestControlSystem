<?php

declare(strict_types=1);

namespace App\Domain\Documents\Exceptions;

use RuntimeException;

/**
 * A file refused by the upload checks (03 §12.2, D-089). The message is
 * an English key shown to the user through __().
 */
final class UnsafeUpload extends RuntimeException
{
    public static function type(): self
    {
        return new self('Upload a PDF, JPG or PNG file.');
    }

    public static function size(): self
    {
        return new self('The file is larger than 5 MB.');
    }

    public static function pdf(): self
    {
        return new self('This PDF cannot be accepted. Save it again without passwords or active content, then upload it.');
    }

    public static function image(): self
    {
        return new self('This image cannot be read. Take the photo again or choose another file.');
    }
}
