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
import { toast } from 'sonner';
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
    Minimize2,
    Keyboard
} from 'lucide-react';

function ShortcutsModal({ open, onClose }) {
    return (
        open && (
            <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                <div className="bg-background rounded-lg shadow-lg max-w-md w-full p-6 relative">
                    <button
                        className="absolute top-3 right-3 text-muted-foreground hover:text-foreground"
                        onClick={onClose}
                        aria-label="Close"
                    >
                        <X className="w-5 h-5" />
                    </button>
                    <h2 className="text-xl font-bold mb-4">Keyboard Shortcuts</h2>
                    <p className="text-sm text-muted-foreground mb-4">Press B + the following keys:</p>
                    <ul className="space-y-2 text-sm">
                        <li><span className="font-mono bg-muted px-2 py-1 rounded">B + f</span> — Toggle Full Screen</li>
                        <li><span className="font-mono bg-muted px-2 py-1 rounded">B + s</span> — Start/Pause Work</li>
                        <li><span className="font-mono bg-muted px-2 py-1 rounded">B + e</span> — Stop Work</li>
                        <li><span className="font-mono bg-muted px-2 py-1 rounded">B + 1</span> — Work Area tab</li>
                        <li><span className="font-mono bg-muted px-2 py-1 rounded">B + 2</span> — Work Notes tab</li>
                        <li><span className="font-mono bg-muted px-2 py-1 rounded">B + 3</span> — Subtasks tab</li>
                        <li><span className="font-mono bg-muted px-2 py-1 rounded">B + 4</span> — Files tab</li>
                        <li><span className="font-mono bg-muted px-2 py-1 rounded">B + ?</span> — Show this help</li>
                    </ul>
                </div>
            </div>
        )
    );
}

