<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Applications\Support\ApplicationSubmissionState;
use App\Domain\CompanyRegistry\Contracts\SubmissionState;
use App\Domain\Identity\Support\PasswordPolicy;
use App\Domain\Notifications\Actions\NotifyWorkflowEvent;
use App\Domain\Notifications\Listeners\MarkNotificationSent;
use App\Domain\Workflow\Registry\WorkflowRegistry;
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
        // The company data edit lock follows the applications (D-073, 02 §6.4).
        $this->app->bind(SubmissionState::class, ApplicationSubmissionState::class);

        // One catalogue of guards and effects; later phases extend it (D-188).
        $this->app->singleton(WorkflowRegistry::class);
        $this->app->singleton(NotifyWorkflowEvent::class);
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
