import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

export default function Login() {
    const [activeTab, setActiveTab] = useState('login');

    const loginForm = useForm({
        email: '',
        password: '',
    });

    const registerForm = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submitLogin = (e) => {
        e.preventDefault();
        console.log('Login form submitted', { email: loginForm.data.email, password: loginForm.data.password ? '[REDACTED]' : 'empty' });
        loginForm.post('/login', {
            onSuccess: () => {
                console.log('Login successful');
            },
            onError: (errors) => {
                console.error('Login failed', errors);
            },
            onFinish: () => {
                console.log('Login request finished');
            }
        });
    };

    const submitRegister = (e) => {
        e.preventDefault();
        console.log('Register form submitted', {
            name: registerForm.data.name,
            email: registerForm.data.email,
            password: registerForm.data.password ? '[REDACTED]' : 'empty'
        });
        registerForm.post('/register', {
            onSuccess: () => {
                console.log('Registration successful');
            },
            onError: (errors) => {
                console.error('Registration failed', errors);
            },
            onFinish: () => {
                console.log('Registration request finished');
            }
        });
    };

    return (
        <>
            <Head title="Login" />

            <div className="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
                <div className="max-w-md w-full space-y-8">
                    <div>
                        <h2 className="mt-6 text-center text-3xl font-extrabold text-foreground">
                            Welcome to <span className="font-orbitron text-[#d1ff75]">Bobbi</span>
                        </h2>
                        <p className="mt-2 text-center text-sm text-muted-foreground">
                            Sign in to your account or create a new one
                        </p>
                    </div>

                    <Card className="bg-card border-border">
                        <CardHeader>
                            <CardTitle className="text-card-foreground">Account Access</CardTitle>
                            <CardDescription className="text-muted-foreground">
                                Choose your preferred action below
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                                <TabsList className="grid w-full grid-cols-2">
                                    <TabsTrigger value="login">Sign In</TabsTrigger>
                                    <TabsTrigger value="register">Sign Up</TabsTrigger>
                                </TabsList>

                                <TabsContent value="login" className="space-y-6">
                                    <form onSubmit={submitLogin} className="space-y-6">
                                        <div>
                                            <label htmlFor="login-email" className="block text-sm font-medium text-foreground">
                                                Email address
                                            </label>
                                            <input
                                                id="login-email"
                                                type="email"
                                                value={loginForm.data.email}
                                                onChange={(e) => loginForm.setData('email', e.target.value)}
                                                className="mt-1 block w-full px-3 py-2 border border-input bg-background text-foreground rounded-md shadow-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring sm:text-sm"
                                                required
                                            />
                                            {loginForm.errors.email && (
                                                <p className="mt-1 text-sm text-destructive">{loginForm.errors.email}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label htmlFor="login-password" className="block text-sm font-medium text-foreground">
                                                Password
                                            </label>
                                            <input
                                                id="login-password"
                                                type="password"
                                                value={loginForm.data.password}
                                                onChange={(e) => loginForm.setData('password', e.target.value)}
                                                className="mt-1 block w-full px-3 py-2 border border-input bg-background text-foreground rounded-md shadow-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring sm:text-sm"
                                                required
                                            />
                                            {loginForm.errors.password && (
                                                <p className="mt-1 text-sm text-destructive">{loginForm.errors.password}</p>
                                            )}
                                        </div>

                                        <div>
                                            <Button
                                                type="submit"
                                                disabled={loginForm.processing}
                                                className="w-full"
                                            >
                                                {loginForm.processing ? 'Signing in...' : 'Sign in'}
                                            </Button>
                                        </div>
                                    </form>
                                </TabsContent>

                                <TabsContent value="register" className="space-y-6">
                                    <form onSubmit={submitRegister} className="space-y-6">
                                        <div>
                                            <label htmlFor="register-name" className="block text-sm font-medium text-foreground">
                                                Full Name
                                            </label>
                                            <input
                                                id="register-name"
                                                type="text"
                                                value={registerForm.data.name}
                                                onChange={(e) => registerForm.setData('name', e.target.value)}
                                                className="mt-1 block w-full px-3 py-2 border border-input bg-background text-foreground rounded-md shadow-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring sm:text-sm"
                                                required
                                            />
                                            {registerForm.errors.name && (
                                                <p className="mt-1 text-sm text-destructive">{registerForm.errors.name}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label htmlFor="register-email" className="block text-sm font-medium text-foreground">
                                                Email address
                                            </label>
                                            <input
                                                id="register-email"
                                                type="email"
                                                value={registerForm.data.email}
                                                onChange={(e) => registerForm.setData('email', e.target.value)}
                                                className="mt-1 block w-full px-3 py-2 border border-input bg-background text-foreground rounded-md shadow-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring sm:text-sm"
                                                required
                                            />
                                            {registerForm.errors.email && (
                                                <p className="mt-1 text-sm text-destructive">{registerForm.errors.email}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label htmlFor="register-password" className="block text-sm font-medium text-foreground">
                                                Password
                                            </label>
                                            <input
                                                id="register-password"
                                                type="password"
                                                value={registerForm.data.password}
                                                onChange={(e) => registerForm.setData('password', e.target.value)}
                                                className="mt-1 block w-full px-3 py-2 border border-input bg-background text-foreground rounded-md shadow-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring sm:text-sm"
                                                required
                                            />
                                            {registerForm.errors.password && (
                                                <p className="mt-1 text-sm text-destructive">{registerForm.errors.password}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label htmlFor="register-password-confirmation" className="block text-sm font-medium text-foreground">
                                                Confirm Password
                                            </label>
                                            <input
                                                id="register-password-confirmation"
                                                type="password"
                                                value={registerForm.data.password_confirmation}
                                                onChange={(e) => registerForm.setData('password_confirmation', e.target.value)}
                                                className="mt-1 block w-full px-3 py-2 border border-input bg-background text-foreground rounded-md shadow-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring sm:text-sm"
                                                required
                                            />
                                            {registerForm.errors.password_confirmation && (
                                                <p className="mt-1 text-sm text-destructive">{registerForm.errors.password_confirmation}</p>
                                            )}
                                        </div>

                                        <div>
                                            <Button
                                                type="submit"
                                                disabled={registerForm.processing}
                                                className="w-full"
                                            >
                                                {registerForm.processing ? 'Creating account...' : 'Create account'}
                                            </Button>
                                        </div>
                                    </form>
                                </TabsContent>
                            </Tabs>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