export default function StartWork({ auth, task, error }) {
    const [loading, setLoading] = useState(false);
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
    const [subtasks, setSubtasks] = useState([]);
    const [activeTab, setActiveTab] = useState('work');
    const [isFullScreen, setIsFullScreen] = useState(false);
    const mainRef = useRef(null);
    const { sidebarCollapsed, setSidebarCollapsed } = useContext(SidebarContext);
    const prevSidebarCollapsed = useRef(sidebarCollapsed);
    const [showShortcuts, setShowShortcuts] = useState(false);
    const [bKeyPressed, setBKeyPressed] = useState(false);

    // Map database status to frontend status
    const mapStatus = (dbStatus) => {
        const statusMap = {
            'todo': 'inbox',
            'in_progress': 'in-progress',
            'done': 'done'
        };
        return statusMap[dbStatus] || dbStatus;
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

    // Transform task data to match component expectations
    const transformedTask = task ? {
        id: task.id,
        title: task.title,
        description: task.description || '',
        client: task.project?.client?.name || 'No Client',
        project: task.project?.name || 'No Project',
        priority: task.priority || 'medium',
        status: mapStatus(task.status),
        dueDate: task.due_date ? task.due_date.split('T')[0] : null,
        estimatedTime: task.estimated_hours ? `${task.estimated_hours}h` : null,
        tags: task.tags || [],
        subtasks: task.subtasks?.map(subtask => ({
            id: subtask.id,
            text: subtask.description,
            completed: subtask.status === 'done'
        })) || [],
        assignee: task.assigned_to ? 'me' : 'client'
    } : null;

    // Handle error case
    if (error || !transformedTask) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Task Not Found" />
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <h1 className="text-2xl font-bold text-foreground mb-2">Task Not Found</h1>
                        <p className="text-muted-foreground mb-4">The task you're looking for doesn't exist or you don't have permission to access it.</p>
                        <Link href="/bobbi-flow">
                            <Button variant="outline">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Flow
                            </Button>
                        </Link>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

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

    // Keyboard shortcuts
    useEffect(() => {
        function handleKeyDown(e) {
            if (showShortcuts) {
                if (e.key === 'Escape') {
                    setShowShortcuts(false);
                    e.preventDefault();
                }
                return;
            }

            // Check for B key press
            if (e.key.toLowerCase() === 'b' && !bKeyPressed) {
                setBKeyPressed(true);
                return;
            }

            // Only process shortcuts if B key is pressed
            if (bKeyPressed) {
                if (e.key === '?' || (e.shiftKey && e.key === '/')) {
                    setShowShortcuts(true);
                    e.preventDefault();
                } else if (e.key === 'f') {
                    handleToggleFullScreen();
                    e.preventDefault();
                } else if (e.key === 's') {
                    if (!workSession.isActive) startWork();
                    else pauseWork();
                    e.preventDefault();
                } else if (e.key === 'e') {
                    if (workSession.isActive) stopWork();
                    e.preventDefault();
                } else if (e.key === '1') {
                    setActiveTab('work');
                    e.preventDefault();
                } else if (e.key === '2') {
                    setActiveTab('notes');
                    e.preventDefault();
                } else if (e.key === '3') {
                    setActiveTab('subtasks');
                    e.preventDefault();
                } else if (e.key === '4') {
                    setActiveTab('attachments');
                    e.preventDefault();
                }
            }
        }

        function handleKeyUp(e) {
            // Reset B key state when released
            if (e.key.toLowerCase() === 'b') {
                setBKeyPressed(false);
            }
        }

        window.addEventListener('keydown', handleKeyDown);
        window.addEventListener('keyup', handleKeyUp);
        return () => {
            window.removeEventListener('keydown', handleKeyDown);
            window.removeEventListener('keyup', handleKeyUp);
        };
    }, [showShortcuts, workSession.isActive, setActiveTab, handleToggleFullScreen, startWork, pauseWork, stopWork, bKeyPressed]);

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

    const progressPercentage = subtasks.length > 0 ? (subtasks.filter(st => st.completed).length / subtasks.length) * 100 : 0;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Start Work - ${transformedTask.title}`} />
            <ShortcutsModal open={showShortcuts} onClose={() => setShowShortcuts(false)} />
            <div ref={mainRef} className="h-screen flex flex-col bg-background">
                {/* Header */}
                <div className="flex-shrink-0 p-6 border-b border-border/50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-4">
                            <Link href={`/tasks/${transformedTask.id}`}>
                                <Button variant="outline" size="sm">
                                    <ArrowLeft className="w-4 h-4 mr-2" />
                                    Back to Task
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-xl font-semibold text-foreground">{transformedTask.title}</h1>
                                <p className="text-sm text-muted-foreground">
                                    {transformedTask.client} • {transformedTask.project}
                                </p>
                            </div>
                        </div>
                        {/* Work Timer, Full Screen, Shortcuts */}
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
                                <Button
                                    variant="outline"
                                    size="icon"
                                    className="ml-2"
                                    onClick={() => setShowShortcuts(true)}
                                    title="Keyboard Shortcuts (? or Shift+/)"
                                >
                                    <Keyboard className="w-5 h-5" />
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
                                                {getPriorityIcon(transformedTask.priority)}
                                                <Badge className={getPriorityColor(transformedTask.priority)}>
                                                    {transformedTask.priority} Priority
                                                </Badge>
                                            </div>
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-muted-foreground leading-relaxed mb-4">{transformedTask.description}</p>
                                        <div className="grid grid-cols-2 gap-4 text-sm">
                                            <div className="flex items-center space-x-2">
                                                <Calendar className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-muted-foreground">Due: {transformedTask.dueDate}</span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Clock className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-muted-foreground">
                                                    {transformedTask.estimatedTime} / {transformedTask.estimatedTime}
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
                                                {subtasks.filter(st => st.completed).length} of {subtasks.length} subtasks completed
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
                                            <div className="text-center py-8 text-muted-foreground">
                                                <MessageSquare className="w-8 h-8 mx-auto mb-2 opacity-50" />
                                                <p className="text-sm">No work notes yet</p>
                                                <p className="text-xs">Add notes to track your progress</p>
                                            </div>
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
                                            {subtasks.map((subtask) => (
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
                                                            {subtask.completedAt ? new Date(subtask.completedAt).toLocaleDateString() : 'Completed'}
                                                        </span>
                                                    )}
                                                </div>
                                            ))}

                                            {subtasks.length === 0 && (
                                                <div className="text-center py-8 text-muted-foreground">
                                                    <p className="text-sm">No subtasks yet</p>
                                                    <p className="text-xs">Add a subtask to break down this task</p>
                                                </div>
                                            )}
                                        </div>

                                        <div className="flex space-x-2">
                                            <Input
                                                placeholder="Add new subtask..."
                                                value={newSubtask}
                                                onChange={(e) => setNewSubtask(e.target.value)}
                                                onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addSubtask())}
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
                                            <div className="text-center py-8 text-muted-foreground">
                                                <FileText className="w-8 h-8 mx-auto mb-2 opacity-50" />
                                                <p className="text-sm">No attachments yet</p>
                                                <p className="text-xs">Upload files to share with your team</p>
                                            </div>
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
                                            {transformedTask.status.replace('-', ' ')}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Priority</span>
                                        <div className="flex items-center space-x-1">
                                            {getPriorityIcon(transformedTask.priority)}
                                            <span className="text-sm capitalize">{transformedTask.priority}</span>
                                        </div>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Assignee</span>
                                        <span className="text-sm">{transformedTask.assignee === 'client' ? 'Client' : 'You'}</span>
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
                                        <span className="text-sm font-medium">{transformedTask.estimatedTime}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Actual</span>
                                        <span className="text-sm font-medium">Not tracked</span>
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
                                        {transformedTask.tags.map((tag, index) => (
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
                                        <span>{task.user?.name || 'Unknown'}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">Created:</span>
                                        <span>{task.created_at ? task.created_at.split('T')[0] : 'Unknown'}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">Updated:</span>
                                        <span>{task.updated_at ? task.updated_at.split('T')[0] : 'Unknown'}</span>
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
