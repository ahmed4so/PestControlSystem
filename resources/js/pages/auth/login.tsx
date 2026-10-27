import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Lock, Shield, User } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';

interface LoginProps {
    status?: string;
    loginError?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}

export default function Login({
    status,
    loginError,
    canResetPassword,
    canRegister,
}: LoginProps) {
    return (
        <>
            <Head title="Log in" />
            <div className="min-h-screen flex">
                {/* Left Side - Information Section */}
                <div className="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary/10 via-primary/5 to-background flex-col justify-between p-12 relative overflow-hidden">
                    {/* Decorative background elements */}
                    <div className="absolute top-0 right-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
                    <div className="absolute bottom-0 left-0 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>
                    
                    <div className="relative z-10">
                        {/* Logo */}
                        <div className="flex items-center gap-3 mb-12">
                            <AppLogoIcon className="size-12 text-primary" />
                            <div>
                                <h1 className="text-2xl font-bold text-foreground">Pest Control System</h1>
                            </div>
                        </div>

                        {/* Information Content */}
                        <div className="space-y-8 max-w-md">
                            <div>
                                <h2 className="text-4xl font-bold text-foreground mb-4">
                                    Welcome Back
                                </h2>
                                <p className="text-lg text-muted-foreground">
                                    Sign in to manage your workspace.
                                </p>
                            </div>

                            <div className="space-y-6 pt-8">
                                <div className="flex items-start gap-4">
                                    <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                        <Shield className="w-5 h-5 text-primary" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-foreground mb-1">Secure access</h3>
                                        <p className="text-sm text-muted-foreground">
                                            Sign in with optional two-factor authentication.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-4">
                                    <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                        <User className="w-5 h-5 text-primary" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-foreground mb-1">Easy Login</h3>
                                        <p className="text-sm text-muted-foreground">
                                            Sign in with your email address to access your dashboard.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-4">
                                    <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                        <Lock className="w-5 h-5 text-primary" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-foreground mb-1">Role-Based Access</h3>
                                        <p className="text-sm text-muted-foreground">
                                            Access control ensures the right people see the right information.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right Side - Login Form */}
                <div className="w-full lg:w-1/2 flex items-center justify-center p-6 bg-background">
                    <div className="w-full max-w-md">
                        {/* Mobile Logo */}
                        <div className="lg:hidden flex items-center gap-3 mb-10 justify-center">
                            <AppLogoIcon className="size-10 text-primary" />
                            <div>
                                <h1 className="text-xl font-bold text-foreground">Pest Control System</h1>
                            </div>
                        </div>

                        <Card className="border-0 shadow-lg">
                            <CardHeader className="space-y-1">
                                <CardTitle className="text-2xl font-bold">Sign in to your account</CardTitle>
                                <CardDescription>
                                    Enter your email and password to access your dashboard
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {loginError && (
                                    <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                        <p className="font-semibold mb-0.5">Access denied</p>
                                        <p>{loginError}</p>
                                    </div>
                                )}
                                {status && !loginError && (
                                    <div className="mb-4 text-center text-sm font-medium text-green-600">
                                        {status}
                                    </div>
                                )}
                                
                                <Form
                                    {...store.form()}
                                    resetOnSuccess={['password']}
                                    className="space-y-4"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <Label htmlFor="email">Email address</Label>
                                                <Input
                                                    id="email"
                                                    type="email"
                                                    name="email"
                                                    required
                                                    autoFocus
                                                    tabIndex={1}
                                                    autoComplete="email"
                                                    placeholder="email@example.com"
                                                />
                                                <InputError message={errors.email} />
                                            </div>

                                            <div className="grid gap-2">
                                                <div className="flex items-center">
                                                    <Label htmlFor="password">Password</Label>
                                                    {canResetPassword && (
                                                        <TextLink
                                                            href={request()}
                                                            className="ml-auto text-sm"
                                                            tabIndex={5}
                                                        >
                                                            Forgot password?
                                                        </TextLink>
                                                    )}
                                                </div>
                                                <Input
                                                    id="password"
                                                    type="password"
                                                    name="password"
                                                    required
                                                    tabIndex={2}
                                                    autoComplete="current-password"
                                                    placeholder="Enter your password"
                                                />
                                                <InputError message={errors.password} />
                                            </div>

                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center space-x-2">
                                                    <Checkbox
                                                        id="remember"
                                                        name="remember"
                                                        tabIndex={3}
                                                    />
                                                    <Label
                                                        htmlFor="remember"
                                                        className="text-sm text-muted-foreground cursor-pointer"
                                                    >
                                                        Remember me
                                                    </Label>
                                                </div>
                                            </div>

                                            <Button 
                                                type="submit" 
                                                className="w-full" 
                                                size="lg" 
                                                disabled={processing}
                                                tabIndex={4}
                                                data-test="login-button"
                                            >
                                                {processing && <Spinner />}
                                                {processing ? "Signing in..." : "Sign In"}
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            </CardContent>
                        </Card>

                    </div>
                </div>
            </div>
        </>
    );
}
