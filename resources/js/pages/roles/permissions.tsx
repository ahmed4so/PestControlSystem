import AlertSuccess from '@/components/alert-success';
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
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Permission, type Role } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, Shield } from 'lucide-react';
import { useEffect, useState } from 'react';
import { cn } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/roles',
    },
    {
        title: 'Permissions',
        href: '/roles/permissions',
    },
];

interface GroupedPermissions {
    [key: string]: Permission[];
}

interface RolePermissionsProps {
    roles: Role[];
    permissions: GroupedPermissions;
}

export default function RolePermissions({
    roles,
    permissions,
}: RolePermissionsProps) {
    const [open, setOpen] = useState(false);
    const [selectedRole, setSelectedRole] = useState<Role | null>(null);
    const [selectedPermissions, setSelectedPermissions] = useState<number[]>(
        []
    );
    const [processing, setProcessing] = useState(false);
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>()
        .props;

    // Load permissions when role is selected
    useEffect(() => {
        if (selectedRole && selectedRole.permissions) {
            setSelectedPermissions(
                selectedRole.permissions.map((p) => p.id)
            );
        } else {
            setSelectedPermissions([]);
        }
    }, [selectedRole]);

    const handleRoleSelect = (role: Role) => {
        setSelectedRole(role);
        setOpen(false);
    };

    const handlePermissionToggle = (permissionId: number) => {
        setSelectedPermissions((prev) =>
            prev.includes(permissionId)
                ? prev.filter((id) => id !== permissionId)
                : [...prev, permissionId]
        );
    };

    const handleSelectAll = (modulePermissions: Permission[]) => {
        const modulePermissionIds = modulePermissions.map((p) => p.id);
        const allSelected = modulePermissionIds.every((id) =>
            selectedPermissions.includes(id)
        );

        if (allSelected) {
            // Deselect all
            setSelectedPermissions((prev) =>
                prev.filter((id) => !modulePermissionIds.includes(id))
            );
        } else {
            // Select all
            setSelectedPermissions((prev) => [
                ...new Set([...prev, ...modulePermissionIds]),
            ]);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (!selectedRole) {
            return;
        }

        setProcessing(true);

        router.post(
            '/roles/permissions',
            {
                role_id: selectedRole.id,
                permission_ids: selectedPermissions,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setProcessing(false);
                },
                onError: () => {
                    setProcessing(false);
                },
            }
        );
    };

    const getModuleName = (module: string): string => {
        return module
            .split('_')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    };

    const isModuleFullySelected = (modulePermissions: Permission[]): boolean => {
        return modulePermissions.every((p) =>
            selectedPermissions.includes(p.id)
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Role Permissions" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Manage Role Permissions
                    </h1>
                    <p className="text-muted-foreground">
                        Select a role and assign permissions to it
                    </p>
                </div>

                {flash?.success && <AlertSuccess message={flash.success} />}

                <Card className="max-w-4xl">
                    <CardHeader>
                        <CardTitle>Role Selection</CardTitle>
                        <CardDescription>
                            Choose a role to manage its permissions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="role">
                                    Select Role{' '}
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
                                            {selectedRole ? (
                                                <span className="flex items-center gap-2">
                                                    <Shield className="size-4" />
                                                    {selectedRole.name}
                                                </span>
                                            ) : (
                                                'Select role...'
                                            )}
                                            <ChevronsUpDown className="ml-2 size-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-[400px] p-0">
                                        <Command>
                                            <CommandInput placeholder="Search role..." />
                                            <CommandList>
                                                <CommandEmpty>
                                                    No role found.
                                                </CommandEmpty>
                                                <CommandGroup>
                                                    {roles.map((role) => (
                                                        <CommandItem
                                                            key={role.id}
                                                            value={role.name}
                                                            onSelect={() =>
                                                                handleRoleSelect(
                                                                    role
                                                                )
                                                            }
                                                        >
                                                            <Check
                                                                className={cn(
                                                                    'mr-2 size-4',
                                                                    selectedRole?.id ===
                                                                        role.id
                                                                        ? 'opacity-100'
                                                                        : 'opacity-0'
                                                                )}
                                                            />
                                                            <Shield className="mr-2 size-4" />
                                                            {role.name}
                                                        </CommandItem>
                                                    ))}
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                            </div>

                            {selectedRole && (
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <Label className="text-base font-semibold">
                                            Permissions
                                        </Label>
                                        <p className="text-sm text-muted-foreground">
                                            {selectedPermissions.length} of{' '}
                                            {Object.values(permissions)
                                                .flat()
                                                .length}{' '}
                                            selected
                                        </p>
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        {Object.entries(permissions).map(
                                            ([module, modulePermissions]) => (
                                                <Card key={module}>
                                                    <CardHeader className="pb-3">
                                                        <div className="flex items-center justify-between">
                                                            <CardTitle className="text-base">
                                                                {getModuleName(
                                                                    module
                                                                )}
                                                            </CardTitle>
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() =>
                                                                    handleSelectAll(
                                                                        modulePermissions
                                                                    )
                                                                }
                                                                className="h-auto p-1 text-xs"
                                                            >
                                                                {isModuleFullySelected(
                                                                    modulePermissions
                                                                )
                                                                    ? 'Deselect All'
                                                                    : 'Select All'}
                                                            </Button>
                                                        </div>
                                                    </CardHeader>
                                                    <CardContent className="space-y-3">
                                                        {modulePermissions.map(
                                                            (permission) => (
                                                                <div
                                                                    key={
                                                                        permission.id
                                                                    }
                                                                    className="flex items-center gap-2"
                                                                >
                                                                    <Checkbox
                                                                        id={`permission-${permission.id}`}
                                                                        checked={selectedPermissions.includes(
                                                                            permission.id
                                                                        )}
                                                                        onCheckedChange={() =>
                                                                            handlePermissionToggle(
                                                                                permission.id
                                                                            )
                                                                        }
                                                                    />
                                                                    <Label
                                                                        htmlFor={`permission-${permission.id}`}
                                                                        className="cursor-pointer text-sm font-normal capitalize"
                                                                    >
                                                                        {
                                                                            permission.name
                                                                        }
                                                                    </Label>
                                                                </div>
                                                            )
                                                        )}
                                                    </CardContent>
                                                </Card>
                                            )
                                        )}
                                    </div>

                                    <div className="flex gap-2 pt-4">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Saving...'
                                                : 'Save Permissions'}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => {
                                                setSelectedRole(null);
                                                setSelectedPermissions([]);
                                            }}
                                        >
                                            Reset
                                        </Button>
                                    </div>
                                </div>
                            )}

                            {!selectedRole && (
                                <Card className="border-dashed">
                                    <CardContent className="flex flex-col items-center justify-center py-12">
                                        <Shield className="mb-4 size-12 text-muted-foreground" />
                                        <p className="text-center text-sm text-muted-foreground">
                                            Select a role from the dropdown
                                            above to manage its permissions
                                        </p>
                                    </CardContent>
                                </Card>
                            )}
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

