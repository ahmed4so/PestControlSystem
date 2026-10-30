<?php

declare(strict_types=1);

use App\Http\Responses\Auth\SameAnswerSpamResponder;
use Spatie\Honeypot\SpamProtection;

/*
| Bot protection on the public account forms: a hidden field and a minimum
| fill time, no outside service (03 §4, D-080).
*/

return [

    'enabled' => env('HONEYPOT_ENABLED', true),

    'name_field_name' => 'contact_details',

    'randomize_name_field_name' => true,

    'valid_from_timestamp' => true,

    'valid_from_field_name' => 'form_started_at',

    'amount_of_seconds' => 3,

    // A refused submission gets the form's normal answer.
    'respond_to_spam_with' => SameAnswerSpamResponder::class,

    // Forms behind the middleware must carry the fields.
    'honeypot_fields_required_for_all_forms' => true,

    'spam_protection' => SpamProtection::class,

    'with_csp' => false,

];
