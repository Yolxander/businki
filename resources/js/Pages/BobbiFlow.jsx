import React, { useState, useEffect, useRef } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Plus, Filter, Calendar, ChevronRight, Target, Timer, Focus, Bot, Play, Pause, ListTodo, CheckCircle2, AlertCircle, CheckCircle, Eye as EyeIcon, Star, MoreHorizontal, Sparkles, Zap
} from 'lucide-react';

export default function BobbiFlow({ auth, tasks = [] }) {
    const [selectedFilter, setSelectedFilter] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [aiAssistantOpen, setAiAssistantOpen] = useState(false);
    const [focusMode, setFocusMode] = useState(false);
    const [timerActive, setTimerActive] = useState(false);

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

    const lanes = [
        { id: 'todo', name: 'To Do', icon: ListTodo, color: 'bg-blue-50 border-blue-200', dbStatus: 'todo' },
        { id: 'in-progress', name: 'In Progress', icon: Play, color: 'bg-yellow-50 border-yellow-200', dbStatus: 'in_progress' },
        { id: 'review', name: 'Review', icon: EyeIcon, color: 'bg-purple-50 border-purple-200', dbStatus: 'in_progress' },
        { id: 'done', name: 'Done', icon: CheckCircle2, color: 'bg-green-50 border-green-200', dbStatus: 'done' }
    ];

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
            if (assignedTaskIds.has(task.id)) return false;
            // Check if task status matches lane's database status
            const taskDbStatus = task.status === 'in-progress' ? 'in_progress' : task.status;
            const matches = taskDbStatus === lane.dbStatus;
            if (matches) assignedTaskIds.add(task.id);
            return matches;
        });
        return { ...lane, tasks: laneTasks };
    });
    // Add any unassigned tasks to the inbox lane
    const unassignedTasks = filteredTasks.filter(task => !assignedTaskIds.has(task.id));
    if (unassignedTasks.length > 0) {
        const inboxLaneIndex = tasksByLane.findIndex(lane => lane.id === 'inbox');
        if (inboxLaneIndex !== -1) {
            tasksByLane[inboxLaneIndex].tasks = [...tasksByLane[inboxLaneIndex].tasks, ...unassignedTasks];
        }
    }

    const completedTasks = transformedTasks.filter(t => t.status === 'done').length;
    const reviewTasks = transformedTasks.filter(t => t.status === 'review').length;
    const activeTasks = transformedTasks.filter(t => t.status === 'in-progress').length;

    return (
        <AuthenticatedLayout user={auth.user} focusMode={focusMode}>
            <Head title="Bobbi Flow" />
            <div className="h-screen flex flex-col bg-background">
                {/* UNIFIED TOP NAVIGATION BAR */}
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
                                <Link href="/tasks/create">
                                    <Button size="sm" className="flex items-center space-x-2 bg-lime-600 hover:bg-lime-700">
                                        <Plus className="w-4 h-4" />
                                        <span>Add Task</span>
                                    </Button>
                                </Link>
                                <Button
                                    variant={timerActive ? "default" : "outline"}
                                    size="sm"
                                    onClick={() => setTimerActive(!timerActive)}
                                    className="flex items-center space-x-2"
                                >
                                    <Timer className="w-4 h-4" />
                                    <span>Timer</span>
                                </Button>
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
                                <EyeIcon className="w-4 h-4" />
                                <span>Exit Focus</span>
                            </Button>
                        </div>
                    </div>
                )}
                {/* KANBAN BOARD – SCROLLABLE WORKFLOW */}
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
                                        <div key={task.id} className="group">
                                            <Card
                                                className="border border-border/50 hover:border-primary/30 hover:shadow-lg transition-all duration-200 bg-sidebar relative overflow-hidden cursor-pointer"
                                                onClick={() => router.visit(`/tasks/${task.id}`)}
                                            >
                                                {/* Zen Mode Button - appears on hover */}
                                                <div className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="h-8 w-8 p-0 bg-background/80 backdrop-blur hover:bg-primary/10"
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            router.visit(`/zen-mode?task=${task.id}`);
                                                        }}
                                                        title="Enter Zen Mode"
                                                    >
                                                        <Zap className="w-4 h-4 text-primary" />
                                                    </Button>
                                                </div>
                                                <CardContent className="p-4">
                                                    {/* Project & Priority */}
                                                    <div className="flex items-center justify-between mb-3">
                                                        <div className="flex items-center space-x-2">
                                                            <span className="text-xs text-muted-foreground">{task.project}</span>
                                                            {task.priority === 'high' && (
                                                                <AlertCircle className="w-3 h-3 text-red-500" />
                                                            )}
                                                        </div>
                                                        {task.estimatedTime && (
                                                            <span className="text-xs text-muted-foreground font-mono">
                                                                {task.estimatedTime}
                                                            </span>
                                                        )}
                                                    </div>
                                                    {/* Task Title */}
                                                    <h4 className="font-medium text-sm leading-tight text-foreground line-clamp-2 mb-3">
                                                        {task.title}
                                                    </h4>
                                                    {/* Progress & Footer */}
                                                    <div className="space-y-3">
                                                        {/* Progress Bar */}
                                                        {task.subtasks.length > 0 && (
                                                            <div>
                                                                <div className="flex justify-between text-xs text-muted-foreground mb-1">
                                                                    <span>Progress</span>
                                                                    <span>{task.subtasks.filter(st => st.completed).length}/{task.subtasks.length}</span>
                                                                </div>
                                                                <div className="w-full bg-muted rounded-full h-1">
                                                                    <div
                                                                        className="bg-primary h-1 rounded-full transition-all duration-300"
                                                                        style={{
                                                                            width: `${(task.subtasks.filter(st => st.completed).length / task.subtasks.length) * 100}%`
                                                                        }}
                                                                    ></div>
                                                                </div>
                                                            </div>
                                                        )}
                                                        {/* Footer */}
                                                        <div className="flex items-center justify-between pt-2 border-t border-border/20">
                                                            <div className="flex items-center space-x-2">
                                                                <div className={`w-1.5 h-1.5 rounded-full ${task.assignee === 'client' ? 'bg-blue-500' : 'bg-green-500'}`}></div>
                                                                <span className="text-xs text-muted-foreground">
                                                                    {task.assignee === 'client' ? 'Client' : 'You'}
                                                                </span>
                                                            </div>
                                                            {task.dueDate && (
                                                                <span className="text-xs text-muted-foreground">
                                                                    {new Date(task.dueDate).toLocaleDateString()}
                                                                </span>
                                                            )}
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

            </div>
        </AuthenticatedLayout>
    );
}
