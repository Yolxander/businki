import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ArrowLeft, Save, Target, Calendar, Clock, User, Building, Tag, Plus, X, MessageSquare, FileText, CheckCircle, AlertCircle, Circle, Flame, AlertTriangle, Minus, Inbox, Play, Eye, CheckCircle2, Flag, Trash2, ExternalLink } from 'lucide-react';

export default function EditTask({ auth, task, projects = [], users = [], error }) {
    const [newTag, setNewTag] = useState('');
    const [newSubtask, setNewSubtask] = useState('');

    // Map database status to frontend status
    const mapStatus = (dbStatus) => {
        const statusMap = {
            'todo': 'inbox',
            'in_progress': 'in-progress',
            'done': 'done'
        };
        return statusMap[dbStatus] || dbStatus;
    };

    // Map frontend status to database status
    const mapStatusToDb = (frontendStatus) => {
        const statusMap = {
            'inbox': 'todo',
            'in-progress': 'in_progress',
            'waiting': 'todo',
            'review': 'in_progress',
            'done': 'done'
        };
        return statusMap[frontendStatus] || frontendStatus;
    };

    // Transform task data to match component expectations
    const transformedTask = task ? {
        id: task.id,
        title: task.title,
        description: task.description || '',
        project_id: task.project_id,
        priority: task.priority || 'medium',
        status: mapStatus(task.status),
        due_date: task.due_date ? task.due_date.split('T')[0] : '',
        estimated_hours: task.estimated_hours || '',
        assigned_to: task.assigned_to || '',
        tags: task.tags || [],
        subtasks: task.subtasks?.map(subtask => ({
            id: subtask.id,
            text: subtask.description,
            completed: subtask.status === 'done'
        })) || []
    } : null;

    // Handle error case
    if (error || !transformedTask) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Task Not Found" />
                <div className="space-y-6">
                    <div className="flex items-center space-x-4">
                        <Link href="/bobbi-flow">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">Task Not Found</h1>
                            <p className="text-muted-foreground">The task you're looking for doesn't exist or you don't have permission to edit it.</p>
                        </div>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    const { data, setData, put, processing, errors } = useForm({
        title: transformedTask.title,
        description: transformedTask.description,
        project_id: transformedTask.project_id,
        priority: transformedTask.priority,
        status: transformedTask.status,
        due_date: transformedTask.due_date,
        estimated_hours: transformedTask.estimated_hours,
        assigned_to: transformedTask.assigned_to,
        tags: transformedTask.tags,
        subtasks: transformedTask.subtasks
    });

    // Users for assignment (fetched from database)

    const getPriorityIcon = (priority) => {
        switch (priority) {
            case 'high': return <Flame className="w-4 h-4 text-red-500" />;
            case 'medium': return <AlertTriangle className="w-4 h-4 text-yellow-500" />;
            case 'low': return <Minus className="w-4 h-4 text-green-500" />;
            default: return <Minus className="w-4 h-4 text-green-500" />;
        }
    };

    const getStatusIcon = (status) => {
        switch (status) {
            case 'inbox': return <Inbox className="w-4 h-4 text-blue-500" />;
            case 'in-progress': return <Play className="w-4 h-4 text-orange-500" />;
            case 'waiting': return <Clock className="w-4 h-4 text-yellow-500" />;
            case 'review': return <Eye className="w-4 h-4 text-purple-500" />;
            case 'done': return <CheckCircle2 className="w-4 h-4 text-green-500" />;
            default: return <Circle className="w-4 h-4 text-gray-500" />;
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'inbox': return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
            case 'in-progress': return 'bg-orange-100 text-orange-800 hover:bg-orange-200';
            case 'waiting': return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
            case 'review': return 'bg-purple-100 text-purple-800 hover:bg-purple-200';
            case 'done': return 'bg-green-100 text-green-800 hover:bg-green-200';
            default: return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
        }
    };

    const addTag = () => {
        if (newTag.trim() && !data.tags.includes(newTag.trim())) {
            setData('tags', [...data.tags, newTag.trim()]);
            setNewTag('');
        }
    };

    const removeTag = (tagToRemove) => {
        setData('tags', data.tags.filter(tag => tag !== tagToRemove));
    };

    const addSubtask = () => {
        if (newSubtask.trim()) {
            const newSubtaskObj = {
                id: Date.now(),
                text: newSubtask.trim(),
                completed: false
            };
            setData('subtasks', [...data.subtasks, newSubtaskObj]);
            setNewSubtask('');
        }
    };

    const removeSubtask = (subtaskId) => {
        setData('subtasks', data.subtasks.filter(subtask => subtask.id !== subtaskId));
    };

    const toggleSubtask = (subtaskId) => {
        setData('subtasks', data.subtasks.map(subtask =>
            subtask.id === subtaskId
                ? { ...subtask, completed: !subtask.completed }
                : subtask
        ));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/tasks/${transformedTask.id}`, {
            onSuccess: () => {
                console.log('Task updated successfully');
            },
            onError: (errors) => {
                console.error('Task update failed', errors);
            }
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Edit ${transformedTask.title}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-start">
                    <div className="flex items-center space-x-4">
                        <Link href={`/tasks/${transformedTask.id}`}>
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">Edit Task</h1>
                            <p className="text-muted-foreground">Update task details and settings</p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <Tabs defaultValue="overview" className="space-y-6">
                        <TabsList className="grid w-full grid-cols-3">
                            <TabsTrigger value="overview">Overview</TabsTrigger>
                            <TabsTrigger value="subtasks">Subtasks</TabsTrigger>
                            <TabsTrigger value="comments">Comments</TabsTrigger>
                        </TabsList>

                        <TabsContent value="overview" className="space-y-6">
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                {/* Main Form */}
                                <div className="lg:col-span-2 space-y-6">
                                    {/* Basic Information */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Basic Information</CardTitle>
                                            <CardDescription>Update the core task details</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div>
                                                <Label htmlFor="title">Task Title</Label>
                                                <Input
                                                    id="title"
                                                    value={data.title}
                                                    onChange={(e) => setData('title', e.target.value)}
                                                    placeholder="Enter task title"
                                                    className={errors.title ? 'border-red-500' : ''}
                                                />
                                                {errors.title && <p className="text-sm text-red-500 mt-1">{errors.title}</p>}
                                            </div>

                                            <div>
                                                <Label htmlFor="description">Description</Label>
                                                <Textarea
                                                    id="description"
                                                    value={data.description}
                                                    onChange={(e) => setData('description', e.target.value)}
                                                    placeholder="Describe the task in detail"
                                                    rows={4}
                                                    className={errors.description ? 'border-red-500' : ''}
                                                />
                                                {errors.description && <p className="text-sm text-red-500 mt-1">{errors.description}</p>}
                                            </div>

                                            <div>
                                                <Label htmlFor="project">Project (Optional)</Label>
                                                <Select value={data.project_id ? data.project_id.toString() : 'none'} onValueChange={(value) => {
                                                    if (value === 'none') {
                                                        setData('project_id', '');
                                                    } else {
                                                        setData('project_id', parseInt(value));
                                                    }
                                                }}>
                                                    <SelectTrigger className={errors.project_id ? 'border-red-500' : ''}>
                                                        <SelectValue placeholder="Select project (optional)" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="none">No Project</SelectItem>
                                                        {projects.map((project) => (
                                                            <SelectItem key={project.id} value={project.id.toString()}>
                                                                {project.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                {data.project_id && (
                                                    <p className="text-sm text-green-600 mt-1">
                                                        ✓ Project selected
                                                    </p>
                                                )}
                                                {!data.project_id && (
                                                    <p className="text-sm text-blue-600 mt-1">
                                                        ✓ Task will be updated without a project
                                                    </p>
                                                )}
                                                {errors.project_id && <p className="text-sm text-red-500 mt-1">{errors.project_id}</p>}
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Task Settings */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Task Settings</CardTitle>
                                            <CardDescription>Configure priority, status, and scheduling</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <Label htmlFor="priority">Priority</Label>
                                                    <Select value={data.priority} onValueChange={(value) => setData('priority', value)}>
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="low">
                                                                <div className="flex items-center">
                                                                    <Minus className="w-4 h-4 mr-2 text-green-500" />
                                                                    Low
                                                                </div>
                                                            </SelectItem>
                                                            <SelectItem value="medium">
                                                                <div className="flex items-center">
                                                                    <AlertTriangle className="w-4 h-4 mr-2 text-yellow-500" />
                                                                    Medium
                                                                </div>
                                                            </SelectItem>
                                                            <SelectItem value="high">
                                                                <div className="flex items-center">
                                                                    <Flame className="w-4 h-4 mr-2 text-red-500" />
                                                                    High
                                                                </div>
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>

                                                <div>
                                                    <Label htmlFor="status">Status</Label>
                                                    <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="inbox">
                                                                <div className="flex items-center">
                                                                    <Inbox className="w-4 h-4 mr-2" />
                                                                    Inbox
                                                                </div>
                                                            </SelectItem>
                                                            <SelectItem value="in-progress">
                                                                <div className="flex items-center">
                                                                    <Play className="w-4 h-4 mr-2" />
                                                                    In Progress
                                                                </div>
                                                            </SelectItem>
                                                            <SelectItem value="waiting">
                                                                <div className="flex items-center">
                                                                    <Clock className="w-4 h-4 mr-2" />
                                                                    Waiting on Client
                                                                </div>
                                                            </SelectItem>
                                                            <SelectItem value="review">
                                                                <div className="flex items-center">
                                                                    <Eye className="w-4 h-4 mr-2" />
                                                                    Review
                                                                </div>
                                                            </SelectItem>
                                                            <SelectItem value="done">
                                                                <div className="flex items-center">
                                                                    <CheckCircle2 className="w-4 h-4 mr-2" />
                                                                    Done
                                                                </div>
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <Label htmlFor="dueDate">Due Date</Label>
                                                    <Input
                                                        id="dueDate"
                                                        type="date"
                                                        value={data.due_date}
                                                        onChange={(e) => setData('due_date', e.target.value)}
                                                    />
                                                </div>

                                                <div>
                                                    <Label htmlFor="estimatedTime">Estimated Hours</Label>
                                                    <Input
                                                        id="estimatedTime"
                                                        type="number"
                                                        step="0.5"
                                                        min="0"
                                                        max="999.99"
                                                        value={data.estimated_hours}
                                                        onChange={(e) => setData('estimated_hours', e.target.value ? parseFloat(e.target.value) : '')}
                                                        placeholder="e.g., 2, 1.5"
                                                    />
                                                </div>
                                            </div>

                                            <div>
                                                <Label htmlFor="assignee">Assignee</Label>
                                                <Select value={data.assigned_to ? data.assigned_to.toString() : 'none'} onValueChange={(value) => {
                                                    if (value === 'none') {
                                                        setData('assigned_to', '');
                                                    } else {
                                                        setData('assigned_to', parseInt(value));
                                                    }
                                                }}>
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Select assignee" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="none">No Assignee</SelectItem>
                                                        {users && users.length > 0 ? users.map((user) => (
                                                            <SelectItem key={user.id} value={user.id.toString()}>
                                                                <div className="flex items-center">
                                                                    <Building className="w-4 h-4 mr-2" />
                                                                    {user.name}
                                                                </div>
                                                            </SelectItem>
                                                        )) : (
                                                            <SelectItem value="no-users" disabled>
                                                                No users available
                                                            </SelectItem>
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Tags */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Tags</CardTitle>
                                            <CardDescription>Add tags to help organize and categorize this task</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="flex space-x-2">
                                                <Input
                                                    placeholder="Add a tag..."
                                                    value={newTag}
                                                    onChange={(e) => setNewTag(e.target.value)}
                                                    onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addTag())}
                                                />
                                                <Button type="button" onClick={addTag} disabled={!newTag.trim()}>
                                                    <Plus className="w-4 h-4" />
                                                </Button>
                                            </div>
                                            <div className="flex flex-wrap gap-2">
                                                {data.tags.map((tag, index) => (
                                                    <Badge key={index} variant="outline" className="bg-muted/30">
                                                        {tag}
                                                        <button
                                                            type="button"
                                                            onClick={() => removeTag(tag)}
                                                            className="ml-1 hover:text-red-500"
                                                        >
                                                            <X className="w-3 h-3" />
                                                        </button>
                                                    </Badge>
                                                ))}
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>

                                {/* Sidebar */}
                                <div className="space-y-6">
                                    {/* Save Actions */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Save Changes</CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-2">
                                            <Button type="submit" className="w-full" disabled={processing}>
                                                <Save className="w-4 h-4 mr-2" />
                                                {processing ? 'Saving...' : 'Save Changes'}
                                            </Button>
                                            <Link href={`/tasks/${transformedTask.id}`}>
                                                <Button variant="outline" className="w-full">
                                                    Cancel
                                                </Button>
                                            </Link>
                                        </CardContent>
                                    </Card>

                                    {/* Task Preview */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Task Preview</CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-3">
                                            <div className="p-3 border rounded-lg">
                                                <h3 className="font-medium text-foreground">{data.title}</h3>
                                                <p className="text-sm text-muted-foreground mt-1 line-clamp-2">{data.description}</p>
                                                <div className="flex items-center justify-between text-xs text-muted-foreground mt-2">
                                                    <span>Due: {data.due_date}</span>
                                                    <span>{data.estimated_hours}h</span>
                                                </div>
                                                <div className="flex items-center space-x-2 mt-2">
                                                    {getPriorityIcon(data.priority)}
                                                    <Badge className={getStatusColor(data.status)}>
                                                        {getStatusIcon(data.status)}
                                                        <span className="ml-1 capitalize">{data.status.replace('-', ' ')}</span>
                                                    </Badge>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="subtasks" className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Subtasks</CardTitle>
                                    <CardDescription>Break down the task into smaller, manageable steps</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex space-x-2">
                                        <Input
                                            placeholder="Add a subtask..."
                                            value={newSubtask}
                                            onChange={(e) => setNewSubtask(e.target.value)}
                                            onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addSubtask())}
                                        />
                                        <Button type="button" onClick={addSubtask} disabled={!newSubtask.trim()}>
                                            <Plus className="w-4 h-4" />
                                        </Button>
                                    </div>
                                    <div className="space-y-2">
                                        {data.subtasks.map((subtask, index) => (
                                            <div key={subtask.id} className="flex items-center space-x-2 p-2 border rounded-md">
                                                <Checkbox
                                                    checked={subtask.completed}
                                                    onCheckedChange={() => toggleSubtask(subtask.id)}
                                                />
                                                <span className={`flex-1 ${subtask.completed ? 'line-through text-muted-foreground' : ''}`}>
                                                    {subtask.text}
                                                </span>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => removeSubtask(subtask.id)}
                                                >
                                                    <X className="w-4 h-4" />
                                                </Button>
                                            </div>
                                        ))}
                                        {data.subtasks.length === 0 && (
                                            <div className="text-center py-8 text-muted-foreground">
                                                <Target className="w-8 h-8 mx-auto mb-2 opacity-50" />
                                                <p className="text-sm">No subtasks yet</p>
                                                <p className="text-xs">Add subtasks to break down this task into smaller steps</p>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="comments" className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Comments</CardTitle>
                                    <CardDescription>Add notes and comments about this task</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Textarea
                                            placeholder="Add a comment..."
                                            rows={3}
                                        />
                                        <div className="flex justify-end">
                                            <Button type="button" size="sm">
                                                <MessageSquare className="w-4 h-4 mr-2" />
                                                Add Comment
                                            </Button>
                                        </div>
                                    </div>
                                    <div className="space-y-4">
                                        <div className="text-center py-8 text-muted-foreground">
                                            <MessageSquare className="w-8 h-8 mx-auto mb-2 opacity-50" />
                                            <p className="text-sm">No comments yet</p>
                                            <p className="text-xs">Add comments to discuss this task with your team</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="attachments" className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center space-x-2">
                                        <FileText className="w-5 h-5" />
                                        <span>Attachments</span>
                                    </CardTitle>
                                    <CardDescription>Manage files and documents related to this task</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="p-4 border rounded-md bg-muted/30">
                                            <p className="text-sm text-muted-foreground">
                                                File upload functionality will be available in the full version.
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
