import React, { useState, useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Plus,
    Filter,
    Search,
    Calendar,
    Clock,
    MessageSquare,
    CheckCircle,
    AlertCircle,
    Eye,
    EyeOff,
    MoreHorizontal,
    ChevronLeft,
    ChevronRight,
    Target,
    FileText,
    Star,
    Zap,
    CalendarDays,
    User,
    Building,
    Settings,
    Smartphone,
    Inbox,
    ListTodo,
    Play,
    Pause,
    Eye as EyeIcon,
    CheckCircle2,
    Circle,
    Timer,
    Bot,
    TrendingUp,
    Lightbulb,
    ArrowRight,
    Sparkles,
    Focus,
    Menu,
    Bell,
    Sun,
    Moon,
    ArrowLeft,
    Flag,
    Send,
    Save,
    BookOpen,
    X,
    Sparkles as SparklesIcon,
    Brain,
    Clock as ClockIcon,
    CheckSquare,
    Square,
    Coffee,
    Heart
} from 'lucide-react';

export default function BobbiFlow({ auth, tasks = [] }) {
    const [clientMode, setClientMode] = useState(false);
    const [selectedFilter, setSelectedFilter] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [aiAssistantOpen, setAiAssistantOpen] = useState(false);
    const [focusMode, setFocusMode] = useState(false);
    const [timerActive, setTimerActive] = useState(false);

    // Zen Mode states
    const [zenMode, setZenMode] = useState(false);
    const [zenTask, setZenTask] = useState(null);
    const [zenTimer, setZenTimer] = useState(0);
    const [zenTimerActive, setZenTimerActive] = useState(false);
    const [zenNotes, setZenNotes] = useState('');
    const [zenSubtaskProgress, setZenSubtaskProgress] = useState({});
    const [zenAiTip, setZenAiTip] = useState('');
    const [showExitModal, setShowExitModal] = useState(false);
    const [zenStartTime, setZenStartTime] = useState(null);
    const timerRef = useRef(null);

    // Debug logging
    console.log('BobbiFlow received tasks:', tasks);
    console.log('Tasks count:', tasks.length);

    // Check for duplicate task IDs
    const taskIds = tasks.map(task => task.id);
    const uniqueTaskIds = [...new Set(taskIds)];
    if (taskIds.length !== uniqueTaskIds.length) {
        console.warn('Duplicate task IDs detected:', taskIds.length - uniqueTaskIds.length, 'duplicates');
        console.log('All task IDs:', taskIds);
        console.log('Unique task IDs:', uniqueTaskIds);
    }

    // Transform database tasks to match the expected format
    const mapStatus = (dbStatus) => {
        const statusMap = {
            'todo': 'todo',
            'in_progress': 'in-progress',
            'done': 'done'
        };
        return statusMap[dbStatus] || 'todo';
    };

    const transformedTasks = tasks.map(task => ({
        id: task.id,
        title: task.title,
        client: task.project?.client?.name || 'No Client',
        project: task.project?.name || 'No Project',
        priority: task.priority || 'medium',
        status: mapStatus(task.status) || 'todo',
        dueDate: task.due_date ? task.due_date.split('T')[0] : null,
        estimatedTime: task.estimated_hours ? `${task.estimated_hours}h` : null,
        tags: task.tags || [],
        subtasks: task.subtasks?.map(subtask => ({
            id: subtask.id,
            text: subtask.description,
            completed: subtask.status === 'done'
        })) || [],
        comments: 0,
        assignee: task.assigned_to ? 'me' : 'client'
    }));

    console.log('Transformed tasks:', transformedTasks);
    console.log('Transformed tasks count:', transformedTasks.length);

    // Check for duplicate transformed task IDs
    const transformedTaskIds = transformedTasks.map(task => task.id);
    const uniqueTransformedTaskIds = [...new Set(transformedTaskIds)];
    if (transformedTaskIds.length !== uniqueTransformedTaskIds.length) {
        console.error('Duplicate transformed task IDs detected:', transformedTaskIds.length - uniqueTransformedTaskIds.length, 'duplicates');
        console.log('All transformed task IDs:', transformedTaskIds);
        console.log('Unique transformed task IDs:', uniqueTransformedTaskIds);
    }

    const lanes = [
        { id: 'todo', name: 'To Do', icon: ListTodo, color: 'bg-blue-50 border-blue-200', dbStatus: 'todo' },
        { id: 'in-progress', name: 'In Progress', icon: Play, color: 'bg-yellow-50 border-yellow-200', dbStatus: 'in_progress' },
        { id: 'review', name: 'Review', icon: EyeIcon, color: 'bg-purple-50 border-purple-200', dbStatus: 'in_progress' },
        { id: 'done', name: 'Done', icon: CheckCircle2, color: 'bg-green-50 border-green-200', dbStatus: 'done' }
    ];

    const getPriorityColor = (priority) => {
        switch (priority) {
            case 'high': return 'bg-red-100 text-red-800 border-red-200';
            case 'medium': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'low': return 'bg-green-100 text-green-800 border-green-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const getPriorityIcon = (priority) => {
        switch (priority) {
            case 'high': return <AlertCircle className="w-4 h-4 text-red-500" />;
            case 'medium': return <Clock className="w-4 h-4 text-yellow-500" />;
            case 'low': return <CheckCircle className="w-4 h-4 text-green-500" />;
            default: return <Circle className="w-4 h-4 text-gray-400" />;
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

    const filteredTasks = transformedTasks.filter(task => {
        if (selectedFilter !== 'all' && task.status !== selectedFilter) return false;
        if (searchQuery && !task.title.toLowerCase().includes(searchQuery.toLowerCase())) return false;
        return true;
    });

    // Create a map to track which tasks have been assigned to lanes
    const assignedTaskIds = new Set();

    const tasksByLane = lanes.map(lane => {
        const laneTasks = filteredTasks.filter(task => {
            // Skip if task is already assigned to another lane
            if (assignedTaskIds.has(task.id)) {
                console.log(`Task ${task.id} (${task.title}) already assigned to another lane, skipping`);
                return false;
            }

            // Check if task status matches lane's database status
            const taskDbStatus = task.status === 'in-progress' ? 'in_progress' : task.status;
            const matches = taskDbStatus === lane.dbStatus;

            if (matches) {
                assignedTaskIds.add(task.id);
                console.log(`Task ${task.id} (${task.title}) assigned to lane ${lane.id} with status ${taskDbStatus}`);
            } else {
                console.log(`Task ${task.id} (${task.title}) status ${taskDbStatus} does not match lane ${lane.id} status ${lane.dbStatus}`);
            }

            return matches;
        });

        console.log(`Lane ${lane.id} (${lane.name}) has ${laneTasks.length} tasks`);

        return {
            ...lane,
            tasks: laneTasks
        };
    });

    // Add any unassigned tasks to the inbox lane
    const unassignedTasks = filteredTasks.filter(task => !assignedTaskIds.has(task.id));
    if (unassignedTasks.length > 0) {
        console.log(`Found ${unassignedTasks.length} unassigned tasks:`, unassignedTasks.map(t => `${t.id} (${t.title})`));
        const inboxLaneIndex = tasksByLane.findIndex(lane => lane.id === 'inbox');
        if (inboxLaneIndex !== -1) {
            tasksByLane[inboxLaneIndex].tasks = [...tasksByLane[inboxLaneIndex].tasks, ...unassignedTasks];
            console.log(`Added ${unassignedTasks.length} unassigned tasks to inbox lane`);
        }
    }

    // Final verification - check for any remaining duplicates across all lanes
    const allLaneTaskIds = tasksByLane.flatMap(lane => lane.tasks.map(task => task.id));
    const uniqueLaneTaskIds = [...new Set(allLaneTaskIds)];
    if (allLaneTaskIds.length !== uniqueLaneTaskIds.length) {
        console.error('Duplicate tasks found across lanes:', allLaneTaskIds.length - uniqueLaneTaskIds.length, 'duplicates');
        console.log('All lane task IDs:', allLaneTaskIds);
        console.log('Unique lane task IDs:', uniqueLaneTaskIds);
    } else {
        console.log('No duplicate tasks found across lanes');
    }

    const completedTasks = transformedTasks.filter(t => t.status === 'done').length;
    const reviewTasks = transformedTasks.filter(t => t.status === 'review').length;
    const activeTasks = transformedTasks.filter(t => t.status === 'in-progress').length;

    // Zen Mode functions
    const enterZenMode = (task) => {
        setZenTask(task);
        setZenMode(true);
        setZenTimerActive(true);
        setZenStartTime(new Date());
        setZenTimer(0);
        setZenNotes('');
        setZenSubtaskProgress({});

        // Generate AI tip
        const tips = [
            "Take a deep breath and start with what feels most natural",
            "Remember to celebrate small wins as you complete subtasks",
            "If you get stuck, try switching to a different subtask",
            "Your progress is valuable - every step counts",
            "Stay hydrated and take gentle breaks when needed",
            "Focus on the process, not just the outcome",
            "You're doing great - trust your ability to figure things out",
            "Break complex tasks into smaller, manageable pieces",
            "Remember why this work matters to you",
            "Be kind to yourself - perfection is not required"
        ];
        setZenAiTip(tips[Math.floor(Math.random() * tips.length)]);
    };

    const exitZenMode = () => {
        setShowExitModal(true);
    };

    const confirmExitZenMode = () => {
        setZenMode(false);
        setZenTask(null);
        setZenTimerActive(false);
        setShowExitModal(false);
        setZenTimer(0);
        setZenNotes('');
        setZenSubtaskProgress({});
        setZenAiTip('');
    };

    const toggleSubtask = (subtaskId) => {
        setZenSubtaskProgress(prev => ({
            ...prev,
            [subtaskId]: !prev[subtaskId]
        }));
    };

    const formatTime = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    };

    // Timer effect
    useEffect(() => {
        if (zenTimerActive) {
            timerRef.current = setInterval(() => {
                setZenTimer(prev => prev + 1);
            }, 1000);
        } else {
            if (timerRef.current) {
                clearInterval(timerRef.current);
            }
        }

        return () => {
            if (timerRef.current) {
                clearInterval(timerRef.current);
            }
        };
    }, [zenTimerActive]);

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyPress = (e) => {
            if (zenMode) {
                if (e.key === 'Escape') {
                    exitZenMode();
                }
            } else if (e.key === 'z' || e.key === 'Z') {
                // Enter Zen Mode for first available task
                const firstTask = transformedTasks.find(t => t.status === 'todo' || t.status === 'in-progress');
                if (firstTask) {
                    enterZenMode(firstTask);
                }
            }
        };

        window.addEventListener('keydown', handleKeyPress);
        return () => window.removeEventListener('keydown', handleKeyPress);
    }, [zenMode, transformedTasks]);

    // Auto-save notes
    useEffect(() => {
        if (zenMode && zenNotes) {
            const timeoutId = setTimeout(() => {
                // Auto-save functionality would go here
                console.log('Auto-saving notes...');
            }, 5000);
            return () => clearTimeout(timeoutId);
        }
    }, [zenNotes, zenMode]);

    if (zenMode && zenTask) {
        return (
            <AuthenticatedLayout user={auth.user} focusMode={true}>
                <Head title={`Zen Mode - ${zenTask.title}`} />

                <div className="h-screen flex flex-col bg-background">
                    {/* Zen Mode Header */}
                    <div className="flex-shrink-0 border-b border-border/50 bg-background/95 backdrop-blur">
                        <div className="flex items-center justify-between px-6 py-4">
                            <div className="flex items-center space-x-4">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={exitZenMode}
                                    className="flex items-center space-x-2"
                                >
                                    <ArrowLeft className="w-4 h-4" />
                                    <span>Back to Flow</span>
                                </Button>
                                <div className="h-4 w-px bg-border"></div>
                                <div className="flex items-center space-x-2">
                                    <Target className="w-4 h-4 text-primary" />
                                    <span className="font-medium">{zenTask.title}</span>
                                </div>
                            </div>

                            <div className="flex items-center space-x-4">
                                <div className="flex items-center space-x-2 bg-primary/10 px-3 py-1 rounded-full border border-primary/20">
                                    <Timer className="w-4 h-4 text-primary" />
                                    <span className="font-mono text-sm font-medium">{formatTime(zenTimer)}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Zen Mode Content */}
                    <div className="flex-1 flex space-x-6 p-6">
                        {/* Task Overview Panel */}
                        <div className="w-1/3 space-y-6">
                            <Card className="border border-border/50 bg-background shadow-sm">
                                <CardContent className="p-6">
                                    <div className="space-y-4">
                                        <div>
                                            <h2 className="text-xl font-semibold text-foreground mb-2">
                                                {zenTask.title}
                                            </h2>
                                            <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                                                <Building className="w-4 h-4" />
                                                <span>{zenTask.project}</span>
                                            </div>
                                        </div>

                                        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <div className="flex items-start space-x-2">
                                                <Brain className="w-4 h-4 text-blue-600 mt-0.5" />
                                                <div>
                                                    <p className="text-sm font-medium text-blue-800">AI Tip</p>
                                                    <p className="text-sm text-blue-700 mt-1">{zenAiTip}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <h3 className="text-sm font-medium text-foreground mb-3">Subtasks</h3>
                                            <div className="space-y-2 max-h-48 overflow-y-auto pr-2">
                                                {zenTask.subtasks.map((subtask, index) => (
                                                    <div key={subtask.id} className="flex items-center space-x-3">
                                                        <Checkbox
                                                            checked={zenSubtaskProgress[subtask.id] || false}
                                                            onCheckedChange={() => toggleSubtask(subtask.id)}
                                                            className="data-[state=checked]:bg-primary data-[state=checked]:border-primary"
                                                        />
                                                        <span className={`text-sm ${zenSubtaskProgress[subtask.id] ? 'line-through text-muted-foreground' : 'text-foreground'}`}>
                                                            {subtask.text}
                                                        </span>
                                                    </div>
                                                ))}
                                                {zenTask.subtasks.length === 0 && (
                                                    <p className="text-sm text-muted-foreground">No subtasks defined</p>
                                                )}
                                            </div>
                                        </div>

                                        {zenTask.dueDate && (
                                            <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                                                <Calendar className="w-4 h-4" />
                                                <span>Due: {new Date(zenTask.dueDate).toLocaleDateString()}</span>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Work Notes Area */}
                        <div className="flex-1 space-y-6">
                            <Card className="border border-border/50 bg-background shadow-sm">
                                <CardContent className="p-6">
                                    <div className="space-y-4">
                                        <div className="flex items-center justify-between">
                                            <h3 className="text-lg font-semibold text-foreground">Progress & Notes</h3>
                                            <div className="flex items-center space-x-2">
                                                <Button variant="ghost" size="sm" className="flex items-center space-x-2">
                                                    <SparklesIcon className="w-4 h-4" />
                                                    <span>AI Suggest</span>
                                                </Button>
                                                <Button variant="ghost" size="sm" className="flex items-center space-x-2">
                                                    <BookOpen className="w-4 h-4" />
                                                    <span>Insert Prompt</span>
                                                </Button>
                                            </div>
                                        </div>

                                        <Textarea
                                            placeholder="Share your progress, thoughts, or any insights..."
                                            value={zenNotes}
                                            onChange={(e) => setZenNotes(e.target.value)}
                                            className="min-h-[200px] resize-none border border-border focus:border-primary"
                                        />

                                        <div className="flex items-center justify-between text-sm text-muted-foreground">
                                            <span>Auto-saved</span>
                                            <span>{zenNotes.length} characters</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Progress & Wellness Section */}
                            <Card className="border border-border/50 bg-background shadow-sm">
                                <CardContent className="p-6">
                                    <div className="space-y-4">
                                        <h3 className="text-lg font-semibold text-foreground">Progress & Wellness</h3>

                                        {/* Progress Bar */}
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="text-muted-foreground">Progress</span>
                                                <span className="font-medium">
                                                    {zenTask.subtasks.length > 0
                                                        ? `${Math.round((Object.values(zenSubtaskProgress).filter(Boolean).length / zenTask.subtasks.length) * 100)}%`
                                                        : '0%'
                                                    }
                                                </span>
                                            </div>
                                            <div className="w-full bg-muted rounded-full h-2">
                                                <div
                                                    className="bg-primary h-2 rounded-full transition-all duration-300"
                                                    style={{
                                                        width: zenTask.subtasks.length > 0
                                                            ? `${(Object.values(zenSubtaskProgress).filter(Boolean).length / zenTask.subtasks.length) * 100}%`
                                                            : '0%'
                                                    }}
                                                />
                                            </div>
                                        </div>

                                        {/* Quick Actions */}
                                        <div className="grid grid-cols-2 gap-3">
                                            <Button variant="outline" size="sm" className="flex items-center space-x-2">
                                                <Coffee className="w-4 h-4" />
                                                <span>Take Break</span>
                                            </Button>
                                            <Button variant="outline" size="sm" className="flex items-center space-x-2">
                                                <Heart className="w-4 h-4" />
                                                <span>Feeling Good</span>
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    {/* Bottom Controls */}
                    <div className="flex-shrink-0 border-t border-border/50 bg-background/95">
                        <div className="flex items-center justify-between px-6 py-4">
                            <div className="flex items-center space-x-4">
                                <Button
                                    variant="default"
                                    size="lg"
                                    className="flex items-center space-x-2 bg-green-600 hover:bg-green-700"
                                >
                                    <CheckCircle className="w-4 h-4" />
                                    <span>Mark Complete</span>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="lg"
                                    className="flex items-center space-x-2"
                                >
                                    <Flag className="w-4 h-4" />
                                    <span>Flag Issue</span>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="lg"
                                    className="flex items-center space-x-2"
                                >
                                    <Send className="w-4 h-4" />
                                    <span>Send Update</span>
                                </Button>
                            </div>

                            <div className="flex items-center space-x-4">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => setZenTimerActive(!zenTimerActive)}
                                    className="flex items-center space-x-2"
                                >
                                    {zenTimerActive ? <Pause className="w-4 h-4" /> : <Play className="w-4 h-4" />}
                                    <span>{zenTimerActive ? 'Pause' : 'Resume'}</span>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Exit Modal */}
                {showExitModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                        <div className="bg-background rounded-lg shadow-xl p-6 w-96 border border-border">
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <h3 className="text-lg font-semibold">Exit Zen Mode?</h3>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => setShowExitModal(false)}
                                    >
                                        <X className="w-4 h-4" />
                                    </Button>
                                </div>

                                <div className="space-y-3 text-sm">
                                    <div className="flex items-center space-x-2">
                                        <CheckCircle className="w-4 h-4 text-green-500" />
                                        <span>Task: {zenTask.title}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <ClockIcon className="w-4 h-4 text-blue-500" />
                                        <span>Time Spent: {formatTime(zenTimer)}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Brain className="w-4 h-4 text-purple-500" />
                                        <span>AI Summary: "Work in progress, notes saved"</span>
                                    </div>
                                </div>

                                <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                                    <Save className="w-4 h-4" />
                                    <span>Notes saved</span>
                                    <span>•</span>
                                    <BookOpen className="w-4 h-4" />
                                    <span>Add to prompt library</span>
                                </div>

                                <div className="flex items-center space-x-3 pt-4">
                                    <Button
                                        variant="outline"
                                        onClick={() => setShowExitModal(false)}
                                        className="flex-1"
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        variant="default"
                                        onClick={confirmExitZenMode}
                                        className="flex-1"
                                    >
                                        Exit Zen Mode
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout user={auth.user} focusMode={focusMode}>
            <Head title="Bobbi Flow" />

            <div className="h-screen flex flex-col bg-background">
                {/* 🧭 TOP NAVIGATION BAR */}
                {!focusMode && (
                    <div className="flex-shrink-0 border-b border-border/50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                        <div className="flex items-center justify-between px-6 py-4">
                            <div className="flex items-center space-x-6">
                                <div className="flex items-center space-x-2">
                                    <Target className="w-5 h-5 text-primary" />
                                    <h1 className="text-lg font-semibold">Bobbi Flow</h1>
                                </div>
                                <div className="h-6 w-px bg-border"></div>
                                <div className="flex items-center space-x-4">
                                    <Button variant="ghost" size="sm" className="flex items-center space-x-2">
                                        <Calendar className="w-4 h-4" />
                                        <span>Today</span>
                                        <ChevronRight className="w-3 h-3" />
                                    </Button>
                                    <Select value={selectedFilter} onValueChange={setSelectedFilter}>
                                        <SelectTrigger className="w-32">
                                            <Filter className="w-4 h-4 mr-2" />
                                            <SelectValue placeholder="Filter" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Tasks</SelectItem>
                                            <SelectItem value="todo">To Do</SelectItem>
                                            <SelectItem value="in-progress">In Progress</SelectItem>
                                            <SelectItem value="review">Review</SelectItem>
                                            <SelectItem value="done">Done</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div className="flex items-center space-x-4">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setFocusMode(true)}
                                    className="flex items-center space-x-2"
                                >
                                    <Focus className="w-4 h-4" />
                                    <span>Enter Focus Mode</span>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => setAiAssistantOpen(!aiAssistantOpen)}
                                    className="flex items-center space-x-2"
                                >
                                    <Bot className="w-4 h-4" />
                                    <span>AI Assistant</span>
                                </Button>
                            </div>
                        </div>
                    </div>
                )}

                {/* 🛠 ACTION TOOLBAR */}
                {!focusMode && (
                    <div className="flex-shrink-0 border-b border-border/50 bg-background/95">
                        <div className="flex items-center justify-between px-6 py-3">
                            <div className="flex items-center space-x-4">
                                <Link href="/tasks/create">
                                    <Button size="sm" className="flex items-center space-x-2">
                                        <Plus className="w-4 h-4" />
                                        <span>Add Task</span>
                                    </Button>
                                </Link>
                                <div className="relative">
                                    <Search className="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="⌘K Search..."
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        className="pl-9 w-64"
                                    />
                                </div>
                                <Button
                                    variant={timerActive ? "default" : "outline"}
                                    size="sm"
                                    onClick={() => setTimerActive(!timerActive)}
                                    className="flex items-center space-x-2"
                                >
                                    <Timer className="w-4 h-4" />
                                    <span>Timer</span>
                                </Button>
                            </div>

                            <div className="flex items-center space-x-2">
                                <Button variant="ghost" size="sm">
                                    <Menu className="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                )}

                {/* Focus Mode Toggle Button - Only visible in focus mode */}
                {focusMode && (
                    <div className="flex-shrink-0 border-b border-border/50 bg-background/95">
                        <div className="flex items-center justify-between px-6 py-3">
                            <div className="flex items-center space-x-2">
                                <Target className="w-4 h-4 text-primary" />
                                <span className="text-sm font-medium">Focus Mode Active</span>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => setFocusMode(false)}
                                className="flex items-center space-x-2"
                            >
                                <Eye className="w-4 h-4" />
                                <span>Exit Focus</span>
                            </Button>
                        </div>
                    </div>
                )}

                {/* 🗂 KANBAN BOARD – SCROLLABLE WORKFLOW */}
                <div className="flex-1 overflow-hidden">
                    <div className="h-full flex space-x-6 p-6 overflow-x-auto">
                        {tasksByLane.map((lane) => (
                            <div key={lane.id} className="flex-shrink-0 w-80">
                                <div className="flex items-center justify-between mb-4">
                                    <div className="flex items-center space-x-2">
                                        <lane.icon className="w-4 h-4 text-muted-foreground" />
                                        <h3 className="font-medium text-foreground">{lane.name}</h3>
                                        <Badge variant="secondary" className="text-xs">
                                            {lane.tasks.length}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center space-x-1">
                                        <Button variant="ghost" size="sm" className="h-6 w-6 p-0">
                                            <Plus className="w-3 h-3" />
                                        </Button>
                                        <Button variant="ghost" size="sm" className="h-6 w-6 p-0">
                                            <Sparkles className="w-3 h-3" />
                                        </Button>
                                        <Button variant="ghost" size="sm" className="h-6 w-6 p-0">
                                            <MoreHorizontal className="w-3 h-3" />
                                        </Button>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    {lane.tasks.length === 0 && (
                                        <div className="text-center py-8 text-muted-foreground">
                                            <div className="text-sm">No tasks in this lane</div>
                                            <Link href="/tasks/create" className="text-xs text-blue-600 hover:text-blue-800 underline mt-2 block">
                                                Create your first task →
                                            </Link>
                                        </div>
                                    )}
                                    {lane.tasks.map((task) => (
                                        <div key={task.id} className="group cursor-pointer block">
                                            <Card className="border border-border/50 hover:border-primary/30 hover:shadow-lg transition-all duration-200 bg-background relative overflow-hidden">
                                                {/* Zen Mode Button - appears on hover */}
                                                <div className="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={(e) => {
                                                            e.preventDefault();
                                                            e.stopPropagation();
                                                            enterZenMode(task);
                                                        }}
                                                        className="h-7 w-7 p-0 bg-primary/10 hover:bg-primary/20 border border-primary/20"
                                                    >
                                                        <SparklesIcon className="w-3 h-3 text-primary" />
                                                    </Button>
                                                </div>

                                                <CardContent className="p-4">
                                                    {/* Project/Category - Faded text at top */}
                                                    <div className="text-xs text-muted-foreground mb-2">
                                                        {task.project}
                                                    </div>

                                                    {/* Task Title & Priority */}
                                                    <div className="flex items-start justify-between mb-3 pr-10">
                                                        <div className="flex items-start space-x-2 flex-1">
                                                            <Building className="w-4 h-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                                            <h4 className="font-medium text-sm leading-tight text-foreground line-clamp-2">
                                                                {task.title}
                                                            </h4>
                                                        </div>
                                                        <div className="flex items-center space-x-1 flex-shrink-0">
                                                            {getPriorityIcon(task.priority)}
                                                            {task.priority === 'high' && (
                                                                <AlertCircle className="w-3 h-3 text-red-500" />
                                                            )}
                                                            <Star className="w-3 h-3 text-green-500" />
                                                        </div>
                                                    </div>

                                                    {/* Estimated Time */}
                                                    <div className="flex justify-end mb-3">
                                                        <span className="text-xs text-muted-foreground font-mono">
                                                            {task.estimatedTime}
                                                        </span>
                                                    </div>

                                                    {/* Tags */}
                                                    {task.tags.length > 0 && (
                                                        <div className="flex flex-wrap gap-1 mb-3">
                                                            {task.tags.slice(0, 2).map((tag, index) => (
                                                                <Badge
                                                                    key={index}
                                                                    variant="secondary"
                                                                    className="text-xs px-2 py-0.5 bg-muted text-muted-foreground"
                                                                >
                                                                    {tag}
                                                                </Badge>
                                                            ))}
                                                            {task.tags.length > 2 && (
                                                                <Badge variant="secondary" className="text-xs px-2 py-0.5 bg-muted text-muted-foreground">
                                                                    +{task.tags.length - 2}
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    )}

                                                    {/* Progress Bar */}
                                                    {task.subtasks.length > 0 && (
                                                        <div className="mb-3">
                                                            <div className="flex justify-between text-xs text-muted-foreground mb-1">
                                                                <span>Progress</span>
                                                                <span>{task.subtasks.filter(st => st.completed).length}/{task.subtasks.length}</span>
                                                            </div>
                                                            <div className="w-full bg-muted rounded-full h-1.5">
                                                                <div
                                                                    className="bg-primary h-1.5 rounded-full transition-all duration-300"
                                                                    style={{
                                                                        width: `${(task.subtasks.filter(st => st.completed).length / task.subtasks.length) * 100}%`
                                                                    }}
                                                                ></div>
                                                            </div>
                                                        </div>
                                                    )}

                                                    {/* Footer */}
                                                    <div className="flex items-center justify-between pt-3 border-t border-border/30">
                                                        <div className="flex items-center space-x-2">
                                                            <div className={`w-2 h-2 rounded-full ${task.assignee === 'client' ? 'bg-blue-500' : 'bg-green-500'}`}></div>
                                                            <span className="text-xs text-muted-foreground">
                                                                {task.assignee === 'client' ? 'Client' : 'You'}
                                                            </span>
                                                        </div>
                                                        <div className="flex items-center space-x-3">
                                                            {task.comments > 0 && (
                                                                <div className="flex items-center space-x-1">
                                                                    <MessageSquare className="w-3 h-3 text-muted-foreground" />
                                                                    <span className="text-xs text-muted-foreground">{task.comments}</span>
                                                                </div>
                                                            )}
                                                            <span className="text-xs text-muted-foreground">
                                                                {task.dueDate ? new Date(task.dueDate).toLocaleDateString() : 'No due date'}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* ⏱️ FLOW SUMMARY BAR (Sticky Under Kanban) */}
                {!focusMode && (
                    <div className="flex-shrink-0 border-t border-border/50 bg-background/95 backdrop-blur">
                        <div className="flex items-center justify-between px-6 py-3">
                            <div className="flex items-center space-x-6">
                                <div className="flex items-center space-x-2">
                                    <Timer className="w-4 h-4 text-muted-foreground" />
                                    <span className="text-sm font-medium">Flow Summary:</span>
                                    <Badge variant="outline" className="text-xs">
                                        {completedTasks} Tasks Done
                                    </Badge>
                                    <Badge variant="outline" className="text-xs">
                                        {reviewTasks} Review
                                    </Badge>
                                </div>
                                <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                                    <Lightbulb className="w-4 h-4" />
                                    <span>AI Suggests: "Quick win task"</span>
                                    <ChevronRight className="w-3 h-3" />
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* 📊 DAILY PERFORMANCE FOOTER */}
                {!focusMode && (
                    <div className="flex-shrink-0 border-t border-border/50 bg-background/95">
                        <div className="flex items-center justify-between px-6 py-3">
                            <div className="flex items-center space-x-6 text-sm">
                                <div className="flex items-center space-x-2">
                                    <CheckCircle className="w-4 h-4 text-green-500" />
                                    <span>{completedTasks} Tasks Completed</span>
                                </div>
                                <div className="flex items-center space-x-2">
                                    <AlertCircle className="w-4 h-4 text-orange-500" />
                                    <span>1 Blocked</span>
                                </div>
                                <div className="flex items-center space-x-2">
                                    <Clock className="w-4 h-4 text-blue-500" />
                                    <span>2h 15m Focused</span>
                                </div>
                                <div className="flex items-center space-x-2">
                                    <Zap className="w-4 h-4 text-purple-500" />
                                    <span>Sync Project</span>
                                </div>
                            </div>

                            <div className="flex items-center space-x-4">
                                <Button variant="ghost" size="sm" className="flex items-center space-x-2">
                                    <TrendingUp className="w-4 h-4" />
                                    <span>Daily Review</span>
                                    <ChevronRight className="w-3 h-3" />
                                </Button>
                                <Button variant="ghost" size="sm" className="flex items-center space-x-2">
                                    <Settings className="w-4 h-4" />
                                    <span>Automation Settings</span>
                                </Button>
                                <Button variant="ghost" size="sm">
                                    <Sun className="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* 🧠 AI ASSISTANT PANEL (SLIDE-OVER) */}
            {aiAssistantOpen && (
                <div className="fixed inset-0 z-50 flex justify-end">
                    <div className="fixed inset-0 bg-black/20" onClick={() => setAiAssistantOpen(false)}></div>
                    <div className="relative w-96 h-full bg-background border-l border-border shadow-xl">
                        <div className="flex items-center justify-between p-4 border-b border-border">
                            <h3 className="font-semibold">AI Assistant</h3>
                            <Button variant="ghost" size="sm" onClick={() => setAiAssistantOpen(false)}>
                                <ChevronRight className="w-4 h-4" />
                            </Button>
                        </div>
                        <div className="p-4 space-y-4">
                            <div className="text-sm text-muted-foreground">
                                🤖 What can I help with?
                            </div>
                            <div className="h-px bg-border"></div>
                            <div className="space-y-3">
                                <Button variant="ghost" className="w-full justify-start text-left h-auto p-3">
                                    <div className="flex items-start space-x-3">
                                        <div className="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                        <div>
                                            <div className="font-medium">Break down this task into subtasks</div>
                                            <div className="text-xs text-muted-foreground">AI will analyze and suggest subtasks</div>
                                        </div>
                                    </div>
                                </Button>
                                <Button variant="ghost" className="w-full justify-start text-left h-auto p-3">
                                    <div className="flex items-start space-x-3">
                                        <div className="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                        <div>
                                            <div className="font-medium">Suggest next priority based on flow</div>
                                            <div className="text-xs text-muted-foreground">AI will analyze your current workload</div>
                                        </div>
                                    </div>
                                </Button>
                                <Button variant="ghost" className="w-full justify-start text-left h-auto p-3">
                                    <div className="flex items-start space-x-3">
                                        <div className="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                                        <div>
                                            <div className="font-medium">Generate a status update to share with client</div>
                                            <div className="text-xs text-muted-foreground">AI will create a professional update</div>
                                        </div>
                                    </div>
                                </Button>
                                <Button variant="ghost" className="w-full justify-start text-left h-auto p-3">
                                    <div className="flex items-start space-x-3">
                                        <div className="w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                                        <div>
                                            <div className="font-medium">Optimize task wording for clarity</div>
                                            <div className="text-xs text-muted-foreground">AI will improve task descriptions</div>
                                        </div>
                                    </div>
                                </Button>
                                <Button variant="ghost" className="w-full justify-start text-left h-auto p-3">
                                    <div className="flex items-start space-x-3">
                                        <div className="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                                        <div>
                                            <div className="font-medium">Turn this into a reusable prompt</div>
                                            <div className="text-xs text-muted-foreground">AI will create a template</div>
                                        </div>
                                    </div>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Custom Floating Action Button */}
            <div className="fixed bottom-6 right-6 z-50 group">
                <div className="w-12 h-12 bg-primary rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 active:scale-95 relative cursor-pointer">
                    <div className="absolute top-1/2 left-1/2 w-6 h-0.5 bg-black transform -translate-x-1/2 -translate-y-1/2 transition-all duration-300"></div>
                    <div className="absolute top-1/2 left-1/2 w-0.5 h-6 bg-black transform -translate-x-1/2 -translate-y-1/2 transition-all duration-300 group-hover:rotate-90"></div>
                </div>

                <ul className="absolute bottom-16 right-0 space-y-2 opacity-0 group-hover:opacity-100 transition-all duration-500 pointer-events-none group-hover:pointer-events-auto">
                    <li className="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-100">
                        <Link href="/tasks/create" className="block w-10 h-10 bg-[#d1ff75] hover:bg-[#c2f066] rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center text-gray-800 hover:scale-110">
                            <Plus className="w-4 h-4" />
                        </Link>
                    </li>
                    <li className="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-200">
                        <Link href="/clients/create" className="block w-10 h-10 bg-[#d1ff75] hover:bg-[#c2f066] rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center text-gray-800 hover:scale-110">
                            <User className="w-4 h-4" />
                        </Link>
                    </li>
                    <li className="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-300">
                        <Link href="/projects/create" className="block w-10 h-10 bg-[#d1ff75] hover:bg-[#c2f066] rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center text-gray-800 hover:scale-110">
                            <FileText className="w-4 h-4" />
                        </Link>
                    </li>
                </ul>
            </div>
        </AuthenticatedLayout>
    );
}
