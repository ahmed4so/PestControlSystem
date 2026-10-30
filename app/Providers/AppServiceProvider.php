<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\CompanyRegistry\Contracts\SubmissionState;
use App\Domain\CompanyRegistry\Support\NothingSubmittedYet;
use App\Domain\Identity\Support\PasswordPolicy;
use App\Domain\Notifications\Listeners\MarkNotificationSent;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No applications exist yet; Phase 4 binds the real state (D-073).
        $this->app->bind(SubmissionState::class, NothingSubmittedYet::class);
    }

    public function boot(): void
    {
        $this->configureDefaults();

        Event::listen(MessageSent::class, MarkNotificationSent::class);

        // File uploads: 30 per minute per user (03 §14).
        RateLimiter::for('uploads', fn (Request $request) => Limit::perMinute(30)
            ->by(($request->user()?->getMorphClass() ?? 'guest').'|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(app()->isProduction());

        Password::defaults(fn (): ?Password => PasswordPolicy::defaults());
    }
}
