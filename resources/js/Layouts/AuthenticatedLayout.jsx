import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import {
    Home,
    Users,
    FileText,
    Calendar,
    Settings,
    LogOut,
    Menu,
    X,
    BarChart3,
    Database,
    HelpCircle,
    Search,
    MoreVertical,
    Plus,
    Brain,
    Server,
    Play,
    BookOpen
} from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function AuthenticatedLayout({ user, header, children }) {
    const [sidebarOpen, setSidebarOpen] = React.useState(false);
    const { post } = useForm();

    const handleLogout = () => {
        post('/logout', {
            onSuccess: () => {
                console.log('Logout successful');
            },
            onError: (errors) => {
                console.error('Logout failed', errors);
            }
        });
    };

    const navigation = [
        { name: 'API Dashboard', href: '/dashboard', icon: Server },
        { name: 'User Management', href: '/user-management', icon: Users },
        { name: 'Analytics', href: '/analytics', icon: BarChart3 },
        { name: 'Projects', href: '/projects', icon: FileText },
    ];

    const aiNavigation = [
        { name: 'AI Settings', href: '/ai-settings', icon: Brain },
        { name: 'Playground', href: '/playground', icon: Play },
        { name: 'Prompt Engineering', href: '/prompt-engineering', icon: BookOpen },
    ];

    return (
        <div className="min-h-screen bg-background">
            {/* Mobile sidebar */}
            <div className={`fixed inset-0 z-50 lg:hidden ${sidebarOpen ? 'block' : 'hidden'}`}>
                <div className="fixed inset-0 bg-black/50" onClick={() => setSidebarOpen(false)} />
                <div className="fixed inset-y-0 left-0 flex w-64 flex-col bg-sidebar border-r border-sidebar-border">
                    <div className="flex h-16 items-center justify-between px-4">
                        <h1 className="text-xl font-bold text-sidebar-foreground orbitron">Bobbi</h1>
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setSidebarOpen(false)}
                            className="text-sidebar-foreground"
                        >
                            <X className="h-6 w-6" />
                        </Button>
                    </div>
                    <div className="flex-1 px-4 py-4">
                        <Button className="w-full bg-primary text-primary-foreground mb-6">
                            <Plus className="w-4 h-4 mr-2" />
                            Quick Create
                        </Button>

                        <nav className="space-y-6">
                            <div>
                                <div className="space-y-1">
                                    {navigation.map((item) => (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                                        >
                                            <item.icon className="mr-3 h-5 w-5" />
                                            {item.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>

                            <div>
                                <h3 className="text-xs font-semibold text-sidebar-foreground uppercase tracking-wider mb-2">
                                    AI
                                </h3>
                                <div className="space-y-1">
                                    {aiNavigation.map((item) => (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                                        >
                                            <item.icon className="mr-3 h-5 w-5" />
                                            {item.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        </nav>
                    </div>

                    <div className="border-t border-sidebar-border p-4">
                        <div className="flex items-center mb-3">
                            <div className="flex-shrink-0">
                                <div className="h-8 w-8 rounded-full bg-sidebar-primary flex items-center justify-center">
                                    <span className="text-sm font-medium text-sidebar-primary-foreground">
                                        {user?.name?.charAt(0) || 'U'}
                                    </span>
                                </div>
                            </div>
                            <div className="ml-3 flex-1">
                                <p className="text-sm font-medium text-sidebar-foreground">{user?.name || 'shadcn'}</p>
                                <p className="text-xs text-sidebar-foreground">{user?.email || 'm@example.com'}</p>
                            </div>
                            <Button variant="ghost" size="icon" className="text-sidebar-foreground">
                                <MoreVertical className="h-4 w-4" />
                            </Button>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={handleLogout}
                            className="w-full text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        >
                            <LogOut className="h-4 w-4 mr-2" />
                            Sign out
                        </Button>
                    </div>
                </div>
            </div>

            {/* Desktop sidebar */}
            <div className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
                <div className="flex flex-col flex-grow bg-sidebar border-r border-sidebar-border">
                    <div className="flex h-16 items-center px-4">
                        <h1 className="text-xl font-bold text-sidebar-foreground orbitron">Bobbi</h1>
                    </div>

                    <div className="flex-1 px-4 py-4">
                        <Button className="w-full bg-primary text-primary-foreground mb-6">
                            <Plus className="w-4 h-4 mr-2" />
                            Quick Create
                        </Button>

                        <nav className="space-y-6">
                            <div>
                                <div className="space-y-1">
                                    {navigation.map((item) => (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                                        >
                                            <item.icon className="mr-3 h-5 w-5" />
                                            {item.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>

                            <div>
                                <h3 className="text-xs font-semibold text-sidebar-foreground uppercase tracking-wider mb-2">
                                    AI
                                </h3>
                                <div className="space-y-1">
                                    {aiNavigation.map((item) => (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                                        >
                                            <item.icon className="mr-3 h-5 w-5" />
                                            {item.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        </nav>
                    </div>

                    <div className="border-t border-sidebar-border p-4">
                        <div className="flex items-center mb-3">
                            <div className="flex-shrink-0">
                                <div className="h-8 w-8 rounded-full bg-sidebar-primary flex items-center justify-center">
                                    <span className="text-sm font-medium text-sidebar-primary-foreground">
                                        {user?.name?.charAt(0) || 'U'}
                                    </span>
                                </div>
                            </div>
                            <div className="ml-3 flex-1">
                                <p className="text-sm font-medium text-sidebar-foreground">{user?.name || 'shadcn'}</p>
                                <p className="text-xs text-sidebar-foreground">{user?.email || 'm@example.com'}</p>
                            </div>
                            <Button variant="ghost" size="icon" className="text-sidebar-foreground">
                                <MoreVertical className="h-4 w-4" />
                            </Button>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={handleLogout}
                            className="w-full text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        >
                            <LogOut className="h-4 w-4 mr-2" />
                            Sign out
                        </Button>
                    </div>
                </div>
            </div>

            {/* Main content */}
            <div className="lg:pl-64">
                {/* Top bar */}
                <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-border bg-background px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                    <Button
                        variant="ghost"
                        size="icon"
                        className="lg:hidden"
                        onClick={() => setSidebarOpen(true)}
                    >
                        <Menu className="h-6 w-6" />
                    </Button>

                    <div className="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                        <div className="flex flex-1" />
                        <div className="flex items-center gap-x-4 lg:gap-x-6">
                            {/* Add notifications, profile dropdown, etc. here */}
                        </div>
                    </div>
                </div>

                {/* Page content */}
                <main className="py-6">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        {header && (
                            <div className="mb-8">
                                <h1 className="text-2xl font-bold text-foreground">{header}</h1>
                            </div>
                        )}
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
