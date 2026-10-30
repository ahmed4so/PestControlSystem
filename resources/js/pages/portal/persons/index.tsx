import { Head, Link } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { create, edit } from '@/routes/portal/persons';

type Person = {
    id: number;
    full_name: string;
    national_id: string;
    is_owner: boolean;
    released: boolean;
};

/**
 * Persons of the company: the Owner/MD and technical persons (02 §4.4).
 */
export default function PersonsIndex({ persons, canCreate }: { persons: Person[]; canCreate: boolean }) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Persons')} />

            <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h1 className="text-xl font-semibold">{t('Persons')}</h1>
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <UserPlus />
                                {t('Add person')}
                            </Link>
                        </Button>
                    )}
                </div>

                {persons.length === 0 ? (
                    <p className="text-muted-foreground">{t('Add the Owner / Managing Director and your technical persons.')}</p>
                ) : (
                    <ul className="divide-y rounded-md border">
                        {persons.map((person) => (
                            <li key={person.id}>
                                <Link href={edit(person.id)} className="flex flex-wrap items-center gap-2 p-3 hover:bg-muted">
                                    <span className="font-medium">{person.full_name}</span>
                                    <span className="text-sm text-muted-foreground">{person.national_id}</span>
                                    {person.is_owner && <Badge>{t('Owner / Managing Director')}</Badge>}
                                    {person.released && <Badge variant="destructive">{t('Released by SARIS')}</Badge>}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </PortalLayout>
    );
}
