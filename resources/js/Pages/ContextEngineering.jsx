import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import {
    FileText,
    Sparkles,
    Copy,
    CheckCircle,
    Search,
    Plus,
    Upload,
    Download,
    Edit,
    Eye,
    Trash2,
    User,
    Calendar,
    Clock,
    FolderPlus,
    FolderOpen
} from 'lucide-react';

export default function ContextEngineering({ auth, projects, types, filters }) {
    const [searchTerm, setSearchTerm] = useState('');
    const [stats, setStats] = useState({
        total_projects: 0,
        total_documents: 0,
        generated: 0,
        active: 0
    });
    const [showCreateProjectDialog, setShowCreateProjectDialog] = useState(false);
    const [isCreatingProject, setIsCreatingProject] = useState(false);
    const [createProjectForm, setCreateProjectForm] = useState({
        description: ''
    });

    useEffect(() => {
        loadStats();
    }, []);

    const loadStats = async () => {
        try {
            const response = await fetch('/api/context-engineering/stats', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await response.json();
            if (data.status === 'success') {
                setStats(data.data);
            }
        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    };

    const handleCreateProject = async () => {
        if (!createProjectForm.description.trim()) {
            alert('Please enter a project description');
            return;
        }

        setIsCreatingProject(true);
        try {
            const response = await fetch('/api/context-engineering/projects', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(createProjectForm)
            });

            const data = await response.json();
            if (data.status === 'success') {
                router.reload();
            } else {
                alert('Failed to create project: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to create project:', error);
            alert('Failed to create project');
        } finally {
            setIsCreatingProject(false);
            setShowCreateProjectDialog(false);
            setCreateProjectForm({ description: '' });
        }
    };

    const handleDeleteProject = async (projectId) => {
        if (!confirm('Are you sure you want to delete this project? This will also delete all associated documents.')) {
            return;
        }

        try {
            const response = await fetch(`/api/context-engineering/projects/${projectId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            if (data.status === 'success') {
                router.reload();
            } else {
                alert('Failed to delete project: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to delete project:', error);
            alert('Failed to delete project');
        }
    };

    const filteredProjects = (projects || []).filter(project => {
        const matchesSearch = !searchTerm ||
            project.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
            project.description?.toLowerCase().includes(searchTerm.toLowerCase());
        return matchesSearch;
    });

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Context Engineering" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Context Engineering</h1>
                        <p className="text-muted-foreground">Manage development projects and context engineering documents</p>
                    </div>
                    <div className="flex gap-2">
                        <Dialog open={showCreateProjectDialog} onOpenChange={setShowCreateProjectDialog}>
                            <DialogTrigger asChild>
                                <Button className="bg-primary hover:bg-primary/90">
                                    <FolderPlus className="h-4 w-4 mr-2" />
                                    New Project
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-2xl">
                                <DialogHeader>
                                    <DialogTitle className="text-foreground">Create New Development Project</DialogTitle>
                                    <DialogDescription className="text-muted-foreground">
                                        Describe your project and AI will generate a title for you.
                                    </DialogDescription>
                                </DialogHeader>
                                <div className="space-y-4">
                                    <div>
                                        <Label htmlFor="project-description" className="text-foreground">Project Description</Label>
                                        <Textarea
                                            id="project-description"
                                            placeholder="Describe your project (AI will generate a title)..."
                                            value={createProjectForm.description}
                                            onChange={(e) => setCreateProjectForm({...createProjectForm, description: e.target.value})}
                                            rows={4}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="flex justify-end gap-2">
                                        <Button variant="outline" onClick={() => setShowCreateProjectDialog(false)}>
                                            Cancel
                                        </Button>
                                        <Button
                                            onClick={handleCreateProject}
                                            disabled={!createProjectForm.description.trim() || isCreatingProject}
                                            className="bg-primary hover:bg-primary/90"
                                        >
                                            {isCreatingProject ? 'Creating...' : 'Create Project'}
                                        </Button>
                                    </div>
                                </div>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card className="bg-card border-border shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="pb-3">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Total Projects
                                </CardTitle>
                                <div className="p-2 bg-primary/10 rounded-lg">
                                    <FolderOpen className="w-4 h-4 text-primary" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.total_projects}</div>
                            <p className="text-xs text-muted-foreground mt-1">Development projects</p>
                        </CardContent>
                    </Card>
                    <Card className="bg-card border-border shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="pb-3">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Total Documents
                                </CardTitle>
                                <div className="p-2 bg-blue-500/10 rounded-lg">
                                    <FileText className="w-4 h-4 text-blue-500" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.total_documents}</div>
                            <p className="text-xs text-muted-foreground mt-1">Context engineering docs</p>
                        </CardContent>
                    </Card>
                    <Card className="bg-card border-border shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="pb-3">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    AI Generated
                                </CardTitle>
                                <div className="p-2 bg-green-500/10 rounded-lg">
                                    <Sparkles className="w-4 h-4 text-green-500" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.generated}</div>
                            <p className="text-xs text-muted-foreground mt-1">AI-powered documents</p>
                        </CardContent>
                    </Card>
                    <Card className="bg-card border-border shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="pb-3">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Active Documents
                                </CardTitle>
                                <div className="p-2 bg-green-500/10 rounded-lg">
                                    <CheckCircle className="w-4 h-4 text-green-500" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.active}</div>
                            <p className="text-xs text-muted-foreground mt-1">Currently active</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Search and Filters */}
                <Card className="bg-card border-border shadow-sm">
                    <CardContent className="pt-6">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search projects..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10 bg-background border-border focus:border-primary"
                                    />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Projects Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {filteredProjects.map((project) => (
                        <Card key={project.id} className="bg-card border-border hover:shadow-lg transition-all duration-200 hover:scale-[1.02] group">
                            <CardHeader>
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <CardTitle className="text-foreground line-clamp-2 group-hover:text-primary transition-colors">
                                            <Link
                                                href={`/context-engineering/projects/${project.id}`}
                                                className="hover:text-primary transition-colors"
                                            >
                                                {project.title}
                                            </Link>
                                        </CardTitle>
                                        <CardDescription className="text-muted-foreground mt-2 line-clamp-3">
                                            {project.description || 'No description available'}
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                                        <span className="flex items-center">
                                            <FileText className="h-3 w-3 mr-1" />
                                            {project.documents_count || 0} documents
                                        </span>
                                        <span className="flex items-center">
                                            <Calendar className="h-3 w-3 mr-1" />
                                            {new Date(project.created_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                    <div className="flex items-center space-x-1">
                                        <Link href={`/context-engineering/projects/${project.id}`}>
                                            <Button variant="outline" size="sm" className="hover:bg-accent">
                                                <Eye className="h-3 w-3 mr-1" />
                                                View
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => handleDeleteProject(project.id)}
                                            className="text-destructive hover:text-destructive hover:bg-destructive/10 border-destructive/30"
                                        >
                                            <Trash2 className="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {filteredProjects.length === 0 && (
                    <Card className="bg-card border-border shadow-sm">
                        <CardContent className="pt-12 pb-12">
                            <div className="text-center">
                                <div className="p-4 bg-muted/50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                    <FolderOpen className="h-8 w-8 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-semibold text-foreground mb-2">No projects found</h3>
                                <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                                    {searchTerm ? 'Try adjusting your search terms.' : 'Get started by creating your first development project.'}
                                </p>
                                {!searchTerm && (
                                    <Button
                                        onClick={() => setShowCreateProjectDialog(true)}
                                        className="bg-primary hover:bg-primary/90"
                                    >
                                        <Plus className="h-4 w-4 mr-2" />
                                        Create Project
                                    </Button>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
