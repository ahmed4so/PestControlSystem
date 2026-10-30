import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import LanguageSelect from '@/components/settings/language-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { edit, index, update } from '@/routes/admin/users';

type Props = {
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        preferred_locale: string;
    };
};

/**
 * Staff user details (05 §3.3). Passwords are never set here (D-083).
 */
export default function EditUser({ user }: Props) {
    const { t } = useTranslation();

    return (
        <AdminLayout
            breadcrumbs={[
                { title: t('Users'), href: index().url },
                { title: user.name, href: edit(user.id).url },
            ]}
        >
            <Head title={t('Edit user')} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-bold tracking-tight">
                    {t('Edit user')}
                </h1>

                <Card className="max-w-2xl">
                    <CardContent>
                        <Form
                            {...update.form(user.id)}
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
                                                defaultValue={user.name}
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
                                                defaultValue={user.email}
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
                                                defaultValue={user.phone ?? ''}
                                            />
                                            <InputError
                                                message={errors.phone}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="preferred_locale">
                                                {t('Preferred language')}
                                            </Label>
                                            <LanguageSelect
                                                defaultValue={
                                                    user.preferred_locale
                                                }
                                            />
                                            <InputError
                                                message={
                                                    errors.preferred_locale
                                                }
                                            />
                                        </div>
                                    </div>

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {t('Save')}
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
