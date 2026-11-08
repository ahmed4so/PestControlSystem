import type { InertiaLinkProps } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

/** A "← Back to …" link at the top of a sub-page. */
export default function BackLink({ href, children }: { href: NonNullable<InertiaLinkProps['href']>; children: string }) {
    return (
        <Link href={href} className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
            <ArrowLeft className="size-4" />
            {children}
        </Link>
    );
}
