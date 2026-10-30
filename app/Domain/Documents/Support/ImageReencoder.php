<?php

declare(strict_types=1);

namespace App\Domain\Documents\Support;

use App\Domain\Documents\Exceptions\UnsafeUpload;
use GdImage;

/**
 * Decodes and re-encodes JPEG and PNG images with GD (03 §12.2, D-089).
 * This drops EXIF and other metadata and anything hidden after the image
 * data. Large photos are scaled down to the configured longest side.
 */
final class ImageReencoder
{
    /**
     * @return array{contents: string, mime: string}
     */
    public function reencode(string $contents, string $mime): array
    {
        $image = @imagecreatefromstring($contents);

        if (! $image instanceof GdImage) {
            throw UnsafeUpload::image();
        }

        $image = $this->scaleDown($image);

        ob_start();
        $mime === 'image/png' ? imagepng($image, null, 6) : imagejpeg($image, null, 82);
        $output = (string) ob_get_clean();
        imagedestroy($image);

        if ($output === '') {
            throw UnsafeUpload::image();
        }

        return ['contents' => $output, 'mime' => $mime === 'image/png' ? 'image/png' : 'image/jpeg'];
    }

    private function scaleDown(GdImage $image): GdImage
    {
        $max = (int) config('documents.photo_max_pixels', 2000);
        $width = imagesx($image);
        $height = imagesy($image);

        if (max($width, $height) <= $max) {
            return $image;
        }

        $scale = $max / max($width, $height);
        $scaled = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));

        return $scaled instanceof GdImage ? $scaled : $image;
    }
}
