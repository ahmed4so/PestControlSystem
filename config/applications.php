<?php

declare(strict_types=1);

return [

    /*
     * Days a never-submitted draft must stay untouched before the System
     * Administrator may discard it (D-193, D-194).
     */
    'stale_draft_days' => (int) env('STALE_DRAFT_DAYS', 30),

];
