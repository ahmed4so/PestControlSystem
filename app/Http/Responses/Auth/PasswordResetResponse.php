<?php

declare(strict_types=1);

namespace App\Http\Responses\Auth;

use App\Domain\Identity\Enums\AuthArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;

/**
 * After a successful reset, go to the current area's login page (D-079).
 */
final class PasswordResetResponse implements PasswordResetResponseContract
{
    public function __construct(private readonly string $status) {}

    /**
     * @param  Request  $request
     */
    public function toResponse($request): RedirectResponse
    {
        $area = AuthArea::current($request) ?? AuthArea::Company;

        return redirect()->route($area->route('login'))->with('status', trans($this->status));
    }
}
