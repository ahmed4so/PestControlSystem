<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Enums;

/**
 * Inspection media (02 §4.8, D-135).
 */
enum MediaType: string
{
    case Photo = 'PHOTO';
    case Video = 'VIDEO';
}
