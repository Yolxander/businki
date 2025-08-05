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
    CreditCard,
    Edit
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { BrowserTabs } from '@/components/ui/tabs';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export const SidebarContext = React.createContext({
    sidebarCollapsed: false,
    setSidebarCollapsed: () => {},
});

export default function AuthenticatedLayout({ user, header, children, focusMode = false, currentPage, onCustomizeClick, isEditMode }) {
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
            '/prompt-management': 'Prompt Management',
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
            } else if (currentPath.startsWith('/tasks/') && currentPath !== '/tasks') {
                currentName = 'Task Details';
            } else if (currentPath.startsWith('/proposals/') && currentPath !== '/proposals') {
                currentName = 'Proposal Details';
            }
        }

        if (currentName && !tabs.find(tab => tab.id === currentPath)) {
            const newTab = {
                id: currentPath,
                name: currentName,
                href: currentPath
            };
            setTabs(prev => {
                // If we already have 3 tabs, remove the first one (oldest)
                if (prev.length >= 3) {
                    return [...prev.slice(1), newTab];
                }
                // Otherwise, just add the new tab
                return [...prev, newTab];
            });
        }
        setActiveTab(currentPath);
    }, [currentPath, tabs]);

    const handleTabClick = (tabId) => {
        setActiveTab(tabId);
        router.visit(tabId);
    };

    const handleTabClose = (tabId) => {
        // Find the tab to be closed
        const tabToClose = tabs.find(tab => tab.id === tabId);
        if (!tabToClose) return;

        // Remove the tab from the list
        const newTabs = tabs.filter(tab => tab.id !== tabId);
        setTabs(newTabs);

        // If we're closing the active tab, navigate to another tab
        if (activeTab === tabId) {
            if (newTabs.length > 0) {
                // Navigate to the last tab in the list
                const lastTab = newTabs[newTabs.length - 1];
                setActiveTab(lastTab.id);
                router.visit(lastTab.id);
            } else {
                // No tabs left, go to dashboard
                setActiveTab('/dashboard');
                router.visit('/dashboard');
            }
        }
    };

    const handleCloseAll = () => {
        setTabs([]);
        router.visit('/dashboard');
    };

    const handleCloseOther = (tabId) => {
        const currentTab = tabs.find(tab => tab.id === tabId);
        if (currentTab) {
            setTabs([currentTab]);
            setActiveTab(tabId);
        }
    };

    const handleMoveTab = (tabId, direction) => {
        const currentIndex = tabs.findIndex(tab => tab.id === tabId);
        if (currentIndex === -1) return;

        const newTabs = [...tabs];
        let targetIndex;

        switch (direction) {
            case 'up':
                targetIndex = currentIndex - 1;
                break;
            case 'down':
                targetIndex = currentIndex + 1;
                break;
            case 'start':
                targetIndex = 0;
                break;
            case 'end':
                targetIndex = newTabs.length - 1;
                break;
            default:
                return;
        }

        if (targetIndex >= 0 && targetIndex < newTabs.length && targetIndex !== currentIndex) {
            // Remove the tab from its current position
            const [movedTab] = newTabs.splice(currentIndex, 1);
            // Insert it at the target position
            newTabs.splice(targetIndex, 0, movedTab);
            setTabs(newTabs);
        }
    };

    const handleLogout = () => {
        post('/logout');
    };

    const workNavigation = [
        { name: 'Projects', href: '/projects', icon: FileText },
        { name: 'Clients', href: '/clients', icon: Users },
        // { name: 'Proposals', href: '/proposals', icon: FileText },
        // { name: 'Subscriptions', href: '/subscriptions', icon: CreditCard },
        { name: 'Bobbi Flow', href: '/bobbi-flow', icon: Target },
        { name: 'Calendar', href: '/calendar', icon: Calendar },
    ];

    const systemNavigation = [
        // { name: 'API Dashboard', href: '/api-dashboard', icon: Server },
        // { name: 'User Management', href: '/user-management', icon: Users },
        { name: 'Analytics', href: '/analytics', icon: BarChart3 },
    ];

    const aiNavigation = [
        // { name: 'AI Settings', href: '/ai-settings', icon: Brain },
        // { name: 'Playground', href: '/playground', icon: Play },
        // { name: 'Prompt Engineering', href: '/prompt-engineering', icon: BookOpen },
        // { name: 'Context Engineering', href: '/context-engineering', icon: Code },
        { name: 'Prompt Management', href: '/prompt-management', icon: BookOpen },
    ];

    return (
        <SidebarContext.Provider value={{ sidebarCollapsed, setSidebarCollapsed }}>
            <div className="min-h-screen bg-background">
                {/* Top navbar - hidden in focus mode */}
                {!focusMode && (
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
                                {/* Customize button - only show on dashboard */}
                                {currentPage === 'dashboard' && (
                                    isEditMode ? (
                                        <Button variant="outline" size="sm" onClick={onCustomizeClick}>
                                            <X className="w-4 h-4 mr-2" />
                                            Exit Mode
                                        </Button>
                                    ) : (
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="outline" size="sm">
                                                    <Settings className="w-4 h-4 mr-2" />
                                                    Customize
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem onClick={onCustomizeClick}>
                                                    <Edit className="w-4 h-4 mr-2" />
                                                    Edit widgets
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    )
                                )}
                                {/* User profile moved to sidebar bottom */}
                            </div>
                        </div>
                    </div>
                )}

                {/* Mobile sidebar - hidden in focus mode */}
                {!focusMode && (
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
                )}

                {/* Desktop sidebar - hidden in focus mode */}
                {!focusMode && (
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

                            {/* User profile section at bottom of sidebar */}
                            <div className="border-t border-sidebar-border p-4">
                                <div className="flex items-center gap-x-3">
                                    <div className="flex-shrink-0">
                                        <div className="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-sm ring-2 ring-primary/20">
                                            <span className="text-sm font-semibold text-primary-foreground">
                                                {user?.name?.charAt(0) || 'U'}
                                            </span>
                                        </div>
                                    </div>
                                    {!sidebarCollapsed && (
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-semibold text-sidebar-foreground leading-tight truncate">{user?.name || 'shadcn'}</p>
                                            <p className="text-xs text-sidebar-foreground/70 leading-tight truncate">{user?.email || 'm@example.com'}</p>
                                        </div>
                                    )}
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                                            >
                                                <MoreVertical className="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent className="w-56" align="end" forceMount>
                                            <DropdownMenuLabel className="font-normal">
                                                <div className="flex flex-col space-y-1">
                                                    <p className="text-sm font-medium leading-none">{user?.name || 'shadcn'}</p>
                                                    <p className="text-xs leading-none text-muted-foreground">{user?.email || 'm@example.com'}</p>
                                                </div>
                                            </DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem onClick={handleLogout}>
                                                <LogOut className="mr-2 h-4 w-4" />
                                                <span>Sign out</span>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Main content */}
                <div className={`transition-all duration-300 ${
                    focusMode
                        ? 'pt-0 lg:pl-0'
                        : `pt-16 ${sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'}`
                }`}>
                    {/* Page content */}
                    <main className={focusMode ? 'py-0' : 'py-6'}>
                        <div className={focusMode ? 'mx-auto w-full' : 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8'}>
                            {header && !focusMode && (
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
