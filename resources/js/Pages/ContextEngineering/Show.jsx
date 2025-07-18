import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    ArrowLeft,
    Edit,
    Download,
    Copy,
    Trash2,
    Eye,
    FileText,
    Sparkles,
    User,
    Calendar,
    Clock,
    Settings,
    BarChart3,
    FolderOpen,
    CheckCircle,
    History,
    CheckSquare
} from 'lucide-react';
import axios from 'axios';

export default function Show({ auth, document }) {
    const [isDeleting, setIsDeleting] = useState(false);
    const [activeTab, setActiveTab] = useState('content');

    const handleDelete = async () => {
        if (!confirm('Are you sure you want to delete this document?')) return;

        setIsDeleting(true);
        try {
            await axios.delete(`/api/context-engineering/documents/${document.id}`);
            router.visit('/context-engineering');
        } catch (error) {
            console.error('Failed to delete document:', error);
            alert('Failed to delete document');
        } finally {
            setIsDeleting(false);
        }
    };

    const handleDownload = () => {
        window.open(`/context-engineering/${document.id}/download`, '_blank');
    };

    const handleCreateVersion = async () => {
        try {
            await axios.post(`/api/context-engineering/documents/${document.id}/version`);
            router.reload();
        } catch (error) {
            console.error('Failed to create version:', error);
            alert('Failed to create version');
        }
    };

    const handleActivate = async () => {
        try {
            await axios.patch(`/api/context-engineering/documents/${document.id}/activate`);
            router.reload();
        } catch (error) {
            console.error('Failed to activate version:', error);
            alert('Failed to activate version');
        }
    };

    const getTypeIcon = (type) => {
        switch (type) {
            case 'implementation': return <Settings className="h-4 w-4" />;
            case 'workflow': return <BarChart3 className="h-4 w-4" />;
            case 'project_structure': return <FolderOpen className="h-4 w-4" />;
            case 'ui_ux': return <FileText className="h-4 w-4" />;
            case 'bug_tracking': return <CheckCircle className="h-4 w-4" />;
            default: return <FileText className="h-4 w-4" />;
        }
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const renderMarkdown = (content) => {
        // Simple markdown rendering - in a real app, you'd use a proper markdown library
        return content
            .replace(/^### (.*$)/gim, '<h3 class="text-lg font-semibold mt-4 mb-2 text-foreground">$1</h3>')
            .replace(/^## (.*$)/gim, '<h2 class="text-xl font-semibold mt-6 mb-3 text-foreground">$1</h2>')
            .replace(/^# (.*$)/gim, '<h1 class="text-2xl font-bold mt-8 mb-4 text-foreground">$1</h1>')
            .replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold">$1</strong>')
            .replace(/\*(.*?)\*/g, '<em class="italic">$1</em>')
            .replace(/`(.*?)`/g, '<code class="bg-muted px-1 rounded text-sm font-mono">$1</code>')
            .replace(/^- \[ \] (.*$)/gim, '<div class="flex items-center mb-1"><input type="checkbox" disabled class="mr-2">$1</div>')
            .replace(/^- \[x\] (.*$)/gim, '<div class="flex items-center mb-1"><input type="checkbox" checked disabled class="mr-2">$1</div>')
            .replace(/^- (.*$)/gim, '<li class="ml-4">$1</li>')
            .replace(/\n\n/g, '</p><p class="mb-4 text-foreground">')
            .replace(/^(?!<[h|d|p|l|i])(.*$)/gim, '<p class="mb-4 text-foreground">$1</p>');
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={document.name} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-start">
                    <div>
                        <div className="flex items-center space-x-3 mb-2">
                            <Button variant="outline" size="sm" onClick={() => router.visit('/context-engineering')}>
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back
                            </Button>
                            <h1 className="text-2xl font-bold text-foreground">{document.name}</h1>
                            {document.is_active && (
                                <Badge variant="default" className="bg-green-500/20 text-green-400 border-green-500/30">
                                    Active
                                </Badge>
                            )}
                        </div>
                        <p className="text-muted-foreground text-sm">{document.description}</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={`/context-engineering/${document.id}/edit`}>
                            <Button variant="outline" size="sm">
                                <Edit className="h-4 w-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Button variant="outline" size="sm" onClick={handleDownload}>
                            <Download className="h-4 w-4 mr-2" />
                            Download
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={handleDelete}
                            disabled={isDeleting}
                            className="text-destructive hover:text-destructive hover:bg-destructive/10 border-destructive/30"
                        >
                            <Trash2 className="h-4 w-4 mr-2" />
                            {isDeleting ? 'Deleting...' : 'Delete'}
                        </Button>
                    </div>
                </div>

                {/* Content Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
                    <TabsList className="grid w-full grid-cols-3 bg-muted/50">
                        <TabsTrigger value="content" className="data-[state=active]:bg-background data-[state=active]:text-foreground">
                            Content
                        </TabsTrigger>
                        <TabsTrigger value="metadata" className="data-[state=active]:bg-background data-[state=active]:text-foreground">
                            Info
                        </TabsTrigger>
                        <TabsTrigger value="versions" className="data-[state=active]:bg-background data-[state=active]:text-foreground">
                            History
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="content" className="space-y-6">
                        <Card className="bg-card border-border shadow-sm">
                            <CardContent className="p-6">
                                <div
                                    className="prose prose-sm max-w-none prose-headings:text-foreground prose-p:text-foreground prose-strong:text-foreground prose-em:text-foreground prose-code:bg-muted prose-code:text-foreground"
                                    dangerouslySetInnerHTML={{
                                        __html: document.is_markdown ? renderMarkdown(document.content) : `<pre class="whitespace-pre-wrap text-foreground bg-muted p-4 rounded-lg border">${document.content}</pre>`
                                    }}
                                />
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="metadata" className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card className="bg-card border-border shadow-sm">
                                <CardHeader className="pb-4">
                                    <CardTitle className="text-foreground">Document</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div>
                                        <span className="text-sm text-muted-foreground">Project:</span>
                                        <p className="text-sm text-foreground font-medium">{document.project?.title}</p>
                                    </div>
                                    <div>
                                        <span className="text-sm text-muted-foreground">Type:</span>
                                        <p className="text-sm text-foreground font-medium">{document.type_label}</p>
                                    </div>
                                    <div>
                                        <span className="text-sm text-muted-foreground">Created by:</span>
                                        <p className="text-sm text-foreground font-medium">{document.creator?.name}</p>
                                    </div>
                                    <div>
                                        <span className="text-sm text-muted-foreground">Created:</span>
                                        <p className="text-sm text-foreground font-medium">{formatDate(document.created_at)}</p>
                                    </div>
                                    <div>
                                        <span className="text-sm text-muted-foreground">Updated:</span>
                                        <p className="text-sm text-foreground font-medium">{formatDate(document.updated_at)}</p>
                                    </div>
                                    <div>
                                        <span className="text-sm text-muted-foreground">Version:</span>
                                        <p className="text-sm text-foreground font-medium">{document.version}</p>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="bg-card border-border shadow-sm">
                                <CardHeader className="pb-4">
                                    <CardTitle className="text-foreground">Status</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div>
                                        <span className="text-sm text-muted-foreground">Status:</span>
                                        <div className="flex items-center space-x-2 mt-1">
                                            {document.is_active && <Badge variant="default" className="bg-green-500/20 text-green-400 border-green-500/30">Active</Badge>}
                                            {document.is_template && <Badge variant="outline" className="border-muted-foreground/30 text-muted-foreground">Template</Badge>}
                                            {document.is_generated && <Badge variant="secondary" className="bg-primary/20 text-primary-foreground border-primary/30">AI Generated</Badge>}
                                        </div>
                                    </div>
                                    {document.file_path && (
                                        <>
                                            <div>
                                                <span className="text-sm text-muted-foreground">File:</span>
                                                <p className="text-sm text-foreground font-medium">{document.file_name}</p>
                                            </div>
                                            <div>
                                                <span className="text-sm text-muted-foreground">Size:</span>
                                                <p className="text-sm text-foreground font-medium">{document.file_size_formatted}</p>
                                            </div>
                                        </>
                                    )}
                                    <div>
                                        <span className="text-sm text-muted-foreground">Content:</span>
                                        <p className="text-sm text-foreground font-medium">{document.content.length.toLocaleString()} characters</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {document.is_generated && document.generation_metadata && (
                            <Card className="bg-card border-border shadow-sm">
                                <CardHeader className="pb-4">
                                    <CardTitle className="text-foreground">AI Generation</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <span className="text-sm text-muted-foreground">Model:</span>
                                            <p className="text-sm text-foreground font-medium">{document.generation_metadata.model}</p>
                                        </div>
                                        <div>
                                            <span className="text-sm text-muted-foreground">Tokens:</span>
                                            <p className="text-sm text-foreground font-medium">{document.generation_metadata.tokens_used || 'N/A'}</p>
                                        </div>
                                        <div>
                                            <span className="text-sm text-muted-foreground">Cost:</span>
                                            <p className="text-sm text-foreground font-medium">{document.generation_metadata.cost || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <div className="mt-4">
                                        <span className="text-sm text-muted-foreground">Prompt:</span>
                                        <p className="text-sm font-mono bg-muted p-3 rounded-lg text-xs text-foreground border mt-1">
                                            {document.generation_metadata.prompt}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </TabsContent>

                    <TabsContent value="versions" className="space-y-6">
                        <Card className="bg-card border-border shadow-sm">
                            <CardHeader className="pb-4">
                                <CardTitle className="text-foreground">Version History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {document.versions && document.versions.length > 0 ? (
                                    <div className="space-y-3">
                                        {document.versions.map((version) => (
                                            <div
                                                key={version.id}
                                                className={`p-3 border rounded-lg transition-colors ${
                                                    version.is_active
                                                        ? 'border-primary bg-primary/5'
                                                        : 'border-border bg-card hover:bg-accent/50'
                                                }`}
                                            >
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <div className="flex items-center space-x-2">
                                                            <span className="font-medium text-foreground">v{version.version}</span>
                                                            {version.is_active && (
                                                                <Badge variant="default" className="bg-green-500/20 text-green-400 border-green-500/30 text-xs">
                                                                    Active
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        <p className="text-xs text-muted-foreground mt-1">
                                                            {version.creator?.name} • {formatDate(version.created_at)}
                                                        </p>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <Link href={`/context-engineering/${version.id}`}>
                                                            <Button variant="outline" size="sm">
                                                                <Eye className="h-3 w-3 mr-1" />
                                                                View
                                                            </Button>
                                                        </Link>
                                                        {!version.is_active && (
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => {
                                                                    // Handle activate version
                                                                }}
                                                            >
                                                                <CheckSquare className="h-3 w-3 mr-1" />
                                                                Activate
                                                            </Button>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <p className="text-sm text-muted-foreground">No other versions found</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AuthenticatedLayout>
    );
}
