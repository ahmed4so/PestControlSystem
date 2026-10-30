/**
 * Shrinks a photo on the phone before upload (09 Phase 2, D-089): the
 * longest side becomes at most 1600 px and the image is saved as JPEG.
 * If the browser cannot do it, the original file is returned; the server
 * still checks and re-encodes every image.
 */
export async function compressImage(
    file: File,
    maxSide = 1600,
    quality = 0.8,
): Promise<File> {
    if (!file.type.startsWith('image/') || typeof createImageBitmap === 'undefined') {
        return file;
    }

    try {
        const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
        const scale = Math.min(1, maxSide / Math.max(bitmap.width, bitmap.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);
        canvas.getContext('2d')?.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        bitmap.close();

        const blob = await new Promise<Blob | null>((resolve) =>
            canvas.toBlob(resolve, 'image/jpeg', quality),
        );

        if (!blob || blob.size >= file.size) {
            return file;
        }

        const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';

        return new File([blob], name, { type: 'image/jpeg', lastModified: file.lastModified });
    } catch {
        return file;
    }
}
