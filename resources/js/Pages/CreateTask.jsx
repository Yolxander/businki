import React, { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Save, Target, Calendar, Clock, User, Building, Tag, Plus, X, Flame, Circle, Inbox, CheckSquare, Zap, Clock as ClockIcon, Eye, CheckCircle, User as UserIcon, Users } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

export default function CreateTask({ auth, projects = [] }) {
    const [selectedProject, setSelectedProject] = useState(null);

    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        project_id: '',
        priority: 'medium',
        status: 'todo',
        due_date: '',
        estimated_hours: '',
        assigned_to: '',
        tags: [],
        subtasks: [],
        newTag: '',
        newSubtask: ''
    });

    const [showTagInput, setShowTagInput] = useState(false);
    const [showSubtaskInput, setShowSubtaskInput] = useState(false);

    // Handle project_id from URL parameters
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const projectId = urlParams.get('project_id');

        if (projectId && projects.length > 0) {
            const project = projects.find(p => p.id.toString() === projectId);
            if (project) {
                setSelectedProject(project);
                setData('project_id', project.id);
                console.log('Project pre-selected:', project.name, 'ID:', project.id);
            }
        }
    }, [projects, setData]);

    // Additional effect to handle project_id when projects are loaded
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const projectId = urlParams.get('project_id');

        if (projectId && projects.length > 0 && !selectedProject) {
            const project = projects.find(p => p.id.toString() === projectId);
            if (project) {
                setSelectedProject(project);
                setData('project_id', project.id);
                console.log('Project pre-selected (fallback):', project.name, 'ID:', project.id);
            }
        }
    }, [projects, selectedProject, setData]);

    // Users for assignment (TODO: Fetch from API)
    const users = [
        { id: 1, name: 'You' },
        { id: 2, name: 'Client' }
    ];

    const availableTags = ['Design', 'UI/UX', 'SEO', 'Content', 'Analytics', 'Setup', 'Review', 'Feedback', 'Development', 'Testing'];

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/tasks', {
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
            setData('subtasks', [...data.subtasks, data.newSubtask]);
            setData('newSubtask', '');
            setShowSubtaskInput(false);
        }
    };

    const removeSubtask = (subtaskIndex) => {
        setData('subtasks', data.subtasks.filter((_, index) => index !== subtaskIndex));
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
                return <Flame className="w-4 h-4 text-red-500" />;
            case 'medium':
                return <Circle className="w-4 h-4 text-yellow-500" />;
            case 'low':
                return <Circle className="w-4 h-4 text-green-500" />;
            default:
                return <Circle className="w-4 h-4 text-gray-500" />;
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={selectedProject ? `Create New Task for ${selectedProject.name}` : "Create Task"} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href={selectedProject ? `/projects/${selectedProject.id}` : "/bobbi-flow"}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                {selectedProject ? `Back to ${selectedProject.name}` : "Back to Bobbi Flow"}
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">
                                {selectedProject ? `Create New Task for ${selectedProject.name}` : "Create New Task"}
                            </h1>
                            <p className="text-muted-foreground">Add a new task to your workflow</p>
                        </div>
                    </div>

                    {/* Form Actions - Moved to top */}
                    <div className="flex space-x-4">
                        <Link href="/bobbi-flow">
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing} onClick={handleSubmit}>
                            <Save className="w-4 h-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Task'}
                        </Button>
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

                            <div>
                                <Label htmlFor="project">Project</Label>
                                <Select
                                    value={data.project_id ? data.project_id.toString() : ''}
                                    onValueChange={(value) => {
                                        const project = projects.find(p => p.id.toString() === value);
                                        setSelectedProject(project);
                                        setData('project_id', parseInt(value));
                                        console.log('Project selected:', project?.name, 'ID:', value);
                                    }}
                                    disabled={projects.length === 0}
                                >
                                    <SelectTrigger className={errors.project_id ? 'border-red-500' : ''}>
                                        <SelectValue placeholder={projects.length === 0 ? "No projects available" : "Select project"} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {projects.map((project) => (
                                            <SelectItem key={project.id} value={project.id.toString()}>
                                                {project.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {data.project_id && selectedProject && (
                                    <p className="text-sm text-green-600 mt-1">
                                        ✓ Selected: {selectedProject.name}
                                    </p>
                                )}
                                {projects.length === 0 && (
                                    <div className="text-amber-600 text-sm mt-1 space-y-2">
                                        <p>No projects found. You need to create a project first before creating tasks.</p>
                                        <Link href="/projects/create" className="text-blue-600 hover:text-blue-800 underline">
                                            Create your first project →
                                        </Link>
                                    </div>
                                )}
                                {errors.project_id && <p className="text-red-500 text-sm mt-1">{errors.project_id}</p>}
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
                                                    <Flame className="w-4 h-4 mr-2 text-red-500" />
                                                    High Priority
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="medium">
                                                <div className="flex items-center">
                                                    <Circle className="w-4 h-4 mr-2 text-yellow-500" />
                                                    Medium Priority
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="low">
                                                <div className="flex items-center">
                                                    <Circle className="w-4 h-4 mr-2 text-green-500" />
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
                                            <SelectItem value="inbox">
                                                <div className="flex items-center">
                                                    <Inbox className="w-4 h-4 mr-2" />
                                                    Inbox
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="todo">
                                                <div className="flex items-center">
                                                    <CheckSquare className="w-4 h-4 mr-2" />
                                                    To Do
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="in-progress">
                                                <div className="flex items-center">
                                                    <Zap className="w-4 h-4 mr-2" />
                                                    In Progress
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="waiting">
                                                <div className="flex items-center">
                                                    <ClockIcon className="w-4 h-4 mr-2" />
                                                    Waiting on Client
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="review">
                                                <div className="flex items-center">
                                                    <Eye className="w-4 h-4 mr-2" />
                                                    Ready for Review
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="done">
                                                <div className="flex items-center">
                                                    <CheckCircle className="w-4 h-4 mr-2" />
                                                    Done
                                                </div>
                                            </SelectItem>
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
                                        value={data.due_date}
                                        onChange={(e) => setData('due_date', e.target.value)}
                                        className={errors.due_date ? 'border-red-500' : ''}
                                    />
                                    {errors.due_date && <p className="text-red-500 text-sm mt-1">{errors.due_date}</p>}
                                </div>
                                <div>
                                    <Label htmlFor="estimatedTime">Estimated Time (hours)</Label>
                                    <Input
                                        id="estimatedTime"
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        max="999.99"
                                        value={data.estimated_hours}
                                        onChange={(e) => setData('estimated_hours', e.target.value ? parseFloat(e.target.value) : '')}
                                        placeholder="e.g., 2, 1.5"
                                        className={errors.estimated_hours ? 'border-red-500' : ''}
                                    />
                                    {errors.estimated_hours && <p className="text-red-500 text-sm mt-1">{errors.estimated_hours}</p>}
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="assignee">Assignee</Label>
                                <Select value={data.assigned_to ? data.assigned_to.toString() : ''} onValueChange={(value) => setData('assigned_to', parseInt(value))}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {users.map((user) => (
                                            <SelectItem key={user.id} value={user.id.toString()}>
                                                <div className="flex items-center">
                                                    {user.id === 1 ? (
                                                        <UserIcon className="w-4 h-4 mr-2" />
                                                    ) : (
                                                        <Users className="w-4 h-4 mr-2" />
                                                    )}
                                                    {user.name}
                                                </div>
                                            </SelectItem>
                                        ))}
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
                                {data.subtasks.map((subtask, index) => (
                                    <div key={index} className="flex items-center space-x-2">
                                        <Checkbox checked={false} /> {/* Subtasks are now just text, no completion status */}
                                        <span className="flex-1">{subtask}</span>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => removeSubtask(index)}
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

                    {/* Form Actions - Moved to end of page */}
                    {/* The buttons are now moved to the top */}
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
