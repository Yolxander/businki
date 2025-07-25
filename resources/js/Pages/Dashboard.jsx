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
    ArrowRight as ArrowRightIcon
} from 'lucide-react';

export default function Dashboard({ auth, stats, clients = [] }) {
    const [showNewProjectModal, setShowNewProjectModal] = useState(false);
    const [showAISetupModal, setShowAISetupModal] = useState(false);
    const [showAlert, setShowAlert] = useState(false);
    const [alertState, setAlertState] = useState('loading'); // 'loading', 'success'
    const [generatedProject, setGeneratedProject] = useState(null);

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

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />

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
                    {quickStats.map((stat) => (
                        <Card key={stat.title} className="bg-card border-border">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    {stat.title}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-foreground">{stat.value}</div>
                                <p className="text-xs text-muted-foreground">{stat.description}</p>
                                <p className="text-xs text-muted-foreground mt-1">{stat.trend}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Main Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Recent Projects */}
                    <div className="lg:col-span-2">
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <div className="flex justify-between items-center">
                                    <div>
                                        <CardTitle className="text-foreground">Recent Projects</CardTitle>
                                        <CardDescription className="text-muted-foreground">Your active and recent projects</CardDescription>
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
                                <div className="space-y-4">
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
                    </div>

                    {/* Quick Actions & Recent Tasks */}
                    <div className="space-y-6">
                        {/* Quick Actions */}
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Quick Actions</CardTitle>
                                <CardDescription className="text-muted-foreground">Get things done faster</CardDescription>
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
                                <Link href="/proposals/create">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Zap className="w-4 h-4 mr-2" />
                                        Generate Proposal
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>

                        {/* Recent Tasks */}
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Recent Tasks</CardTitle>
                                <CardDescription className="text-muted-foreground">Latest task updates</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4 max-h-80 overflow-y-auto">
                                    {statsData.recentTasks && statsData.recentTasks.length > 0 ? (
                                        statsData.recentTasks.map((task) => (
                                            <Link key={task.id} href={`/tasks/${task.id}`}>
                                                <div className="flex items-start space-x-3 p-3 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
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
                                                </div>
                                            </Link>
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
                    </div>
                </div>

                {/* Recent Proposals */}
                {statsData.recentProposals && statsData.recentProposals.length > 0 && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex justify-between items-center">
                                <div>
                                    <CardTitle className="text-foreground">Recent Proposals</CardTitle>
                                    <CardDescription className="text-muted-foreground">Latest client proposals</CardDescription>
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
        </AuthenticatedLayout>
    );
}
