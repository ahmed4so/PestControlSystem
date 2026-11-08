import type { InertiaLinkProps } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { toUrl } from '@/lib/utils';

export type IsCurrentUrlFn = (
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl?: string,
) => boolean;

export type WhenCurrentUrlFn = <TIfTrue, TIfFalse = null>(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    ifTrue: TIfTrue,
    ifFalse?: TIfFalse,
) => TIfTrue | TIfFalse;

export type UseCurrentUrlReturn = {
    currentUrl: string;
    isCurrentUrl: IsCurrentUrlFn;
    activeSection: (items: { href: NonNullable<InertiaLinkProps['href']>; match?: string[] }[]) => number;
    whenCurrentUrl: WhenCurrentUrlFn;
};

export function useCurrentUrl(): UseCurrentUrlReturn {
    const page = usePage();
    const currentUrlPath = new URL(page.url, window?.location.origin).pathname;

    const isCurrentUrl: IsCurrentUrlFn = (
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        currentUrl?: string,
    ) => {
        const urlToCompare = currentUrl ?? currentUrlPath;
        const urlString = toUrl(urlToCheck);

        if (!urlString.startsWith('http')) {
            return urlString === urlToCompare;
        }

        try {
            const absoluteUrl = new URL(urlString);
            return absoluteUrl.pathname === urlToCompare;
        } catch {
            return false;
        }
    };

    // The menu entry for the current page: the one whose path (or one of its
    // `match` paths) is the longest prefix of the current URL, so
    // /portal/applications is active on /portal/applications/1. A portal's
    // root page (one path segment, the dashboard) is active only on itself.
    const activeSection = (
        items: { href: NonNullable<InertiaLinkProps['href']>; match?: string[] }[],
    ): number => {
        const pathOf = (url: string): string => {
            try {
                return url.startsWith('http') ? new URL(url).pathname : url.split('?')[0];
            } catch {
                return url;
            }
        };
        let best = -1;
        let bestLength = -1;

        items.forEach((item, index) => {
            for (const candidate of [pathOf(toUrl(item.href)), ...(item.match ?? [])]) {
                const path = candidate.replace(/\/$/, '');
                const isRoot = path.split('/').filter(Boolean).length <= 1;
                const hit = currentUrlPath === path || (!isRoot && currentUrlPath.startsWith(`${path}/`));

                if (hit && path.length > bestLength) {
                    best = index;
                    bestLength = path.length;
                }
            }
        });

        return best;
    };

    const whenCurrentUrl: WhenCurrentUrlFn = <TIfTrue, TIfFalse = null>(
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        ifTrue: TIfTrue,
        ifFalse: TIfFalse = null as TIfFalse,
    ): TIfTrue | TIfFalse => {
        return isCurrentUrl(urlToCheck) ? ifTrue : ifFalse;
    };

    return {
        currentUrl: currentUrlPath,
        isCurrentUrl,
        activeSection,
        whenCurrentUrl,
    };
}
