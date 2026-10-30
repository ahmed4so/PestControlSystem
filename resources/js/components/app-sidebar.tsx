import { Link, usePage } from '@inertiajs/react';
import { Building2, Home, MailWarning, Shield, Users } from 'lucide-react';
import { NavFooter } from '@/components/nav-footer';
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
import { useTranslation } from '@/lib/i18n';
import { dashboard } from '@/routes/admin';
import { index as failedEmails } from '@/routes/admin/failed-emails';
import { index as roles, permissions } from '@/routes/admin/roles';
import { index as users } from '@/routes/admin/users';
import type { NavItem } from '@/types';
import AppLogo from './app-logo';

export function AppSidebar() {
    const { auth } = usePage().props;
    const { t } = useTranslation();
    const can = (permission: string) =>
        auth.permissions?.includes(permission) ?? false;

    const mainNavItems: NavItem[] = [
        { title: t('Dashboard'), href: dashboard(), icon: Home },
        ...(can('view user')
            ? [{ title: t('Users'), href: users(), icon: Users }]
            : []),
        ...(can('assign permissions')
            ? [
                  {
                      title: t('Permissions'),
                      href: permissions(),
                      icon: Shield,
                  },
              ]
            : []),
        ...(can('view role')
            ? [{ title: t('Roles'), href: roles(), icon: Building2 }]
            : []),
        ...(can('manage system settings')
            ? [
                  {
                      title: t('Failed emails'),
                      href: failedEmails(),
                      icon: MailWarning,
                  },
              ]
            : []),
    ];

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
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={[]} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
