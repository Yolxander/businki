import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { toast } from 'sonner';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger,
} from '@/components/ui/dialog';
import {
    ArrowLeft,
    Edit,
    Calendar,
    Clock,
    User,
    Building,
    Tag,
    MessageSquare,
    CheckCircle,
    AlertCircle,
    Circle,
    Play,
    Pause,
    Eye,
    CheckCircle2,
    Plus,
    MoreHorizontal,
    Trash2,
    Copy,
    ExternalLink,
    FileText,
    Link as LinkIcon,
    Flag,
    Star,
    Target,
    MessageSquare as MessageSquareIcon,
    Download,
    Share,
    MoreVertical,
    Trash2 as Trash2Icon,
    Brain,
    PenTool,
    Users,
    Zap,
    X
} from 'lucide-react';

export default function ProjectDetails({ auth, project }) {
    const [activeTab, setActiveTab] = useState('overview');
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const [showTaskCreationModal, setShowTaskCreationModal] = useState(false);
    const [generatingTasks, setGeneratingTasks] = useState(false);
    const [visibleTasks, setVisibleTasks] = useState(new Set());
    const [tasks, setTasks] = useState([]);

    // Use real project data or fallback to mock data for development
    const projectData = project || {
        id: 1,
        name: 'Website Redesign',
        description: 'Complete redesign of the company website with modern UI/UX, responsive design, and improved performance.',
        client: { first_name: 'Acme', last_name: 'Corp' },
        status: 'in_progress',
        priority: 'high',
        progress: 65,
        start_date: '2024-01-15',
        due_date: '2024-02-15',
        tasks: [],
        milestones: [],
        team: []
    };

    // Initialize tasks from project data
    React.useEffect(() => {
        if (projectData.tasks) {
            setTasks(projectData.tasks);

            // Make existing tasks visible immediately
            const existingTaskIds = new Set(projectData.tasks.map(task => task.id));
            setVisibleTasks(existingTaskIds);
        }
    }, [projectData.tasks]);

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in_progress':
                return 'bg-blue-100 text-blue-800';
            case 'not_started':
                return 'bg-gray-100 text-gray-800';
            case 'planned':
                return 'bg-yellow-100 text-yellow-800';
            case 'paused':
                return 'bg-orange-100 text-orange-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
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
                return <CheckCircle className="w-4 h-4 text-green-600" />;
            case 'in-progress':
                return <Clock className="w-4 h-4 text-blue-600" />;
            case 'todo':
                return <AlertCircle className="w-4 h-4 text-orange-600" />;
            default:
                return <AlertCircle className="w-4 h-4 text-gray-600" />;
        }
    };

    const completedTasks = projectData.tasks ? projectData.tasks.filter(task => task.status === 'completed').length : 0;
    const totalTasks = projectData.tasks ? projectData.tasks.length : 0;

    const handleDeleteProject = () => {
        const deleteUrl = `/projects/${projectData.id}`;
        console.log('Attempting to delete project:', projectData.id);
        console.log('Delete URL:', deleteUrl);
        router.delete(deleteUrl, {
            onError: (errors) => {
                console.error('Delete project error:', errors);
                toast.error('Failed to delete project. Please try again.');
            }
        });
    };

    const handleManualTaskCreation = () => {
        setShowTaskCreationModal(false);
        // Navigate to task creation with project pre-selected
        router.visit(`/tasks/create?project_id=${projectData.id}`);
    };

    const handleAITaskGeneration = () => {
        setShowTaskCreationModal(false);
        generateTasks();
    };

    const generateTasks = async () => {
        setGeneratingTasks(true);
        try {
            const response = await fetch(`/api/projects/${projectData.id}/generate-tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    project_description: projectData.description || 'Project description not available',
                    project_scope: projectData.description || 'Project scope not available',
                    max_tasks: 2,
                    timeline: [],
                    include_subtasks: false
                })
            });

            if (response.ok) {
                const result = await response.json();
                const newTasks = result.data;

                // Add new tasks to the existing list
                setTasks(prev => [...prev, ...newTasks]);

                // Staggered fade-in animation for each task
                const newTaskIds = newTasks.map(task => task.id);
                newTaskIds.forEach((taskId, index) => {
                    setTimeout(() => {
                        setVisibleTasks(prev => new Set([...prev, taskId]));
                    }, index * 200); // 200ms delay between each task
                });

                toast.success(`Generated ${newTasks.length} tasks successfully`);
            } else {
                const error = await response.json();
                toast.error(error.message || 'Failed to generate tasks');
            }
        } catch (error) {
            console.error('Error generating tasks:', error);
            toast.error('Failed to generate tasks');
        } finally {
            setGeneratingTasks(false);
        }
    };

    const deleteTask = async (taskId) => {
        try {
            const response = await fetch(`/api/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                // Remove from visible tasks first for fade-out effect
                setVisibleTasks(prev => {
                    const newSet = new Set(prev);
                    newSet.delete(taskId);
                    return newSet;
                });

                // Remove from tasks list after fade-out
                setTimeout(() => {
                    setTasks(prev => prev.filter(task => task.id !== taskId));
                }, 300);

                toast.success('Task deleted successfully');
            } else {
                const error = await response.json();
                toast.error(error.message || 'Failed to delete task');
            }
        } catch (error) {
            console.error('Error deleting task:', error);
            toast.error('Failed to delete task');
        }
    };

    // Task Creation Modal Component
    const TaskCreationModal = () => (
        <Dialog open={showTaskCreationModal} onOpenChange={setShowTaskCreationModal}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader className="space-y-3">
                    <DialogTitle className="flex items-center space-x-3 text-xl">
                        <div className="p-2 bg-primary/10 rounded-lg">
                            <Plus className="w-6 h-6 text-primary" />
                        </div>
                        <span>Create New Task</span>
                    </DialogTitle>
                    <DialogDescription className="text-base leading-relaxed">
                        Choose how you'd like to create a task for <span className="font-semibold text-foreground">"{projectData.name}"</span>
                    </DialogDescription>
                </DialogHeader>
                <div className="space-y-4 py-6">
                    <div className="grid grid-cols-1 gap-4">
                        <Button
                            onClick={handleManualTaskCreation}
                            className="h-auto p-6 flex items-start space-x-4 hover:bg-muted/50 transition-all duration-200 border-2 hover:border-primary/20"
                            variant="outline"
                        >
                            <div className="p-3 bg-blue-500/10 rounded-lg">
                                <PenTool className="w-6 h-6 text-blue-500" />
                            </div>
                            <div className="text-left flex-1">
                                <h3 className="font-semibold text-lg mb-1">Manual Creation</h3>
                                <p className="text-sm text-muted-foreground leading-relaxed">
                                    Create a task manually with full control
                                </p>
                            </div>
                        </Button>

                        <Button
                            onClick={handleAITaskGeneration}
                            className="h-auto p-6 flex items-start space-x-4 hover:bg-muted/50 transition-all duration-200 border-2 hover:border-primary/20"
                            variant="outline"
                        >
                            <div className="p-3 bg-purple-500/10 rounded-lg">
                                <Brain className="w-6 h-6 text-purple-500" />
                            </div>
                            <div className="text-left flex-1">
                                <h3 className="font-semibold text-lg mb-1">AI Generation</h3>
                                <p className="text-sm text-muted-foreground leading-relaxed">
                                    Let AI generate tasks for you
                                </p>
                            </div>
                        </Button>
                    </div>
                </div>
                <DialogFooter className="pt-4">
                    <Button variant="outline" onClick={() => setShowTaskCreationModal(false)} className="px-6">
                        Cancel
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${projectData.name} - Project Details`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-start">
                    <div className="flex items-center space-x-4">
                        <Link href="/projects">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{projectData.name}</h1>
                            <p className="text-muted-foreground">
                                Client: {projectData.client ? `${projectData.client.first_name} ${projectData.client.last_name}` : 'No Client'}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline">
                            <Share className="w-4 h-4 mr-2" />
                            Share
                        </Button>
                        <Link href={`/projects/${projectData.id}/edit`}>
                            <Button variant="outline">
                                <Edit className="w-4 h-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Button onClick={() => setShowTaskCreationModal(true)}>
                            <Plus className="w-4 h-4 mr-2" />
                            Add Task
                        </Button>
                    </div>
                </div>

                {/* Project Stats */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Progress</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{projectData.progress}%</div>
                            <div className="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div
                                    className="bg-primary h-2 rounded-full transition-all duration-300"
                                    style={{ width: `${projectData.progress}%` }}
                                ></div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Tasks</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{completedTasks}/{totalTasks}</div>
                            <p className="text-xs text-muted-foreground">Completed</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Budget</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">$0</div>
                            <p className="text-xs text-muted-foreground">Budget not set</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Team</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{projectData.team ? projectData.team.length : 0}</div>
                            <p className="text-xs text-muted-foreground">Members</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs */}
                <div className="flex space-x-1 bg-muted p-1 rounded-lg">
                    <Button
                        variant={activeTab === 'overview' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('overview')}
                        className="flex-1"
                    >
                        Overview
                    </Button>
                    <Button
                        variant={activeTab === 'tasks' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('tasks')}
                        className="flex-1"
                    >
                        Tasks
                    </Button>
                    <Button
                        variant={activeTab === 'team' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('team')}
                        className="flex-1"
                    >
                        Team
                    </Button>
                    <Button
                        variant={activeTab === 'timeline' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('timeline')}
                        className="flex-1"
                    >
                        Timeline
                    </Button>
                </div>

                {/* Tab Content */}
                {activeTab === 'overview' && (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Project Info */}
                        <div className="lg:col-span-2 space-y-6">
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Project Information</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <h3 className="font-medium text-foreground mb-2">Description</h3>
                                        <p className="text-muted-foreground">{projectData.description}</p>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Status</h3>
                                            <Badge className={getStatusColor(projectData.status)}>
                                                {projectData.status.replace('-', ' ')}
                                            </Badge>
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Priority</h3>
                                            <Badge className={getPriorityColor(projectData.priority)}>
                                                {projectData.priority}
                                            </Badge>
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Start Date</h3>
                                            <p className="text-muted-foreground">
                                                {projectData.start_date ? new Date(projectData.start_date).toLocaleDateString() : 'Not set'}
                                            </p>
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Due Date</h3>
                                            <p className="text-muted-foreground">
                                                {projectData.due_date ? new Date(projectData.due_date).toLocaleDateString() : 'Not set'}
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Recent Tasks</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {projectData.tasks && projectData.tasks.length > 0 ? (
                                            projectData.tasks.slice(0, 3).map(task => (
                                                <Link key={task.id} href={`/tasks/${task.id}`} className="block">
                                                    <div className="flex items-center justify-between p-3 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                                        <div className="flex items-center space-x-3">
                                                            {getTaskStatusIcon(task.status)}
                                                            <div>
                                                                <h3 className="font-medium text-foreground">{task.title}</h3>
                                                                <p className="text-sm text-muted-foreground">{task.assignee || 'Unassigned'}</p>
                                                            </div>
                                                        </div>
                                                        <div className="flex items-center space-x-2">
                                                            {task.priority && (
                                                                <Badge className={getPriorityColor(task.priority)} size="sm">
                                                                    {task.priority}
                                                                </Badge>
                                                            )}
                                                            <span className="text-sm text-muted-foreground">
                                                                {task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No due date'}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </Link>
                                            ))
                                        ) : (
                                            <div className="text-center py-8">
                                                <p className="text-muted-foreground">No tasks yet</p>
                                                <Button className="mt-2" size="sm" onClick={() => setShowTaskCreationModal(true)}>
                                                    <Plus className="w-4 h-4 mr-2" />
                                                    Add First Task
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Quick Actions</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <Button className="w-full justify-start" variant="outline" onClick={() => setShowTaskCreationModal(true)}>
                                        <Plus className="w-4 h-4 mr-2" />
                                        Add Task
                                    </Button>
                                    <Button className="w-full justify-start" variant="outline">
                                        <MessageSquare className="w-4 h-4 mr-2" />
                                        Send Update
                                    </Button>
                                    <Button className="w-full justify-start" variant="outline">
                                        <Download className="w-4 h-4 mr-2" />
                                        Export Report
                                    </Button>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button
                                                className="w-full justify-start text-red-600 hover:text-red-700 hover:bg-red-50"
                                                variant="outline"
                                            >
                                                <Trash2 className="w-4 h-4 mr-2" />
                                                Delete Project
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the project
                                                    "{projectData.name}" and remove all associated data from our servers.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction
                                                    onClick={handleDeleteProject}
                                                    className="bg-red-600 hover:bg-red-700 text-white"
                                                >
                                                    Delete Project
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </CardContent>
                            </Card>

                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Milestones</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {projectData.milestones && projectData.milestones.length > 0 ? (
                                            projectData.milestones.map(milestone => (
                                                <div key={milestone.id} className="flex items-center space-x-3">
                                                    <div className={`w-2 h-2 rounded-full ${
                                                        milestone.status === 'completed' ? 'bg-green-500' :
                                                        milestone.status === 'in-progress' ? 'bg-blue-500' : 'bg-gray-500'
                                                    }`}></div>
                                                    <div className="flex-1">
                                                        <h3 className="text-sm font-medium text-foreground">{milestone.title}</h3>
                                                        <p className="text-xs text-muted-foreground">
                                                            {milestone.date ? new Date(milestone.date).toLocaleDateString() : 'No date set'}
                                                        </p>
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="text-center py-4">
                                                <p className="text-muted-foreground">No milestones yet</p>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                )}

                {activeTab === 'tasks' && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-foreground">Project Tasks</CardTitle>
                                    <CardDescription className="text-muted-foreground">Manage and track project tasks</CardDescription>
                                </div>
                                <Button
                                    onClick={generateTasks}
                                    disabled={generatingTasks}
                                    variant="outline"
                                    size="sm"
                                    className="flex items-center space-x-2"
                                >
                                    {generatingTasks ? (
                                        <div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" />
                                    ) : (
                                        <Zap className="w-4 h-4" />
                                    )}
                                    <span>Generate with AI</span>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {tasks.map(task => (
                                    <div
                                        key={task.id}
                                        className={`flex items-center justify-between p-4 border border-border rounded-lg transition-all duration-300 ease-out ${
                                            visibleTasks.has(task.id)
                                                ? 'opacity-100 translate-y-0'
                                                : 'opacity-0 translate-y-2'
                                        }`}
                                    >
                                        <Link href={`/tasks/${task.id}`} className="flex-1">
                                            <div className="flex items-center space-x-3">
                                                {getTaskStatusIcon(task.status)}
                                                <div>
                                                    <h3 className="font-medium text-foreground">{task.title}</h3>
                                                    <p className="text-sm text-muted-foreground">
                                                        Assigned to {task.assignee || 'Unassigned'}
                                                    </p>
                                                </div>
                                            </div>
                                        </Link>
                                        <div className="flex items-center space-x-2">
                                            {task.priority && (
                                                <Badge className={getPriorityColor(task.priority)} size="sm">
                                                    {task.priority}
                                                </Badge>
                                            )}
                                            <span className="text-sm text-muted-foreground">
                                                Due {task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No due date'}
                                            </span>
                                            <button
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    deleteTask(task.id);
                                                }}
                                                className="p-1 text-muted-foreground hover:text-red-500 hover:bg-red-50 rounded transition-colors duration-200"
                                                title="Delete task"
                                            >
                                                <X className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                ))}

                                {tasks.length === 0 && !generatingTasks && (
                                    <div className="text-center py-12">
                                        <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                                            <Target className="w-8 h-8 text-muted-foreground" />
                                        </div>
                                        <h3 className="text-lg font-medium text-foreground mb-2">No tasks yet</h3>
                                        <p className="text-muted-foreground mb-4">
                                            Get started by creating your first task
                                        </p>
                                        <Button onClick={() => setShowTaskCreationModal(true)}>
                                            <Plus className="w-4 h-4 mr-2" />
                                            Create Task
                                        </Button>
                                    </div>
                                )}

                                {generatingTasks && (
                                    <div className="text-center py-8 text-muted-foreground">
                                        <div className="flex flex-col items-center space-y-3">
                                            <div className="relative">
                                                <div className="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                                                <div className="absolute inset-0 w-8 h-8 border-2 border-primary/20 rounded-full"></div>
                                            </div>
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium">Generating tasks...</p>
                                                <p className="text-xs">AI is analyzing your project and creating actionable tasks</p>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {activeTab === 'team' && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex justify-between items-center">
                                <div>
                                    <CardTitle className="text-foreground">Project Team</CardTitle>
                                    <CardDescription className="text-muted-foreground">Team members working on this project</CardDescription>
                                </div>
                                <Button>
                                    <Plus className="w-4 h-4 mr-2" />
                                    Add Member
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {projectData.team && projectData.team.length > 0 ? (
                                    projectData.team.map(member => (
                                        <div key={member.id} className="flex items-center space-x-3 p-4 border border-border rounded-lg">
                                            <div className="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                                <span className="text-sm font-semibold text-primary-foreground">{member.avatar}</span>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-foreground">{member.name}</h3>
                                                <p className="text-sm text-muted-foreground">{member.role}</p>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="col-span-full text-center py-12">
                                        <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                                            <Users className="w-8 h-8 text-muted-foreground" />
                                        </div>
                                        <h3 className="text-lg font-medium text-foreground mb-2">No team members yet</h3>
                                        <p className="text-muted-foreground mb-4">
                                            Add team members to collaborate on this project
                                        </p>
                                        <Button>
                                            <Plus className="w-4 h-4 mr-2" />
                                            Add Member
                                        </Button>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {activeTab === 'timeline' && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <CardTitle className="text-foreground">Project Timeline</CardTitle>
                            <CardDescription className="text-muted-foreground">Project milestones and deadlines</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-6">
                                {projectData.milestones && projectData.milestones.length > 0 ? (
                                    projectData.milestones.map((milestone, index) => (
                                        <div key={milestone.id} className="flex items-start space-x-4">
                                            <div className="flex flex-col items-center">
                                                <div className={`w-4 h-4 rounded-full ${
                                                    milestone.status === 'completed' ? 'bg-green-500' :
                                                    milestone.status === 'in-progress' ? 'bg-blue-500' : 'bg-gray-500'
                                                }`}></div>
                                                {index < projectData.milestones.length - 1 && (
                                                    <div className="w-0.5 h-8 bg-gray-300 mt-2"></div>
                                                )}
                                            </div>
                                            <div className="flex-1">
                                                <h3 className="font-medium text-foreground">{milestone.title}</h3>
                                                <p className="text-sm text-muted-foreground">
                                                    {milestone.date ? new Date(milestone.date).toLocaleDateString() : 'No date set'}
                                                </p>
                                                <Badge className={`mt-2 ${getStatusColor(milestone.status)}`}>
                                                    {milestone.status.replace('-', ' ')}
                                                </Badge>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-12">
                                        <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                                            <Calendar className="w-8 h-8 text-muted-foreground" />
                                        </div>
                                        <h3 className="text-lg font-medium text-foreground mb-2">No timeline yet</h3>
                                        <p className="text-muted-foreground mb-4">
                                            Create milestones to track project progress
                                        </p>
                                        <Button>
                                            <Plus className="w-4 h-4 mr-2" />
                                            Add Milestone
                                        </Button>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
            <TaskCreationModal />
        </AuthenticatedLayout>
    );
}
