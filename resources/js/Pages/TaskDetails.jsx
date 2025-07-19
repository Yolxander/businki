import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
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
    Star
} from 'lucide-react';

export default function TaskDetails({ auth, taskId }) {
    const [newComment, setNewComment] = useState('');
    const [newSubtask, setNewSubtask] = useState('');

    // Mock task data - in real app this would come from props
    const task = {
        id: taskId,
        title: 'Design homepage mockups',
        description: 'Create modern, responsive homepage mockups for the new website redesign project. Focus on user experience and conversion optimization.',
        client: 'Acme Corporation',
        project: 'Website Redesign',
        priority: 'high',
        status: 'in-progress',
        dueDate: '2024-02-15',
        estimatedTime: '4h',
        actualTime: '2.5h',
        tags: ['Design', 'UI/UX', 'Homepage'],
        assignee: 'client',
        createdBy: 'John Smith',
        createdAt: '2024-02-01',
        updatedAt: '2024-02-10',
        subtasks: [
            { id: 1, text: 'Create wireframes', completed: true, completedAt: '2024-02-05' },
            { id: 2, text: 'Design desktop version', completed: true, completedAt: '2024-02-08' },
            { id: 3, text: 'Design mobile version', completed: false },
            { id: 4, text: 'Create responsive breakpoints', completed: false },
            { id: 5, text: 'Design call-to-action buttons', completed: false }
        ],
        comments: [
            {
                id: 1,
                author: 'John Smith',
                content: 'Initial wireframes are ready for review. Please check the desktop layout.',
                timestamp: '2024-02-05 10:30 AM',
                avatar: 'JS'
            },
            {
                id: 2,
                author: 'Client',
                content: 'The layout looks great! Can we add more emphasis to the hero section?',
                timestamp: '2024-02-06 02:15 PM',
                avatar: 'C'
            },
            {
                id: 3,
                author: 'John Smith',
                content: 'Updated the hero section with better visual hierarchy. Mobile version is next.',
                timestamp: '2024-02-08 11:45 AM',
                avatar: 'JS'
            }
        ],
        attachments: [
            { id: 1, name: 'wireframes.pdf', size: '2.4 MB', type: 'pdf' },
            { id: 2, name: 'design-specs.fig', size: '1.8 MB', type: 'figma' },
            { id: 3, name: 'brand-guidelines.pdf', size: '3.1 MB', type: 'pdf' }
        ],
        relatedTasks: [
            { id: 2, title: 'Review SEO content', status: 'waiting', priority: 'medium' },
            { id: 3, title: 'Setup analytics tracking', status: 'todo', priority: 'high' }
        ]
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

    const progressPercentage = (task.subtasks.filter(st => st.completed).length / task.subtasks.length) * 100;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${task.title} - Task Details`} />

            <div className="max-w-6xl mx-auto">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center space-x-4">
                        <Link href="/bobbi-flow">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Flow
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{task.title}</h1>
                            <p className="text-sm text-muted-foreground">
                                {task.client} • {task.project}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={`/tasks/${taskId}/edit`}>
                            <Button variant="outline" size="sm">
                                <Edit className="w-4 h-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Button variant="outline" size="sm">
                            <MoreHorizontal className="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Task Overview */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>Task Overview</span>
                                    <div className="flex items-center space-x-2">
                                        {getPriorityIcon(task.priority)}
                                        <Badge className={getStatusColor(task.status)}>
                                            {getStatusIcon(task.status)}
                                            <span className="ml-1 capitalize">{task.status.replace('-', ' ')}</span>
                                        </Badge>
                                    </div>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <h3 className="font-medium text-foreground mb-2">Description</h3>
                                    <p className="text-muted-foreground leading-relaxed">{task.description}</p>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="flex items-center space-x-2">
                                        <Calendar className="w-4 h-4 text-muted-foreground" />
                                        <span className="text-sm text-muted-foreground">Due: {task.dueDate}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Clock className="w-4 h-4 text-muted-foreground" />
                                        <span className="text-sm text-muted-foreground">
                                            {task.actualTime} / {task.estimatedTime}
                                        </span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <User className="w-4 h-4 text-muted-foreground" />
                                        <span className="text-sm text-muted-foreground">
                                            {task.assignee === 'client' ? 'Client' : 'You'}
                                        </span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Building className="w-4 h-4 text-muted-foreground" />
                                        <span className="text-sm text-muted-foreground">{task.client}</span>
                                    </div>
                                </div>

                                {task.tags.length > 0 && (
                                    <div>
                                        <h3 className="font-medium text-foreground mb-2">Tags</h3>
                                        <div className="flex flex-wrap gap-2">
                                            {task.tags.map((tag, index) => (
                                                <Badge key={index} variant="outline" className="bg-muted/30">
                                                    {tag}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Subtasks */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>Subtasks</span>
                                    <div className="flex items-center space-x-2">
                                        <span className="text-sm text-muted-foreground">
                                            {task.subtasks.filter(st => st.completed).length} of {task.subtasks.length} completed
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
                                    {task.subtasks.map((subtask) => (
                                        <div key={subtask.id} className="flex items-center space-x-3">
                                            <Checkbox
                                                checked={subtask.completed}
                                                className="flex-shrink-0"
                                            />
                                            <span className={`flex-1 ${subtask.completed ? 'line-through text-muted-foreground' : 'text-foreground'}`}>
                                                {subtask.text}
                                            </span>
                                            {subtask.completed && (
                                                <span className="text-xs text-muted-foreground">
                                                    {subtask.completedAt}
                                                </span>
                                            )}
                                        </div>
                                    ))}
                                </div>

                                <div className="mt-4 flex space-x-2">
                                    <input
                                        type="text"
                                        placeholder="Add new subtask..."
                                        value={newSubtask}
                                        onChange={(e) => setNewSubtask(e.target.value)}
                                        className="flex-1 px-3 py-2 border border-input rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    />
                                    <Button size="sm" disabled={!newSubtask.trim()}>
                                        <Plus className="w-4 h-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Comments */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center space-x-2">
                                    <MessageSquare className="w-5 h-5" />
                                    <span>Comments ({task.comments.length})</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {task.comments.map((comment) => (
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
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Quick Actions */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button variant="outline" className="w-full justify-start">
                                    <Play className="w-4 h-4 mr-2" />
                                    Start Work
                                </Button>
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
                            </CardContent>
                        </Card>

                        {/* Attachments */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Attachments</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    {task.attachments.map((attachment) => (
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
                            </CardContent>
                        </Card>

                        {/* Related Tasks */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Related Tasks</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    {task.relatedTasks.map((relatedTask) => (
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
                                    <span>{task.createdBy}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Created:</span>
                                    <span>{task.createdAt}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Updated:</span>
                                    <span>{task.updatedAt}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Task ID:</span>
                                    <span className="font-mono">#{task.id}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
