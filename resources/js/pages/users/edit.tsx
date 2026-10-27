import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';

interface User {
    id: number;
    name: string;
    email: string;
}

const breadcrumbs = (user: User): BreadcrumbItem[] => [
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: user.name,
        href: `/users/${user.id}/edit`,
    },
];

export default function EditUser({ user }: { user: User }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(user)}>
            <Head title={`Edit ${user.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Edit User
                    </h1>
                    <p className="text-muted-foreground">
                        Update the details for {user.name}
                    </p>
                </div>

                <Card className="max-w-2xl">
                    <CardContent>
                        <Form
                            action={`/users/${user.id}`}
                            method="post"
                            options={{
                                preserveScroll: true,
                            }}
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <input
                                        type="hidden"
                                        name="_method"
                                        value="PUT"
                                    />

                                    <Heading
                                        title="User Information"
                                        description="Update the details for this user"
                                        variant="small"
                                    />

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">
                                                Full Name{' '}
                                                <span className="text-red-600">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                defaultValue={user.name}
                                                placeholder="e.g., John Doe"
                                                required
                                                autoFocus
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="email">
                                                Email Address{' '}
                                                <span className="text-red-600">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                defaultValue={user.email}
                                                placeholder="user@example.com"
                                                required
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>
                                    </div>


                                    <div className="space-y-2">
                                        <Heading
                                            title="Change Password (Optional)"
                                            description="Leave blank to keep the current password"
                                            variant="small"
                                        />

                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="password">
                                                    New Password
                                                </Label>
                                                <Input
                                                    id="password"
                                                    name="password"
                                                    type="password"
                                                    placeholder="••••••••"
                                                />
                                                <InputError
                                                    message={errors.password}
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="password_confirmation">
                                                    Confirm New Password
                                                </Label>
                                                <Input
                                                    id="password_confirmation"
                                                    name="password_confirmation"
                                                    type="password"
                                                    placeholder="••••••••"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Saving...'
                                                : 'Save Changes'}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            asChild
                                        >
                                            <Link href="/users">Cancel</Link>
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
