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
import { ArrowLeft, Save, Target, Calendar, Clock, User, Building, Tag, Plus, X } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

export default function CreateTask({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        client: '',
        project: '',
        priority: 'medium',
        status: 'todo',
        dueDate: '',
        estimatedTime: '',
        assignee: 'me',
        tags: [],
        subtasks: [],
        newTag: '',
        newSubtask: ''
    });

    const [showTagInput, setShowTagInput] = useState(false);
    const [showSubtaskInput, setShowSubtaskInput] = useState(false);

    const clients = [
        { id: 1, name: 'Acme Corp' },
        { id: 2, name: 'TechStart' },
        { id: 3, name: 'RetailPlus' },
        { id: 4, name: 'InnovateLab' }
    ];

    const projects = [
        { id: 1, name: 'Website Redesign', client: 'Acme Corp' },
        { id: 2, name: 'Content Strategy', client: 'TechStart' },
        { id: 3, name: 'E-commerce Platform', client: 'RetailPlus' },
        { id: 4, name: 'Brand Identity', client: 'Acme Corp' }
    ];

    const availableTags = ['Design', 'UI/UX', 'SEO', 'Content', 'Analytics', 'Setup', 'Review', 'Feedback', 'Development', 'Testing'];

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/tasks', {
            onSuccess: () => {
                console.log('Task created successfully');
            },
            onError: (errors) => {
                console.error('Task creation failed', errors);
            }
        });
    };

    const addTag = () => {
        if (data.newTag && !data.tags.includes(data.newTag)) {
            setData('tags', [...data.tags, data.newTag]);
            setData('newTag', '');
            setShowTagInput(false);
        }
    };

    const removeTag = (tagToRemove) => {
        setData('tags', data.tags.filter(tag => tag !== tagToRemove));
    };

    const addSubtask = () => {
        if (data.newSubtask) {
            setData('subtasks', [...data.subtasks, { id: Date.now(), text: data.newSubtask, completed: false }]);
            setData('newSubtask', '');
            setShowSubtaskInput(false);
        }
    };

    const removeSubtask = (subtaskId) => {
        setData('subtasks', data.subtasks.filter(subtask => subtask.id !== subtaskId));
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
                return '🔥';
            case 'medium':
                return '🟡';
            case 'low':
                return '🟢';
            default:
                return '⚪';
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Task" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/bobbi-flow">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Bobbi Flow
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">Create New Task</h1>
                            <p className="text-muted-foreground">Add a new task to your workflow</p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Target className="w-5 h-5 mr-2" />
                                Task Information
                            </CardTitle>
                            <CardDescription>
                                Enter the basic details for your task
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label htmlFor="title">Task Title *</Label>
                                <Input
                                    id="title"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    placeholder="Enter task title"
                                    className={errors.title ? 'border-red-500' : ''}
                                />
                                {errors.title && <p className="text-red-500 text-sm mt-1">{errors.title}</p>}
                            </div>

                            <div>
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Enter task description"
                                    rows={3}
                                    className={errors.description ? 'border-red-500' : ''}
                                />
                                {errors.description && <p className="text-red-500 text-sm mt-1">{errors.description}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="client">Client</Label>
                                    <Select value={data.client} onValueChange={(value) => setData('client', value)}>
                                        <SelectTrigger className={errors.client ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Select client" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {clients.map((client) => (
                                                <SelectItem key={client.id} value={client.name}>
                                                    {client.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.client && <p className="text-red-500 text-sm mt-1">{errors.client}</p>}
                                </div>
                                <div>
                                    <Label htmlFor="project">Project</Label>
                                    <Select value={data.project} onValueChange={(value) => setData('project', value)}>
                                        <SelectTrigger className={errors.project ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Select project" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {projects
                                                .filter(project => !data.client || project.client === data.client)
                                                .map((project) => (
                                                    <SelectItem key={project.id} value={project.name}>
                                                        {project.name}
                                                    </SelectItem>
                                                ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.project && <p className="text-red-500 text-sm mt-1">{errors.project}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Priority and Status */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Target className="w-5 h-5 mr-2" />
                                Priority & Status
                            </CardTitle>
                            <CardDescription>
                                Set the priority level and current status
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="priority">Priority</Label>
                                    <Select value={data.priority} onValueChange={(value) => setData('priority', value)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="high">
                                                <div className="flex items-center">
                                                    <span className="mr-2">🔥</span>
                                                    High Priority
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="medium">
                                                <div className="flex items-center">
                                                    <span className="mr-2">🟡</span>
                                                    Medium Priority
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="low">
                                                <div className="flex items-center">
                                                    <span className="mr-2">🟢</span>
                                                    Low Priority
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
                                            <SelectItem value="inbox">📥 Inbox</SelectItem>
                                            <SelectItem value="todo">📋 To Do</SelectItem>
                                            <SelectItem value="in-progress">⚡ In Progress</SelectItem>
                                            <SelectItem value="waiting">⏳ Waiting on Client</SelectItem>
                                            <SelectItem value="review">👀 Ready for Review</SelectItem>
                                            <SelectItem value="done">✅ Done</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="dueDate">Due Date</Label>
                                    <Input
                                        id="dueDate"
                                        type="date"
                                        value={data.dueDate}
                                        onChange={(e) => setData('dueDate', e.target.value)}
                                        className={errors.dueDate ? 'border-red-500' : ''}
                                    />
                                    {errors.dueDate && <p className="text-red-500 text-sm mt-1">{errors.dueDate}</p>}
                                </div>
                                <div>
                                    <Label htmlFor="estimatedTime">Estimated Time</Label>
                                    <Input
                                        id="estimatedTime"
                                        value={data.estimatedTime}
                                        onChange={(e) => setData('estimatedTime', e.target.value)}
                                        placeholder="e.g., 2h, 1.5h"
                                        className={errors.estimatedTime ? 'border-red-500' : ''}
                                    />
                                    {errors.estimatedTime && <p className="text-red-500 text-sm mt-1">{errors.estimatedTime}</p>}
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="assignee">Assignee</Label>
                                <Select value={data.assignee} onValueChange={(value) => setData('assignee', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="me">👤 You</SelectItem>
                                        <SelectItem value="client">👥 Client</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Tags */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Tag className="w-5 h-5 mr-2" />
                                Tags
                            </CardTitle>
                            <CardDescription>
                                Add tags to categorize your task
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex flex-wrap gap-2">
                                {data.tags.map((tag, index) => (
                                    <Badge
                                        key={index}
                                        variant="outline"
                                        className="flex items-center space-x-1"
                                    >
                                        <span>{tag}</span>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="h-4 w-4 p-0 hover:bg-transparent"
                                            onClick={() => removeTag(tag)}
                                        >
                                            <X className="w-3 h-3" />
                                        </Button>
                                    </Badge>
                                ))}
                            </div>

                            {showTagInput ? (
                                <div className="flex space-x-2">
                                    <Input
                                        value={data.newTag}
                                        onChange={(e) => setData('newTag', e.target.value)}
                                        placeholder="Enter tag name"
                                        onKeyPress={(e) => e.key === 'Enter' && addTag()}
                                    />
                                    <Button type="button" onClick={addTag} size="sm">
                                        Add
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setShowTagInput(false)}
                                        size="sm"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            ) : (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setShowTagInput(true)}
                                    size="sm"
                                >
                                    <Plus className="w-4 h-4 mr-2" />
                                    Add Tag
                                </Button>
                            )}

                            <div className="text-sm text-muted-foreground">
                                <p className="font-medium mb-2">Suggested tags:</p>
                                <div className="flex flex-wrap gap-1">
                                    {availableTags
                                        .filter(tag => !data.tags.includes(tag))
                                        .map((tag) => (
                                            <Button
                                                key={tag}
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => {
                                                    if (!data.tags.includes(tag)) {
                                                        setData('tags', [...data.tags, tag]);
                                                    }
                                                }}
                                                className="h-6 px-2 text-xs"
                                            >
                                                {tag}
                                            </Button>
                                        ))}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Subtasks */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Target className="w-5 h-5 mr-2" />
                                Subtasks
                            </CardTitle>
                            <CardDescription>
                                Break down your task into smaller steps
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                {data.subtasks.map((subtask) => (
                                    <div key={subtask.id} className="flex items-center space-x-2">
                                        <Checkbox checked={subtask.completed} />
                                        <span className="flex-1">{subtask.text}</span>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => removeSubtask(subtask.id)}
                                            className="h-6 w-6 p-0"
                                        >
                                            <X className="w-3 h-3" />
                                        </Button>
                                    </div>
                                ))}
                            </div>

                            {showSubtaskInput ? (
                                <div className="flex space-x-2">
                                    <Input
                                        value={data.newSubtask}
                                        onChange={(e) => setData('newSubtask', e.target.value)}
                                        placeholder="Enter subtask"
                                        onKeyPress={(e) => e.key === 'Enter' && addSubtask()}
                                    />
                                    <Button type="button" onClick={addSubtask} size="sm">
                                        Add
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setShowSubtaskInput(false)}
                                        size="sm"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            ) : (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setShowSubtaskInput(true)}
                                    size="sm"
                                >
                                    <Plus className="w-4 h-4 mr-2" />
                                    Add Subtask
                                </Button>
                            )}
                        </CardContent>
                    </Card>

                    {/* Form Actions */}
                    <div className="flex justify-end space-x-4">
                        <Link href="/bobbi-flow">
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save className="w-4 h-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Task'}
                        </Button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
