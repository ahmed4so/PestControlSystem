import { Head, router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, Shield, UserCircle } from 'lucide-react';
import { useState } from 'react';
import AlertSuccess from '@/components/alert-success';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/command';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import AdminLayout from '@/layouts/admin-layout';
import { cn } from '@/lib/utils';
import { assign, index } from '@/routes/admin/roles';
import * as assignRoutes from '@/routes/admin/roles/assign';
import { type BreadcrumbItem, type Role, type User } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: index().url,
    },
    {
        title: 'Assign Roles',
        href: assign().url,
    },
];

interface UserWithRoles extends User {
    roles?: Role[];
}

interface AssignRolesProps {
    users: UserWithRoles[];
    roles: Role[];
}

export default function AssignRoles({ users, roles }: AssignRolesProps) {
    const [open, setOpen] = useState(false);
    const [selectedUser, setSelectedUser] = useState<UserWithRoles | null>(
        null,
    );
    const [selectedRoles, setSelectedRoles] = useState<number[]>([]);
    const [processing, setProcessing] = useState(false);
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>()
        .props;

    const handleUserSelect = (user: UserWithRoles) => {
        setSelectedUser(user);
        // Load the user's current roles
        setSelectedRoles(user.roles?.map((r) => r.id) ?? []);
        setOpen(false);
    };

    const handleRoleToggle = (roleId: number) => {
        setSelectedRoles((prev) =>
            prev.includes(roleId)
                ? prev.filter((id) => id !== roleId)
                : [...prev, roleId],
        );
    };

    const handleSelectAll = () => {
        const allRoleIds = roles.map((r) => r.id);
        const allSelected = allRoleIds.every((id) =>
            selectedRoles.includes(id),
        );

        if (allSelected) {
            // Deselect all
            setSelectedRoles([]);
        } else {
            // Select all
            setSelectedRoles(allRoleIds);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (!selectedUser) {
            return;
        }

        setProcessing(true);

        router.post(
            assignRoutes.store.url(),
            {
                user_id: selectedUser.id,
                role_ids: selectedRoles,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setProcessing(false);
                },
                onError: () => {
                    setProcessing(false);
                },
            },
        );
    };

    const isAllRolesSelected = (): boolean => {
        return roles.every((r) => selectedRoles.includes(r.id));
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Assign Roles to Users" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Assign Roles to Users
                    </h1>
                    <p className="text-muted-foreground">
                        Select a user and assign roles to them
                    </p>
                </div>

                {flash?.success && <AlertSuccess message={flash.success} />}

                <Card className="max-w-4xl">
                    <CardHeader>
                        <CardTitle>User Selection</CardTitle>
                        <CardDescription>
                            Choose a user to manage their roles
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="user">
                                    Select User{' '}
                                    <span className="text-red-600">*</span>
                                </Label>
                                <Popover open={open} onOpenChange={setOpen}>
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            aria-expanded={open}
                                            className="w-full justify-between"
                                        >
                                            {selectedUser ? (
                                                <span className="flex items-center gap-2">
                                                    <UserCircle className="size-4" />
                                                    <span>
                                                        {selectedUser.name}
                                                        <span className="ml-2 text-sm text-muted-foreground">
                                                            (
                                                            {selectedUser.email}
                                                            )
                                                        </span>
                                                    </span>
                                                </span>
                                            ) : (
                                                'Select user...'
                                            )}
                                            <ChevronsUpDown className="ml-2 size-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-[400px] p-0">
                                        <Command>
                                            <CommandInput placeholder="Search user..." />
                                            <CommandList>
                                                <CommandEmpty>
                                                    No user found.
                                                </CommandEmpty>
                                                <CommandGroup>
                                                    {users.map((user) => (
                                                        <CommandItem
                                                            key={user.id}
                                                            value={`${user.name} ${user.email}`}
                                                            onSelect={() =>
                                                                handleUserSelect(
                                                                    user,
                                                                )
                                                            }
                                                        >
                                                            <Check
                                                                className={cn(
                                                                    'mr-2 size-4',
                                                                    selectedUser?.id ===
                                                                        user.id
                                                                        ? 'opacity-100'
                                                                        : 'opacity-0',
                                                                )}
                                                            />
                                                            <UserCircle className="mr-2 size-4" />
                                                            <div className="flex flex-col">
                                                                <span>
                                                                    {user.name}
                                                                </span>
                                                                <span className="text-xs text-muted-foreground">
                                                                    {user.email}
                                                                </span>
                                                            </div>
                                                        </CommandItem>
                                                    ))}
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                            </div>

                            {selectedUser && (
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <Label className="text-base font-semibold">
                                            Roles
                                        </Label>
                                        <div className="flex items-center gap-3">
                                            <p className="text-sm text-muted-foreground">
                                                {selectedRoles.length} of{' '}
                                                {roles.length} selected
                                            </p>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={handleSelectAll}
                                                className="h-auto p-1 text-xs"
                                            >
                                                {isAllRolesSelected()
                                                    ? 'Deselect All'
                                                    : 'Select All'}
                                            </Button>
                                        </div>
                                    </div>

                                    {roles.length === 0 ? (
                                        <Card className="border-dashed">
                                            <CardContent className="flex flex-col items-center justify-center py-8">
                                                <Shield className="mb-3 size-10 text-muted-foreground" />
                                                <p className="text-center text-sm text-muted-foreground">
                                                    No roles available. Please
                                                    create roles first.
                                                </p>
                                            </CardContent>
                                        </Card>
                                    ) : (
                                        <Card>
                                            <CardContent className="space-y-3 pt-6">
                                                {roles.map((role) => (
                                                    <div
                                                        key={role.id}
                                                        className="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent/50"
                                                    >
                                                        <Checkbox
                                                            id={`role-${role.id}`}
                                                            checked={selectedRoles.includes(
                                                                role.id,
                                                            )}
                                                            onCheckedChange={() =>
                                                                handleRoleToggle(
                                                                    role.id,
                                                                )
                                                            }
                                                        />
                                                        <Label
                                                            htmlFor={`role-${role.id}`}
                                                            className="flex-1 cursor-pointer text-sm font-normal"
                                                        >
                                                            <div className="flex items-center gap-2">
                                                                <Shield className="size-4 text-muted-foreground" />
                                                                <span className="font-medium">
                                                                    {role.name}
                                                                </span>
                                                            </div>
                                                        </Label>
                                                    </div>
                                                ))}
                                            </CardContent>
                                        </Card>
                                    )}

                                    <div className="flex gap-2 pt-4">
                                        <Button
                                            type="submit"
                                            disabled={
                                                processing || roles.length === 0
                                            }
                                        >
                                            {processing
                                                ? 'Saving...'
                                                : 'Save Roles'}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => {
                                                setSelectedUser(null);
                                                setSelectedRoles([]);
                                            }}
                                        >
                                            Reset
                                        </Button>
                                    </div>
                                </div>
                            )}

                            {!selectedUser && (
                                <Card className="border-dashed">
                                    <CardContent className="flex flex-col items-center justify-center py-12">
                                        <UserCircle className="mb-4 size-12 text-muted-foreground" />
                                        <p className="text-center text-sm text-muted-foreground">
                                            Select a user from the dropdown
                                            above to manage their roles
                                        </p>
                                    </CardContent>
                                </Card>
                            )}
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
