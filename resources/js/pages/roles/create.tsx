import HeadingSmall from '@/components/heading-small';
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
        title: 'Roles',
        href: '/roles',
    },
    {
        title: 'Create',
        href: '/roles/create',
    },
];

export default function CreateRole() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Role" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Add New Role
                    </h1>
                    <p className="text-muted-foreground">
                        Create a new role for user management
                    </p>
                </div>

                <Card className="max-w-2xl">
                    <CardContent>
                        <Form
                            action="/roles"
                            method="post"
                            options={{
                                preserveScroll: true,
                            }}
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <HeadingSmall
                                        title="Role Information"
                                        description="Enter the customer's basic details"
                                    />

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">
                                                Name{' '}
                                                <span className="text-red-600">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                placeholder="Role name"
                                                required
                                                autoFocus
                                            />
                                            <InputError message={errors.name} />
                                        </div>

          
                                    </div>

                                   

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Creating...'
                                                : 'Create Role'}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            asChild
                                        >
                                            <Link href="/roles">Cancel</Link>
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

