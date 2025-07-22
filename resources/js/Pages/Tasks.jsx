import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Plus,
    Search,
    Filter,
    Calendar,
    Target,
    CheckCircle,
    Clock,
    AlertCircle,
    Edit,
    Trash2,
    Eye,
    MoreHorizontal
} from 'lucide-react';

export default function Tasks({ auth }) {
    const [tasks, setTasks] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [priorityFilter, setPriorityFilter] = useState('all');

    useEffect(() => {
        // Mock data for tasks - in real app this would fetch from API
        const mockTasks = [
            {
                id: 1,
                title: 'Set up project repository',
                description: 'Create the GitHub repo and initialize with README and .gitignore.',
                status: 'todo',
                priority: 'high',
                project: 'Website Redesign',
                assigned_to: 'John Doe',
                due_date: '2024-02-10',
                estimated_hours: 2.00,
                tags: ['setup', 'repository']
            },
            {
                id: 2,
                title: 'Design system setup',
                description: 'Create the design system with Tailwind and component library.',
                status: 'in_progress',
                priority: 'medium',
                project: 'Website Redesign',
                assigned_to: 'Jane Smith',
                due_date: '2024-02-15',
                estimated_hours: 8.00,
                tags: ['design', 'frontend']
            },
            {
                id: 3,
                title: 'Build landing page',
                description: 'Create the main landing page with modern UI components.',
                status: 'todo',
                priority: 'high',
                project: 'Brand Identity',
                assigned_to: 'Mike Johnson',
                due_date: '2024-02-20',
                estimated_hours: 12.00,
                tags: ['frontend', 'landing']
            },
            {
                id: 4,
                title: 'API development',
                description: 'Develop the backend API endpoints for the application.',
                status: 'todo',
                priority: 'high',
                project: 'Mobile App',
                assigned_to: 'Sarah Wilson',
                due_date: '2024-02-25',
                estimated_hours: 20.00,
                tags: ['backend', 'api']
            },
            {
                id: 5,
                title: 'Database design',
                description: 'Design and implement the database schema.',
                status: 'done',
                priority: 'high',
                project: 'E-commerce Platform',
                assigned_to: 'Alex Brown',
                due_date: '2024-02-05',
                estimated_hours: 6.00,
                tags: ['database', 'backend']
            }
        ];

        setTasks(mockTasks);
        setLoading(false);
    }, []);

    const filteredTasks = tasks.filter(task => {
        const matchesSearch = task.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            task.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            task.project.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || task.status === statusFilter;
        const matchesPriority = priorityFilter === 'all' || task.priority === priorityFilter;
        return matchesSearch && matchesStatus && matchesPriority;
    });

    const getStatusColor = (status) => {
        switch (status) {
            case 'done':
                return 'bg-green-100 text-green-800';
            case 'in_progress':
                return 'bg-blue-100 text-blue-800';
            case 'todo':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'done':
                return 'Done';
            case 'in_progress':
                return 'In Progress';
            case 'todo':
                return 'To Do';
            default:
                return 'Unknown';
        }
    };

    const getPriorityColor = (priority) => {
        switch (priority) {
            case 'high':
                return 'bg-red-100 text-red-800';
            case 'medium':
                return 'bg-yellow-100 text-yellow-800';
            case 'low':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusIcon = (status) => {
        switch (status) {
            case 'done':
                return <CheckCircle className="w-4 h-4 text-green-600" />;
            case 'in_progress':
                return <Clock className="w-4 h-4 text-blue-600" />;
            case 'todo':
                return <AlertCircle className="w-4 h-4 text-orange-600" />;
            default:
                return <AlertCircle className="w-4 h-4 text-gray-600" />;
        }
    };

    if (loading) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Tasks" />
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                        <p className="mt-2 text-muted-foreground">Loading tasks...</p>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Tasks" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Tasks</h1>
                        <p className="text-muted-foreground">Manage and track your project tasks</p>
                    </div>
                    <Link href="/tasks/create">
                        <Button>
                            <Plus className="w-4 h-4 mr-2" />
                            New Task
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="p-6">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                                    <Input
                                        placeholder="Search tasks..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-40">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="todo">To Do</SelectItem>
                                        <SelectItem value="in_progress">In Progress</SelectItem>
                                        <SelectItem value="done">Done</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={priorityFilter} onValueChange={setPriorityFilter}>
                                    <SelectTrigger className="w-40">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Priority</SelectItem>
                                        <SelectItem value="high">High</SelectItem>
                                        <SelectItem value="medium">Medium</SelectItem>
                                        <SelectItem value="low">Low</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button variant="outline">
                                    <Filter className="w-4 h-4 mr-2" />
                                    More Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Tasks List */}
                <div className="space-y-4">
                    {filteredTasks.map((task) => (
                        <Card key={task.id}>
                            <CardContent className="p-6">
                                <div className="flex items-start justify-between">
                                    <div className="flex items-start space-x-4 flex-1">
                                        <div className="mt-1">
                                            {getStatusIcon(task.status)}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center space-x-3 mb-2">
                                                <h3 className="text-lg font-semibold text-foreground">{task.title}</h3>
                                                <Badge className={getStatusColor(task.status)}>
                                                    {getStatusText(task.status)}
                                                </Badge>
                                                <Badge className={getPriorityColor(task.priority)}>
                                                    {task.priority}
                                                </Badge>
                                            </div>
                                            <p className="text-sm text-muted-foreground mb-3 line-clamp-2">
                                                {task.description}
                                            </p>
                                            <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                                                <span className="flex items-center">
                                                    <Target className="w-4 h-4 mr-1" />
                                                    {task.project}
                                                </span>
                                                <span className="flex items-center">
                                                    <Calendar className="w-4 h-4 mr-1" />
                                                    Due {new Date(task.due_date).toLocaleDateString()}
                                                </span>
                                                <span className="flex items-center">
                                                    <Clock className="w-4 h-4 mr-1" />
                                                    {task.estimated_hours}h
                                                </span>
                                                <span className="text-muted-foreground">
                                                    Assigned to {task.assigned_to}
                                                </span>
                                            </div>
                                            {task.tags && task.tags.length > 0 && (
                                                <div className="flex items-center space-x-2 mt-3">
                                                    {task.tags.map((tag, index) => (
                                                        <Badge key={index} variant="outline" className="text-xs">
                                                            {tag}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Link href={`/tasks/${task.id}`}>
                                            <Button variant="outline" size="sm">
                                                <Eye className="w-4 h-4 mr-1" />
                                                View
                                            </Button>
                                        </Link>
                                        <Link href={`/tasks/${task.id}/edit`}>
                                            <Button variant="outline" size="sm">
                                                <Edit className="w-4 h-4 mr-1" />
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button variant="outline" size="sm">
                                            <MoreHorizontal className="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}

                    {filteredTasks.length === 0 && (
                        <Card>
                            <CardContent className="p-12 text-center">
                                <div className="mx-auto w-12 h-12 bg-muted rounded-full flex items-center justify-center mb-4">
                                    <Target className="w-6 h-6 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-medium text-foreground mb-2">No tasks found</h3>
                                <p className="text-muted-foreground mb-4">
                                    {searchTerm || statusFilter !== 'all' || priorityFilter !== 'all'
                                        ? 'Try adjusting your search or filters'
                                        : 'Get started by creating your first task'
                                    }
                                </p>
                                {!searchTerm && statusFilter === 'all' && priorityFilter === 'all' && (
                                    <Link href="/tasks/create">
                                        <Button>
                                            <Plus className="w-4 h-4 mr-2" />
                                            Create Task
                                        </Button>
                                    </Link>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
