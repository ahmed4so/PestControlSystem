import { Link, usePage } from '@inertiajs/react';
import { Building2, Home, Shield, Users } from 'lucide-react';
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
import { dashboard } from '@/routes';
import { index as users } from '@/routes/users';
import type { NavItem } from '@/types';
import AppLogo from './app-logo';

export function AppSidebar() {
    const { auth } = usePage().props;
    const can = (permission: string) =>
        auth.permissions?.includes(permission) ?? false;

    const mainNavItems: NavItem[] = [
        { title: 'Dashboard', href: dashboard(), icon: Home },
        ...(can('view user')
            ? [{ title: 'Users', href: users(), icon: Users }]
            : []),
        ...(can('assign permissions')
            ? [
                  {
                      title: 'Permissions',
                      href: '/roles/permissions',
                      icon: Shield,
                  },
              ]
            : []),
        ...(can('view role')
            ? [{ title: 'Roles', href: '/roles', icon: Building2 }]
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
