import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'Create',
        href: '/users/create',
    },
];

export default function CreateUser() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create User" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Add New User
                    </h1>
                    <p className="text-muted-foreground">
                        Create a new user for the system
                    </p>
                </div>

                <Card className="max-w-2xl">
                    <CardContent>
                        <Form
                            action="/users"
                            method="post"
                            options={{
                                preserveScroll: true,
                            }}
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <Heading
                                        title="User Information"
                                        description="Enter the user details"
                                        variant="small"
                                    />

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">
                                                User Name{' '}
                                                <span className="text-red-600">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                placeholder="e.g., Ahmed"
                                                required
                                                autoFocus
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="email">
                                                User Email{' '}
                                                <span className="text-red-600">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                placeholder="e.g., ahmed@example.com"
                                                required
                                            />
                                            <InputError message={errors.email} />
                                        </div>
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="password">
                                                Password{' '}
                                                <span className="text-red-600">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="password"
                                                name="password"
                                                type="password"
                                                placeholder="********"
                                                required
                                            />
                                            <InputError message={errors.password} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="password_confirmation">
                                                Confirm Password{' '}
                                                <span className="text-red-600">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                type="password"
                                                placeholder="********"
                                                required
                                            />
                                        </div>
                                    </div>


                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Creating...'
                                                : 'Create User'}
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
