import React, { useState } from 'react';
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
    Download,
    Edit,
    Eye,
    Trash2,
    User,
    Calendar,
    Clock,
    ArrowLeft,
    RefreshCw,
    FolderOpen
} from 'lucide-react';

export default function ProjectDetails({ auth, project, types, stats }) {
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedType, setSelectedType] = useState('all');
    const [showGenerateDialog, setShowGenerateDialog] = useState(false);
    const [isGenerating, setIsGenerating] = useState(false);
    const [generateForm, setGenerateForm] = useState({
        type: '',
        prompt: ''
    });

    const handleGenerate = async () => {
        if (!generateForm.type || !generateForm.prompt.trim()) {
            alert('Please fill in all required fields');
            return;
        }

        setIsGenerating(true);
        try {
            const response = await fetch('/api/context-engineering/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    project_id: project.id,
                    type: generateForm.type,
                    prompt: generateForm.prompt
                })
            });

            const data = await response.json();
            if (data.status === 'success') {
                router.reload();
            } else {
                alert('Failed to generate document: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to generate document:', error);
            alert('Failed to generate document');
        } finally {
            setIsGenerating(false);
            setShowGenerateDialog(false);
        }
    };

    const handleRegenerate = async (documentId) => {
        if (!confirm('Are you sure you want to regenerate this document? This will create a new version.')) {
            return;
        }

        try {
            const response = await fetch(`/api/context-engineering/documents/${documentId}/regenerate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            if (data.status === 'success') {
                router.reload();
            } else {
                alert('Failed to regenerate document: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to regenerate document:', error);
            alert('Failed to regenerate document');
        }
    };

    const handleDelete = async (documentId) => {
        if (!confirm('Are you sure you want to delete this document?')) {
            return;
        }

        try {
            const response = await fetch(`/api/context-engineering/documents/${documentId}`, {
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
                alert('Failed to delete document: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to delete document:', error);
            alert('Failed to delete document');
        }
    };

    const handleDownload = (document) => {
        window.open(`/api/context-engineering/documents/${document.id}/download`, '_blank');
    };

    const handleDownloadAll = () => {
        window.open(`/api/context-engineering/projects/${project.id}/download`, '_blank');
    };

    const getTypeIcon = (type) => {
        const iconClass = "h-5 w-5 text-muted-foreground";
        switch (type) {
            case 'requirements':
                return <FileText className={iconClass} />;
            case 'architecture':
                return <Copy className={iconClass} />;
            case 'api_docs':
                return <CheckCircle className={iconClass} />;
            default:
                return <FileText className={iconClass} />;
        }
    };

    const filteredDocuments = (project.documents || []).filter(doc => {
        const matchesSearch = !searchTerm ||
            doc.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            doc.description?.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesType = selectedType === 'all' || !selectedType || doc.type === selectedType;
        return matchesSearch && matchesType;
    });

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${project.title} - Documents`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div className="flex items-center space-x-4">
                        <Button variant="outline" size="sm" onClick={() => router.visit('/context-engineering')}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Projects
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{project.title}</h1>
                            <p className="text-muted-foreground">
                                {stats.total_documents} documents • {project.description ? project.description.substring(0, 100) + '...' : 'No description'}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            onClick={handleDownloadAll}
                            disabled={stats.total_documents === 0}
                        >
                            <FolderOpen className="h-4 w-4 mr-2" />
                            Download All
                        </Button>
                        <Dialog open={showGenerateDialog} onOpenChange={setShowGenerateDialog}>
                            <DialogTrigger asChild>
                                <Button className="bg-primary hover:bg-primary/90">
                                    <Sparkles className="h-4 w-4 mr-2" />
                                    Generate Document
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-2xl">
                                <DialogHeader>
                                    <DialogTitle className="text-foreground">Generate Document with AI</DialogTitle>
                                    <DialogDescription className="text-muted-foreground">
                                        Use AI to generate a new context engineering document for this project.
                                    </DialogDescription>
                                </DialogHeader>
                                <div className="space-y-4">
                                    <div>
                                        <Label htmlFor="generate-type" className="text-foreground">Document Type</Label>
                                        <Select value={generateForm.type} onValueChange={(value) => setGenerateForm({...generateForm, type: value})}>
                                            <SelectTrigger className="mt-2">
                                                <SelectValue placeholder="Select document type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(types).map(([key, label]) => (
                                                    <SelectItem key={key} value={key}>
                                                        {label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label htmlFor="generate-prompt" className="text-foreground">Prompt</Label>
                                        <Textarea
                                            id="generate-prompt"
                                            placeholder="Describe what you want to generate..."
                                            value={generateForm.prompt}
                                            onChange={(e) => setGenerateForm({...generateForm, prompt: e.target.value})}
                                            rows={4}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="flex justify-end gap-2">
                                        <Button variant="outline" onClick={() => setShowGenerateDialog(false)}>
                                            Cancel
                                        </Button>
                                        <Button
                                            onClick={handleGenerate}
                                            disabled={!generateForm.type || !generateForm.prompt.trim() || isGenerating}
                                            className="bg-primary hover:bg-primary/90"
                                        >
                                            {isGenerating ? 'Generating...' : 'Generate Document'}
                                        </Button>
                                    </div>
                                </div>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                {/* Quick Stats */}
                <div className="flex items-center space-x-6 text-sm text-muted-foreground">
                    <div className="flex items-center space-x-2">
                        <FileText className="h-4 w-4" />
                        <span>{stats.total_documents} total documents</span>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Sparkles className="h-4 w-4" />
                        <span>{stats.generated} AI generated</span>
                    </div>
                    <div className="flex items-center space-x-2">
                        <CheckCircle className="h-4 w-4" />
                        <span>{stats.active} active</span>
                    </div>
                </div>

                {/* Search and Filters */}
                <Card className="bg-card border-border shadow-sm">
                    <CardContent className="pt-6">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search documents..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10 bg-background border-border focus:border-primary"
                                    />
                                </div>
                            </div>
                            <div className="w-full sm:w-48">
                                <Select value={selectedType} onValueChange={setSelectedType}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Filter by type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Types</SelectItem>
                                        {Object.entries(types).map(([key, label]) => (
                                            <SelectItem key={key} value={key}>
                                                {label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Documents Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {filteredDocuments.map((document) => (
                        <Card key={document.id} className="bg-card border-border hover:shadow-lg transition-all duration-200 hover:scale-[1.02] group">
                            <CardHeader>
                                <div className="flex items-start justify-between">
                                    <div className="flex items-center space-x-2">
                                        <div className="p-2 bg-primary/10 rounded-lg">
                                            {getTypeIcon(document.type)}
                                        </div>
                                        <div className="flex-1">
                                            <CardTitle className="text-foreground line-clamp-2 group-hover:text-primary transition-colors">
                                                <Link
                                                    href={`/context-engineering/${document.id}`}
                                                    className="hover:text-primary transition-colors"
                                                >
                                                    {document.name}
                                                </Link>
                                            </CardTitle>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-1">
                                        {document.is_generated && (
                                            <Badge variant="secondary" className="text-xs bg-primary/20 text-primary-foreground border-primary/30">
                                                <Sparkles className="h-2 w-2 mr-1" />
                                                AI
                                            </Badge>
                                        )}
                                        {document.is_active && (
                                            <Badge variant="default" className="text-xs bg-green-500/20 text-green-400 border-green-500/30">
                                                Active
                                            </Badge>
                                        )}
                                    </div>
                                </div>
                                <CardDescription className="text-muted-foreground line-clamp-2">
                                    {document.description || 'No description available'}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center justify-between text-sm text-muted-foreground mb-4">
                                    <div className="flex items-center space-x-4">
                                        <span className="flex items-center">
                                            <User className="h-3 w-3 mr-1" />
                                            {document.creator?.name}
                                        </span>
                                        <span className="flex items-center">
                                            <Calendar className="h-3 w-3 mr-1" />
                                            {new Date(document.created_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center space-x-1">
                                        <Link href={`/context-engineering/${document.id}`}>
                                            <Button variant="outline" size="sm" className="hover:bg-accent">
                                                <Eye className="h-3 w-3 mr-1" />
                                                View
                                            </Button>
                                        </Link>
                                        <Link href={`/context-engineering/${document.id}/edit`}>
                                            <Button variant="outline" size="sm" className="hover:bg-accent">
                                                <Edit className="h-3 w-3 mr-1" />
                                                Edit
                                            </Button>
                                        </Link>
                                    </div>
                                    <div className="flex items-center space-x-1">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => handleDownload(document)}
                                            className="hover:bg-accent"
                                        >
                                            <Download className="h-3 w-3" />
                                        </Button>
                                        {document.is_generated && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handleRegenerate(document.id)}
                                                className="hover:bg-accent"
                                            >
                                                <RefreshCw className="h-3 w-3" />
                                            </Button>
                                        )}
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => handleDelete(document.id)}
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

                {filteredDocuments.length === 0 && (
                    <Card className="bg-card border-border shadow-sm">
                        <CardContent className="pt-12 pb-12">
                            <div className="text-center">
                                <div className="p-4 bg-muted/50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                    <FileText className="h-8 w-8 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-semibold text-foreground mb-2">No documents found</h3>
                                <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                                    {searchTerm || selectedType !== 'all'
                                        ? 'Try adjusting your search or filter terms.'
                                        : 'Get started by creating your first document for this project.'
                                    }
                                </p>
                                {!searchTerm && selectedType === 'all' && (
                                    <Button
                                        onClick={() => setShowGenerateDialog(true)}
                                        className="bg-primary hover:bg-primary/90"
                                    >
                                        <Sparkles className="h-4 w-4 mr-2" />
                                        Generate Document
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
