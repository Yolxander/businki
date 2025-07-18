import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    BookOpen,
    Plus,
    Edit,
    Trash2,
    Save,
    Copy,
    Eye,
    Play,
    Search,
    Filter,
    Settings,
    Sparkles,
    Target,
    Layers,
    MessageSquare,
    Code,
    Palette,
    BarChart3,
    User,
    Activity,
    TestTube,
    FileText,
    Lightbulb,
    TrendingUp,
    Clock,
    Star,
    MoreVertical,
    ChevronDown,
    ChevronUp,
    Loader2,
    CheckCircle,
    AlertCircle,
    Zap
} from 'lucide-react';

export default function PromptEngineering({ auth }) {
    const [templates, setTemplates] = useState([]);
    const [savedPrompts, setSavedPrompts] = useState([]);
    const [activeTab, setActiveTab] = useState('templates');
    const [selectedTemplate, setSelectedTemplate] = useState(null);
    const [isEditing, setIsEditing] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterType, setFilterType] = useState('all');
    const [filterStatus, setFilterStatus] = useState('all');
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [isLoading, setIsLoading] = useState(false);

    // Form state for creating/editing templates
    const [formData, setFormData] = useState({
        name: '',
        type: 'content',
        description: '',
        template: '',
        is_active: true
    });

    useEffect(() => {
        loadTemplates();
        loadSavedPrompts();
    }, []);

    const loadTemplates = async () => {
        try {
            const response = await fetch('/api/playground/templates', {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });
            const data = await response.json();
            if (data.status === 'success') {
                setTemplates(data.data);
            }
        } catch (error) {
            console.error('Failed to load templates:', error);
        }
    };

    const loadSavedPrompts = async () => {
        try {
            const response = await fetch('/api/prompt-engineering/saved-prompts', {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });
            const data = await response.json();
            if (data.status === 'success') {
                setSavedPrompts(data.data);
            }
        } catch (error) {
            console.error('Failed to load saved prompts:', error);
        }
    };

    const saveTemplate = async () => {
        setIsLoading(true);
        try {
            const url = selectedTemplate ? `/api/prompt-engineering/templates/${selectedTemplate.id}` : '/api/prompt-engineering/templates';
            const method = selectedTemplate ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method,
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            if (data.status === 'success') {
                await loadTemplates();
                setShowCreateForm(false);
                setSelectedTemplate(null);
                setIsEditing(false);
                setFormData({
                    name: '',
                    type: 'content',
                    description: '',
                    template: '',
                    is_active: true
                });
            }
        } catch (error) {
            console.error('Failed to save template:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const deleteTemplate = async (templateId) => {
        if (!confirm('Are you sure you want to delete this template?')) return;

        try {
            const response = await fetch(`/api/prompt-engineering/templates/${templateId}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });

            const data = await response.json();
            if (data.status === 'success') {
                await loadTemplates();
            }
        } catch (error) {
            console.error('Failed to delete template:', error);
        }
    };

    const editTemplate = (template) => {
        setSelectedTemplate(template);
        setFormData({
            name: template.name,
            type: template.type,
            description: template.description,
            template: template.template,
            is_active: template.is_active
        });
        setIsEditing(true);
        setShowCreateForm(true);
    };

    const createNewTemplate = () => {
        setSelectedTemplate(null);
        setFormData({
            name: '',
            type: 'content',
            description: '',
            template: '',
            is_active: true
        });
        setIsEditing(false);
        setShowCreateForm(true);
    };

    const getTemplateIcon = (type) => {
        switch (type) {
            case 'proposal': return FileText;
            case 'project': return Target;
            case 'task': return Layers;
            case 'content': return MessageSquare;
            case 'code': return Code;
            case 'creative': return Palette;
            case 'analysis': return BarChart3;
            case 'personal_project': return User;
            case 'personal_task': return Activity;
            case 'testing': return TestTube;
            case 'communication': return MessageSquare;
            default: return BookOpen;
        }
    };

    const getTemplateTypeLabel = (type) => {
        switch (type) {
            case 'proposal': return 'Proposal';
            case 'project': return 'Project';
            case 'task': return 'Task';
            case 'content': return 'Content';
            case 'code': return 'Code';
            case 'creative': return 'Creative';
            case 'analysis': return 'Analysis';
            case 'personal_project': return 'Personal';
            case 'personal_task': return 'Learning';
            case 'testing': return 'Testing';
            case 'communication': return 'Communication';
            default: return type;
        }
    };

    const filteredTemplates = templates.filter(template => {
        const matchesSearch = template.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            template.description.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesType = filterType === 'all' || template.type === filterType;
        const matchesStatus = filterStatus === 'all' ||
                            (filterStatus === 'active' && template.is_active) ||
                            (filterStatus === 'inactive' && !template.is_active);

        return matchesSearch && matchesType && matchesStatus;
    });

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Prompt Engineering" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Prompt Engineering</h1>
                        <p className="text-muted-foreground">Create, manage, and optimize AI prompt templates</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline">
                            <Settings className="w-4 h-4 mr-2" />
                            Settings
                        </Button>
                        <Button onClick={createNewTemplate}>
                            <Plus className="w-4 h-4 mr-2" />
                            New Template
                        </Button>
                    </div>
                </div>

                {/* Tabs */}
                <div className="flex space-x-1 bg-muted p-1 rounded-lg">
                    <Button
                        variant={activeTab === 'templates' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('templates')}
                        className="flex-1"
                    >
                        <BookOpen className="w-4 h-4 mr-2" />
                        Templates
                    </Button>
                    <Button
                        variant={activeTab === 'saved' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('saved')}
                        className="flex-1"
                    >
                        <Star className="w-4 h-4 mr-2" />
                        Saved Prompts
                    </Button>
                    <Button
                        variant={activeTab === 'analytics' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('analytics')}
                        className="flex-1"
                    >
                        <TrendingUp className="w-4 h-4 mr-2" />
                        Analytics
                    </Button>
                </div>

                {/* Templates Tab */}
                {activeTab === 'templates' && (
                    <div className="space-y-6">
                        {/* Filters */}
                        <Card className="bg-card border-border">
                            <CardContent className="pt-6">
                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-foreground mb-2 block">Search</label>
                                        <div className="relative">
                                            <Search className="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground" />
                                            <input
                                                type="text"
                                                placeholder="Search templates..."
                                                value={searchTerm}
                                                onChange={(e) => setSearchTerm(e.target.value)}
                                                className="w-full pl-10 pr-4 py-2 text-sm border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-foreground mb-2 block">Type</label>
                                        <select
                                            value={filterType}
                                            onChange={(e) => setFilterType(e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="all">All Types</option>
                                            <option value="proposal">Proposal</option>
                                            <option value="project">Project</option>
                                            <option value="task">Task</option>
                                            <option value="content">Content</option>
                                            <option value="code">Code</option>
                                            <option value="creative">Creative</option>
                                            <option value="analysis">Analysis</option>
                                            <option value="personal_project">Personal</option>
                                            <option value="personal_task">Learning</option>
                                            <option value="testing">Testing</option>
                                            <option value="communication">Communication</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-foreground mb-2 block">Status</label>
                                        <select
                                            value={filterStatus}
                                            onChange={(e) => setFilterStatus(e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="all">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div className="flex items-end">
                                        <Button variant="outline" className="w-full">
                                            <Filter className="w-4 h-4 mr-2" />
                                            Clear Filters
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Templates Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {filteredTemplates.map((template) => {
                                const IconComponent = getTemplateIcon(template.type);
                                return (
                                    <Card key={template.id} className="bg-card border-border hover:shadow-md transition-shadow">
                                        <CardHeader className="pb-3">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center space-x-2">
                                                    <IconComponent className="w-5 h-5 text-primary" />
                                                    <Badge variant="secondary" className="text-xs">
                                                        {getTemplateTypeLabel(template.type)}
                                                    </Badge>
                                                </div>
                                                <div className="flex items-center space-x-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => editTemplate(template)}
                                                        title="Edit Template"
                                                    >
                                                        <Edit className="w-4 h-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => deleteTemplate(template.id)}
                                                        title="Delete Template"
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                            <CardTitle className="text-foreground text-lg">{template.name}</CardTitle>
                                            <CardDescription className="text-muted-foreground">
                                                {template.description}
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent className="pt-0">
                                            <div className="flex items-center justify-between text-sm text-muted-foreground mb-3">
                                                <span>Status: {template.is_active ? 'Active' : 'Inactive'}</span>
                                                <span>Updated: {new Date(template.updated_at).toLocaleDateString()}</span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Link href={`/prompt-engineering/prompts/${template.id}`}>
                                                    <Button variant="outline" size="sm" className="flex-1">
                                                        <Eye className="w-4 h-4 mr-1" />
                                                        Details
                                                    </Button>
                                                </Link>
                                                <Button variant="outline" size="sm" className="flex-1">
                                                    <Play className="w-4 h-4 mr-1" />
                                                    Test
                                                </Button>
                                            </div>
                                        </CardContent>
                                    </Card>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* Saved Prompts Tab */}
                {activeTab === 'saved' && (
                    <div className="space-y-6">
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Saved Prompts</CardTitle>
                                <CardDescription className="text-muted-foreground">
                                    Your saved and optimized prompts for quick access
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="text-center py-8 text-muted-foreground">
                                    <Lightbulb className="w-12 h-12 mx-auto mb-4 opacity-50" />
                                    <p>No saved prompts yet</p>
                                    <p className="text-sm">Save useful prompts from your testing sessions</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Analytics Tab */}
                {activeTab === 'analytics' && (
                    <div className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <Card className="bg-card border-border">
                                <CardContent className="pt-6">
                                    <div className="flex items-center space-x-2">
                                        <BookOpen className="w-8 h-8 text-primary" />
                                        <div>
                                            <p className="text-sm text-muted-foreground">Total Templates</p>
                                            <p className="text-2xl font-bold text-foreground">{templates.length}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card className="bg-card border-border">
                                <CardContent className="pt-6">
                                    <div className="flex items-center space-x-2">
                                        <Zap className="w-8 h-8 text-green-500" />
                                        <div>
                                            <p className="text-sm text-muted-foreground">Active Templates</p>
                                            <p className="text-2xl font-bold text-foreground">
                                                {templates.filter(t => t.is_active).length}
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card className="bg-card border-border">
                                <CardContent className="pt-6">
                                    <div className="flex items-center space-x-2">
                                        <Star className="w-8 h-8 text-yellow-500" />
                                        <div>
                                            <p className="text-sm text-muted-foreground">Saved Prompts</p>
                                            <p className="text-2xl font-bold text-foreground">{savedPrompts.length}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Template Usage Analytics</CardTitle>
                                <CardDescription className="text-muted-foreground">
                                    Track how your templates are performing
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="text-center py-8 text-muted-foreground">
                                    <BarChart3 className="w-12 h-12 mx-auto mb-4 opacity-50" />
                                    <p>Analytics coming soon</p>
                                    <p className="text-sm">Track template performance and usage metrics</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Create/Edit Template Modal */}
                {showCreateForm && (
                    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                        <div className="bg-background border border-border rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                            <div className="flex items-center justify-between mb-6">
                                <h2 className="text-xl font-bold text-foreground">
                                    {isEditing ? 'Edit Template' : 'Create New Template'}
                                </h2>
                                <Button
                                    variant="ghost"
                                    onClick={() => setShowCreateForm(false)}
                                >
                                    ×
                                </Button>
                            </div>

                            <div className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-foreground mb-2 block">Template Name</label>
                                        <input
                                            type="text"
                                            value={formData.name}
                                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                            className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                            placeholder="Enter template name"
                                        />
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-foreground mb-2 block">Type</label>
                                        <select
                                            value={formData.type}
                                            onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                                            className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="proposal">Proposal</option>
                                            <option value="project">Project</option>
                                            <option value="task">Task</option>
                                            <option value="content">Content</option>
                                            <option value="code">Code</option>
                                            <option value="creative">Creative</option>
                                            <option value="analysis">Analysis</option>
                                            <option value="personal_project">Personal</option>
                                            <option value="personal_task">Learning</option>
                                            <option value="testing">Testing</option>
                                            <option value="communication">Communication</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-foreground mb-2 block">Description</label>
                                    <input
                                        type="text"
                                        value={formData.description}
                                        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                        className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter template description"
                                    />
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-foreground mb-2 block">Template Content</label>
                                    <textarea
                                        value={formData.template}
                                        onChange={(e) => setFormData({ ...formData, template: e.target.value })}
                                        rows={12}
                                        className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
                                        placeholder="Enter your template content with variables in {variable_name} format..."
                                    />
                                </div>

                                <div className="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="is_active"
                                        checked={formData.is_active}
                                        onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                        className="rounded border-border"
                                    />
                                    <label htmlFor="is_active" className="text-sm text-foreground">
                                        Active Template
                                    </label>
                                </div>

                                <div className="flex items-center justify-end space-x-2 pt-4">
                                    <Button
                                        variant="outline"
                                        onClick={() => setShowCreateForm(false)}
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        onClick={saveTemplate}
                                        disabled={isLoading || !formData.name || !formData.template}
                                    >
                                        {isLoading ? (
                                            <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                        ) : (
                                            <Save className="w-4 h-4 mr-2" />
                                        )}
                                        {isEditing ? 'Update Template' : 'Create Template'}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
