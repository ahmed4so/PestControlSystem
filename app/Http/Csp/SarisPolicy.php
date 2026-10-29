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
 */
class SarisPolicy implements Preset
{
    // Removed when the fonts are self-hosted in Phase 1 (D-157).
    private const FONTS = 'https://fonts.bunny.net';

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
            ->add(Directive::FONT, [Keyword::SELF, self::FONTS])
            ->add(Directive::SCRIPT, Keyword::SELF)
            ->addNonce(Directive::SCRIPT)
            ->add(Directive::STYLE_ELEM, [Keyword::SELF, self::FONTS])
            ->addNonce(Directive::STYLE_ELEM)
            ->add(Directive::STYLE_ATTR, Keyword::UNSAFE_INLINE);
    }
}
