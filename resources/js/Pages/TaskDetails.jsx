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
    Target
} from 'lucide-react';

export default function TaskDetails({ auth, task, error }) {
    const [newComment, setNewComment] = useState('');
    const [newSubtask, setNewSubtask] = useState('');
    const [subtasks, setSubtasks] = useState([]);

    // Map database status to frontend status
    const mapStatus = (dbStatus) => {
        const statusMap = {
            'todo': 'todo',
            'in_progress': 'in-progress',
            'done': 'done'
        };
        return statusMap[dbStatus] || 'todo';
    };

    // Initialize subtasks from task data
    React.useEffect(() => {
        if (task && task.subtasks) {
            setSubtasks(task.subtasks.map(subtask => ({
                id: subtask.id,
                text: subtask.description,
                completed: subtask.status === 'done',
                completedAt: subtask.status === 'done' ? subtask.updated_at : null
            })));
        }
    }, [task]);

    const handleDeleteTask = () => {
        if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
            router.delete(`/tasks/${transformedTask.id}`, {
                onSuccess: () => {
                    router.visit('/bobbi-flow');
                }
            });
        }
    };

    const addSubtask = async () => {
        if (!newSubtask.trim()) return;

        try {
            const response = await fetch(`/tasks/${transformedTask.id}/subtasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    description: newSubtask.trim()
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Add the new subtask to local state
                const newSubtaskItem = {
                    id: data.subtask.id,
                    text: data.subtask.description,
                    completed: false,
                    completedAt: null
                };
                setSubtasks(prev => [...prev, newSubtaskItem]);

                toast.success("Subtask added successfully!");
                setNewSubtask('');
            } else {
                toast.error(data.error || "Failed to add subtask. Please try again.");
            }
        } catch (error) {
            console.error('Failed to add subtask:', error);
            toast.error("Failed to add subtask. Please try again.");
        }
    };

    const toggleSubtask = async (subtaskId) => {
        // Find the current subtask to determine its status
        const currentSubtask = subtasks.find(st => st.id === subtaskId);
        if (!currentSubtask) return;

        const newStatus = currentSubtask.completed ? 'todo' : 'done';

        try {
            const response = await fetch(`/tasks/${transformedTask.id}/subtasks/${subtaskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: newStatus
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Update the subtask in local state
                setSubtasks(prev => prev.map(subtask =>
                    subtask.id === subtaskId
                        ? {
                            ...subtask,
                            completed: newStatus === 'done',
                            completedAt: newStatus === 'done' ? new Date().toISOString() : null
                          }
                        : subtask
                ));

                toast.success(`Subtask marked as ${newStatus === 'done' ? 'completed' : 'incomplete'}!`);
            } else {
                toast.error(data.error || "Failed to update subtask. Please try again.");
            }
        } catch (error) {
            console.error('Failed to update subtask:', error);
            toast.error("Failed to update subtask. Please try again.");
        }
    };

    // If there's an error or no task, show error state
    if (error || !task) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Task Details" />
                <div className="py-12">
                    <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6 text-gray-900">
                                <h2 className="text-xl font-semibold mb-4">Task Not Found</h2>
                                <p className="text-gray-600 mb-4">
                                    {error || 'The requested task could not be found.'}
                                </p>
                                <Link href="/bobbi-flow">
                                    <Button variant="outline">
                                        <ArrowLeft className="w-4 h-4 mr-2" />
                                        Back to Bobbi Flow
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    // Transform database task to match component expectations
    const transformedTask = {
        id: task.id,
        title: task.title,
        description: task.description || '',
        client: task.project?.client ? `${task.project.client.first_name} ${task.project.client.last_name}`.trim() : 'No Client',
        project: task.project?.name || 'No Project',
        projectId: task.project?.id || null,
        priority: task.priority || 'medium',
        status: mapStatus(task.status),
        dueDate: task.due_date ? task.due_date.split('T')[0] : null,
        estimatedTime: task.estimated_hours ? `${task.estimated_hours}h` : null,
        actualTime: null, // Not in database yet
        tags: task.tags || [],
        assignee: task.assigned_to ? 'me' : 'client',
        createdBy: task.user?.name || task.created_by || 'Unknown',
        createdAt: task.created_at ? task.created_at.split('T')[0] : null,
        updatedAt: task.updated_at ? task.updated_at.split('T')[0] : null,
        subtasks: task.subtasks?.map(subtask => ({
            id: subtask.id,
            text: subtask.description,
            completed: subtask.status === 'done',
            completedAt: subtask.status === 'done' ? subtask.updated_at : null
        })) || [],
        comments: [], // Not implemented yet
        attachments: [], // Not implemented yet
        relatedTasks: [] // Not implemented yet
    };

    const getPriorityColor = (priority) => {
        switch (priority) {
            case 'high':
                return 'bg-red-100 text-red-800 border-red-200';
            case 'medium':
                return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'low':
                return 'bg-green-100 text-green-800 border-green-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const getPriorityIcon = (priority) => {
        switch (priority) {
            case 'high':
                return <AlertCircle className="w-4 h-4 text-red-500" />;
            case 'medium':
                return <Clock className="w-4 h-4 text-yellow-500" />;
            case 'low':
                return <CheckCircle className="w-4 h-4 text-green-500" />;
            default:
                return <Circle className="w-4 h-4 text-gray-400" />;
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'in-progress':
                return 'bg-blue-100 text-blue-800';
            case 'waiting':
                return 'bg-orange-100 text-orange-800';
            case 'review':
                return 'bg-purple-100 text-purple-800';
            case 'done':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusIcon = (status) => {
        switch (status) {
            case 'in-progress':
                return <Play className="w-4 h-4" />;
            case 'waiting':
                return <Pause className="w-4 h-4" />;
            case 'review':
                return <Eye className="w-4 h-4" />;
            case 'done':
                return <CheckCircle2 className="w-4 h-4" />;
            default:
                return <Circle className="w-4 h-4" />;
        }
    };

    const progressPercentage = (transformedTask.subtasks.filter(st => st.completed).length / transformedTask.subtasks.length) * 100;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${transformedTask.title} - Task Details`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-start">
                    <div className="flex items-center space-x-4">
                        <Link href="/bobbi-flow">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{transformedTask.title}</h1>
                            <p className="text-muted-foreground">
                                {transformedTask.client} • {transformedTask.projectId ? (
                                    <Link
                                        href={`/projects/${transformedTask.projectId}`}
                                        className="text-primary hover:text-primary/80 hover:underline transition-colors"
                                    >
                                        {transformedTask.project}
                                    </Link>
                                ) : (
                                    transformedTask.project
                                )}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={`/tasks/${transformedTask.id}/edit`}>
                            <Button variant="outline">
                                <Edit className="w-4 h-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Button variant="outline">
                            <MoreHorizontal className="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                {/* Task Stats */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Status</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2">
                                {getStatusIcon(transformedTask.status)}
                                <span className="text-lg font-semibold text-foreground capitalize">{transformedTask.status.replace('-', ' ')}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Priority</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2">
                                {getPriorityIcon(transformedTask.priority)}
                                <span className="text-lg font-semibold text-foreground capitalize">{transformedTask.priority}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Progress</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg font-semibold text-foreground">
                                {transformedTask.subtasks.filter(st => st.completed).length}/{transformedTask.subtasks.length}
                            </div>
                            <p className="text-xs text-muted-foreground">Subtasks completed</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Time</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg font-semibold text-foreground">{transformedTask.estimatedTime || 'Not set'}</div>
                            <p className="text-xs text-muted-foreground">Estimated</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content */}
                <Tabs defaultValue="overview" className="space-y-6">
                    <TabsList className="grid w-full grid-cols-3">
                        <TabsTrigger value="overview">Overview</TabsTrigger>
                        <TabsTrigger value="subtasks">Subtasks</TabsTrigger>
                        <TabsTrigger value="comments">Comments</TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview" className="space-y-6">
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {/* Main Content */}
                            <div className="lg:col-span-2 space-y-6">
                                {/* Task Overview */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center justify-between">
                                            <span>Task Overview</span>
                                            <div className="flex items-center space-x-2">
                                                {getPriorityIcon(transformedTask.priority)}
                                                <Badge className={getStatusColor(transformedTask.status)}>
                                                    {getStatusIcon(transformedTask.status)}
                                                    <span className="ml-1 capitalize">{transformedTask.status.replace('-', ' ')}</span>
                                                </Badge>
                                            </div>
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Description</h3>
                                            <p className="text-muted-foreground leading-relaxed">{transformedTask.description}</p>
                                        </div>

                                        <div className="grid grid-cols-2 gap-4">
                                            <div className="flex items-center space-x-2">
                                                <Calendar className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-sm text-muted-foreground">Due: {transformedTask.dueDate}</span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Clock className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-sm text-muted-foreground">
                                                    {transformedTask.actualTime} / {transformedTask.estimatedTime}
                                                </span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <User className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-sm text-muted-foreground">
                                                    {transformedTask.assignee === 'client' ? 'Client' : 'You'}
                                                </span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Building className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-sm text-muted-foreground">{transformedTask.client}</span>
                                            </div>
                                        </div>

                                        {transformedTask.tags.length > 0 && (
                                            <div>
                                                <h3 className="font-medium text-foreground mb-2">Tags</h3>
                                                <div className="flex flex-wrap gap-2">
                                                    {transformedTask.tags.map((tag, index) => (
                                                        <Badge key={index} variant="outline" className="bg-muted/30">
                                                            {tag}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>

                                {/* Attachments */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Attachments</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        {transformedTask.attachments.length > 0 ? (
                                            <div className="space-y-2">
                                                {transformedTask.attachments.map((attachment) => (
                                                    <div key={attachment.id} className="flex items-center justify-between p-2 border rounded-md">
                                                        <div className="flex items-center space-x-2">
                                                            <FileText className="w-4 h-4 text-muted-foreground" />
                                                            <div>
                                                                <p className="text-sm font-medium">{attachment.name}</p>
                                                                <p className="text-xs text-muted-foreground">{attachment.size}</p>
                                                            </div>
                                                        </div>
                                                        <Button variant="ghost" size="sm">
                                                            <ExternalLink className="w-4 h-4" />
                                                        </Button>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="text-center py-8 text-muted-foreground">
                                                <FileText className="w-8 h-8 mx-auto mb-2 opacity-50" />
                                                <p className="text-sm">No attachments</p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>

                                {/* Related Tasks */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Related Tasks</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        {transformedTask.relatedTasks.length > 0 ? (
                                            <div className="space-y-2">
                                                {transformedTask.relatedTasks.map((relatedTask) => (
                                                    <Link key={relatedTask.id} href={`/tasks/${relatedTask.id}`}>
                                                        <div className="p-2 border rounded-md hover:bg-muted/50 transition-colors cursor-pointer">
                                                            <p className="text-sm font-medium">{relatedTask.title}</p>
                                                            <div className="flex items-center space-x-2 mt-1">
                                                                <Badge className={getStatusColor(relatedTask.status)}>
                                                                    {getStatusIcon(relatedTask.status)}
                                                                    <span className="ml-1 capitalize">{relatedTask.status.replace('-', ' ')}</span>
                                                                </Badge>
                                                                {getPriorityIcon(relatedTask.priority)}
                                                            </div>
                                                        </div>
                                                    </Link>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="text-center py-8 text-muted-foreground">
                                                <Target className="w-8 h-8 mx-auto mb-2 opacity-50" />
                                                <p className="text-sm">No related tasks</p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            </div>

                            {/* Sidebar */}
                            <div className="space-y-6">
                                {/* Quick Actions */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Quick Actions</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-2">
                                        <Link href={`/tasks/${transformedTask.id}/start-work`}>
                                            <Button variant="outline" className="w-full justify-start">
                                                <Play className="w-4 h-4 mr-2" />
                                                Start Work
                                            </Button>
                                        </Link>
                                        <Button variant="outline" className="w-full justify-start">
                                            <Eye className="w-4 h-4 mr-2" />
                                            Mark for Review
                                        </Button>
                                        <Button variant="outline" className="w-full justify-start">
                                            <CheckCircle2 className="w-4 h-4 mr-2" />
                                            Mark Complete
                                        </Button>
                                        <Button variant="outline" className="w-full justify-start">
                                            <Flag className="w-4 h-4 mr-2" />
                                            Flag Task
                                        </Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger asChild>
                                                <Button
                                                    variant="outline"
                                                    className="w-full justify-start text-red-600 hover:text-red-700 hover:bg-red-50"
                                                >
                                                    <Trash2 className="w-4 h-4 mr-2" />
                                                    Delete Task
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This action cannot be undone. This will permanently delete the task
                                                        "{transformedTask.title}" and remove all associated data from our servers.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction
                                                        onClick={handleDeleteTask}
                                                        className="bg-red-600 hover:bg-red-700 text-white"
                                                    >
                                                        Delete Task
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </CardContent>
                                </Card>

                                {/* Task Info */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Task Info</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-3 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Created by:</span>
                                            <span>{transformedTask.createdBy}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Created:</span>
                                            <span>{transformedTask.createdAt}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Updated:</span>
                                            <span>{transformedTask.updatedAt}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Task ID:</span>
                                            <span className="font-mono">#{transformedTask.id}</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </TabsContent>

                    {/* Subtasks */}
                    <TabsContent value="subtasks" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>Subtasks</span>
                                    <div className="flex items-center space-x-2">
                                        <span className="text-sm text-muted-foreground">
                                            {transformedTask.subtasks.filter(st => st.completed).length} of {transformedTask.subtasks.length} completed
                                        </span>
                                        <div className="w-20 bg-muted rounded-full h-2">
                                            <div
                                                className="bg-primary h-2 rounded-full transition-all duration-300"
                                                style={{ width: `${progressPercentage}%` }}
                                            ></div>
                                        </div>
                                    </div>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {subtasks.map((subtask) => (
                                        <div key={subtask.id} className="flex items-center space-x-3">
                                            <Checkbox
                                                checked={subtask.completed}
                                                onCheckedChange={() => toggleSubtask(subtask.id)}
                                                className="flex-shrink-0"
                                            />
                                            <span className={`flex-1 ${subtask.completed ? 'line-through text-muted-foreground' : 'text-foreground'}`}>
                                                {subtask.text}
                                            </span>
                                            {subtask.completed && (
                                                <span className="text-xs text-muted-foreground">
                                                    {subtask.completedAt ? new Date(subtask.completedAt).toLocaleDateString() : 'Completed'}
                                                </span>
                                            )}
                                        </div>
                                    ))}

                                    {subtasks.length === 0 && (
                                        <div className="text-center py-4 text-muted-foreground">
                                            <p className="text-sm">No subtasks yet</p>
                                            <p className="text-xs">Add a subtask to break down this task</p>
                                        </div>
                                    )}
                                </div>

                                <div className="mt-4 flex space-x-2">
                                    <input
                                        type="text"
                                        placeholder="Add new subtask..."
                                        value={newSubtask}
                                        onChange={(e) => setNewSubtask(e.target.value)}
                                        onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addSubtask())}
                                        className="flex-1 px-3 py-2 border border-input rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    />
                                    <Button size="sm" disabled={!newSubtask.trim()} onClick={addSubtask}>
                                        <Plus className="w-4 h-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Comments */}
                    <TabsContent value="comments" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center space-x-2">
                                    <MessageSquare className="w-5 h-5" />
                                    <span>Comments ({transformedTask.comments.length})</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {transformedTask.comments.map((comment) => (
                                        <div key={comment.id} className="flex space-x-3">
                                            <div className="flex-shrink-0">
                                                <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                                                    <span className="text-xs font-semibold text-primary-foreground">
                                                        {comment.avatar}
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-2 mb-1">
                                                    <span className="font-medium text-sm">{comment.author}</span>
                                                    <span className="text-xs text-muted-foreground">{comment.timestamp}</span>
                                                </div>
                                                <p className="text-sm text-muted-foreground">{comment.content}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <div className="mt-4">
                                    <Textarea
                                        placeholder="Add a comment..."
                                        value={newComment}
                                        onChange={(e) => setNewComment(e.target.value)}
                                        className="mb-2"
                                        rows={3}
                                    />
                                    <div className="flex justify-end">
                                        <Button size="sm" disabled={!newComment.trim()}>
                                            Post Comment
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AuthenticatedLayout>
    );
}
