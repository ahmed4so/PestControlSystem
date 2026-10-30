<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Models\CompanyUser;
use Illuminate\Support\Facades\DB;

/**
 * Applies a login-email change when its link is used (D-093). Returns
 * false for an unknown, used or expired token, or when the new address was
 * taken by another account in the meantime.
 */
final class ConfirmLoginEmailChange
{
    public function __invoke(string $token): bool
    {
        return DB::transaction(function () use ($token): bool {
            $account = CompanyUser::query()
                ->where('pending_email_token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if ($account === null || $account->pending_email === null) {
                return false;
            }

            $expired = $account->pending_email_expires_at === null || $account->pending_email_expires_at->isPast();
            $taken = CompanyUser::withTrashed()
                ->where('email', $account->pending_email)
                ->whereKeyNot($account->getKey())
                ->exists();

            if ($expired || $taken) {
                $account->forceFill([
                    'pending_email' => null,
                    'pending_email_token_hash' => null,
                    'pending_email_expires_at' => null,
                ])->saveQuietly();

                return false;
            }

            $account->forceFill([
                'email' => $account->pending_email,
                'pending_email' => null,
                'pending_email_token_hash' => null,
                'pending_email_expires_at' => null,
            ])->save();

            AuditLog::record('email_changed', 'Login email changed', $account, $account);

            return true;
        });
    }
}
