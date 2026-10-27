import { Head, Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen flex-col items-center justify-center gap-6 bg-background p-6 text-center">
                <AppLogoIcon className="size-12 text-primary" />
                <h1 className="text-3xl font-bold tracking-tight text-foreground">
                    Pest Control System
                </h1>
                <Button asChild size="lg">
                    {auth.user ? (
                        <Link href={dashboard()}>Go to dashboard</Link>
                    ) : (
                        <Link href={login()}>Log in</Link>
                    )}
                </Button>
            </div>
        </>
    );
}
