import { Head, Link, router, usePage } from '@inertiajs/react';
import StatusMessage from '@/components/auth/status-message';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import {
    edit,
    index,
    resetTwoFactor,
    show,
    temporaryPassword,
    unlock,
} from '@/routes/admin/users';

type Props = {
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        status: 'ACTIVE' | 'INACTIVE';
        preferred_locale: string;
        roles: string[];
        locked: boolean;
        locked_until: string | null;
        two_factor_enabled: boolean;
        awaiting_first_login: boolean;
        last_login_at: string | null;
    };
};

/**
 * One staff user, with the account actions of the System Administrator:
 * unlock (D-081), 2FA reset (D-087) and a new temporary password (D-083).
 */
export default function ShowUser({ user }: Props) {
    const { t } = useTranslation();
    const { auth, flash } = usePage<{
        flash: { success?: string; error?: string };
    }>().props;
    const can = (permission: string) =>
        auth.permissions?.includes(permission) ?? false;
    const post = (url: string, question: string) => {
        if (window.confirm(question)) {
            router.post(url, {}, { preserveScroll: true });
        }
    };

    return (
        <AdminLayout
            breadcrumbs={[
                { title: t('Users'), href: index().url },
                { title: user.name, href: show(user.id).url },
            ]}
        >
            <Head title={user.name} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h1 className="text-2xl font-bold tracking-tight">
                        {user.name}
                    </h1>
                    {can('update user') && (
                        <Button asChild variant="outline">
                            <Link href={edit(user.id)}>{t('Edit')}</Link>
                        </Button>
                    )}
                </div>

                <StatusMessage status={flash?.success} />
                <StatusMessage status={flash?.error} tone="error" />

                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle className="flex flex-wrap gap-2">
                            {user.status === 'ACTIVE' ? (
                                <Badge>{t('Active')}</Badge>
                            ) : (
                                <Badge variant="destructive">
                                    {t('Inactive')}
                                </Badge>
                            )}
                            {user.locked && (
                                <Badge variant="destructive">
                                    {t('Locked')}
                                </Badge>
                            )}
                            {user.two_factor_enabled ? (
                                <Badge variant="secondary">
                                    {t('2FA enabled')}
                                </Badge>
                            ) : (
                                <Badge variant="outline">
                                    {t('2FA not set up')}
                                </Badge>
                            )}
                            {user.awaiting_first_login && (
                                <Badge variant="outline">
                                    {t('Awaiting first login')}
                                </Badge>
                            )}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <p>
                            <span className="text-muted-foreground">
                                {t('Email address')}:
                            </span>{' '}
                            {user.email}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Phone')}:
                            </span>{' '}
                            {user.phone ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Roles')}:
                            </span>{' '}
                            {user.roles.join(', ') || '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Last login')}:
                            </span>{' '}
                            {user.last_login_at ?? '—'}
                        </p>
                    </CardContent>
                </Card>

                <div className="flex flex-wrap gap-2">
                    {user.locked && can('unlock account') && (
                        <Button
                            variant="outline"
                            onClick={() =>
                                post(
                                    unlock.url(user.id),
                                    t('Unlock this account?'),
                                )
                            }
                        >
                            {t('Unlock account')}
                        </Button>
                    )}
                    {user.two_factor_enabled && can('reset two factor') && (
                        <Button
                            variant="destructive"
                            onClick={() =>
                                post(
                                    resetTwoFactor.url(user.id),
                                    t(
                                        'Reset two-factor authentication? Only do this after checking the person face to face.',
                                    ),
                                )
                            }
                        >
                            {t('Reset 2FA')}
                        </Button>
                    )}
                    {user.awaiting_first_login && can('create user') && (
                        <Button
                            variant="outline"
                            onClick={() =>
                                post(
                                    temporaryPassword.url(user.id),
                                    t('Send a new temporary password?'),
                                )
                            }
                        >
                            {t('Send a new temporary password')}
                        </Button>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
