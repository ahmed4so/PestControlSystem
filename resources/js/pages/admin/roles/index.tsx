import { Alert } from '@heroui/alert';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Building2, Edit, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AdminLayout from '@/layouts/admin-layout';
import { create, destroy, edit, index } from '@/routes/admin/roles';
import { type BreadcrumbItem } from '@/types';
import { type Role } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: index().url,
    },
];

export default function RoleIndex({ roles }: { roles: Role[] }) {
    const [deletingRole, setDeletingRole] = useState<Role | null>(null);
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>()
        .props;

    const handleDelete = () => {
        if (deletingRole) {
            router.delete(destroy.url(deletingRole.id), {
                preserveScroll: true,
                onSuccess: () => setDeletingRole(null),
            });
        }
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Roles" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Roles
                        </h1>
                        <p className="text-muted-foreground">
                            Manage your organization's roles
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={create()}>
                            <Building2 />
                            Create Role
                        </Link>
                    </Button>
                </div>

                {flash?.success && (
                    <Alert color="success" title={flash.success} />
                )}

                {roles.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <Building2 className="mb-4 size-12 text-muted-foreground" />
                            <h3 className="mb-2 text-lg font-semibold">
                                No roles yet
                            </h3>
                            <p className="mb-4 text-center text-sm text-muted-foreground">
                                Get started by creating your first role
                                location.
                            </p>
                            <Button asChild>
                                <Link href={create()}>Create Role</Link>
                            </Button>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {roles.map((role) => (
                            <Card key={role.id} className="overflow-hidden">
                                <CardHeader className="pb-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <CardTitle className="flex items-center gap-2">
                                                {role.name}
                                            </CardTitle>
                                            <CardDescription className="mt-1 flex items-center gap-1"></CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent className="space-y-4">
                                    <div className="space-y-2 text-sm">
                                        {role.name && (
                                            <div>
                                                <span className="font-medium">
                                                    Name:
                                                </span>{' '}
                                                {role.name}
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex gap-2 pt-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="flex-1"
                                            asChild
                                        >
                                            <Link href={edit(role.id)}>
                                                <Edit />
                                                Edit
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            onClick={() =>
                                                setDeletingRole(role)
                                            }
                                        >
                                            <Trash2 />
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>

            <Dialog
                open={!!deletingRole}
                onOpenChange={(open) => !open && setDeletingRole(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Role</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete{' '}
                            <span className="font-semibold">
                                {deletingRole?.name}
                            </span>
                            ? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeletingRole(null)}
                        >
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleDelete}>
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}
