import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
    Moon
} from 'lucide-react';

export default function BobbiFlow({ auth, tasks = [] }) {
    const [clientMode, setClientMode] = useState(false);
    const [selectedFilter, setSelectedFilter] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [aiAssistantOpen, setAiAssistantOpen] = useState(false);
    const [focusMode, setFocusMode] = useState(false);
    const [timerActive, setTimerActive] = useState(false);

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
                                    variant={focusMode ? "default" : "outline"}
                                    size="sm"
                                    onClick={() => setFocusMode(!focusMode)}
                                    className="flex items-center space-x-2"
                                >
                                    <Focus className="w-4 h-4" />
                                    <span>Focus Mode</span>
                                </Button>
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
                                        <Link key={task.id} href={`/tasks/${task.id}`} className="group cursor-pointer block">
                                            <Card className={`border hover:border-primary/30 hover:shadow-lg transition-all duration-200 ${lane.color}`}>
                                                <CardContent className="p-4">
                                                    {/* Task Title & Priority */}
                                                    <div className="flex items-start justify-between mb-3">
                                                        <h4 className="font-medium text-base leading-tight text-foreground pr-3 line-clamp-2">
                                                            {task.title}
                                                        </h4>
                                                        <span className="flex-shrink-0 ml-2">{getPriorityIcon(task.priority)}</span>
                                                    </div>

                                                    {/* Project & Time */}
                                                    <div className="flex items-center justify-between text-sm mb-3">
                                                        <div className="flex items-center space-x-2 text-muted-foreground">
                                                            <Building className="w-3 h-3" />
                                                            <span>{task.project}</span>
                                                        </div>
                                                        <span className="text-muted-foreground">{task.estimatedTime}</span>
                                                    </div>

                                                    {/* Tags */}
                                                    {task.tags.length > 0 && (
                                                        <div className="flex flex-wrap gap-2 mb-3">
                                                            {task.tags.slice(0, 2).map((tag, index) => (
                                                                <Badge
                                                                    key={index}
                                                                    variant="outline"
                                                                    className="text-xs px-2 py-1 bg-muted/30 border-muted-foreground/20"
                                                                >
                                                                    {tag}
                                                                </Badge>
                                                            ))}
                                                            {task.tags.length > 2 && (
                                                                <Badge variant="outline" className="text-xs px-2 py-1 bg-muted/30 border-muted-foreground/20">
                                                                    +{task.tags.length - 2}
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    )}

                                                    {/* Progress Bar */}
                                                    {task.subtasks.length > 0 && (
                                                        <div className="mb-3">
                                                            <div className="flex justify-between text-sm text-muted-foreground mb-2">
                                                                <span>Progress</span>
                                                                <span>{task.subtasks.filter(st => st.completed).length}/{task.subtasks.length}</span>
                                                            </div>
                                                            <div className="w-full bg-muted rounded-full h-2">
                                                                <div
                                                                    className="bg-primary h-2 rounded-full transition-all duration-300"
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
                                                            <div className={`w-3 h-3 rounded-full ${task.assignee === 'client' ? 'bg-blue-500' : 'bg-green-500'}`}></div>
                                                            <span className="text-sm text-muted-foreground">
                                                                {task.assignee === 'client' ? 'Client' : 'You'}
                                                            </span>
                                                        </div>
                                                        <div className="flex items-center space-x-3">
                                                            {task.comments > 0 && (
                                                                <div className="flex items-center space-x-1">
                                                                    <MessageSquare className="w-4 h-4 text-muted-foreground" />
                                                                    <span className="text-sm text-muted-foreground">{task.comments}</span>
                                                                </div>
                                                            )}
                                                            <span className="text-sm text-muted-foreground">
                                                                {task.dueDate ? new Date(task.dueDate).toLocaleDateString() : 'No due date'}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        </Link>
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
