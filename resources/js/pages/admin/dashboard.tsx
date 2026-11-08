import { Head, Link, usePage } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { Building, Building2, Fingerprint, Inbox, MailWarning, MapPin, Settings, Shield, UserCog, UserPlus, Users } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { dashboard, queues, settings } from '@/routes/admin';
import { index as companies } from '@/routes/admin/companies';
import { index as failedEmails } from '@/routes/admin/failed-emails';
import { show as nationalIds } from '@/routes/admin/national-ids';
import { index as fms } from '@/routes/admin/reference-data/fms';
import { assign as assignRoles, index as roles, permissions } from '@/routes/admin/roles';
import { create as createUser, index as users } from '@/routes/admin/users';

type Props = {
    roles: string[];
    myWork: number | null;
    workflowRoles: string[];
};

type Shortcut = { title: string; description: string; href: ReturnType<typeof dashboard>; icon: LucideIcon; show: boolean };

/**
 * Staff dashboard: what this member can do, with a shortcut to each page
 * their permissions open. Application work needs a workflow role; the
 * System Administrator role alone has none (D-023).
 */
export default function Dashboard({ roles: myRoles, myWork, workflowRoles }: Props) {
    const { t } = useTranslation();
    const { auth, flash } = usePage<{ flash: { success?: string } }>().props;
    const can = (permission: string) => auth.permissions?.includes(permission) ?? false;
    const doesApplicationWork = myWork !== null;

    const shortcuts: Shortcut[] = [
        { title: t('Work queues'), description: t('Applications waiting for you and for your stage.'), href: queues(), icon: Inbox, show: doesApplicationWork },
        { title: t('Companies'), description: t('Company profiles, persons, premises and documents.'), href: companies(), icon: Building, show: can('view applications') },
        { title: t('Users'), description: t('Create staff accounts, give roles, unlock and deactivate.'), href: users(), icon: Users, show: can('view user') },
        { title: t('Roles'), description: t('The staff roles and what each may do.'), href: roles(), icon: Building2, show: can('view role') },
        { title: t('Assign roles'), description: t('Give roles to staff members.'), href: assignRoles(), icon: UserCog, show: can('assign role') },
        { title: t('Permissions'), description: t('Permissions of each role.'), href: permissions(), icon: Shield, show: can('assign permissions') },
        { title: t('National-ID release'), description: t('Free a national ID held by another company.'), href: nationalIds(), icon: Fingerprint, show: can('release national id') },
        { title: t('Reference data'), description: t('Federal member states and regions.'), href: fms(), icon: MapPin, show: can('manage reference data') },
        { title: t('Failed emails'), description: t('Emails that could not be sent.'), href: failedEmails(), icon: MailWarning, show: can('manage system settings') },
        { title: t('Settings'), description: t('Your profile, password and two-factor authentication.'), href: settings(), icon: Settings, show: true },
    ];

    return (
        <AdminLayout breadcrumbs={[{ title: t('Dashboard'), href: dashboard().url }]}>
            <Head title={t('Dashboard')} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                {flash?.success && (
                    <div role="status" className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200">
                        {flash.success}
                    </div>
                )}

                <div className="space-y-2">
                    <h1 className="text-2xl font-semibold">{t('Welcome, :name', { name: auth.user?.name ?? '' })}</h1>
                    <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                        <span>{t('Your roles:')}</span>
                        {myRoles.length === 0 ? <span>{t('none')}</span> : myRoles.map((role) => <Badge key={role} variant="secondary">{t(role)}</Badge>)}
                    </div>
                </div>

                {doesApplicationWork && (
                    <Link href={queues()} className="flex items-center justify-between rounded-xl border p-4 hover:bg-muted/50">
                        <div>
                            <p className="font-medium">{t('My work')}</p>
                            <p className="text-sm text-muted-foreground">{t('Applications and inspections waiting for you.')}</p>
                        </div>
                        <span className="text-3xl font-semibold">{myWork}</span>
                    </Link>
                )}

                {!doesApplicationWork && (
                    <div className="space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">
                        <p className="font-medium">{t('Applications are handled by staff with a workflow role')}</p>
                        <p>{t('Your roles do not include application work, so you do not see applications or work queues. Create a staff user for each role below; they receive a temporary password by email.')}</p>
                        <div className="flex flex-wrap gap-2">
                            {workflowRoles.map((role) => (
                                <Badge key={role} variant="outline">
                                    {t(role)}
                                </Badge>
                            ))}
                        </div>
                        {can('create user') && (
                            <Button asChild size="sm">
                                <Link href={createUser()}>
                                    <UserPlus />
                                    {t('Create a staff user')}
                                </Link>
                            </Button>
                        )}
                    </div>
                )}

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {shortcuts
                        .filter((shortcut) => shortcut.show)
                        .map((shortcut) => (
                            <Link key={shortcut.title} href={shortcut.href} className="flex gap-3 rounded-xl border p-4 hover:bg-muted/50">
                                <shortcut.icon className="mt-0.5 size-5 shrink-0 text-muted-foreground" />
                                <div>
                                    <p className="font-medium">{shortcut.title}</p>
                                    <p className="text-sm text-muted-foreground">{shortcut.description}</p>
                                </div>
                            </Link>
                        ))}
                </div>
            </div>
        </AdminLayout>
    );
}
