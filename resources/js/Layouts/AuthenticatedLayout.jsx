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
    BookOpen,
    Code,
    Briefcase,
    Shield,
    ChevronLeft,
    ChevronRight
} from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function AuthenticatedLayout({ user, header, children }) {
    const [sidebarOpen, setSidebarOpen] = React.useState(false);
    const [sidebarCollapsed, setSidebarCollapsed] = React.useState(false);
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

    const workNavigation = [
        { name: 'Projects', href: '/projects', icon: FileText },
    ];

    const systemNavigation = [
        { name: 'API Dashboard', href: '/api-dashboard', icon: Server },
        { name: 'User Management', href: '/user-management', icon: Users },
        { name: 'Analytics', href: '/analytics', icon: BarChart3 },
    ];

    const aiNavigation = [
        { name: 'AI Settings', href: '/ai-settings', icon: Brain },
        { name: 'Playground', href: '/playground', icon: Play },
        { name: 'Prompt Engineering', href: '/prompt-engineering', icon: BookOpen },
        { name: 'Context Engineering', href: '/context-engineering', icon: Code },
    ];

    return (
        <div className="min-h-screen bg-background">
            {/* Top navbar */}
            <div className={`fixed top-0 right-0 z-50 bg-sidebar border-b border-sidebar-border shadow-sm transition-all duration-300 ${
                sidebarCollapsed ? 'left-16' : 'left-64'
            }`}>
                <div className="flex h-16 items-center justify-between px-6 lg:px-8">
                    <div className="flex items-center">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="lg:hidden text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground mr-3"
                            onClick={() => setSidebarOpen(true)}
                        >
                            <Menu className="h-5 w-5" />
                        </Button>
                    </div>

                    <div className="flex items-center gap-x-6">
                        <div className="flex items-center gap-x-4">
                            <div className="flex-shrink-0">
                                <div className="h-9 w-9 rounded-full bg-primary flex items-center justify-center shadow-sm ring-2 ring-primary/20">
                                    <span className="text-sm font-semibold text-primary-foreground">
                                        {user?.name?.charAt(0) || 'U'}
                                    </span>
                                </div>
                            </div>
                            <div className="hidden sm:block">
                                <p className="text-sm font-semibold text-sidebar-foreground leading-tight">{user?.name || 'shadcn'}</p>
                                <p className="text-xs text-sidebar-foreground/70 leading-tight">{user?.email || 'm@example.com'}</p>
                            </div>
                        </div>
                        <div className="h-6 w-px bg-sidebar-border"></div>
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={handleLogout}
                            className="text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground px-3 py-2 h-auto"
                        >
                            <LogOut className="h-4 w-4 mr-2" />
                            <span className="hidden sm:inline font-medium">Sign out</span>
                        </Button>
                    </div>
                </div>
            </div>

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
                        <Link href="/dashboard">
                            <Button className="w-full bg-primary text-primary-foreground mb-6">
                                <Home className="w-4 h-4 mr-2" />
                                Dashboard
                            </Button>
                        </Link>

                        <nav className="space-y-6">
                            <div>
                                <h3 className="text-xs font-semibold text-sidebar-foreground uppercase tracking-wider mb-2">
                                    Work
                                </h3>
                                <div className="space-y-1">
                                    {workNavigation.map((item) => (
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
                                    System
                                </h3>
                                <div className="space-y-1">
                                    {systemNavigation.map((item) => (
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
                </div>
            </div>

            {/* Desktop sidebar */}
            <div className={`hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col transition-all duration-300 ${
                sidebarCollapsed ? 'lg:w-16' : 'lg:w-64'
            }`}>
                <div className="flex flex-col flex-grow bg-sidebar border-r border-sidebar-border">
                    <div className="flex h-16 items-center px-4 justify-between">
                        {!sidebarCollapsed && (
                            <h1 className="text-xl font-bold text-sidebar-foreground orbitron">Bobbi</h1>
                        )}
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setSidebarCollapsed(!sidebarCollapsed)}
                            className="text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        >
                            {sidebarCollapsed ? (
                                <ChevronRight className="h-4 w-4" />
                            ) : (
                                <ChevronLeft className="h-4 w-4" />
                            )}
                        </Button>
                    </div>

                    <div className="flex-1 px-4 py-4">
                        <Link href="/dashboard">
                            <Button className={`w-full bg-primary text-primary-foreground mb-6 ${
                                sidebarCollapsed ? 'px-2' : ''
                            }`}>
                                <Home className="w-4 h-4" />
                                {!sidebarCollapsed && <span className="ml-2">Dashboard</span>}
                            </Button>
                        </Link>

                        <nav className="space-y-6">
                            <div>
                                {!sidebarCollapsed && (
                                    <h3 className="text-xs font-semibold text-sidebar-foreground uppercase tracking-wider mb-2">
                                        Work
                                    </h3>
                                )}
                                <div className="space-y-1">
                                    {workNavigation.map((item) => (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground ${
                                                sidebarCollapsed ? 'justify-center' : ''
                                            }`}
                                            title={sidebarCollapsed ? item.name : ''}
                                        >
                                            <item.icon className={`h-5 w-5 ${!sidebarCollapsed ? 'mr-3' : ''}`} />
                                            {!sidebarCollapsed && item.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>

                            <div>
                                {!sidebarCollapsed && (
                                    <h3 className="text-xs font-semibold text-sidebar-foreground uppercase tracking-wider mb-2">
                                        System
                                    </h3>
                                )}
                                <div className="space-y-1">
                                    {systemNavigation.map((item) => (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground ${
                                                sidebarCollapsed ? 'justify-center' : ''
                                            }`}
                                            title={sidebarCollapsed ? item.name : ''}
                                        >
                                            <item.icon className={`h-5 w-5 ${!sidebarCollapsed ? 'mr-3' : ''}`} />
                                            {!sidebarCollapsed && item.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>

                            <div>
                                {!sidebarCollapsed && (
                                    <h3 className="text-xs font-semibold text-sidebar-foreground uppercase tracking-wider mb-2">
                                        AI
                                    </h3>
                                )}
                                <div className="space-y-1">
                                    {aiNavigation.map((item) => (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground ${
                                                sidebarCollapsed ? 'justify-center' : ''
                                            }`}
                                            title={sidebarCollapsed ? item.name : ''}
                                        >
                                            <item.icon className={`h-5 w-5 ${!sidebarCollapsed ? 'mr-3' : ''}`} />
                                            {!sidebarCollapsed && item.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>

            {/* Main content */}
            <div className={`pt-16 transition-all duration-300 ${
                sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'
            }`}>
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
