import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import {
    ArrowLeft,
    Save,
    Eye,
    FileText,
    Sparkles,
    User,
    Calendar,
    Clock
} from 'lucide-react';
import axios from 'axios';

export default function Edit({ auth, document, projects, types }) {
    const [form, setForm] = useState({
        project_id: document.project_id.toString(),
        name: document.name,
        description: document.description || '',
        type: document.type,
        content: document.content,
        is_template: document.is_template,
        variables: document.variables || {}
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showPreview, setShowPreview] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!form.project_id || !form.name || !form.type || !form.content) {
            alert('Please fill in all required fields');
            return;
        }

        setIsSubmitting(true);
        try {
            const response = await axios.put(`/api/context-engineering/documents/${document.id}`, form);
            router.visit(`/context-engineering/${document.id}`);
        } catch (error) {
            console.error('Failed to update document:', error);
            alert('Failed to update document: ' + (error.response?.data?.error || error.message));
        } finally {
            setIsSubmitting(false);
        }
    };

    const getTypeIcon = (type) => {
        switch (type) {
            case 'implementation': return <FileText className="h-4 w-4" />;
            case 'workflow': return <FileText className="h-4 w-4" />;
            case 'project_structure': return <FileText className="h-4 w-4" />;
            case 'ui_ux': return <FileText className="h-4 w-4" />;
            case 'bug_tracking': return <FileText className="h-4 w-4" />;
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
            <Head title={`Edit ${document.name}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-start">
                    <div className="flex items-center space-x-4">
                        <Button variant="outline" size="sm" onClick={() => router.visit(`/context-engineering/${document.id}`)}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Document
                        </Button>
                        <div>
                            <div className="flex items-center space-x-3 mb-2">
                                {getTypeIcon(document.type)}
                                <h1 className="text-2xl font-bold text-foreground">Edit Document</h1>
                                <div className="flex items-center space-x-2">
                                    {document.is_generated && (
                                        <Badge variant="secondary">
                                            <Sparkles className="h-3 w-3 mr-1" />
                                            AI Generated
                                        </Badge>
                                    )}
                                    {document.is_template && (
                                        <Badge variant="outline">Template</Badge>
                                    )}
                                    {document.is_active && (
                                        <Badge variant="default">Active</Badge>
                                    )}
                                </div>
                            </div>
                            <p className="text-muted-foreground">Update the document content and metadata</p>
                            <div className="flex items-center space-x-4 mt-2 text-sm text-muted-foreground">
                                <span className="flex items-center">
                                    <User className="h-3 w-3 mr-1" />
                                    {document.creator?.name}
                                </span>
                                <span className="flex items-center">
                                    <Calendar className="h-3 w-3 mr-1" />
                                    Created {formatDate(document.created_at)}
                                </span>
                                <span className="flex items-center">
                                    <Clock className="h-3 w-3 mr-1" />
                                    Version {document.version}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Main Form */}
                    <div className="lg:col-span-2">
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Edit Document</CardTitle>
                                <CardDescription className="text-muted-foreground">
                                    Update the document details and content
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="project_id" className="text-foreground">Project *</Label>
                                            <Select value={form.project_id} onValueChange={(value) => setForm({...form, project_id: value})}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select a project" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {projects.map(project => (
                                                        <SelectItem key={project.id} value={project.id.toString()}>
                                                            {project.title}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Label htmlFor="type" className="text-foreground">Document Type *</Label>
                                            <Select value={form.type} onValueChange={(value) => setForm({...form, type: value})}>
                                                <SelectTrigger>
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
                                    </div>

                                    <div>
                                        <Label htmlFor="name" className="text-foreground">Document Name *</Label>
                                        <Input
                                            id="name"
                                            value={form.name}
                                            onChange={(e) => setForm({...form, name: e.target.value})}
                                            placeholder="Enter document name"
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="description" className="text-foreground">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={form.description}
                                            onChange={(e) => setForm({...form, description: e.target.value})}
                                            placeholder="Enter document description (optional)"
                                            rows={3}
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="content" className="text-foreground">Content *</Label>
                                        <div className="relative">
                                            <Textarea
                                                id="content"
                                                value={form.content}
                                                onChange={(e) => setForm({...form, content: e.target.value})}
                                                placeholder="Enter document content..."
                                                rows={20}
                                                className="font-mono text-sm"
                                            />
                                            <div className="absolute top-2 right-2">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => setShowPreview(!showPreview)}
                                                >
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="is_template"
                                            checked={form.is_template}
                                            onCheckedChange={(checked) => setForm({...form, is_template: checked})}
                                        />
                                        <Label htmlFor="is_template" className="text-foreground">Save as template for future use</Label>
                                    </div>

                                    <div className="flex justify-end space-x-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => router.visit(`/context-engineering/${document.id}`)}
                                        >
                                            Cancel
                                        </Button>
                                        <Button type="submit" disabled={isSubmitting}>
                                            <Save className="h-4 w-4 mr-2" />
                                            {isSubmitting ? 'Saving...' : 'Save Changes'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>

                        {/* Preview */}
                        {showPreview && (
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Preview</CardTitle>
                                    <CardDescription className="text-muted-foreground">
                                        How your document will appear
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div
                                        className="prose max-w-none"
                                        dangerouslySetInnerHTML={{
                                            __html: document.is_markdown ? renderMarkdown(form.content) : `<pre class="whitespace-pre-wrap text-foreground">${form.content}</pre>`
                                        }}
                                    />
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Document Info Sidebar */}
                    <div className="space-y-6">
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Document Information</CardTitle>
                                <CardDescription className="text-muted-foreground">
                                    Current document details
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label className="text-sm font-medium text-muted-foreground">Current Name</Label>
                                    <p className="text-sm text-foreground">{document.name}</p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-muted-foreground">Type</Label>
                                    <p className="text-sm text-foreground">{types[document.type]}</p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-muted-foreground">Project</Label>
                                    <p className="text-sm text-foreground">{document.project?.title}</p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-muted-foreground">Created By</Label>
                                    <p className="text-sm text-foreground">{document.creator?.name}</p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-muted-foreground">Created At</Label>
                                    <p className="text-sm text-foreground">{formatDate(document.created_at)}</p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-muted-foreground">Last Updated</Label>
                                    <p className="text-sm text-foreground">{formatDate(document.updated_at)}</p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-muted-foreground">Version</Label>
                                    <p className="text-sm text-foreground">{document.version}</p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-muted-foreground">Status</Label>
                                    <div className="flex items-center space-x-2 mt-1">
                                        {document.is_active && <Badge variant="default">Active</Badge>}
                                        {document.is_template && <Badge variant="outline">Template</Badge>}
                                        {document.is_generated && <Badge variant="secondary">AI Generated</Badge>}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
