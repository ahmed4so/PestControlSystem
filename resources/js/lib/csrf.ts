import { router } from '@inertiajs/react';

/**
 * Sends the current page's CSRF token with every Inertia request
 * (D-173). Each portal sets its own XSRF-TOKEN cookie, and on admin pages
 * the browser holds both; which cookie a library reads first is not
 * guaranteed. Laravel checks the X-CSRF-TOKEN header before the cookie, so
 * the token of the page's own session always wins.
 */
let token: string | undefined;

function tokenOf(props: Record<string, unknown> | undefined): string | undefined {
    const value = props?.csrf_token;

    return typeof value === 'string' ? value : undefined;
}

export function sendCsrfTokenWithRequests(initialProps: Record<string, unknown>): void {
    token = tokenOf(initialProps);

    // Every response carries the session's current token (it changes on login).
    router.on('navigate', (event) => {
        token = tokenOf(event.detail.page.props) ?? token;
    });

    router.on('before', (event) => {
        if (token) {
            event.detail.visit.headers['X-CSRF-TOKEN'] = token;
        }
    });
}
