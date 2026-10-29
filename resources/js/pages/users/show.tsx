import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Mail, Shield, User } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

interface Role {
    id: number;
    name: string;
}

interface UserDetail {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: Role[];
}

interface Props {
    user: UserDetail;
}

const breadcrumbs = (user: UserDetail): BreadcrumbItem[] => [
    { title: 'Users', href: '/users' },
    { title: user.name, href: `/users/${user.id}` },
];

export default function ShowUser({ user }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(user)}>
            <Head title={user.name} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">User Details</h1>
                        <p className="text-muted-foreground">View details for {user.name}</p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/users">
                                <ArrowLeft />
                                Back to Users
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={`/users/${user.id}/edit`}>
                                <Edit />
                                Edit User
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Profile card */}
                <Card className="max-w-2xl">
                    <CardHeader>
                        <div className="flex items-start justify-between">
                            <div className="flex items-center gap-3">
                                <div className="flex size-12 items-center justify-center rounded-full bg-primary/10">
                                    <User className="size-6 text-primary" />
                                </div>
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        {user.name}
                                        {user.email_verified_at ? (
                                            <Badge variant="default">Verified</Badge>
                                        ) : (
                                            <Badge variant="secondary">Unverified</Badge>
                                        )}
                                    </CardTitle>
                                    <CardDescription className="mt-1 flex items-center gap-1">
                                        <Mail className="size-3" />
                                        {user.email}
                                    </CardDescription>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="space-y-2">
                            <h4 className="flex items-center gap-2 text-sm font-medium">
                                <Shield className="size-4" />
                                Roles
                            </h4>
                            {user.roles?.length > 0 ? (
                                <div className="flex flex-wrap gap-2">
                                    {user.roles.map((role) => (
                                        <Badge key={role.id} variant="secondary">
                                            {role.name}
                                        </Badge>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">No roles assigned</p>
                            )}
                        </div>

                        <div className="border-t pt-4 text-sm text-muted-foreground">
                            <p>Created {new Date(user.created_at).toLocaleDateString()}</p>
                            <p>Last updated {new Date(user.updated_at).toLocaleDateString()}</p>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </AppLayout>
    );
}
