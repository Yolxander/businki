import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    ArrowLeft,
    Edit,
    Plus,
    Users,
    Calendar,
    Target,
    CheckCircle,
    Clock,
    AlertCircle,
    FileText,
    DollarSign,
    MessageSquare,
    Download,
    Share,
    MoreVertical,
    Trash2
} from 'lucide-react';

export default function ProjectDetails({ auth, project }) {
    const [activeTab, setActiveTab] = useState('overview');

    // Sample project data (in real app, this would come from props)
    const projectData = project || {
        id: 1,
        name: 'Website Redesign',
        description: 'Complete redesign of the company website with modern UI/UX, responsive design, and improved performance.',
        client: 'Acme Corp',
        status: 'in-progress',
        priority: 'high',
        progress: 65,
        startDate: '2024-01-15',
        dueDate: '2024-02-15',
        budget: 15000,
        actualCost: 8500,
        tasks: [
            {
                id: 1,
                title: 'Design homepage mockups',
                status: 'completed',
                priority: 'high',
                assignee: 'Alex Johnson',
                dueDate: '2024-01-25',
                completed: true
            },
            {
                id: 2,
                title: 'Develop responsive layout',
                status: 'in-progress',
                priority: 'high',
                assignee: 'Maria Garcia',
                dueDate: '2024-02-05',
                completed: false
            },
            {
                id: 3,
                title: 'Implement user authentication',
                status: 'todo',
                priority: 'medium',
                assignee: 'David Chen',
                dueDate: '2024-02-10',
                completed: false
            },
            {
                id: 4,
                title: 'Content migration',
                status: 'todo',
                priority: 'medium',
                assignee: 'Sarah Wilson',
                dueDate: '2024-02-12',
                completed: false
            }
        ],
        milestones: [
            {
                id: 1,
                title: 'Design Phase Complete',
                date: '2024-01-25',
                status: 'completed'
            },
            {
                id: 2,
                title: 'Development Phase Complete',
                date: '2024-02-10',
                status: 'in-progress'
            },
            {
                id: 3,
                title: 'Testing & Launch',
                date: '2024-02-15',
                status: 'pending'
            }
        ],
        team: [
            {
                id: 1,
                name: 'Alex Johnson',
                role: 'UI/UX Designer',
                avatar: 'AJ',
                status: 'active'
            },
            {
                id: 2,
                name: 'Maria Garcia',
                role: 'Frontend Developer',
                avatar: 'MG',
                status: 'active'
            },
            {
                id: 3,
                name: 'David Chen',
                role: 'Backend Developer',
                avatar: 'DC',
                status: 'active'
            }
        ]
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in-progress':
                return 'bg-blue-100 text-blue-800';
            case 'planned':
                return 'bg-gray-100 text-gray-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
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

    const completedTasks = projectData.tasks.filter(task => task.completed).length;
    const totalTasks = projectData.tasks.length;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${projectData.name} - Project Details`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-start">
                    <div className="flex items-center space-x-4">
                        <Link href="/projects">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{projectData.name}</h1>
                            <p className="text-muted-foreground">Client: {projectData.client}</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline">
                            <Share className="w-4 h-4 mr-2" />
                            Share
                        </Button>
                        <Link href={`/projects/${projectData.id}/edit`}>
                            <Button variant="outline">
                                <Edit className="w-4 h-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Button>
                            <Plus className="w-4 h-4 mr-2" />
                            Add Task
                        </Button>
                    </div>
                </div>

                {/* Project Stats */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Progress</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{projectData.progress}%</div>
                            <div className="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div
                                    className="bg-primary h-2 rounded-full transition-all duration-300"
                                    style={{ width: `${projectData.progress}%` }}
                                ></div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Tasks</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{completedTasks}/{totalTasks}</div>
                            <p className="text-xs text-muted-foreground">Completed</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Budget</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">${projectData.actualCost.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">of ${projectData.budget.toLocaleString()}</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Team</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{projectData.team.length}</div>
                            <p className="text-xs text-muted-foreground">Members</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs */}
                <div className="flex space-x-1 bg-muted p-1 rounded-lg">
                    <Button
                        variant={activeTab === 'overview' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('overview')}
                        className="flex-1"
                    >
                        Overview
                    </Button>
                    <Button
                        variant={activeTab === 'tasks' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('tasks')}
                        className="flex-1"
                    >
                        Tasks
                    </Button>
                    <Button
                        variant={activeTab === 'team' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('team')}
                        className="flex-1"
                    >
                        Team
                    </Button>
                    <Button
                        variant={activeTab === 'timeline' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('timeline')}
                        className="flex-1"
                    >
                        Timeline
                    </Button>
                </div>

                {/* Tab Content */}
                {activeTab === 'overview' && (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Project Info */}
                        <div className="lg:col-span-2 space-y-6">
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Project Information</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <h3 className="font-medium text-foreground mb-2">Description</h3>
                                        <p className="text-muted-foreground">{projectData.description}</p>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Status</h3>
                                            <Badge className={getStatusColor(projectData.status)}>
                                                {projectData.status.replace('-', ' ')}
                                            </Badge>
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Priority</h3>
                                            <Badge className={getPriorityColor(projectData.priority)}>
                                                {projectData.priority}
                                            </Badge>
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Start Date</h3>
                                            <p className="text-muted-foreground">{new Date(projectData.startDate).toLocaleDateString()}</p>
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-foreground mb-2">Due Date</h3>
                                            <p className="text-muted-foreground">{new Date(projectData.dueDate).toLocaleDateString()}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Recent Tasks</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {projectData.tasks.slice(0, 3).map(task => (
                                            <div key={task.id} className="flex items-center justify-between p-3 border border-border rounded-lg">
                                                <div className="flex items-center space-x-3">
                                                    {getTaskStatusIcon(task.status)}
                                                    <div>
                                                        <h3 className="font-medium text-foreground">{task.title}</h3>
                                                        <p className="text-sm text-muted-foreground">{task.assignee}</p>
                                                    </div>
                                                </div>
                                                <div className="flex items-center space-x-2">
                                                    <Badge className={getPriorityColor(task.priority)} size="sm">
                                                        {task.priority}
                                                    </Badge>
                                                    <span className="text-sm text-muted-foreground">
                                                        {new Date(task.dueDate).toLocaleDateString()}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Quick Actions</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Plus className="w-4 h-4 mr-2" />
                                        Add Task
                                    </Button>
                                    <Button className="w-full justify-start" variant="outline">
                                        <MessageSquare className="w-4 h-4 mr-2" />
                                        Send Update
                                    </Button>
                                    <Button className="w-full justify-start" variant="outline">
                                        <Download className="w-4 h-4 mr-2" />
                                        Export Report
                                    </Button>
                                </CardContent>
                            </Card>

                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Milestones</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {projectData.milestones.map(milestone => (
                                            <div key={milestone.id} className="flex items-center space-x-3">
                                                <div className={`w-2 h-2 rounded-full ${
                                                    milestone.status === 'completed' ? 'bg-green-500' :
                                                    milestone.status === 'in-progress' ? 'bg-blue-500' : 'bg-gray-500'
                                                }`}></div>
                                                <div className="flex-1">
                                                    <h3 className="text-sm font-medium text-foreground">{milestone.title}</h3>
                                                    <p className="text-xs text-muted-foreground">{new Date(milestone.date).toLocaleDateString()}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                )}

                {activeTab === 'tasks' && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex justify-between items-center">
                                <div>
                                    <CardTitle className="text-foreground">Project Tasks</CardTitle>
                                    <CardDescription className="text-muted-foreground">Manage and track project tasks</CardDescription>
                                </div>
                                <Button>
                                    <Plus className="w-4 h-4 mr-2" />
                                    Add Task
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {projectData.tasks.map(task => (
                                    <div key={task.id} className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors">
                                        <div className="flex items-center space-x-3">
                                            {getTaskStatusIcon(task.status)}
                                            <div>
                                                <h3 className="font-medium text-foreground">{task.title}</h3>
                                                <p className="text-sm text-muted-foreground">Assigned to {task.assignee}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge className={getPriorityColor(task.priority)} size="sm">
                                                {task.priority}
                                            </Badge>
                                            <span className="text-sm text-muted-foreground">
                                                Due {new Date(task.dueDate).toLocaleDateString()}
                                            </span>
                                            <Button variant="ghost" size="sm">
                                                <MoreVertical className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {activeTab === 'team' && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex justify-between items-center">
                                <div>
                                    <CardTitle className="text-foreground">Project Team</CardTitle>
                                    <CardDescription className="text-muted-foreground">Team members working on this project</CardDescription>
                                </div>
                                <Button>
                                    <Plus className="w-4 h-4 mr-2" />
                                    Add Member
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {projectData.team.map(member => (
                                    <div key={member.id} className="flex items-center space-x-3 p-4 border border-border rounded-lg">
                                        <div className="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                            <span className="text-sm font-semibold text-primary-foreground">{member.avatar}</span>
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-foreground">{member.name}</h3>
                                            <p className="text-sm text-muted-foreground">{member.role}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {activeTab === 'timeline' && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <CardTitle className="text-foreground">Project Timeline</CardTitle>
                            <CardDescription className="text-muted-foreground">Project milestones and deadlines</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-6">
                                {projectData.milestones.map((milestone, index) => (
                                    <div key={milestone.id} className="flex items-start space-x-4">
                                        <div className="flex flex-col items-center">
                                            <div className={`w-4 h-4 rounded-full ${
                                                milestone.status === 'completed' ? 'bg-green-500' :
                                                milestone.status === 'in-progress' ? 'bg-blue-500' : 'bg-gray-500'
                                            }`}></div>
                                            {index < projectData.milestones.length - 1 && (
                                                <div className="w-0.5 h-8 bg-gray-300 mt-2"></div>
                                            )}
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="font-medium text-foreground">{milestone.title}</h3>
                                            <p className="text-sm text-muted-foreground">{new Date(milestone.date).toLocaleDateString()}</p>
                                            <Badge className={`mt-2 ${getStatusColor(milestone.status)}`}>
                                                {milestone.status.replace('-', ' ')}
                                            </Badge>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
