import React from 'react';
import { Link, useForm, router } from '@inertiajs/react';
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
    ChevronRight,
    Target,
    CreditCard
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { BrowserTabs } from '@/components/ui/tabs';

export const SidebarContext = React.createContext({
    sidebarCollapsed: false,
    setSidebarCollapsed: () => {},
});

export default function AuthenticatedLayout({ user, header, children }) {
    const [sidebarOpen, setSidebarOpen] = React.useState(false);
    const [sidebarCollapsed, setSidebarCollapsed] = React.useState(() => {
        // Initialize sidebar collapsed state from localStorage
        const savedSidebarState = localStorage.getItem('bobbi-sidebar-collapsed');
        return savedSidebarState ? JSON.parse(savedSidebarState) : false;
    });
    const [tabs, setTabs] = React.useState(() => {
        // Initialize tabs from localStorage
        const savedTabs = localStorage.getItem('bobbi-tabs');
        return savedTabs ? JSON.parse(savedTabs) : [];
    });
    const [activeTab, setActiveTab] = React.useState(() => {
        // Initialize active tab from localStorage
        const savedActiveTab = localStorage.getItem('bobbi-active-tab');
        return savedActiveTab || window.location.pathname;
    });
    const { post } = useForm();

    // Get current pathname
    const currentPath = window.location.pathname;

    // Save tabs to localStorage whenever tabs change
    React.useEffect(() => {
        localStorage.setItem('bobbi-tabs', JSON.stringify(tabs));
    }, [tabs]);

    // Save active tab to localStorage whenever it changes
    React.useEffect(() => {
        localStorage.setItem('bobbi-active-tab', activeTab);
    }, [activeTab]);

    // Save sidebar collapsed state to localStorage whenever it changes
    React.useEffect(() => {
        localStorage.setItem('bobbi-sidebar-collapsed', JSON.stringify(sidebarCollapsed));
    }, [sidebarCollapsed]);

    // Update tabs when route changes
    React.useEffect(() => {
        const pathToName = {
            '/dashboard': 'Dashboard',
            '/projects': 'Projects',
            '/clients': 'Clients',
            '/bobbi-flow': 'Bobbi Flow',
            '/calendar': 'Calendar',
            '/ai-settings': 'AI Settings',
            '/playground': 'Playground',
            '/prompt-engineering': 'Prompt Engineering',
            '/context-engineering': 'Context Engineering',
            '/api-dashboard': 'API Dashboard',
            '/user-management': 'User Management',
            '/analytics': 'Analytics',
            '/proposals': 'Proposals',
            '/proposals/create': 'Create Proposal',
            '/tasks/create': 'Task Create',
            '/subscriptions': 'Subscriptions'
        };

        // Handle dynamic routes
        let currentName = pathToName[currentPath];
        if (!currentName) {
            if (currentPath.startsWith('/clients/') && currentPath !== '/clients') {
                currentName = 'Client Details';
            } else if (currentPath.startsWith('/projects/') && currentPath !== '/projects') {
                currentName = 'Project Details';
            } else if (currentPath.startsWith('/proposals/') && currentPath !== '/proposals/create') {
                // Extract proposal ID for better naming
                const proposalId = currentPath.split('/').pop();
                currentName = `Proposal ${proposalId}`;
            } else if (currentPath.startsWith('/subscriptions/') && currentPath !== '/subscriptions') {
                // Extract subscription ID for better naming
                const subscriptionId = currentPath.split('/').pop();
                currentName = `Subscription ${subscriptionId}`;
            } else if (currentPath.startsWith('/tasks/') && currentPath.includes('/start-work')) {
                // Extract task ID for Start Work page
                const taskId = currentPath.split('/')[2];
                currentName = `Start Work - Task ${taskId}`;
            } else if (currentPath.startsWith('/tasks/') && currentPath !== '/tasks') {
                currentName = 'Task Details';
            } else {
                currentName = 'Unknown Page';
            }
        }

        const tabId = currentPath;

        // Add new tab if it doesn't exist
        setTabs(prev => {
            const existingTab = prev.find(tab => tab.id === tabId);
            if (!existingTab) {
                let newTabs = [...prev, { id: tabId, name: currentName, path: currentPath }];
                if (newTabs.length > 5) {
                    newTabs = newTabs.slice(1); // Remove the first (oldest) tab
                }
                return newTabs;
            }
            return prev;
        });

        // Set active tab
        setActiveTab(tabId);
    }, [currentPath]);

    const handleTabClick = (tabId) => {
        const tab = tabs.find(t => t.id === tabId);
        if (tab) {
            router.visit(tab.path);
            setActiveTab(tabId);
        }
    };

    const handleTabClose = (tabId) => {
        const tabIndex = tabs.findIndex(t => t.id === tabId);
        if (tabIndex === -1) return;

        const newTabs = tabs.filter(t => t.id !== tabId);
        setTabs(newTabs);

        // If closing active tab, switch to another tab
        if (activeTab === tabId) {
            const nextTab = newTabs[tabIndex] || newTabs[tabIndex - 1] || newTabs[0];
            if (nextTab) {
                router.visit(nextTab.path);
                setActiveTab(nextTab.id);
            } else {
                // If no tabs left, go to dashboard
                router.visit('/dashboard');
                setActiveTab('/dashboard');
            }
        }
    };

    const handleCloseAll = () => {
        // Keep only the dashboard tab
        const dashboardTab = tabs.find(t => t.id === '/dashboard');
        if (dashboardTab) {
            setTabs([dashboardTab]);
            setActiveTab('/dashboard');
            router.visit('/dashboard');
        } else {
            // If no dashboard tab exists, clear all and go to dashboard
            setTabs([]);
            setActiveTab('/dashboard');
            router.visit('/dashboard');
        }
    };

    const handleCloseOther = (tabId) => {
        const targetTab = tabs.find(t => t.id === tabId);
        if (!targetTab) return;

        // Keep only the selected tab
        setTabs([targetTab]);
        setActiveTab(tabId);
        router.visit(targetTab.path);
    };

    const handleMoveTab = (tabId, direction) => {
        const tabIndex = tabs.findIndex(t => t.id === tabId);
        if (tabIndex === -1) return;

        const newTabs = [...tabs];
        const tab = newTabs[tabIndex];

        switch (direction) {
            case 'up':
                if (tabIndex > 0) {
                    newTabs.splice(tabIndex, 1);
                    newTabs.splice(tabIndex - 1, 0, tab);
                }
                break;
            case 'down':
                if (tabIndex < newTabs.length - 1) {
                    newTabs.splice(tabIndex, 1);
                    newTabs.splice(tabIndex + 1, 0, tab);
                }
                break;
            case 'start':
                newTabs.splice(tabIndex, 1);
                newTabs.unshift(tab);
                break;
            case 'end':
                newTabs.splice(tabIndex, 1);
                newTabs.push(tab);
                break;
            default:
                return;
        }

        setTabs(newTabs);
    };

    // Clear tabs when user logs out
    const handleLogout = () => {
        localStorage.removeItem('bobbi-tabs');
        localStorage.removeItem('bobbi-active-tab');
        localStorage.removeItem('bobbi-sidebar-collapsed');
        post('/logout', {
            onSuccess: () => {
                console.log('Logout successful - redirecting to login page');
            },
            onError: (errors) => {
                console.error('Logout failed', errors);
            },
            onFinish: () => {
                // The backend will handle the redirect to /login
            }
        });
    };

    const workNavigation = [
        { name: 'Projects', href: '/projects', icon: FileText },
        { name: 'Clients', href: '/clients', icon: Users },
        { name: 'Proposals', href: '/proposals', icon: FileText },
        { name: 'Subscriptions', href: '/subscriptions', icon: CreditCard },
        { name: 'Bobbi Flow', href: '/bobbi-flow', icon: Target },
        { name: 'Calendar', href: '/calendar', icon: Calendar },
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
        <SidebarContext.Provider value={{ sidebarCollapsed, setSidebarCollapsed }}>
            <div className="min-h-screen bg-background">
                {/* Top navbar */}
                <div className={`fixed top-0 right-0 z-50 bg-sidebar border-b border-sidebar-border shadow-sm transition-all duration-300 ${
                    sidebarCollapsed ? 'left-16' : 'left-64'
                }`}>
                    <div className="flex h-16 items-center justify-between px-6 lg:px-8">
                        <div className="flex items-center flex-1 min-w-0">
                            <Button
                                variant="ghost"
                                size="icon"
                                className="lg:hidden text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground mr-3 flex-shrink-0"
                                onClick={() => setSidebarOpen(true)}
                            >
                                <Menu className="h-5 w-5" />
                            </Button>

                            {/* Browser Tabs - Left Side */}
                            {tabs.length > 0 && (
                                <div className="flex-1 min-w-0 mr-4">
                                    <BrowserTabs
                                        tabs={tabs}
                                        activeTab={activeTab}
                                        onTabClick={handleTabClick}
                                        onTabClose={handleTabClose}
                                        onCloseAll={handleCloseAll}
                                        onCloseOther={handleCloseOther}
                                        onMoveTab={handleMoveTab}
                                    />
                                </div>
                            )}
                        </div>

                        <div className="flex items-center gap-x-6 flex-shrink-0">
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
                            <h1 className="text-2xl font-bold text-sidebar-foreground orbitron">Bobbi</h1>
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
        </SidebarContext.Provider>
    );
}
