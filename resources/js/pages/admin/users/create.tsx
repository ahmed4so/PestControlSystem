import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import LanguageSelect from '@/components/settings/language-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { create, index, store } from '@/routes/admin/users';

/**
 * New staff user (05 §3.3). There is no password field: the user receives
 * a temporary password by email, valid 24 hours (D-083).
 */
export default function CreateUser({ roles }: { roles: string[] }) {
    const { t } = useTranslation();

    return (
        <AdminLayout
            breadcrumbs={[
                { title: t('Users'), href: index().url },
                { title: t('Create user'), href: create().url },
            ]}
        >
            <Head title={t('Create user')} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        {t('Create user')}
                    </h1>
                    <p className="text-muted-foreground">
                        {t(
                            'The new user receives a temporary password by email, valid for 24 hours.',
                        )}
                    </p>
                </div>

                <Card className="max-w-2xl">
                    <CardContent>
                        <Form
                            {...store.form()}
                            options={{ preserveScroll: true }}
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">
                                                {t('Name')}
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                required
                                                maxLength={150}
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="email">
                                                {t('Email address')}
                                            </Label>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                required
                                                maxLength={190}
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="phone">
                                                {t('Phone (optional)')}
                                            </Label>
                                            <Input
                                                id="phone"
                                                name="phone"
                                                type="tel"
                                                placeholder="+252 61 234 5678"
                                            />
                                            <InputError
                                                message={errors.phone}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="preferred_locale">
                                                {t('Preferred language')}
                                            </Label>
                                            <LanguageSelect defaultValue="en" />
                                            <InputError
                                                message={
                                                    errors.preferred_locale
                                                }
                                            />
                                        </div>
                                    </div>

                                    <fieldset className="space-y-3">
                                        <legend className="text-sm font-medium">
                                            {t('Roles')}
                                        </legend>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            {roles.map((role) => (
                                                <label
                                                    key={role}
                                                    className="flex items-center gap-2 text-sm"
                                                >
                                                    <Checkbox
                                                        name="roles[]"
                                                        value={role}
                                                    />
                                                    {role}
                                                </label>
                                            ))}
                                        </div>
                                        <InputError message={errors.roles} />
                                    </fieldset>

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {t('Create user')}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            asChild
                                        >
                                            <Link href={index()}>
                                                {t('Cancel')}
                                            </Link>
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
