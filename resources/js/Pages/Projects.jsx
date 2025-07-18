import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Plus,
    Search,
    Filter,
    Calendar,
    Users,
    Target,
    CheckCircle,
    Clock,
    AlertCircle,
    FileText
} from 'lucide-react';

export default function Projects({ auth }) {
    const projects = [
        {
            id: 1,
            name: 'Website Redesign',
            client: 'Acme Corp',
            status: 'in-progress',
            progress: 65,
            dueDate: '2024-02-15',
            tasks: { completed: 8, total: 12 },
            priority: 'high'
        },
        {
            id: 2,
            name: 'Brand Identity',
            client: 'TechStart',
            status: 'completed',
            progress: 100,
            dueDate: '2024-01-30',
            tasks: { completed: 15, total: 15 },
            priority: 'medium'
        },
        {
            id: 3,
            name: 'Mobile App',
            client: 'InnovateLab',
            status: 'planned',
            progress: 0,
            dueDate: '2024-03-01',
            tasks: { completed: 0, total: 20 },
            priority: 'high'
        },
        {
            id: 4,
            name: 'E-commerce Platform',
            client: 'RetailPlus',
            status: 'in-progress',
            progress: 35,
            dueDate: '2024-02-28',
            tasks: { completed: 7, total: 20 },
            priority: 'medium'
        }
    ];

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in-progress':
                return 'bg-blue-100 text-blue-800';
            case 'planned':
                return 'bg-gray-100 text-gray-800';
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

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Projects" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Projects</h1>
                        <p className="text-muted-foreground">Manage your client projects and track progress</p>
                    </div>
                    <Link href="/projects/create">
                        <Button>
                            <Plus className="w-4 h-4 mr-2" />
                            New Project
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                                    <input
                                        type="text"
                                        placeholder="Search projects..."
                                        className="w-full pl-10 pr-4 py-2 border border-input rounded-md bg-background text-foreground placeholder:text-muted-foreground"
                                    />
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button variant="outline" size="sm">
                                    <Filter className="w-4 h-4 mr-2" />
                                    Filter
                                </Button>
                                <Button variant="outline" size="sm">
                                    <Calendar className="w-4 h-4 mr-2" />
                                    Sort
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Projects Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {projects.map((project) => (
                        <Card key={project.id} className="hover:shadow-lg transition-shadow">
                            <CardHeader>
                                <div className="flex justify-between items-start">
                                    <div className="flex-1">
                                        <CardTitle className="text-lg">{project.name}</CardTitle>
                                        <CardDescription className="flex items-center mt-1">
                                            <Users className="w-4 h-4 mr-1" />
                                            {project.client}
                                        </CardDescription>
                                    </div>
                                    <div className="flex flex-col items-end space-y-1">
                                        <Badge className={getStatusColor(project.status)}>
                                            {project.status.replace('-', ' ')}
                                        </Badge>
                                        <Badge className={getPriorityColor(project.priority)} size="sm">
                                            {project.priority}
                                        </Badge>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {/* Progress Bar */}
                                <div>
                                    <div className="flex justify-between items-center mb-2">
                                        <span className="text-sm font-medium text-foreground">Progress</span>
                                        <span className="text-sm text-muted-foreground">{project.progress}%</span>
                                    </div>
                                    <div className="w-full bg-gray-200 rounded-full h-2">
                                        <div
                                            className="bg-primary h-2 rounded-full transition-all duration-300"
                                            style={{ width: `${project.progress}%` }}
                                        ></div>
                                    </div>
                                </div>

                                {/* Task Count */}
                                <div className="flex items-center justify-between text-sm">
                                    <div className="flex items-center">
                                        <Target className="w-4 h-4 mr-1 text-muted-foreground" />
                                        <span className="text-muted-foreground">
                                            {project.tasks.completed}/{project.tasks.total} tasks
                                        </span>
                                    </div>
                                    <div className="flex items-center">
                                        <Calendar className="w-4 h-4 mr-1 text-muted-foreground" />
                                        <span className="text-muted-foreground">
                                            {new Date(project.dueDate).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>

                                {/* Actions */}
                                <div className="flex gap-2 pt-2">
                                    <Link href={`/projects/${project.id}`}>
                                        <Button variant="outline" size="sm" className="flex-1">
                                            View Details
                                        </Button>
                                    </Link>
                                    <Button variant="outline" size="sm">
                                        <CheckCircle className="w-4 h-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Empty State */}
                {projects.length === 0 && (
                    <Card>
                        <CardContent className="pt-12 pb-12">
                            <div className="text-center">
                                <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                                    <FileText className="w-8 h-8 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-medium text-foreground mb-2">No projects yet</h3>
                                <p className="text-muted-foreground mb-4">
                                    Get started by creating your first project
                                </p>
                                <Button>
                                    <Plus className="w-4 h-4 mr-2" />
                                    Create Project
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
