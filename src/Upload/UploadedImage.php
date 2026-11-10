<?php

declare(strict_types=1);

namespace App\Upload;

use GdImage;

/**
 * Saves an uploaded profile photo or logo. The upload must be a JPEG, PNG
 * or GIF image. The class decodes the image and writes a new copy, so
 * nothing else from the uploaded file reaches the uploads folder. The
 * extension comes from the image type, not from the uploaded file name.
 * The PDF headers read the logo, and they handle these three types.
 */
final class UploadedImage
{
    /** The extension each accepted image type is saved with. */
    private const EXTENSIONS = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
    ];

    /** The largest image, in pixels, that the class decodes. */
    private const MAX_PIXELS = 40_000_000;

    /**
     * Saves the upload as "$name.<extension>" in $folder, scaled down to fit
     * $maxWidth by $maxHeight if both are given.
     *
     * @param mixed $file One entry of $_FILES.
     * @return string|null The saved file name, or null if the upload is not
     *                     an accepted image or the copy fails.
     */
    public static function save(mixed $file, string $folder, string $name, int $maxWidth = 0, int $maxHeight = 0): ?string
    {
        if (!is_array($file) || ($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            return null;
        }
        $size = @getimagesize($file['tmp_name']);
        $extension = $size === false ? null : (self::EXTENSIONS[$size[2]] ?? null);
        if ($extension === null || $size[0] < 1 || $size[1] < 1 || $size[0] * $size[1] > self::MAX_PIXELS) {
            error_log('Refused an upload that is not a JPEG, PNG or GIF image: ' . ($file['name'] ?? ''));
            return null;
        }
        $image = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));
        if ($image === false) {
            error_log('Could not read the uploaded image: ' . ($file['name'] ?? ''));
            return null;
        }
        if ($maxWidth > 0 && $maxHeight > 0) {
            $image = self::fit($image, $maxWidth, $maxHeight);
        }

        $fileName = $name . '.' . $extension;
        $path = $folder . DIRECTORY_SEPARATOR . $fileName;
        imagesavealpha($image, true);
        $saved = match ($extension) {
            'jpg' => imagejpeg($image, $path, 90),
            'png' => imagepng($image, $path),
            'gif' => imagegif($image, $path),
        };

        return $saved ? $fileName : null;
    }

    /** The image scaled down, keeping its shape, to fit the box. A smaller image stays as it is. */
    private static function fit(GdImage $image, int $maxWidth, int $maxHeight): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min($maxWidth / $width, $maxHeight / $height);
        if ($scale >= 1) {
            return $image;
        }
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }
}
