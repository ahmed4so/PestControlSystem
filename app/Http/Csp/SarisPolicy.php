<?php

declare(strict_types=1);

namespace App\Http\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

/**
 * Nonce-based Content-Security-Policy (D-090, 03-auth-security §12.3).
 * Scripts and style elements need the per-request nonce; inline style
 * attributes are allowed because server-rendered React pages use them.
 * Fonts are self-hosted, so no outside host is allowed (D-157).
 */
class SarisPolicy implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::FRAME_ANCESTORS, Keyword::NONE)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::CONNECT, Keyword::SELF)
            ->add(Directive::IMG, [Keyword::SELF, 'data:', 'blob:'])
            ->add(Directive::FONT, Keyword::SELF)
            ->add(Directive::SCRIPT, Keyword::SELF)
            ->addNonce(Directive::SCRIPT)
            ->add(Directive::STYLE_ELEM, Keyword::SELF)
            ->addNonce(Directive::STYLE_ELEM)
            ->add(Directive::STYLE_ATTR, Keyword::UNSAFE_INLINE);
    }
}
