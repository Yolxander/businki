import React, { useState, useEffect, useRef, useContext } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout, { SidebarContext } from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    ArrowLeft,
    Play,
    Pause,
    Square,
    Clock,
    CheckCircle,
    AlertCircle,
    FileText,
    MessageSquare,
    Download,
    Upload,
    Eye,
    Edit,
    Save,
    Send,
    Timer,
    Target,
    Calendar,
    User,
    Building,
    Tag,
    Plus,
    X,
    ExternalLink,
    Copy,
    Star,
    Flag,
    Maximize2,
    Minimize2
} from 'lucide-react';

export default function StartWork({ auth, taskId }) {
    const [task, setTask] = useState(null);
    const [loading, setLoading] = useState(true);
    const [workSession, setWorkSession] = useState({
        isActive: false,
        startTime: null,
        elapsedTime: 0,
        notes: '',
        progress: 0,
        status: 'not_started'
    });
    const [newNote, setNewNote] = useState('');
    const [newSubtask, setNewSubtask] = useState('');
    const [activeTab, setActiveTab] = useState('work');
    const [isFullScreen, setIsFullScreen] = useState(false);
    const mainRef = useRef(null);
    const { sidebarCollapsed, setSidebarCollapsed } = useContext(SidebarContext);
    const prevSidebarCollapsed = useRef(sidebarCollapsed);

    useEffect(() => {
        // Mock task data - in real app this would fetch from API
        const mockTask = {
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
            workNotes: [
                {
                    id: 1,
                    content: 'Started working on mobile design. Using Figma for wireframes.',
                    timestamp: '2024-02-10 09:30 AM',
                    type: 'work'
                },
                {
                    id: 2,
                    content: 'Client feedback received: "Love the desktop design, can we make the hero section more prominent?"',
                    timestamp: '2024-02-10 11:45 AM',
                    type: 'feedback'
                }
            ],
            attachments: [
                { id: 1, name: 'wireframes.pdf', size: '2.4 MB', type: 'pdf' },
                { id: 2, name: 'design-specs.fig', size: '1.8 MB', type: 'figma' },
                { id: 3, name: 'brand-guidelines.pdf', size: '3.1 MB', type: 'pdf' }
            ]
        };

        setTask(mockTask);
        setLoading(false);
    }, [taskId]);

    // Timer functionality
    useEffect(() => {
        let interval;
        if (workSession.isActive) {
            interval = setInterval(() => {
                setWorkSession(prev => ({
                    ...prev,
                    elapsedTime: prev.elapsedTime + 1
                }));
            }, 1000);
        }
        return () => clearInterval(interval);
    }, [workSession.isActive]);

    // Full screen logic
    useEffect(() => {
        function handleFullScreenChange() {
            const isFs = document.fullscreenElement === mainRef.current;
            setIsFullScreen(isFs);
            if (!isFs) {
                setSidebarCollapsed(prevSidebarCollapsed.current);
            }
        }
        document.addEventListener('fullscreenchange', handleFullScreenChange);
        return () => document.removeEventListener('fullscreenchange', handleFullScreenChange);
    }, [setSidebarCollapsed]);

    const handleToggleFullScreen = () => {
        if (!isFullScreen) {
            prevSidebarCollapsed.current = sidebarCollapsed;
            setSidebarCollapsed(true);
            if (mainRef.current.requestFullscreen) {
                mainRef.current.requestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    };

    const startWork = () => {
        setWorkSession(prev => ({
            ...prev,
            isActive: true,
            startTime: new Date(),
            status: 'in_progress'
        }));
    };

    const pauseWork = () => {
        setWorkSession(prev => ({
            ...prev,
            isActive: false,
            status: 'paused'
        }));
    };

    const stopWork = () => {
        setWorkSession(prev => ({
            ...prev,
            isActive: false,
            status: 'completed'
        }));
    };

    const formatTime = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    };

    const addWorkNote = () => {
        if (!newNote.trim()) return;

        const note = {
            id: Date.now(),
            content: newNote,
            timestamp: new Date().toLocaleString(),
            type: 'work'
        };

        setTask(prev => ({
            ...prev,
            workNotes: [note, ...prev.workNotes]
        }));
        setNewNote('');
    };

    const toggleSubtask = (subtaskId) => {
        setTask(prev => ({
            ...prev,
            subtasks: prev.subtasks.map(st =>
                st.id === subtaskId
                    ? { ...st, completed: !st.completed, completedAt: !st.completed ? new Date().toLocaleString() : null }
                    : st
            )
        }));
    };

    const addSubtask = () => {
        if (!newSubtask.trim()) return;

        const subtask = {
            id: Date.now(),
            text: newSubtask,
            completed: false
        };

        setTask(prev => ({
            ...prev,
            subtasks: [...prev.subtasks, subtask]
        }));
        setNewSubtask('');
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
                return <Target className="w-4 h-4 text-gray-400" />;
        }
    };

    if (loading) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Loading Work Session" />
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900 mx-auto"></div>
                        <p className="mt-4 text-muted-foreground">Loading work session...</p>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    const progressPercentage = (task.subtasks.filter(st => st.completed).length / task.subtasks.length) * 100;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Start Work - ${task.title}`} />

            <div ref={mainRef} className="h-screen flex flex-col bg-background">
                {/* Header */}
                <div className="flex-shrink-0 p-6 border-b border-border/50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-4">
                            <Link href={`/tasks/${taskId}`}>
                                <Button variant="outline" size="sm">
                                    <ArrowLeft className="w-4 h-4 mr-2" />
                                    Back to Task
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-xl font-semibold text-foreground">{task.title}</h1>
                                <p className="text-sm text-muted-foreground">
                                    {task.client} • {task.project}
                                </p>
                            </div>
                        </div>

                        {/* Work Timer & Full Screen */}
                        <div className="flex items-center space-x-4">
                            <div className="text-center">
                                <div className="text-2xl font-mono font-bold text-foreground">
                                    {formatTime(workSession.elapsedTime)}
                                </div>
                                <div className="text-xs text-muted-foreground">Elapsed Time</div>
                            </div>
                            <div className="flex items-center space-x-2">
                                {!workSession.isActive ? (
                                    <Button onClick={startWork} className="bg-green-600 hover:bg-green-700">
                                        <Play className="w-4 h-4 mr-2" />
                                        Start Work
                                    </Button>
                                ) : (
                                    <>
                                        <Button onClick={pauseWork} variant="outline">
                                            <Pause className="w-4 h-4 mr-2" />
                                            Pause
                                        </Button>
                                        <Button onClick={stopWork} variant="outline" className="bg-red-600 hover:bg-red-700 text-white">
                                            <Square className="w-4 h-4 mr-2" />
                                            Stop
                                        </Button>
                                    </>
                                )}
                                <Button
                                    variant={isFullScreen ? 'secondary' : 'outline'}
                                    size="icon"
                                    className="ml-2"
                                    onClick={handleToggleFullScreen}
                                    title={isFullScreen ? 'Exit Full Screen' : 'Full Screen'}
                                >
                                    {isFullScreen ? <Minimize2 className="w-5 h-5" /> : <Maximize2 className="w-5 h-5" />}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="flex-1 flex overflow-hidden">
                    {/* Left Panel - Work Area */}
                    <div className="flex-1 flex flex-col min-h-0">
                        <Tabs value={activeTab} onValueChange={setActiveTab} className="flex-1 flex flex-col min-h-0">
                            <div className="flex-shrink-0 px-6 pt-4 pb-2">
                                <TabsList className="grid w-full grid-cols-4">
                                    <TabsTrigger value="work">Work Area</TabsTrigger>
                                    <TabsTrigger value="notes">Work Notes</TabsTrigger>
                                    <TabsTrigger value="subtasks">Subtasks</TabsTrigger>
                                    <TabsTrigger value="attachments">Files</TabsTrigger>
                                </TabsList>
                            </div>

                            <TabsContent value="work" className="flex-1 p-6 space-y-6 overflow-y-auto pb-8">
                                {/* Task Overview */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center justify-between">
                                            <span>Task Overview</span>
                                            <div className="flex items-center space-x-2">
                                                {getPriorityIcon(task.priority)}
                                                <Badge className={getPriorityColor(task.priority)}>
                                                    {task.priority} Priority
                                                </Badge>
                                            </div>
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-muted-foreground leading-relaxed mb-4">{task.description}</p>
                                        <div className="grid grid-cols-2 gap-4 text-sm">
                                            <div className="flex items-center space-x-2">
                                                <Calendar className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-muted-foreground">Due: {task.dueDate}</span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Clock className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-muted-foreground">
                                                    {task.actualTime} / {task.estimatedTime}
                                                </span>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Work Progress */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Work Progress</CardTitle>
                                        <CardDescription>Track your progress on this task</CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm font-medium">Overall Progress</span>
                                            <span className="text-sm text-muted-foreground">{Math.round(progressPercentage)}%</span>
                                        </div>
                                        <div className="w-full bg-muted rounded-full h-2">
                                            <div
                                                className="bg-primary h-2 rounded-full transition-all duration-300"
                                                style={{ width: `${progressPercentage}%` }}
                                            ></div>
                                        </div>
                                        <div className="flex items-center justify-between text-sm">
                                            <span className="text-muted-foreground">
                                                {task.subtasks.filter(st => st.completed).length} of {task.subtasks.length} subtasks completed
                                            </span>
                                            <span className="text-muted-foreground">
                                                {formatTime(workSession.elapsedTime)} worked today
                                            </span>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Quick Actions */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Quick Actions</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="grid grid-cols-2 gap-3">
                                            <Button variant="outline" className="justify-start">
                                                <Eye className="w-4 h-4 mr-2" />
                                                Mark for Review
                                            </Button>
                                            <Button variant="outline" className="justify-start">
                                                <CheckCircle className="w-4 h-4 mr-2" />
                                                Mark Complete
                                            </Button>
                                            <Button variant="outline" className="justify-start">
                                                <Flag className="w-4 h-4 mr-2" />
                                                Flag Issue
                                            </Button>
                                            <Button variant="outline" className="justify-start">
                                                <Send className="w-4 h-4 mr-2" />
                                                Send Update
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="notes" className="flex-1 p-6 space-y-6 overflow-y-auto pb-8">
                                {/* Work Notes */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Work Notes</CardTitle>
                                        <CardDescription>Add notes about your work progress</CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="flex space-x-2">
                                            <Textarea
                                                placeholder="Add a work note..."
                                                value={newNote}
                                                onChange={(e) => setNewNote(e.target.value)}
                                                className="flex-1"
                                                rows={3}
                                            />
                                            <Button onClick={addWorkNote} disabled={!newNote.trim()}>
                                                <Plus className="w-4 h-4" />
                                            </Button>
                                        </div>

                                        <div className="space-y-3">
                                            {task.workNotes.map((note) => (
                                                <div key={note.id} className="p-3 border rounded-lg">
                                                    <div className="flex items-center justify-between mb-2">
                                                        <span className="text-sm font-medium">
                                                            {note.type === 'work' ? 'Work Note' : 'Feedback'}
                                                        </span>
                                                        <span className="text-xs text-muted-foreground">{note.timestamp}</span>
                                                    </div>
                                                    <p className="text-sm text-muted-foreground">{note.content}</p>
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="subtasks" className="flex-1 p-6 space-y-6 overflow-y-auto pb-8">
                                {/* Subtasks */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Subtasks</CardTitle>
                                        <CardDescription>Manage your task breakdown</CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="space-y-3">
                                            {task.subtasks.map((subtask) => (
                                                <div key={subtask.id} className="flex items-center space-x-3 p-2 border rounded-lg">
                                                    <Checkbox
                                                        checked={subtask.completed}
                                                        onCheckedChange={() => toggleSubtask(subtask.id)}
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

                                        <div className="flex space-x-2">
                                            <Input
                                                placeholder="Add new subtask..."
                                                value={newSubtask}
                                                onChange={(e) => setNewSubtask(e.target.value)}
                                                className="flex-1"
                                            />
                                            <Button onClick={addSubtask} disabled={!newSubtask.trim()}>
                                                <Plus className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="attachments" className="flex-1 p-6 space-y-6 overflow-y-auto pb-8">
                                {/* Attachments */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Files & Attachments</CardTitle>
                                        <CardDescription>Access and manage task files</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-3">
                                            {task.attachments.map((attachment) => (
                                                <div key={attachment.id} className="flex items-center justify-between p-3 border rounded-lg">
                                                    <div className="flex items-center space-x-3">
                                                        <FileText className="w-5 h-5 text-blue-500" />
                                                        <div>
                                                            <p className="font-medium">{attachment.name}</p>
                                                            <p className="text-sm text-muted-foreground">{attachment.size}</p>
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <Button variant="ghost" size="sm">
                                                            <Eye className="w-4 h-4" />
                                                        </Button>
                                                        <Button variant="ghost" size="sm">
                                                            <Download className="w-4 h-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>

                                        <div className="mt-4 pt-4 border-t">
                                            <Button variant="outline" className="w-full">
                                                <Upload className="w-4 h-4 mr-2" />
                                                Upload File
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </Tabs>
                    </div>

                    {/* Right Panel - Task Info */}
                    <div className="w-80 border-l border-border/50 bg-muted/30 overflow-y-auto">
                        <div className="p-6 space-y-6">
                            {/* Task Status */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Task Status</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Status</span>
                                        <Badge className="bg-blue-100 text-blue-800">
                                            {task.status.replace('-', ' ')}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Priority</span>
                                        <div className="flex items-center space-x-1">
                                            {getPriorityIcon(task.priority)}
                                            <span className="text-sm capitalize">{task.priority}</span>
                                        </div>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Assignee</span>
                                        <span className="text-sm">{task.assignee === 'client' ? 'Client' : 'You'}</span>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Time Tracking */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Time Tracking</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Estimated</span>
                                        <span className="text-sm font-medium">{task.estimatedTime}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Actual</span>
                                        <span className="text-sm font-medium">{task.actualTime}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Today</span>
                                        <span className="text-sm font-medium">{formatTime(workSession.elapsedTime)}</span>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Tags */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Tags</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-wrap gap-2">
                                        {task.tags.map((tag, index) => (
                                            <Badge key={index} variant="outline" className="bg-muted/30">
                                                {tag}
                                            </Badge>
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
            </div>
        </AuthenticatedLayout>
    );
}
