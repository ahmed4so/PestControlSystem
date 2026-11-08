import { Link, usePage } from '@inertiajs/react';
import { Building, Building2, Fingerprint, Home, Inbox, MailWarning, MapPin, Settings, Shield, UserCog, Users } from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useTranslation } from '@/lib/i18n';
import { dashboard, queues, settings } from '@/routes/admin';
import { index as companies } from '@/routes/admin/companies';
import { index as failedEmails } from '@/routes/admin/failed-emails';
import { show as nationalIds } from '@/routes/admin/national-ids';
import { index as fms } from '@/routes/admin/reference-data/fms';
import { assign as assignRoles, index as roles, permissions } from '@/routes/admin/roles';
import { index as users } from '@/routes/admin/users';
import type { NavItem } from '@/types';
import AppLogo from './app-logo';

/**
 * The Admin Portal menu, in sections. Each entry shows only when the
 * staff member holds its permission (the server checks it again).
 */
export function AppSidebar() {
    const { auth } = usePage().props;
    const { t } = useTranslation();
    const { activeSection } = useCurrentUrl();
    const can = (permission: string) => auth.permissions?.includes(permission) ?? false;
    const when = (condition: boolean, item: NavItem): NavItem[] => (condition ? [item] : []);

    const sections: { label: string; items: NavItem[] }[] = [
        {
            label: t('Overview'),
            items: [{ title: t('Dashboard'), href: dashboard(), icon: Home }],
        },
        {
            label: t('Applications'),
            items: [
                ...when(can('view applications') || can('view assigned applications'), {
                    title: t('Work queues'),
                    href: queues(),
                    icon: Inbox,
                    match: ['/admin/applications', '/admin/inspections'],
                }),
                ...when(can('view applications'), { title: t('Companies'), href: companies(), icon: Building }),
            ],
        },
        {
            label: t('Staff and access'),
            items: [
                ...when(can('view user'), { title: t('Users'), href: users(), icon: Users }),
                ...when(can('view role'), { title: t('Roles'), href: roles(), icon: Building2 }),
                ...when(can('assign role'), { title: t('Assign roles'), href: assignRoles(), icon: UserCog }),
                ...when(can('assign permissions'), { title: t('Permissions'), href: permissions(), icon: Shield }),
            ],
        },
        {
            label: t('System'),
            items: [
                ...when(can('release national id'), { title: t('National-ID release'), href: nationalIds(), icon: Fingerprint }),
                ...when(can('manage reference data'), { title: t('Reference data'), href: fms(), icon: MapPin }),
                ...when(can('manage system settings'), { title: t('Failed emails'), href: failedEmails(), icon: MailWarning }),
            ],
        },
        {
            label: t('My account'),
            items: [{ title: t('Settings'), href: settings(), icon: Settings, match: ['/admin/settings'] }],
        },
    ];

    const active = activeSection(sections.flatMap((section) => section.items));
    let position = 0;
    const marked = sections.map((section) => ({
        ...section,
        items: section.items.map((item) => ({ ...item, isActive: position++ === active })),
    }));

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {marked.map((section) => (
                    <NavMain key={section.label} label={section.label} items={section.items} />
                ))}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
