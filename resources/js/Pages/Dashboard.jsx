import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Plus,
    Users,
    FileText,
    CheckCircle,
    Clock,
    AlertCircle,
    Calendar,
    TrendingUp,
    DollarSign,
    Briefcase,
    Target,
    Zap,
    Eye,
    ArrowRight
} from 'lucide-react';

export default function Dashboard({ auth, stats }) {
    const defaultStats = {
        totalClients: 0,
        totalProjects: 0,
        totalTasks: 0,
        pendingTasks: 0,
        revenue: 0,
        recentProposals: [],
        recentTasks: []
    };

    const statsData = stats || defaultStats;

    const quickStats = [
        {
            title: 'Active Projects',
            value: statsData.totalProjects || '0',
            icon: Briefcase,
            description: 'Projects in progress',
            trend: '+3 this week'
        },
        {
            title: 'Total Clients',
            value: statsData.totalClients || '0',
            icon: Users,
            description: 'Active clients',
            trend: '+2 this month'
        },
        {
            title: 'Pending Tasks',
            value: statsData.pendingTasks || '0',
            icon: Clock,
            description: 'Tasks to complete',
            trend: '-5 from yesterday'
        },
        {
            title: 'Revenue',
            value: `$${(statsData.revenue || 0).toLocaleString()}`,
            icon: DollarSign,
            description: 'This month',
            trend: '+12% from last month'
        }
    ];

    const recentProjects = [
        {
            id: 1,
            name: 'Website Redesign',
            client: 'Acme Corp',
            status: 'in-progress',
            progress: 65,
            dueDate: '2024-02-15',
            tasks: { completed: 8, total: 12 }
        },
        {
            id: 2,
            name: 'Brand Identity',
            client: 'TechStart',
            status: 'completed',
            progress: 100,
            dueDate: '2024-01-30',
            tasks: { completed: 15, total: 15 }
        },
        {
            id: 3,
            name: 'Mobile App',
            client: 'InnovateLab',
            status: 'planned',
            progress: 0,
            dueDate: '2024-03-01',
            tasks: { completed: 0, total: 20 }
        }
    ];

    const recentTasks = [
        {
            id: 1,
            title: 'Design homepage mockups',
            project: 'Website Redesign',
            status: 'in-progress',
            priority: 'high',
            dueDate: '2024-02-10'
        },
        {
            id: 2,
            title: 'Review client feedback',
            project: 'Brand Identity',
            status: 'completed',
            priority: 'medium',
            dueDate: '2024-01-28'
        },
        {
            id: 3,
            title: 'Setup development environment',
            project: 'Mobile App',
            status: 'todo',
            priority: 'high',
            dueDate: '2024-02-20'
        }
    ];

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
                return 'bg-green-500';
            case 'in-progress':
                return 'bg-blue-500';
            case 'planned':
                return 'bg-gray-500';
            default:
                return 'bg-gray-500';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'completed':
                return 'Completed';
            case 'in-progress':
                return 'In Progress';
            case 'planned':
                return 'Planned';
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

    const getTaskStatusIcon = (status) => {
        switch (status) {
            case 'completed':
                return <CheckCircle className="w-4 h-4 text-green-600" />;
            case 'in-progress':
                return <Clock className="w-4 h-4 text-blue-600" />;
            case 'todo':
                return <AlertCircle className="w-4 h-4 text-orange-600" />;
            default:
                return <AlertCircle className="w-4 h-4 text-gray-600" />;
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Dashboard</h1>
                        <p className="text-muted-foreground">Welcome back! Here's what's happening with your projects.</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href="/calendar">
                            <Button variant="outline">
                                <Calendar className="w-4 h-4 mr-2" />
                                View Calendar
                            </Button>
                        </Link>
                        <Link href="/projects/create">
                            <Button>
                                <Plus className="w-4 h-4 mr-2" />
                                New Project
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Quick Stats */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {quickStats.map((stat) => (
                        <Card key={stat.title} className="bg-card border-border">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    {stat.title}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-foreground">{stat.value}</div>
                                <p className="text-xs text-muted-foreground">{stat.description}</p>
                                <p className="text-xs text-muted-foreground mt-1">{stat.trend}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Main Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Recent Projects */}
                    <div className="lg:col-span-2">
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <div className="flex justify-between items-center">
                                    <div>
                                        <CardTitle className="text-foreground">Recent Projects</CardTitle>
                                        <CardDescription className="text-muted-foreground">Your active and recent projects</CardDescription>
                                    </div>
                                    <Link href="/projects">
                                        <Button variant="outline" size="sm">
                                            View All
                                            <ArrowRight className="w-4 h-4 ml-2" />
                                        </Button>
                                    </Link>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {recentProjects.map((project) => (
                                        <Link key={project.id} href={`/projects/${project.id}`}>
                                            <div className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                                <div className="flex-1">
                                                    <div className="flex items-center space-x-3">
                                                        <h3 className="font-medium text-foreground">{project.name}</h3>
                                                        <div className="flex items-center">
                                                            <div className={`w-2 h-2 rounded-full ${getStatusColor(project.status)} mr-2`}></div>
                                                            <span className="text-sm text-muted-foreground">{getStatusText(project.status)}</span>
                                                        </div>
                                                    </div>
                                                    <p className="text-sm text-muted-foreground mt-1">{project.client}</p>
                                                    <div className="flex items-center space-x-4 mt-2">
                                                        <div className="flex items-center space-x-2">
                                                            <Target className="w-4 h-4 text-muted-foreground" />
                                                            <span className="text-sm text-muted-foreground">
                                                                {project.tasks.completed}/{project.tasks.total} tasks
                                                            </span>
                                                        </div>
                                                        <div className="flex items-center space-x-2">
                                                            <Calendar className="w-4 h-4 text-muted-foreground" />
                                                            <span className="text-sm text-muted-foreground">
                                                                Due {new Date(project.dueDate).toLocaleDateString()}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="flex items-center space-x-2">
                                                    <div className="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                        <div
                                                            className="h-full bg-primary transition-all duration-300"
                                                            style={{ width: `${project.progress}%` }}
                                                        ></div>
                                                    </div>
                                                    <span className="text-sm font-medium text-muted-foreground">
                                                        {project.progress}%
                                                    </span>
                                                    <Button variant="ghost" size="sm">
                                                        <Eye className="w-4 h-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quick Actions & Recent Tasks */}
                    <div className="space-y-6">
                        {/* Quick Actions */}
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Quick Actions</CardTitle>
                                <CardDescription className="text-muted-foreground">Get things done faster</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Link href="/clients/create">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Users className="w-4 h-4 mr-2" />
                                        Add New Client
                                    </Button>
                                </Link>
                                <Link href="/projects/create">
                                    <Button className="w-full justify-start" variant="outline">
                                        <FileText className="w-4 h-4 mr-2" />
                                        Create Project
                                    </Button>
                                </Link>
                                <Link href="/tasks/create">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Target className="w-4 h-4 mr-2" />
                                        Add Task
                                    </Button>
                                </Link>
                                <Link href="/proposals/create">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Zap className="w-4 h-4 mr-2" />
                                        Generate Proposal
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>

                        {/* Recent Tasks */}
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Recent Tasks</CardTitle>
                                <CardDescription className="text-muted-foreground">Latest task updates</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {recentTasks.map((task) => (
                                        <Link key={task.id} href={`/tasks/${task.id}`}>
                                            <div className="flex items-start space-x-3 p-3 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                                {getTaskStatusIcon(task.status)}
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-medium text-foreground truncate">
                                                        {task.title}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">{task.project}</p>
                                                    <div className="flex items-center space-x-2 mt-1">
                                                        <Badge className={getPriorityColor(task.priority)} size="sm">
                                                            {task.priority}
                                                        </Badge>
                                                        <span className="text-xs text-muted-foreground">
                                                            Due {new Date(task.dueDate).toLocaleDateString()}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Recent Proposals */}
                {statsData.recentProposals && statsData.recentProposals.length > 0 && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex justify-between items-center">
                                <div>
                                    <CardTitle className="text-foreground">Recent Proposals</CardTitle>
                                    <CardDescription className="text-muted-foreground">Latest client proposals</CardDescription>
                                </div>
                                <Link href="/proposals">
                                    <Button variant="outline" size="sm">
                                        View All
                                        <ArrowRight className="w-4 h-4 ml-2" />
                                    </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {statsData.recentProposals.map((proposal) => (
                                    <Link key={proposal.id} href={`/proposals/${proposal.id}`}>
                                        <div className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                            <div>
                                                <h3 className="font-medium text-foreground">{proposal.title}</h3>
                                                <p className="text-sm text-muted-foreground">{proposal.client_name}</p>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Badge variant={proposal.status === 'draft' ? 'outline' : 'default'}>
                                                    {proposal.status}
                                                </Badge>
                                                <span className="text-sm text-muted-foreground">{proposal.created_at}</span>
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
