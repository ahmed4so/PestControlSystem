import { Alert } from '@heroui/alert';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Edit, Mail, UserCheck, UserPlus, UserX, Users } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
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
import AppLayout from '@/layouts/app-layout';
import { activate, deactivate } from '@/routes/users';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    status: 'ACTIVE' | 'INACTIVE';
    created_at: string;
    updated_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

export default function UserIndex({ users }: { users: User[] }) {
    // Staff users are deactivated, never deleted (D-053).
    const [deactivatingUser, setDeactivatingUser] = useState<User | null>(
        null,
    );
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    const handleDeactivate = () => {
        if (deactivatingUser) {
            router.post(deactivate.url(deactivatingUser.id), {}, {
                preserveScroll: true,
                onSuccess: () => setDeactivatingUser(null),
            });
        }
    };

    const handleActivate = (user: User) => {
        router.post(activate.url(user.id), {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Users
                        </h1>
                        <p className="text-muted-foreground">
                            Manage user accounts
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/users/create">
                            <UserPlus />
                            Create User
                        </Link>
                    </Button>
                </div>

                {flash?.success && <Alert color="success" title={flash.success} />}
                {flash?.error && <Alert color="danger" title={flash.error} />}

                {users.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <Users className="mb-4 size-12 text-muted-foreground" />
                            <h3 className="mb-2 text-lg font-semibold">
                                No users yet
                            </h3>
                            <p className="mb-4 text-center text-sm text-muted-foreground">
                                Get started by creating your first user account.
                            </p>
                            <Button asChild>
                                <Link href="/users/create">Create User</Link>
                            </Button>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {users.map((user) => (
                            <Card key={user.id} className="overflow-hidden">
                                <CardHeader className="pb-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <CardTitle className="flex items-center gap-2">
                                                {user.name}
                                                {user.status === 'INACTIVE' && (
                                                    <Badge variant="destructive">
                                                        Inactive
                                                    </Badge>
                                                )}
                                                {user.email_verified_at ? (
                                                    <Badge variant="default">
                                                        Verified
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="secondary">
                                                        Unverified
                                                    </Badge>
                                                )}
                                            </CardTitle>
                                            <CardDescription className="mt-1 flex items-center gap-1">
                                                <Mail className="size-3" />
                                                {user.email}
                                            </CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent className="space-y-4">
                                    <div className="flex gap-2 pt-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="flex-1"
                                            asChild
                                        >
                                            <Link href={`/users/${user.id}`}>
                                                View
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="flex-1"
                                            asChild
                                        >
                                            <Link href={`/users/${user.id}/edit`}>
                                                <Edit />
                                                Edit
                                            </Link>
                                        </Button>
                                        {user.status === 'ACTIVE' ? (
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                aria-label="Deactivate"
                                                onClick={() =>
                                                    setDeactivatingUser(user)
                                                }
                                            >
                                                <UserX />
                                            </Button>
                                        ) : (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                aria-label="Activate"
                                                onClick={() =>
                                                    handleActivate(user)
                                                }
                                            >
                                                <UserCheck />
                                            </Button>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>

            <Dialog
                open={!!deactivatingUser}
                onOpenChange={(open) => !open && setDeactivatingUser(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Deactivate User</DialogTitle>
                        <DialogDescription>
                            Deactivate{' '}
                            <span className="font-semibold">
                                {deactivatingUser?.name}
                            </span>
                            ? They are signed out at once and cannot sign in
                            until the account is activated again.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeactivatingUser(null)}
                        >
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleDeactivate}>
                            Deactivate
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}

