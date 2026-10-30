/**
 * A short notice above a form (e.g. "Check your email").
 */
export default function StatusMessage({
    status,
    tone = 'success',
}: {
    status?: string | null;
    tone?: 'success' | 'error';
}) {
    if (!status) {
        return null;
    }

    return (
        <div
            role="status"
            className={
                tone === 'success'
                    ? 'rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200'
                    : 'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200'
            }
        >
            {status}
        </div>
    );
}
