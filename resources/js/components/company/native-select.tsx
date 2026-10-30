import type { SelectHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

/**
 * A plain select that works well on basic phones and with Inertia forms.
 */
export default function NativeSelect({
    className,
    children,
    ...props
}: SelectHTMLAttributes<HTMLSelectElement>) {
    return (
        <select
            className={cn(
                'h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs disabled:opacity-50',
                className,
            )}
            {...props}
        >
            {children}
        </select>
    );
}
