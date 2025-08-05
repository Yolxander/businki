import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import ChatInterface from '@/components/ui/chat-interface';
import RecentChatsSidebar from '@/components/ui/recent-chats-sidebar';
import {
    Plus,
    Users,
    FileText,
    CheckCircle,
    Clock,
    AlertCircle,
    Calendar,
    TrendingUp,
    DollarSign,
    Briefcase,
    Target,
    Zap,
    Eye,
    ArrowRight,
    Brain,
    PenTool,
    Loader2,
    CheckCircle2,
    ExternalLink,
    User,
    Building,
    ArrowLeft,
    ArrowRight as ArrowRightIcon,
    Settings,
    Edit,
    Search,
    ChevronDown,
    ChevronRight,
    ChevronLeft,
    Paperclip,
    Command,
    BarChart3,
    CheckSquare,
    Wifi,
    Volume2,
    Home,
    MessageSquare
} from 'lucide-react';

export default function Dashboard({ auth, stats, clients = [], widgets = [], dashboardMode: initialDashboardMode = 'default' }) {
    const [showNewProjectModal, setShowNewProjectModal] = useState(false);
    const [showAISetupModal, setShowAISetupModal] = useState(false);
    const [showAlert, setShowAlert] = useState(false);
    const [alertState, setAlertState] = useState('loading'); // 'loading', 'success'
    const [generatedProject, setGeneratedProject] = useState(null);
    const [isEditMode, setIsEditMode] = useState(false);
    const [showWidgetEditModal, setShowWidgetEditModal] = useState(false);
    const [selectedWidget, setSelectedWidget] = useState(null);
    const [widgetEditData, setWidgetEditData] = useState({
        title: '',
        description: '',
        userPrompt: '',
    });
    const [isGeneratingWidget, setIsGeneratingWidget] = useState(false);
    const [dashboardMode, setDashboardMode] = useState(initialDashboardMode); // 'default' or 'ai_assistant'
    const [aiContext, setAiContext] = useState('general'); // 'general', 'projects', 'clients', 'bobbi-flow', 'calendar', 'analytics'
    const [rightSidebarCollapsed, setRightSidebarCollapsed] = useState(false);

    // Chat state
    const [chatMessages, setChatMessages] = useState([]);
    const [isChatLoading, setIsChatLoading] = useState(false);
    const [currentChatId, setCurrentChatId] = useState(null);
    const [showChatSidebar, setShowChatSidebar] = useState(false);

    // Collapsible sections state
    const [workCollapsed, setWorkCollapsed] = useState(false);
    const [systemCollapsed, setSystemCollapsed] = useState(false);

    // AI Generation form state
    const [aiFormData, setAiFormData] = useState({
        projectType: 'personal', // 'personal' or 'client'
        clientId: '',
        projectDescription: '',
        timeFrame: 'hours', // 'hours', 'days', 'weeks'
        timeValue: '',
        taskCount: 3,
        useTimeEstimate: true // true for time-based, false for task count
    });

    const defaultStats = {
        totalClients: 0,
        totalProjects: 0,
        totalTasks: 0,
        pendingTasks: 0,
        revenue: 0,
        recentProposals: [],
        recentTasks: [],
        recentProjects: []
    };

    const statsData = stats || defaultStats;

    const handleManualCreation = () => {
        setShowNewProjectModal(false);
        router.visit('/projects/create');
    };

    const handleAIGeneration = () => {
        setShowNewProjectModal(false);
        setShowAISetupModal(true);
    };

    const handleAISetupSubmit = () => {
        // Validate form data
        if (!aiFormData.projectDescription.trim()) {
            alert('Please enter a project description');
            return;
        }

        if (aiFormData.projectType === 'client' && !aiFormData.clientId) {
            alert('Please select a client');
            return;
        }

        if (aiFormData.useTimeEstimate && !aiFormData.timeValue) {
            alert('Please enter the time estimate');
            return;
        }

        if (!aiFormData.useTimeEstimate && !aiFormData.taskCount) {
            alert('Please enter the number of tasks');
            return;
        }

        setShowAISetupModal(false);
        setShowAlert(true);
        setAlertState('loading');
        setGeneratedProject(null);

        // Start AI generation process using fetch for AJAX
        fetch('/ai/generate-project', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(aiFormData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setAlertState('success');
                setGeneratedProject(data.project);
            } else {
                setAlertState('error');
                console.error('AI generation failed:', data.message);
            }
        })
        .catch(error => {
            setAlertState('error');
            console.error('AI generation failed:', error);
        });
    };

    const resetAIForm = () => {
        setAiFormData({
            projectType: 'personal',
            clientId: '',
            projectDescription: '',
            timeFrame: 'hours',
            timeValue: '',
            taskCount: 3,
            useTimeEstimate: true
        });
    };

    const handleViewProject = () => {
        if (generatedProject) {
            router.visit(`/projects/${generatedProject.id}`);
        }
    };

    const quickStats = [
        {
            title: 'Active Projects',
            value: statsData.totalProjects || '0',
            icon: Briefcase,
            description: 'Projects in progress',
            trend: '+3 this week'
        },
        {
            title: 'Total Clients',
            value: statsData.totalClients || '0',
            icon: Users,
            description: 'Active clients',
            trend: '+2 this month'
        },
        {
            title: 'Pending Tasks',
            value: statsData.pendingTasks || '0',
            icon: Clock,
            description: 'Tasks to complete',
            trend: '-5 from yesterday'
        },
        {
            title: 'Revenue',
            value: `$${(statsData.revenue || 0).toLocaleString()}`,
            icon: DollarSign,
            description: 'This month',
            trend: '+12% from last month'
        }
    ];

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
            case 'done':
                return 'bg-green-500';
            case 'in-progress':
            case 'in_progress':
                return 'bg-blue-500';
            case 'planned':
            case 'todo':
                return 'bg-gray-500';
            default:
                return 'bg-gray-500';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'completed':
            case 'done':
                return 'Completed';
            case 'in-progress':
            case 'in_progress':
                return 'In Progress';
            case 'planned':
            case 'todo':
                return 'Planned';
            default:
                return 'Unknown';
        }
    };

    const getPriorityColor = (priority) => {
        switch (priority) {
            case 'high':
                return 'bg-red-100 text-red-800';
            case 'medium':
                return 'bg-yellow-100 text-yellow-800';
            case 'low':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getTaskStatusIcon = (status) => {
        switch (status) {
            case 'completed':
            case 'done':
                return <CheckCircle className="w-4 h-4 text-green-600" />;
            case 'in-progress':
            case 'in_progress':
                return <Clock className="w-4 h-4 text-blue-600" />;
            case 'todo':
                return <AlertCircle className="w-4 h-4 text-orange-600" />;
            default:
                return <AlertCircle className="w-4 h-4 text-gray-600" />;
        }
    };

    const handleCustomizeClick = () => {
        setIsEditMode(!isEditMode);
    };

    const handleDashboardModeChange = async (mode) => {
        try {
            const response = await fetch('/api/user/dashboard-mode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    dashboard_mode: mode
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                setDashboardMode(mode);
                setIsEditMode(false);
            } else {
                console.error('Failed to update dashboard mode:', data.message);
            }
        } catch (error) {
            console.error('Error updating dashboard mode:', error);
        }
    };

    const handleContextChange = (context) => {
        setAiContext(context);
        // Reset chat when context changes
        setCurrentChatId(null);
        setChatMessages([]);
    };

    // Chat handlers
    const handleSendMessage = async (message) => {
        try {
            // If no current chat, create a new one
            if (!currentChatId) {
                const createResponse = await fetch('/api/chats', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: aiContext,
                        first_message: message
                    })
                });

                if (createResponse.ok) {
                    const chatData = await createResponse.json();
                    setCurrentChatId(chatData.chat.id);
                } else {
                    console.error('Failed to create chat');
                    return;
                }
            }

            // Add user message to chat
            const userMessage = {
                role: 'user',
                content: message,
                user: auth.user?.name
            };

            setChatMessages(prev => [...prev, userMessage]);
            setIsChatLoading(true);

            // Send message to server
            const messageResponse = await fetch(`/api/chats/${currentChatId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    content: message,
                    role: 'user'
                })
            });

            if (!messageResponse.ok) {
                console.error('Failed to send message');
                setIsChatLoading(false);
                return;
            }

            // Simulate AI processing and response
            setTimeout(() => {
                // Add processing message
                const processingMessage = {
                    role: 'assistant',
                    type: 'processing',
                    intent: 'Processing your request...',
                    agent: 'Bobbi'
                };

                setChatMessages(prev => [...prev, processingMessage]);

                // Simulate AI response after processing
                setTimeout(async () => {
                    // Send assistant response to server
                    await fetch(`/api/chats/${currentChatId}/messages`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            content: `I've processed your request: "${message}". Here's what I found...`,
                            role: 'assistant'
                        })
                    });

                    const responseMessage = {
                        role: 'assistant',
                        type: 'response',
                        summary: `Response to: ${message}`,
                        content: `I've processed your request: "${message}". Here's what I found...`
                    };

                    setChatMessages(prev => [...prev, responseMessage]);
                    setIsChatLoading(false);
                }, 2000);
            }, 1000);

        } catch (error) {
            console.error('Error sending message:', error);
            setIsChatLoading(false);
        }
    };

    const handlePresetClick = (prompt) => {
        handleSendMessage(prompt);
    };

    // Chat management functions
    const handleNewChat = () => {
        setCurrentChatId(null);
        setChatMessages([]);
        setShowChatSidebar(false);
    };

    const handleChatSelect = async (chatId) => {
        try {
            const response = await fetch(`/api/chats/${chatId}`);
            if (response.ok) {
                const data = await response.json();
                setCurrentChatId(chatId);
                setChatMessages(data.messages);
                setShowChatSidebar(false);
            }
        } catch (error) {
            console.error('Error loading chat:', error);
        }
    };

    const handleDeleteChat = async (chatId) => {
        try {
            const response = await fetch(`/api/chats/${chatId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                if (currentChatId === chatId) {
                    setCurrentChatId(null);
                    setChatMessages([]);
                }
            }
        } catch (error) {
            console.error('Error deleting chat:', error);
        }
    };

    const handleEditChat = (chatId) => {
        // TODO: Implement chat editing functionality
        console.log('Edit chat:', chatId);
    };

    const getContextPrompts = (context) => {
        switch (context) {
            case 'projects':
                return [
                    'Show me all active projects with their progress',
                    'Create a new project timeline and milestones',
                    'Generate a project progress report for this month',
                    'Find projects with overdue tasks',
                    'Review project budget and expenses',
                    'Assign tasks to team members',
                    'Schedule project meetings',
                    'Track project deliverables'
                ];
            case 'clients':
                return [
                    'List all clients with their contact information',
                    'Show me clients with outstanding invoices',
                    'Generate a client satisfaction report',
                    'Find clients with recent activity',
                    'Schedule client meetings',
                    'Track client communications',
                    'Create client proposals',
                    'Review client feedback'
                ];
            case 'bobbi-flow':
                return [
                    'Show me the current workflow status',
                    'List all automated processes',
                    'Generate a workflow efficiency report',
                    'Find bottlenecks in current processes',
                    'Create workflow templates',
                    'Track workflow metrics',
                    'Automate workflow steps',
                    'Generate workflow reports'
                ];
            case 'calendar':
                return [
                    'Show me all meetings this week',
                    'List upcoming deadlines',
                    'Generate a calendar summary',
                    'Find conflicting appointments',
                    'Block time for focused work',
                    'Coordinate team schedules',
                    'Set up recurring events',
                    'Track time allocation'
                ];
            case 'analytics':
                return [
                    'Generate a revenue report for this quarter',
                    'Show me performance metrics',
                    'Create a trend analysis report',
                    'Find areas for improvement',
                    'Track business KPIs',
                    'Monitor trends and patterns',
                    'Generate insights dashboard',
                    'Export analytics data'
                ];
            default:
                return [
                    'Show me invoices ranked by amount',
                    'Generate a project report',
                    'Show me all pending tasks for this week',
                    'Create a summary of client communications',
                    'Review and update task priorities across all projects',
                    'Create a proposal template for new client work',
                    'Analyze project performance and identify bottlenecks',
                    'Schedule team meetings for the upcoming week'
                ];
        }
    };

    const handleWidgetClick = async (widgetType, widgetKey) => {
        if (!isEditMode) return;

        try {
            const response = await fetch('/api/dashboard-widgets/info', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    widget_type: widgetType,
                    widget_key: widgetKey,
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                setSelectedWidget(data.data);
                setWidgetEditData({
                    title: data.data.title || '',
                    description: data.data.description || '',
                    userPrompt: '',
                });
                setShowWidgetEditModal(true);
            }
        } catch (error) {
            console.error('Error getting widget info:', error);
        }
    };

    const handleGenerateWidget = async () => {
        if (!selectedWidget || !widgetEditData.userPrompt.trim()) {
            alert('Please enter a prompt for AI generation');
            return;
        }

        setIsGeneratingWidget(true);

        try {
            const response = await fetch('/api/dashboard-widgets/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    widget_type: selectedWidget.widget_type,
                    widget_key: selectedWidget.widget_key,
                    user_prompt: widgetEditData.userPrompt,
                    current_configuration: selectedWidget.configuration || {},
                })
            });

                        const data = await response.json();

            if (data.status === 'success') {
                setSelectedWidget(data.data);
                setWidgetEditData({
                    title: data.data.title || '',
                    description: data.data.description || '',
                    userPrompt: '',
                });
                // Refresh the page to show updated widgets
                window.location.reload();
            } else if (data.can_fulfill === false) {
                // Show context-aware error message
                let errorMessage = data.message + '\n\n';
                if (data.suggestions && data.suggestions.length > 0) {
                    errorMessage += 'Suggestions:\n' + data.suggestions.join('\n');
                }
                if (data.recommendations && data.recommendations.length > 0) {
                    errorMessage += '\n\nRecommendations:\n' + data.recommendations.join('\n');
                }
                alert(errorMessage);
            } else {
                alert('Failed to generate widget: ' + data.message);
            }
        } catch (error) {
            console.error('Error generating widget:', error);
            alert('Failed to generate widget');
        } finally {
            setIsGeneratingWidget(false);
        }
    };

            const handleUpdateWidget = async () => {
        if (!selectedWidget || !widgetEditData.title.trim()) {
            alert('Please enter a title for the widget');
            return;
        }

        try {
            const response = await fetch('/api/dashboard-widgets/update', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    widget_type: selectedWidget.widget_type,
                    widget_key: selectedWidget.widget_key,
                    title: widgetEditData.title,
                    description: widgetEditData.description,
                    configuration: selectedWidget.configuration || {},
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                setShowWidgetEditModal(false);
                // Refresh the page to show updated widgets
                window.location.reload();
            } else {
                alert('Failed to update widget: ' + data.message);
            }
        } catch (error) {
            console.error('Error updating widget:', error);
            alert('Failed to update widget');
        }
    };

    const handleDeleteWidget = async () => {
        if (!selectedWidget) {
            return;
        }

        if (!confirm('Are you sure you want to delete this widget? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch('/api/dashboard-widgets/delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    widget_type: selectedWidget.widget_type,
                    widget_key: selectedWidget.widget_key,
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                setShowWidgetEditModal(false);
                // Refresh the page to show updated widgets
                window.location.reload();
            } else {
                alert('Failed to delete widget: ' + data.message);
            }
        } catch (error) {
            console.error('Error deleting widget:', error);
            alert('Failed to delete widget');
        }
    };

    // Function to render widgets based on their configuration
    const renderWidgets = () => {
        const quickStatsWidgets = widgets.filter(w => w.widget_type === 'quick_stats');
        const recentTasksWidget = widgets.find(w => w.widget_type === 'recent_tasks');
        const recentProjectsWidget = widgets.find(w => w.widget_type === 'recent_projects');
        const quickActionsWidget = widgets.find(w => w.widget_type === 'quick_actions');
        const recentProposalsWidget = widgets.find(w => w.widget_type === 'recent_proposals');

        return {
            quickStatsWidgets,
            recentTasksWidget,
            recentProjectsWidget,
            quickActionsWidget,
            recentProposalsWidget,
        };
    };

    const widgetData = renderWidgets();

    return (
        <AuthenticatedLayout
            user={auth.user}
            currentPage="dashboard"
            onCustomizeClick={handleCustomizeClick}
            isEditMode={isEditMode}
            dashboardMode={dashboardMode}
            onDashboardModeChange={handleDashboardModeChange}
        >
            <Head title="Dashboard" />

            {dashboardMode === 'ai_assistant' ? (
                <div className="fixed inset-0 bg-background">
                    {/* AI Assistant Layout - Full Screen */}
                    <div className="flex h-full">
                        {/* Left Sidebar */}
                        <div className="w-64 bg-sidebar border-r border-sidebar-border flex flex-col">
                            {/* Logo */}
                            <div className="p-6 border-b border-sidebar-border">
                                <div className="flex items-center space-x-2">
                                    <div className="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                                        <Brain className="w-5 h-5 text-primary-foreground" />
                                    </div>
                                    <span className="text-xl font-bold text-sidebar-foreground">Bobbi</span>
                                </div>
                            </div>

                            {/* Navigation */}
                            <div className="flex-1 p-6 space-y-4">
                                <Button
                                    className="w-full justify-start bg-primary text-primary-foreground hover:bg-primary/90"
                                    onClick={() => handleContextChange('general')}
                                >
                                    <Plus className="w-4 h-4 mr-2" />
                                    New Chat
                                </Button>

                                {/* Work Section */}
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <h3 className="text-xs font-semibold text-sidebar-foreground/70 uppercase tracking-wider">Work</h3>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="h-6 w-6 p-0 text-sidebar-foreground/70 hover:text-sidebar-foreground"
                                            onClick={() => setWorkCollapsed(!workCollapsed)}
                                        >
                                            {workCollapsed ? <ChevronRight className="w-3 h-3" /> : <ChevronDown className="w-3 h-3" />}
                                        </Button>
                                    </div>
                                    {!workCollapsed && (
                                        <div className="space-y-1 ml-2">
                                            <Button
                                                variant="ghost"
                                                className={`w-full justify-start text-sidebar-foreground hover:text-sidebar-accent-foreground hover:bg-sidebar-accent ${aiContext === 'projects' ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''}`}
                                                onClick={() => handleContextChange('projects')}
                                            >
                                                <Briefcase className="w-4 h-4 mr-2" />
                                                Projects
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                className={`w-full justify-start text-sidebar-foreground hover:text-sidebar-accent-foreground hover:bg-sidebar-accent ${aiContext === 'clients' ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''}`}
                                                onClick={() => handleContextChange('clients')}
                                            >
                                                <Users className="w-4 h-4 mr-2" />
                                                Clients
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                className={`w-full justify-start text-sidebar-foreground hover:text-sidebar-accent-foreground hover:bg-sidebar-accent ${aiContext === 'bobbi-flow' ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''}`}
                                                onClick={() => handleContextChange('bobbi-flow')}
                                            >
                                                <Target className="w-4 h-4 mr-2" />
                                                Bobbi Flow
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                className={`w-full justify-start text-sidebar-foreground hover:text-sidebar-accent-foreground hover:bg-sidebar-accent ${aiContext === 'calendar' ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''}`}
                                                onClick={() => handleContextChange('calendar')}
                                            >
                                                <Calendar className="w-4 h-4 mr-2" />
                                                Calendar
                                            </Button>
                                        </div>
                                    )}
                                </div>

                                {/* System Section */}
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <h3 className="text-xs font-semibold text-sidebar-foreground/70 uppercase tracking-wider">System</h3>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="h-6 w-6 p-0 text-sidebar-foreground/70 hover:text-sidebar-foreground"
                                            onClick={() => setSystemCollapsed(!systemCollapsed)}
                                        >
                                            {systemCollapsed ? <ChevronRight className="w-3 h-3" /> : <ChevronDown className="w-3 h-3" />}
                                        </Button>
                                    </div>
                                    {!systemCollapsed && (
                                        <div className="space-y-1 ml-2">
                                            <Button
                                                variant="ghost"
                                                className={`w-full justify-start text-sidebar-foreground hover:text-sidebar-accent-foreground hover:bg-sidebar-accent ${aiContext === 'analytics' ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''}`}
                                                onClick={() => handleContextChange('analytics')}
                                            >
                                                <BarChart3 className="w-4 h-4 mr-2" />
                                                Analytics
                                            </Button>
                                        </div>
                                    )}
                                </div>

                                {/* Recent */}
                                <div className="mt-8 flex-1">
                                    <h3 className="text-sm font-semibold text-sidebar-foreground/70 mb-3">Recent</h3>
                                    <div className="space-y-2 overflow-y-auto max-h-32">
                                        {chatMessages.filter(msg => msg.role === 'user').slice(-5).map((message, index) => (
                                            <div
                                                key={index}
                                                className="text-sm text-sidebar-foreground hover:text-sidebar-accent-foreground cursor-pointer p-2 rounded hover:bg-sidebar-accent"
                                                onClick={() => handlePresetClick(message.content)}
                                            >
                                                {message.content.length > 30 ? `${message.content.substring(0, 30)}...` : message.content}
                                            </div>
                                        ))}
                                        {chatMessages.filter(msg => msg.role === 'user').length === 0 && (
                                            <>
                                                <div className="text-sm text-sidebar-foreground hover:text-sidebar-accent-foreground cursor-pointer p-2 rounded hover:bg-sidebar-accent">
                                                    Send me the detail file for t...
                                                </div>
                                                <div className="text-sm text-sidebar-foreground hover:text-sidebar-accent-foreground cursor-pointer p-2 rounded hover:bg-sidebar-accent">
                                                    I need to generate report
                                                </div>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Trial Info */}
                            <div className="p-6 border-t border-sidebar-border">
                                <div className="p-4 bg-gradient-to-br from-[#d1ff75]/10 to-[#d1ff75]/5 rounded-lg border border-[#d1ff75]/20 shadow-sm">
                                    <div className="flex items-center space-x-2 mb-2">
                                        <div className="w-2 h-2 bg-[#d1ff75] rounded-full animate-pulse"></div>
                                        <p className="text-sm font-medium text-sidebar-foreground">Your trial ends in 14 days</p>
                                    </div>
                                    <p className="text-xs text-sidebar-foreground/70 mb-3 leading-relaxed">
                                        Enjoy working with reports, extract data, advanced search experience and much more.
                                    </p>
                                    <Button className="w-full bg-[#d1ff75] hover:bg-[#d1ff75]/90 text-black font-medium text-sm shadow-sm">
                                        <ArrowRight className="w-4 h-4 mr-1" />
                                        Upgrade
                                    </Button>
                                </div>

                                {/* User Profile */}
                                <div className="mt-4 flex items-center space-x-3 p-3 bg-sidebar-accent rounded-lg">
                                    <div className="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                        <span className="text-sm font-medium text-primary-foreground">
                                            {auth.user?.name?.charAt(0) || 'U'}
                                        </span>
                                    </div>
                                    <div className="flex-1">
                                        <p className="text-sm font-medium text-sidebar-foreground">{auth.user?.name || 'User'}</p>
                                        <p className="text-xs text-sidebar-foreground/70">{auth.user?.email || 'user@example.com'}</p>
                                    </div>
                                    <ChevronDown className="w-4 h-4 text-sidebar-foreground/70" />
                                </div>
                            </div>
                        </div>

                        {/* Chat Sidebar */}
                        {showChatSidebar && (
                            <RecentChatsSidebar
                                currentChatId={currentChatId}
                                onChatSelect={handleChatSelect}
                                onNewChat={handleNewChat}
                                onDeleteChat={handleDeleteChat}
                                onEditChat={handleEditChat}
                                chatType={aiContext}
                                collapsed={false}
                            />
                        )}

                        {/* Main Content */}
                        <div className="flex-1 bg-background flex flex-col">
                            <div className="flex items-center justify-between p-4 border-b border-border">
                                <div className="flex items-center space-x-2">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => setShowChatSidebar(!showChatSidebar)}
                                        className="text-muted-foreground hover:text-foreground"
                                    >
                                        <MessageSquare className="w-4 h-4 mr-2" />
                                        {aiContext === 'general' ? 'Recent Chats' : `${aiContext.charAt(0).toUpperCase() + aiContext.slice(1)} Chats`}
                                    </Button>
                                </div>
                            </div>
                            <ChatInterface
                                messages={chatMessages}
                                onSendMessage={handleSendMessage}
                                isLoading={isChatLoading}
                                onPresetClick={handlePresetClick}
                                context={aiContext}
                                presetPrompts={getContextPrompts(aiContext)}
                            />
                        </div>

                        {/* Right Sidebar */}
                        <div className={`${rightSidebarCollapsed ? 'w-16' : 'w-80'} bg-sidebar border-l border-sidebar-border flex flex-col transition-all duration-300`}>
                            <div className="p-6 border-b border-sidebar-border">
                                <div className="flex items-center justify-between">
                                    {!rightSidebarCollapsed && (
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="outline" size="sm" className="text-sidebar-foreground/70 hover:text-sidebar-foreground">
                                                    <Settings className="w-4 h-4 mr-2" />
                                                    Customize
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                {dashboardMode === 'ai_assistant' ? (
                                                    <DropdownMenuItem onClick={() => handleDashboardModeChange('default')}>
                                                        <Home className="w-4 h-4 mr-2" />
                                                        Classic Mode
                                                    </DropdownMenuItem>
                                                ) : (
                                                    <DropdownMenuItem onClick={() => handleDashboardModeChange('ai_assistant')}>
                                                        <Brain className="w-4 h-4 mr-2" />
                                                        Chat Mode
                                                    </DropdownMenuItem>
                                                )}
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    )}
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="text-sidebar-foreground/70 hover:text-sidebar-foreground"
                                        onClick={() => setRightSidebarCollapsed(!rightSidebarCollapsed)}
                                    >
                                        {rightSidebarCollapsed ? <ChevronLeft className="w-4 h-4" /> : <ChevronRight className="w-4 h-4" />}
                                    </Button>
                                </div>
                            </div>

                                                        {/* Dashboard Widgets */}
                            {!rightSidebarCollapsed && (
                                <div className="flex-1 p-6 space-y-4 overflow-y-auto">
                                    {widgetData.quickStatsWidgets.slice(0, 4).map((widget, index) => {
                                        // Calculate dynamic value based on widget configuration
                                        const getDynamicValue = (widget) => {
                                            const config = widget.configuration || {};
                                            const metricType = config.metric_type || '';
                                            const metricFilter = config.metric_filter || '';

                                            // Generate the dynamic key that matches the backend
                                            const dynamicKey = `dynamic_${metricType}_${metricFilter}`;

                                            // Try to get the dynamic value first
                                            if (statsData[dynamicKey] !== undefined) {
                                                if (metricType === 'revenue') {
                                                    return `$${(statsData[dynamicKey] || 0).toLocaleString()}`;
                                                }
                                                return statsData[dynamicKey]?.toString() || '0';
                                            }

                                            // Fallback to static values if dynamic key doesn't exist
                                            switch (metricType) {
                                                case 'projects':
                                                    if (metricFilter === 'active') {
                                                        return statsData.totalProjects || '0';
                                                    }
                                                    return statsData.totalProjects || '0';

                                                case 'clients':
                                                    return statsData.totalClients || '0';

                                                case 'tasks':
                                                    if (metricFilter === 'pending') {
                                                        return statsData.pendingTasks || '0';
                                                    }
                                                    return statsData.totalTasks || '0';

                                                case 'proposals':
                                                    if (metricFilter === 'draft') {
                                                        return statsData.dynamic_proposals_draft || '0';
                                                    } else if (metricFilter === 'sent') {
                                                        return statsData.dynamic_proposals_sent || '0';
                                                    } else if (metricFilter === 'accepted') {
                                                        return statsData.dynamic_proposals_accepted || '0';
                                                    }
                                                    return statsData.dynamic_proposals_all || '0';

                                                case 'revenue':
                                                    return '0'; // Revenue widget removed

                                                case 'subtasks':
                                                    return '0'; // Will be calculated dynamically

                                                default:
                                                    return '0';
                                            }
                                        };

                                        const dynamicValue = getDynamicValue(widget);
                                        const trend = widget.configuration?.trend || '+0 this week';

                                        return (
                                            <Card key={widget.widget_key} className="bg-card border-border">
                                                <CardHeader className="pb-2">
                                                    <CardTitle className="text-sm font-medium text-muted-foreground">
                                                        {widget.title}
                                                    </CardTitle>
                                                </CardHeader>
                                                <CardContent>
                                                    <div className="text-2xl font-bold text-foreground">{dynamicValue}</div>
                                                    <p className="text-xs text-muted-foreground">{trend}</p>
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>
                            )}


                        </div>
                    </div>
                </div>
            ) : (
                <div className="space-y-6">
                    {/* Header */}
                    <div className="flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">Dashboard</h1>
                            <p className="text-muted-foreground">Welcome back! Here's what's happening with your projects.</p>
                        </div>
                        <div className="flex items-center space-x-2">
                            <Link href="/calendar">
                                <Button variant="outline">
                                    <Calendar className="w-4 h-4 mr-2" />
                                    View Calendar
                                </Button>
                            </Link>
                            <Button onClick={() => setShowNewProjectModal(true)}>
                                <Plus className="w-4 h-4 mr-2" />
                                New Project
                            </Button>
                        </div>
                    </div>

                                                {/* Quick Stats */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {widgetData.quickStatsWidgets.map((widget, index) => {
                        // Calculate dynamic value based on widget configuration
                        const getDynamicValue = (widget) => {
                            const config = widget.configuration || {};
                            const metricType = config.metric_type || '';
                            const metricFilter = config.metric_filter || '';

                            // Generate the dynamic key that matches the backend
                            const dynamicKey = `dynamic_${metricType}_${metricFilter}`;

                            // Try to get the dynamic value first
                            if (statsData[dynamicKey] !== undefined) {
                                if (metricType === 'revenue') {
                                    return `$${(statsData[dynamicKey] || 0).toLocaleString()}`;
                                }
                                return statsData[dynamicKey]?.toString() || '0';
                            }

                            // Fallback to static values if dynamic key doesn't exist
                            switch (metricType) {
                                case 'projects':
                                    if (metricFilter === 'active') {
                                        return statsData.totalProjects || '0';
                                    }
                                    return statsData.totalProjects || '0';

                                case 'clients':
                                    return statsData.totalClients || '0';

                                case 'tasks':
                                    if (metricFilter === 'pending') {
                                        return statsData.pendingTasks || '0';
                                    }
                                    return statsData.totalTasks || '0';

                                                                case 'proposals':
                                    if (metricFilter === 'draft') {
                                        return statsData.dynamic_proposals_draft || '0';
                                    } else if (metricFilter === 'sent') {
                                        return statsData.dynamic_proposals_sent || '0';
                                    } else if (metricFilter === 'accepted') {
                                        return statsData.dynamic_proposals_accepted || '0';
                                    }
                                    return statsData.dynamic_proposals_all || '0';

                                case 'revenue':
                                    return '0'; // Revenue widget removed

                                case 'subtasks':
                                    return '0'; // Will be calculated dynamically

                                default:
                                    return '0';
                            }
                        };

                        const dynamicValue = getDynamicValue(widget);
                        const trend = widget.configuration?.trend || '+0 this week';

                        return (
                            <Card
                                key={widget.widget_key}
                                className={`bg-card border-border relative ${isEditMode ? 'cursor-pointer hover:bg-muted/50 transition-colors' : ''}`}
                                onClick={() => isEditMode && handleWidgetClick('quick_stats', widget.widget_key)}
                            >
                                {isEditMode && (
                                    <div className="absolute top-2 right-2 z-10">
                                        <Edit className="w-5 h-5 text-[#d1ff75] animate-pulse" />
                                    </div>
                                )}
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-sm font-medium text-muted-foreground">
                                        {widget.title}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-foreground">{dynamicValue}</div>
                                    <p className="text-xs text-muted-foreground">{widget.description}</p>
                                    <p className="text-xs text-muted-foreground mt-1">{trend}</p>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Main Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Recent Tasks & Projects */}
                    <div className="lg:col-span-2 space-y-6">
                                                {/* Recent Tasks */}
                        {widgetData.recentTasksWidget && (
                            <Card
                                className={`bg-card border-border relative ${isEditMode ? 'cursor-pointer hover:bg-muted/50 transition-colors' : ''}`}
                                onClick={() => isEditMode && handleWidgetClick('recent_tasks', widgetData.recentTasksWidget.widget_key)}
                            >
                                {isEditMode && (
                                    <div className="absolute top-2 right-2 z-10">
                                        <Edit className="w-5 h-5 text-[#d1ff75] animate-pulse" />
                                    </div>
                                )}
                                <CardHeader>
                                    <div className="flex justify-between items-center">
                                        <div>
                                            <CardTitle className="text-foreground">{widgetData.recentTasksWidget.title}</CardTitle>
                                            <CardDescription className="text-muted-foreground">{widgetData.recentTasksWidget.description}</CardDescription>
                                        </div>
                                        <Link href="/tasks">
                                            <Button variant="outline" size="sm">
                                                View All
                                                <ArrowRight className="w-4 h-4 ml-2" />
                                            </Button>
                                        </Link>
                                    </div>
                                </CardHeader>
                            <CardContent>
                                <div className="space-y-4 max-h-60 overflow-y-auto">
                                    {statsData.recentTasks && statsData.recentTasks.length > 0 ? (
                                        statsData.recentTasks.map((task) => (
                                            <div key={task.id} className="group">
                                                <div className="flex items-start space-x-3 p-3 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer relative">
                                                    {/* Zen Mode Button - appears on hover */}
                                                    <div className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10">
                                                        <Link href={`/zen-mode?task=${task.id}`}>
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                className="h-6 w-6 p-0 bg-background/80 backdrop-blur hover:bg-primary/10"
                                                                title="Enter Zen Mode"
                                                            >
                                                                <Zap className="w-3 h-3 text-primary" />
                                                            </Button>
                                                        </Link>
                                                    </div>
                                                    <Link href={`/tasks/${task.id}`} className="flex items-start space-x-3 flex-1">
                                                        {getTaskStatusIcon(task.status)}
                                                        <div className="flex-1 min-w-0">
                                                            <p className="text-sm font-medium text-foreground truncate">
                                                                {task.title}
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">{task.project_name}</p>
                                                            <div className="flex items-center space-x-2 mt-1">
                                                                <Badge className={getPriorityColor(task.priority)} size="sm">
                                                                    {task.priority}
                                                                </Badge>
                                                                {task.due_date && (
                                                                    <span className="text-xs text-muted-foreground">
                                                                        Due {new Date(task.due_date).toLocaleDateString()}
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </Link>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-8 text-muted-foreground">
                                            <Target className="w-8 h-8 mx-auto mb-2 opacity-50" />
                                            <p className="text-sm">No tasks yet</p>
                                            <p className="text-xs">Create your first task to get started</p>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                        )}

                        {/* Recent Projects */}
                        {widgetData.recentProjectsWidget && (
                            <Card
                                className={`bg-card border-border relative ${isEditMode ? 'cursor-pointer hover:bg-muted/50 transition-colors' : ''}`}
                                onClick={() => isEditMode && handleWidgetClick('recent_projects', widgetData.recentProjectsWidget.widget_key)}
                            >
                                {isEditMode && (
                                    <div className="absolute top-2 right-2 z-10">
                                        <Edit className="w-5 h-5 text-[#d1ff75] animate-pulse" />
                                    </div>
                                )}
                                <CardHeader>
                                    <div className="flex justify-between items-center">
                                        <div>
                                            <CardTitle className="text-foreground">{widgetData.recentProjectsWidget.title}</CardTitle>
                                            <CardDescription className="text-muted-foreground">{widgetData.recentProjectsWidget.description}</CardDescription>
                                        </div>
                                        <Link href="/projects">
                                            <Button variant="outline" size="sm">
                                                View All
                                                <ArrowRight className="w-4 h-4 ml-2" />
                                            </Button>
                                        </Link>
                                    </div>
                                </CardHeader>
                            <CardContent>
                                <div className="space-y-4 max-h-80 overflow-y-auto">
                                    {statsData.recentProjects && statsData.recentProjects.length > 0 ? (
                                        statsData.recentProjects.map((project) => (
                                            <Link key={project.id} href={`/projects/${project.id}`}>
                                                <div className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                                    <div className="flex-1">
                                                        <div className="flex items-center space-x-3">
                                                            <h3 className="font-medium text-foreground">{project.name}</h3>
                                                            <div className="flex items-center">
                                                                <div className={`w-2 h-2 rounded-full ${getStatusColor(project.status)} mr-2`}></div>
                                                                <span className="text-sm text-muted-foreground">{getStatusText(project.status)}</span>
                                                            </div>
                                                        </div>
                                                        <p className="text-sm text-muted-foreground mt-1">{project.client}</p>
                                                        <div className="flex items-center space-x-4 mt-2">
                                                            <div className="flex items-center space-x-2">
                                                                <Target className="w-4 h-4 text-muted-foreground" />
                                                                <span className="text-sm text-muted-foreground">
                                                                    {project.tasks.completed}/{project.tasks.total} tasks
                                                                </span>
                                                            </div>
                                                            {project.due_date && (
                                                                <div className="flex items-center space-x-2">
                                                                    <Calendar className="w-4 h-4 text-muted-foreground" />
                                                                    <span className="text-sm text-muted-foreground">
                                                                        Due {new Date(project.due_date).toLocaleDateString()}
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <div className="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                            <div
                                                                className="h-full bg-primary transition-all duration-300"
                                                                style={{ width: `${project.progress}%` }}
                                                            ></div>
                                                        </div>
                                                        <span className="text-sm font-medium text-muted-foreground">
                                                            {project.progress}%
                                                        </span>
                                                        <Button variant="ghost" size="sm">
                                                            <Eye className="w-4 h-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Link>
                                        ))
                                    ) : (
                                        <div className="text-center py-8 text-muted-foreground">
                                            <Briefcase className="w-8 h-8 mx-auto mb-2 opacity-50" />
                                            <p className="text-sm">No projects yet</p>
                                            <p className="text-xs">Create your first project to get started</p>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                        )}
                    </div>

                    {/* Quick Actions */}
                    <div className="space-y-6">
                        {widgetData.quickActionsWidget && (
                            <Card
                                className={`bg-card border-border relative ${isEditMode ? 'cursor-pointer hover:bg-muted/50 transition-colors' : ''}`}
                                onClick={() => isEditMode && handleWidgetClick('quick_actions', widgetData.quickActionsWidget.widget_key)}
                            >
                                {isEditMode && (
                                    <div className="absolute top-2 right-2 z-10">
                                        <Edit className="w-5 h-5 text-[#d1ff75] animate-pulse" />
                                    </div>
                                )}
                                <CardHeader>
                                    <CardTitle className="text-foreground">{widgetData.quickActionsWidget.title}</CardTitle>
                                    <CardDescription className="text-muted-foreground">{widgetData.quickActionsWidget.description}</CardDescription>
                                </CardHeader>
                            <CardContent className="space-y-3">
                                <Link href="/clients/create">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Users className="w-4 h-4 mr-2" />
                                        Add New Client
                                    </Button>
                                </Link>
                                <Button
                                    className="w-full justify-start"
                                    variant="outline"
                                    onClick={() => setShowNewProjectModal(true)}
                                >
                                    <FileText className="w-4 h-4 mr-2" />
                                    Create Project
                                </Button>
                                <Link href="/tasks/create">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Target className="w-4 h-4 mr-2" />
                                        Add Task
                                    </Button>
                                </Link>

                                <Link href="/zen-mode">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Zap className="w-4 h-4 mr-2" />
                                        Zen Mode
                                    </Button>
                                </Link>
                                <Link href="/playground">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Brain className="w-4 h-4 mr-2" />
                                        AI Playground
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                        )}
                    </div>
                </div>

                {/* Recent Proposals */}
                {widgetData.recentProposalsWidget && statsData.recentProposals && statsData.recentProposals.length > 0 && (
                    <Card
                        className={`bg-card border-border relative ${isEditMode ? 'cursor-pointer hover:bg-muted/50 transition-colors' : ''}`}
                        onClick={() => isEditMode && handleWidgetClick('recent_proposals', widgetData.recentProposalsWidget.widget_key)}
                    >
                        {isEditMode && (
                            <div className="absolute top-2 right-2 z-10">
                                <Edit className="w-5 h-5 text-[#d1ff75] animate-pulse" />
                            </div>
                        )}
                        <CardHeader>
                            <div className="flex justify-between items-center">
                                <div>
                                    <CardTitle className="text-foreground">{widgetData.recentProposalsWidget.title}</CardTitle>
                                    <CardDescription className="text-muted-foreground">{widgetData.recentProposalsWidget.description}</CardDescription>
                                </div>
                                <Link href="/proposals">
                                    <Button variant="outline" size="sm">
                                        View All
                                        <ArrowRight className="w-4 h-4 ml-2" />
                                    </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {statsData.recentProposals.map((proposal) => (
                                    <Link key={proposal.id} href={`/proposals/${proposal.id}`}>
                                        <div className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                            <div>
                                                <h3 className="font-medium text-foreground">{proposal.title}</h3>
                                                <p className="text-sm text-muted-foreground">{proposal.client_name}</p>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Badge variant={proposal.status === 'draft' ? 'outline' : 'default'}>
                                                    {proposal.status}
                                                </Badge>
                                                <span className="text-sm text-muted-foreground">{proposal.created_at}</span>
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
            )}

                        {/* New Project Modal */}
            <Dialog open={showNewProjectModal} onOpenChange={setShowNewProjectModal}>
                <DialogContent className="sm:max-w-lg">
                    <DialogHeader className="space-y-3">
                        <DialogTitle className="flex items-center space-x-3 text-xl">
                            <div className="p-2 bg-primary/10 rounded-lg">
                                <Plus className="w-6 h-6 text-primary" />
                            </div>
                            <span>Create New Project</span>
                        </DialogTitle>
                        <DialogDescription className="text-base leading-relaxed">
                            Choose how you'd like to create your new project
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4 py-6">
                        <div className="grid grid-cols-1 gap-4">
                            <Button
                                onClick={handleManualCreation}
                                className="h-auto p-6 flex items-start space-x-4 hover:bg-muted/50 transition-all duration-200 border-2 hover:border-primary/20"
                                variant="outline"
                            >
                                <div className="p-3 bg-blue-500/10 rounded-lg">
                                    <PenTool className="w-6 h-6 text-blue-500" />
                                </div>
                                <div className="text-left flex-1">
                                    <h3 className="font-semibold text-lg mb-1">Manual Creation</h3>
                                    <p className="text-sm text-muted-foreground leading-relaxed">
                                        Create a project manually with full control
                                    </p>
                                </div>
                            </Button>

                            <Button
                                onClick={handleAIGeneration}
                                className="h-auto p-6 flex items-start space-x-4 hover:bg-muted/50 transition-all duration-200 border-2 hover:border-primary/20"
                                variant="outline"
                            >
                                <div className="p-3 bg-purple-500/10 rounded-lg">
                                    <Brain className="w-6 h-6 text-purple-500" />
                                </div>
                                <div className="text-left flex-1">
                                    <h3 className="font-semibold text-lg mb-1">AI Generation</h3>
                                    <p className="text-sm text-muted-foreground leading-relaxed">
                                        Let AI generate a project with tasks for you
                                    </p>
                                </div>
                            </Button>
                        </div>
                    </div>
                    <DialogFooter className="pt-4">
                        <Button variant="outline" onClick={() => setShowNewProjectModal(false)} className="px-6">
                            Cancel
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* AI Setup Modal */}
            <Dialog open={showAISetupModal} onOpenChange={(open) => {
                setShowAISetupModal(open);
                if (!open) resetAIForm();
            }}>
                <DialogContent className="sm:max-w-2xl max-h-[80vh] overflow-y-auto">
                    <DialogHeader className="space-y-3">
                        <DialogTitle className="flex items-center space-x-3 text-xl">
                            <div className="p-2 bg-purple-500/10 rounded-lg">
                                <Brain className="w-6 h-6 text-purple-500" />
                            </div>
                            <span>AI Project Setup</span>
                        </DialogTitle>
                        <DialogDescription className="text-base leading-relaxed">
                            Tell us about your project and AI will generate it with tasks
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4 py-4">
                                                {/* Project Type Selection */}
                        <div className="space-y-2">
                            <Label className="text-base font-medium">Project Type</Label>
                            <RadioGroup
                                value={aiFormData.projectType}
                                onValueChange={(value) => setAiFormData({...aiFormData, projectType: value, clientId: ''})}
                                className="grid grid-cols-1 md:grid-cols-2 gap-3"
                            >
                                <div className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/50 transition-colors">
                                    <RadioGroupItem value="personal" id="personal" />
                                    <Label htmlFor="personal" className="flex items-center space-x-2 cursor-pointer">
                                        <User className="w-5 h-5 text-blue-500" />
                                        <div>
                                            <div className="font-medium">Personal Project</div>
                                            <div className="text-sm text-muted-foreground">For your own portfolio or learning</div>
                                        </div>
                                    </Label>
                                </div>
                                <div className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/50 transition-colors">
                                    <RadioGroupItem value="client" id="client" />
                                    <Label htmlFor="client" className="flex items-center space-x-2 cursor-pointer">
                                        <Building className="w-5 h-5 text-green-500" />
                                        <div>
                                            <div className="font-medium">Client Project</div>
                                            <div className="text-sm text-muted-foreground">For a specific client</div>
                                        </div>
                                    </Label>
                                </div>
                            </RadioGroup>
                        </div>

                        {/* Client Selection (if client project) */}
                        {aiFormData.projectType === 'client' && (
                            <div className="space-y-2">
                                <Label htmlFor="client-select" className="text-base font-medium">Select Client</Label>
                                <Select
                                    value={aiFormData.clientId}
                                    onValueChange={(value) => setAiFormData({...aiFormData, clientId: value})}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Choose a client" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {clients.map((client) => (
                                            <SelectItem key={client.id} value={client.id.toString()}>
                                                {client.first_name} {client.last_name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        {/* Project Description */}
                        <div className="space-y-2">
                            <Label htmlFor="project-description" className="text-base font-medium">Project Description</Label>
                            <Textarea
                                id="project-description"
                                placeholder="Describe what you want to build or accomplish..."
                                value={aiFormData.projectDescription}
                                onChange={(e) => setAiFormData({...aiFormData, projectDescription: e.target.value})}
                                rows={4}
                                className="resize-none"
                            />
                        </div>

                        {/* Time/Task Configuration */}
                        <div className="space-y-2">
                            <Label className="text-base font-medium">Task Generation</Label>
                            <RadioGroup
                                value={aiFormData.useTimeEstimate ? 'time' : 'count'}
                                onValueChange={(value) => setAiFormData({...aiFormData, useTimeEstimate: value === 'time'})}
                                className="grid grid-cols-1 md:grid-cols-2 gap-3"
                            >
                                <div className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/50 transition-colors">
                                    <RadioGroupItem value="time" id="time-based" />
                                    <Label htmlFor="time-based" className="flex items-center space-x-2 cursor-pointer">
                                        <Clock className="w-5 h-5 text-orange-500" />
                                        <div>
                                            <div className="font-medium">Time-based</div>
                                            <div className="text-sm text-muted-foreground">Generate tasks to fit your timeline</div>
                                        </div>
                                    </Label>
                                </div>
                                <div className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/50 transition-colors">
                                    <RadioGroupItem value="count" id="count-based" />
                                    <Label htmlFor="count-based" className="flex items-center space-x-2 cursor-pointer">
                                        <Target className="w-5 h-5 text-purple-500" />
                                        <div>
                                            <div className="font-medium">Task count</div>
                                            <div className="text-sm text-muted-foreground">Generate specific number of tasks</div>
                                        </div>
                                    </Label>
                                </div>
                            </RadioGroup>
                        </div>

                        {/* Time Estimate Input */}
                        {aiFormData.useTimeEstimate && (
                            <div className="space-y-2">
                                <Label className="text-base font-medium">Time Estimate</Label>
                                <div className="flex space-x-3">
                                    <Input
                                        type="number"
                                        placeholder="Enter time value"
                                        value={aiFormData.timeValue}
                                        onChange={(e) => setAiFormData({...aiFormData, timeValue: e.target.value})}
                                        className="flex-1"
                                        min="1"
                                    />
                                    <Select
                                        value={aiFormData.timeFrame}
                                        onValueChange={(value) => setAiFormData({...aiFormData, timeFrame: value})}
                                    >
                                        <SelectTrigger className="w-32">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="hours">Hours</SelectItem>
                                            <SelectItem value="days">Days</SelectItem>
                                            <SelectItem value="weeks">Weeks</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        )}

                        {/* Task Count Input */}
                        {!aiFormData.useTimeEstimate && (
                            <div className="space-y-2">
                                <Label className="text-base font-medium">Number of Tasks</Label>
                                <Input
                                    type="number"
                                    placeholder="Enter number of tasks"
                                    value={aiFormData.taskCount}
                                    onChange={(e) => setAiFormData({...aiFormData, taskCount: parseInt(e.target.value) || 3})}
                                    className="w-32"
                                    min="1"
                                    max="20"
                                />
                            </div>
                        )}
                    </div>

                                        <DialogFooter className="pt-3 space-x-2">
                        <Button
                            variant="outline"
                            onClick={() => {
                                setShowAISetupModal(false);
                                resetAIForm();
                            }}
                            className="px-4"
                        >
                            Cancel
                        </Button>
                                                <Button
                            onClick={handleAISetupSubmit}
                            className="px-4"
                            style={{ backgroundColor: '#d1ff75', color: '#000' }}
                        >
                            <Brain className="w-4 h-4 mr-2" />
                            Generate Project
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* AI Generation Alert */}
            {showAlert && (
                <div className="fixed bottom-4 right-4 z-50 max-w-sm">
                    <Alert className="border-l-4 border-l-primary">
                        <div className="flex items-start space-x-3">
                            {alertState === 'loading' ? (
                                <Loader2 className="w-5 h-5 animate-spin text-primary" />
                            ) : alertState === 'success' ? (
                                <CheckCircle2 className="w-5 h-5 text-green-600" />
                            ) : (
                                <AlertCircle className="w-5 h-5 text-red-600" />
                            )}
                            <div className="flex-1">
                                <AlertTitle>
                                    {alertState === 'loading' ? 'Generating Project...' :
                                     alertState === 'success' ? 'Project Generated!' :
                                     'Generation Failed'}
                                </AlertTitle>
                                <AlertDescription className="mt-1">
                                    {alertState === 'loading' ? (
                                        <div>
                                            <div>Creating project and 3 tasks...</div>
                                            <div className="text-xs text-muted-foreground mt-1">
                                                Press B + P to view when complete
                                            </div>
                                        </div>
                                    ) : alertState === 'success' ? (
                                        <div>
                                            <div>Project generated with 3 tasks</div>
                                            <div className="text-xs text-muted-foreground mt-1">
                                                Press B + P to view
                                            </div>
                                            {generatedProject && (
                                                <Button
                                                    variant="link"
                                                    className="p-0 h-auto text-primary mt-1"
                                                    onClick={handleViewProject}
                                                >
                                                    {generatedProject.name} <ExternalLink className="w-3 h-3 ml-1" />
                                                </Button>
                                            )}
                                        </div>
                                    ) : (
                                        <div>Something went wrong. Please try again.</div>
                                    )}
                                </AlertDescription>
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => setShowAlert(false)}
                                className="text-muted-foreground hover:text-foreground"
                            >
                                ×
                            </Button>
                        </div>
                    </Alert>
                </div>
            )}

            {/* Widget Edit Modal */}
            <Dialog open={showWidgetEditModal} onOpenChange={setShowWidgetEditModal}>
                <DialogContent className="sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle className="flex items-center space-x-3">
                            <div className="p-2 bg-primary/10 rounded-lg">
                                <Edit className="w-6 h-6 text-primary" />
                            </div>
                            <span>Edit Widget</span>
                        </DialogTitle>
                        <DialogDescription>
                            Customize your dashboard widget with AI assistance
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4 py-4">
                        <div className="space-y-2">
                            <Label htmlFor="ai-prompt">AI Prompt</Label>
                            <Textarea
                                id="ai-prompt"
                                value={widgetEditData.userPrompt}
                                onChange={(e) => setWidgetEditData({...widgetEditData, userPrompt: e.target.value})}
                                placeholder="Tell AI what you want this widget to do or show..."
                                rows={4}
                            />
                            <p className="text-xs text-muted-foreground">
                                Describe what you want the widget to display or how you want it to behave. After entering your prompt, click "Generate with AI" to populate the fields below.
                            </p>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="widget-title">Widget Title</Label>
                            <Input
                                id="widget-title"
                                value={widgetEditData.title}
                                onChange={(e) => setWidgetEditData({...widgetEditData, title: e.target.value})}
                                placeholder="Enter widget title"
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="widget-description">Description</Label>
                            <Textarea
                                id="widget-description"
                                value={widgetEditData.description}
                                onChange={(e) => setWidgetEditData({...widgetEditData, description: e.target.value})}
                                placeholder="Enter widget description"
                                rows={2}
                            />
                        </div>

                        {selectedWidget && (
                            <div className="space-y-2">
                                <Label>Current Configuration</Label>
                                <div className="p-3 bg-muted rounded-lg">
                                    <pre className="text-xs text-muted-foreground overflow-auto">
                                        {JSON.stringify(selectedWidget.configuration, null, 2)}
                                    </pre>
                                </div>
                            </div>
                        )}
                    </div>

                    <DialogFooter className="space-x-2">
                        <Button
                            variant="outline"
                            onClick={() => setShowWidgetEditModal(false)}
                        >
                            Cancel
                        </Button>
                        <Button
                            onClick={handleDeleteWidget}
                            variant="destructive"
                        >
                            Delete Widget
                        </Button>
                        <Button
                            onClick={handleUpdateWidget}
                            variant="outline"
                        >
                            Save Changes
                        </Button>
                        <Button
                            onClick={handleGenerateWidget}
                            disabled={isGeneratingWidget || !widgetEditData.userPrompt.trim()}
                            style={{ backgroundColor: '#d1ff75', color: '#000' }}
                        >
                            {isGeneratingWidget ? (
                                <>
                                    <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                    Generating...
                                </>
                            ) : (
                                <>
                                    <Brain className="w-4 h-4 mr-2" />
                                    Generate with AI
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}

