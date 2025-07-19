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
import { ArrowLeft, Save, Target, Calendar, Clock, User, Building, Tag, Plus, X, MessageSquare, FileText, CheckCircle, AlertCircle, Circle } from 'lucide-react';

export default function EditTask({ auth, taskId }) {
    const [newTag, setNewTag] = useState('');
    const [newSubtask, setNewSubtask] = useState('');

    // Mock task data - in real app this would come from props
    const task = {
        id: taskId,
        title: 'Design homepage mockups',
        description: 'Create modern, responsive homepage mockups for the new website redesign project. Focus on user experience and conversion optimization.',
        client: 'acme-corp',
        project: 'website-redesign',
        priority: 'high',
        status: 'in-progress',
        dueDate: '2024-02-15',
        estimatedTime: '4h',
        assignee: 'client',
        tags: ['Design', 'UI/UX', 'Homepage'],
        subtasks: [
            { id: 1, text: 'Create wireframes', completed: true },
            { id: 2, text: 'Design desktop version', completed: true },
            { id: 3, text: 'Design mobile version', completed: false },
            { id: 4, text: 'Create responsive breakpoints', completed: false },
            { id: 5, text: 'Design call-to-action buttons', completed: false }
        ]
    };

    const { data, setData, put, processing, errors } = useForm({
        title: task.title,
        description: task.description,
        client: task.client,
        project: task.project,
        priority: task.priority,
        status: task.status,
        dueDate: task.dueDate,
        estimatedTime: task.estimatedTime,
        assignee: task.assignee,
        tags: task.tags,
        subtasks: task.subtasks
    });

    // Mock data for dropdowns
    const clients = [
        { id: 'acme-corp', name: 'Acme Corporation' },
        { id: 'techstart', name: 'TechStart Inc' },
        { id: 'retailplus', name: 'RetailPlus' }
    ];

    const projects = [
        { id: 'website-redesign', name: 'Website Redesign', client: 'acme-corp' },
        { id: 'content-strategy', name: 'Content Strategy', client: 'techstart' },
        { id: 'ecommerce-platform', name: 'E-commerce Platform', client: 'retailplus' }
    ];

    const filteredProjects = projects.filter(project => project.client === data.client);

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
        put(`/tasks/${taskId}`, {
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
            <Head title="Edit Task" />

            <div className="max-w-4xl mx-auto">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center space-x-4">
                        <Link href={`/tasks/${taskId}`}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Task
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">Edit Task</h1>
                            <p className="text-sm text-muted-foreground">Update task details and settings</p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <Tabs defaultValue="overview" className="space-y-6">
                        <TabsList className="grid w-full grid-cols-4">
                            <TabsTrigger value="overview">Overview</TabsTrigger>
                            <TabsTrigger value="subtasks">Subtasks</TabsTrigger>
                            <TabsTrigger value="comments">Comments</TabsTrigger>
                            <TabsTrigger value="attachments">Attachments</TabsTrigger>
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

                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <Label htmlFor="client">Client</Label>
                                                    <Select value={data.client} onValueChange={(value) => setData('client', value)}>
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Select client" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {clients.map((client) => (
                                                                <SelectItem key={client.id} value={client.id}>
                                                                    {client.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </div>

                                                <div>
                                                    <Label htmlFor="project">Project</Label>
                                                    <Select value={data.project} onValueChange={(value) => setData('project', value)}>
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Select project" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {filteredProjects.map((project) => (
                                                                <SelectItem key={project.id} value={project.id}>
                                                                    {project.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Task Settings */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Task Settings</CardTitle>
                                            <CardDescription>Configure priority, status, and timing</CardDescription>
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
                                                            <SelectItem value="low">Low</SelectItem>
                                                            <SelectItem value="medium">Medium</SelectItem>
                                                            <SelectItem value="high">High</SelectItem>
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
                                                            <SelectItem value="todo">To Do</SelectItem>
                                                            <SelectItem value="in-progress">In Progress</SelectItem>
                                                            <SelectItem value="waiting">Waiting on Client</SelectItem>
                                                            <SelectItem value="review">Ready for Review</SelectItem>
                                                            <SelectItem value="done">Done</SelectItem>
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
                                                        value={data.dueDate}
                                                        onChange={(e) => setData('dueDate', e.target.value)}
                                                    />
                                                </div>

                                                <div>
                                                    <Label htmlFor="estimatedTime">Estimated Time</Label>
                                                    <Input
                                                        id="estimatedTime"
                                                        value={data.estimatedTime}
                                                        onChange={(e) => setData('estimatedTime', e.target.value)}
                                                        placeholder="e.g., 2h, 1.5h"
                                                    />
                                                </div>
                                            </div>

                                            <div>
                                                <Label htmlFor="assignee">Assignee</Label>
                                                <Select value={data.assignee} onValueChange={(value) => setData('assignee', value)}>
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="me">You</SelectItem>
                                                        <SelectItem value="client">Client</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Tags */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Tags</CardTitle>
                                            <CardDescription>Add tags to categorize the task</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="flex flex-wrap gap-2">
                                                {data.tags.map((tag, index) => (
                                                    <Badge key={index} variant="outline" className="bg-muted/30">
                                                        {tag}
                                                        <button
                                                            type="button"
                                                            onClick={() => removeTag(tag)}
                                                            className="ml-2 hover:text-red-500"
                                                        >
                                                            <X className="w-3 h-3" />
                                                        </button>
                                                    </Badge>
                                                ))}
                                            </div>
                                            <div className="flex space-x-2">
                                                <Input
                                                    placeholder="Add new tag..."
                                                    value={newTag}
                                                    onChange={(e) => setNewTag(e.target.value)}
                                                    onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addTag())}
                                                />
                                                <Button type="button" onClick={addTag} disabled={!newTag.trim()}>
                                                    <Plus className="w-4 h-4" />
                                                </Button>
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
                                        <CardContent className="space-y-3">
                                            <Button type="submit" className="w-full" disabled={processing}>
                                                <Save className="w-4 h-4 mr-2" />
                                                {processing ? 'Saving...' : 'Save Changes'}
                                            </Button>
                                            <Link href={`/tasks/${taskId}`}>
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
                                            <div>
                                                <h3 className="font-medium text-sm text-foreground">{data.title}</h3>
                                                <p className="text-xs text-muted-foreground line-clamp-2">{data.description}</p>
                                            </div>
                                            <div className="flex items-center justify-between text-xs text-muted-foreground">
                                                <span>Due: {data.dueDate}</span>
                                                <span>{data.estimatedTime}</span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Badge variant="outline" className="text-xs">
                                                    {data.priority}
                                                </Badge>
                                                <Badge variant="outline" className="text-xs">
                                                    {data.status.replace('-', ' ')}
                                                </Badge>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="subtasks" className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center justify-between">
                                        <span>Subtasks</span>
                                        <div className="flex items-center space-x-2">
                                            <span className="text-sm text-muted-foreground">
                                                {data.subtasks.filter(st => st.completed).length} of {data.subtasks.length} completed
                                            </span>
                                            <div className="w-20 bg-muted rounded-full h-2">
                                                <div
                                                    className="bg-primary h-2 rounded-full transition-all duration-300"
                                                    style={{
                                                        width: `${(data.subtasks.filter(st => st.completed).length / data.subtasks.length) * 100}%`
                                                    }}
                                                ></div>
                                            </div>
                                        </div>
                                    </CardTitle>
                                    <CardDescription>Break down the task into smaller steps</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-3">
                                        {data.subtasks.map((subtask) => (
                                            <div key={subtask.id} className="flex items-center space-x-3">
                                                <Checkbox
                                                    checked={subtask.completed}
                                                    onCheckedChange={() => toggleSubtask(subtask.id)}
                                                />
                                                <span className={`flex-1 ${subtask.completed ? 'line-through text-muted-foreground' : 'text-foreground'}`}>
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
                                    </div>
                                    <div className="flex space-x-2">
                                        <Input
                                            placeholder="Add new subtask..."
                                            value={newSubtask}
                                            onChange={(e) => setNewSubtask(e.target.value)}
                                            onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addSubtask())}
                                        />
                                        <Button type="button" onClick={addSubtask} disabled={!newSubtask.trim()}>
                                            <Plus className="w-4 h-4" />
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="comments" className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center space-x-2">
                                        <MessageSquare className="w-5 h-5" />
                                        <span>Comments</span>
                                    </CardTitle>
                                    <CardDescription>Add comments and notes to the task</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="p-4 border rounded-md bg-muted/30">
                                            <p className="text-sm text-muted-foreground">
                                                Comments functionality will be available in the full version.
                                            </p>
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
