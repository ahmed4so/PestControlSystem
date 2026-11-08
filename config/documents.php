<?php

declare(strict_types=1);

/*
| Company files (D-017, D-056, D-089, D-180).
*/

return [

    // R2 in production and staging; local development may use 'local'.
    'disk' => env('DOCUMENTS_DISK', 'r2'),

    // Upload limit for company files, in kilobytes (D-017).
    'max_upload_kb' => 5120,

    // Types allowed for company files, detected from content (D-017, D-089).
    'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'],

    // Inspection videos: MP4 only, checked from the content (D-135, D-200).
    'max_video_kb' => 51200,

    // Photos are re-encoded no larger than this on the longest side.
    'photo_max_pixels' => 2000,

];
