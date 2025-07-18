import React, { useState, useEffect } from 'react';
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
    FileText,
    Copy,
    Sparkles,
    Eye
} from 'lucide-react';
import axios from 'axios';

export default function Create({ auth, projects, types, templates, preselected }) {
    const [form, setForm] = useState({
        project_id: preselected.project_id || '',
        name: '',
        description: '',
        type: preselected.type || '',
        content: '',
        is_template: false,
        variables: {}
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [selectedTemplate, setSelectedTemplate] = useState(null);
    const [showPreview, setShowPreview] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!form.project_id || !form.name || !form.type || !form.content) {
            alert('Please fill in all required fields');
            return;
        }

        setIsSubmitting(true);
        try {
            const response = await axios.post('/api/context-engineering/documents', form);
            router.visit('/context-engineering');
        } catch (error) {
            console.error('Failed to create document:', error);
            alert('Failed to create document: ' + (error.response?.data?.error || error.message));
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleTemplateSelect = (template) => {
        setSelectedTemplate(template);
        setForm({
            ...form,
            name: template.name,
            description: template.description,
            type: template.type,
            content: template.content,
            is_template: template.is_template,
            variables: template.variables || {}
        });
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

    const filteredTemplates = templates.filter(template =>
        !form.type || template.type === form.type
    );

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Context Engineering Document" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="space-y-6">
                        {/* Header */}
                        <div className="flex justify-between items-center">
                            <div className="flex items-center space-x-4">
                                <Button variant="outline" size="sm" onClick={() => router.visit('/context-engineering')}>
                                    <ArrowLeft className="h-4 w-4 mr-2" />
                                    Back to Projects
                                </Button>
                                <div>
                                    <h1 className="text-2xl font-bold text-foreground">Create Document</h1>
                                    <p className="text-muted-foreground">Create a new context engineering document</p>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            {/* Main Form */}
                            <div className="lg:col-span-2">
                                <Card className="bg-card border-border">
                                    <CardHeader>
                                        <CardTitle className="text-foreground">Document Details</CardTitle>
                                        <CardDescription className="text-muted-foreground">
                                            Fill in the details for your context engineering document
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
                                                    onClick={() => router.visit('/context-engineering')}
                                                >
                                                    Cancel
                                                </Button>
                                                <Button type="submit" disabled={isSubmitting}>
                                                    <Save className="h-4 w-4 mr-2" />
                                                    {isSubmitting ? 'Creating...' : 'Create Document'}
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
                                            <div className="prose max-w-none">
                                                <h1 className="text-2xl font-bold text-foreground">{form.name}</h1>
                                                {form.description && (
                                                    <p className="text-muted-foreground">{form.description}</p>
                                                )}
                                                <div className="mt-4 p-4 bg-muted rounded-lg">
                                                    <pre className="whitespace-pre-wrap text-foreground text-sm">{form.content}</pre>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}
                            </div>

                            {/* Templates Sidebar */}
                            <div className="space-y-6">
                                <Card className="bg-card border-border">
                                    <CardHeader>
                                        <CardTitle className="text-foreground">Templates</CardTitle>
                                        <CardDescription className="text-muted-foreground">
                                            Use a template to get started quickly
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-3">
                                            {filteredTemplates.map((template) => (
                                                <div
                                                    key={template.id}
                                                    className={`p-3 border rounded-lg cursor-pointer transition-colors ${
                                                        selectedTemplate?.id === template.id
                                                            ? 'border-primary bg-primary/5'
                                                            : 'border-border hover:border-primary/50'
                                                    }`}
                                                    onClick={() => handleTemplateSelect(template)}
                                                >
                                                    <div className="flex items-center space-x-2 mb-2">
                                                        {getTypeIcon(template.type)}
                                                        <span className="font-medium text-foreground">{template.name}</span>
                                                        {template.is_template && (
                                                            <Badge variant="outline" className="text-xs">Template</Badge>
                                                        )}
                                                    </div>
                                                    <p className="text-sm text-muted-foreground line-clamp-2">
                                                        {template.description}
                                                    </p>
                                                </div>
                                            ))}
                                            {filteredTemplates.length === 0 && (
                                                <div className="text-center py-4">
                                                    <FileText className="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                                                    <p className="text-sm text-muted-foreground">No templates available</p>
                                                </div>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </div>

                    {/* Preview Modal */}
                    {showPreview && (
                        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div className="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-lg font-semibold">Document Preview</h3>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => setShowPreview(false)}
                                    >
                                        ×
                                    </Button>
                                </div>
                                <div className="prose max-w-none">
                                    <pre className="whitespace-pre-wrap text-sm font-mono bg-gray-50 p-4 rounded">
                                        {form.content}
                                    </pre>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
