import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Plus,
    Filter,
    Search,
    Calendar,
    Users,
    Tag,
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
    Circle
} from 'lucide-react';

export default function BobbiFlow({ auth }) {
    const [clientMode, setClientMode] = useState(false);
    const [selectedFilter, setSelectedFilter] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');

    // Mock data for tasks
    const tasks = [
        {
            id: 1,
            title: 'Design homepage mockups',
            client: 'Acme Corp',
            project: 'Website Redesign',
            priority: 'high',
            status: 'in-progress',
            dueDate: '2024-02-15',
            estimatedTime: '4h',
            tags: ['Design', 'UI/UX'],
            subtasks: [
                { id: 1, text: 'Create wireframes', completed: true },
                { id: 2, text: 'Design desktop version', completed: true },
                { id: 3, text: 'Design mobile version', completed: false }
            ],
            comments: 3,
            assignee: 'client'
        },
        {
            id: 2,
            title: 'Review SEO content',
            client: 'TechStart',
            project: 'Content Strategy',
            priority: 'medium',
            status: 'waiting',
            dueDate: '2024-02-20',
            estimatedTime: '2h',
            tags: ['SEO', 'Content'],
            subtasks: [
                { id: 1, text: 'Review blog posts', completed: false },
                { id: 2, text: 'Update meta descriptions', completed: false }
            ],
            comments: 1,
            assignee: 'me'
        },
        {
            id: 3,
            title: 'Setup analytics tracking',
            client: 'RetailPlus',
            project: 'E-commerce Platform',
            priority: 'high',
            status: 'todo',
            dueDate: '2024-02-18',
            estimatedTime: '3h',
            tags: ['Analytics', 'Setup'],
            subtasks: [
                { id: 1, text: 'Install Google Analytics', completed: false },
                { id: 2, text: 'Configure conversion tracking', completed: false }
            ],
            comments: 0,
            assignee: 'me'
        },
        {
            id: 4,
            title: 'Client feedback review',
            client: 'Acme Corp',
            project: 'Website Redesign',
            priority: 'low',
            status: 'review',
            dueDate: '2024-02-25',
            estimatedTime: '1h',
            tags: ['Review', 'Feedback'],
            subtasks: [
                { id: 1, text: 'Review client comments', completed: false },
                { id: 2, text: 'Update design files', completed: false }
            ],
            comments: 5,
            assignee: 'me'
        }
    ];

    const lanes = [
        { id: 'inbox', name: 'Inbox', icon: Inbox, clientName: 'New' },
        { id: 'todo', name: 'To Do', icon: ListTodo, clientName: 'Planned' },
        { id: 'in-progress', name: 'In Progress', icon: Play, clientName: 'Doing' },
        { id: 'waiting', name: 'Waiting on Client', icon: Pause, clientName: 'Waiting for You' },
        { id: 'review', name: 'Ready for Review', icon: EyeIcon, clientName: 'Review' },
        { id: 'done', name: 'Done', icon: CheckCircle2, clientName: 'Done' }
    ];

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

    const filteredTasks = tasks.filter(task => {
        if (selectedFilter !== 'all' && task.status !== selectedFilter) return false;
        if (searchQuery && !task.title.toLowerCase().includes(searchQuery.toLowerCase())) return false;
        return true;
    });

    const tasksByLane = lanes.map(lane => ({
        ...lane,
        tasks: filteredTasks.filter(task => task.status === lane.id)
    }));

        return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Bobbi Flow" />

            <div className="h-screen flex flex-col">
                {/* Compact Header */}
                <div className="flex-shrink-0 p-6 border-b border-border/50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="flex justify-between items-center">
                        <div className="flex items-center space-x-4">
                            <div>
                                <h1 className="text-xl font-semibold text-foreground">Bobbi Flow</h1>
                                <p className="text-sm text-muted-foreground">Visual workflow management</p>
                            </div>
                            <div className="h-4 w-px bg-border"></div>
                            <div className="flex items-center space-x-2">
                                <Button
                                    variant={clientMode ? "default" : "outline"}
                                    size="sm"
                                    onClick={() => setClientMode(!clientMode)}
                                    className="h-8 px-3 text-xs"
                                >
                                    {clientMode ? <EyeOff className="w-3 h-3 mr-1" /> : <Eye className="w-3 h-3 mr-1" />}
                                    {clientMode ? 'Client' : 'Client'}
                                </Button>
                                <Link href="/tasks/create">
                                    <Button size="sm" className="h-8 px-3 text-xs">
                                        <Plus className="w-3 h-3 mr-1" />
                                        Add Task
                                    </Button>
                                </Link>
                            </div>
                        </div>

                        {/* Quick Stats */}
                        <div className="flex items-center space-x-6 text-sm">
                            <div className="text-center">
                                <div className="font-semibold text-foreground">{tasks.length}</div>
                                <div className="text-muted-foreground">Total</div>
                            </div>
                            <div className="text-center">
                                <div className="font-semibold text-blue-600">{tasks.filter(t => t.status === 'in-progress').length}</div>
                                <div className="text-muted-foreground">Active</div>
                            </div>
                            <div className="text-center">
                                <div className="font-semibold text-orange-600">{tasks.filter(t => t.status === 'waiting').length}</div>
                                <div className="text-muted-foreground">Waiting</div>
                            </div>
                        </div>
                    </div>
                </div>



                                {/* Kanban Board - Full Height */}
                <div className="flex-1 overflow-hidden">
                    <div className="h-full flex gap-8 p-6 overflow-x-auto">
                        {tasksByLane.map((lane) => (
                            <div key={lane.id} className="flex flex-col min-w-[320px] w-[320px]">
                                {/* Lane Header */}
                                <div className="flex-shrink-0 mb-6">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-3">
                                            <div className="w-8 h-8 rounded-lg bg-muted flex items-center justify-center">
                                                <lane.icon className="w-4 h-4 text-muted-foreground" />
                                            </div>
                                            <div>
                                                <h3 className="font-semibold text-base text-foreground">
                                                    {clientMode ? lane.clientName : lane.name}
                                                </h3>
                                                <p className="text-sm text-muted-foreground">
                                                    {lane.tasks.length} task{lane.tasks.length !== 1 ? 's' : ''}
                                                </p>
                                            </div>
                                        </div>
                                        {lane.id === 'in-progress' && (
                                            <Badge variant="outline" className="text-xs bg-blue-50 border-blue-200 text-blue-700">
                                                {lane.tasks.length}/5
                                            </Badge>
                                        )}
                                    </div>
                                </div>

                                {/* Tasks Container */}
                                <div className="flex-1 space-y-4 overflow-y-auto">
                                    {lane.tasks.map((task) => (
                                        <div key={task.id} className="group cursor-pointer">
                                            <Card className="border border-border/50 hover:border-primary/30 hover:shadow-lg transition-all duration-200 bg-background">
                                                <CardContent className="p-4">
                                                    {/* Task Title & Priority */}
                                                    <div className="flex items-start justify-between mb-3">
                                                        <h4 className="font-medium text-base leading-tight text-foreground pr-3 line-clamp-2">
                                                            {task.title}
                                                        </h4>
                                                        <span className="flex-shrink-0 ml-2">{getPriorityIcon(task.priority)}</span>
                                                    </div>

                                                    {/* Client & Time */}
                                                    <div className="flex items-center justify-between text-sm mb-3">
                                                        <span className="text-muted-foreground font-medium">{task.client}</span>
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
                                                                {new Date(task.dueDate).toLocaleDateString()}
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

                {/* Floating AI Insights */}
                {tasks.filter(t => t.status === 'in-progress').length > 5 && (
                    <div className="fixed bottom-6 right-6 z-50">
                        <Card className="w-80 shadow-lg border-amber-200 bg-amber-50/90 backdrop-blur">
                            <CardContent className="p-4">
                                <div className="flex items-start space-x-3">
                                    <AlertCircle className="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
                                    <div className="flex-1">
                                        <h4 className="font-medium text-amber-800 text-sm mb-1">Workload Alert</h4>
                                        <p className="text-xs text-amber-700 mb-2">
                                            You have {tasks.filter(t => t.status === 'in-progress').length} active tasks. Consider rescheduling some.
                                        </p>
                                        <Button variant="outline" size="sm" className="h-7 text-xs bg-white/50 border-amber-300 text-amber-700 hover:bg-amber-100">
                                            Review
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>

                        {/* Custom Floating Action Button with Menu */}
            <div className="fixed bottom-6 right-6 z-50 group">
                <div className="w-12 h-12 bg-primary rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 active:scale-95 relative cursor-pointer">
                    {/* Horizontal line */}
                    <div className="absolute top-1/2 left-1/2 w-6 h-0.5 bg-black transform -translate-x-1/2 -translate-y-1/2 transition-all duration-300"></div>
                    {/* Vertical line */}
                    <div className="absolute top-1/2 left-1/2 w-0.5 h-6 bg-black transform -translate-x-1/2 -translate-y-1/2 transition-all duration-300 group-hover:rotate-90"></div>
                </div>

                {/* Menu Items */}
                <ul className="absolute bottom-16 right-0 space-y-2 opacity-0 group-hover:opacity-100 transition-all duration-500 pointer-events-none group-hover:pointer-events-auto">
                    <li className="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-100">
                        <Link href="/tasks/create" className="block w-10 h-10 bg-[#d1ff75] hover:bg-[#c2f066] rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center text-gray-800 hover:scale-110">
                            <Plus className="w-4 h-4" />
                        </Link>
                    </li>
                    <li className="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-200">
                        <Link href="/clients/create" className="block w-10 h-10 bg-[#d1ff75] hover:bg-[#c2f066] rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center text-gray-800 hover:scale-110">
                            <Users className="w-4 h-4" />
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
