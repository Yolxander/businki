import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    ArrowLeft,
    Edit,
    Copy,
    Play,
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
    BookOpen,
    Brain,
    Zap,
    TrendingUp,
    Clock,
    Star,
    CheckCircle,
    AlertCircle,
    Loader2,
    ChevronDown,
    ChevronUp,
    Settings,
    Save,
    RefreshCw,
    Eye,
    TestTube2,
    Lightbulb,
    BarChart,
    History,
    Share2,
    Download,
    Upload
} from 'lucide-react';

export default function PromptDetails({ auth, promptId }) {
    const [prompt, setPrompt] = useState(null);
    const [models, setModels] = useState([]);
    const [selectedModel, setSelectedModel] = useState(null);
    const [optimizationType, setOptimizationType] = useState('effectiveness');
    const [isOptimizing, setIsOptimizing] = useState(false);
    const [optimizedPrompt, setOptimizedPrompt] = useState(null);
    const [optimizationHistory, setOptimizationHistory] = useState([]);
    const [showOptimizationHistory, setShowOptimizationHistory] = useState(false);
    const [isLoading, setIsLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('details');

    useEffect(() => {
        loadPromptDetails();
        loadModels();
    }, [promptId]);

    const loadPromptDetails = async () => {
        try {
            const response = await fetch(`/api/prompt-engineering/prompts/${promptId}`, {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });
            const data = await response.json();
            if (data.status === 'success') {
                setPrompt(data.data);
            }
        } catch (error) {
            console.error('Failed to load prompt details:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const loadModels = async () => {
        try {
            const response = await fetch('/api/ai-settings/models', {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });
            const data = await response.json();
            if (data.status === 'success') {
                setModels(data.data);
                if (data.data.length > 0) {
                    setSelectedModel(data.data[0]);
                }
            }
        } catch (error) {
            console.error('Failed to load models:', error);
        }
    };

    const optimizePrompt = async () => {
        if (!selectedModel || !prompt) return;

        setIsOptimizing(true);
        try {
            const response = await fetch('/api/prompt-engineering/optimize', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    prompt_id: promptId,
                    model_id: selectedModel.id,
                    optimization_type: optimizationType,
                    prompt_content: prompt.content || prompt.template
                })
            });

            const data = await response.json();
            if (data.status === 'success') {
                setOptimizedPrompt(data.data);
                setOptimizationHistory(prev => [data.data, ...prev]);
            }
        } catch (error) {
            console.error('Failed to optimize prompt:', error);
        } finally {
            setIsOptimizing(false);
        }
    };

    const testPrompt = async () => {
        if (!selectedModel || !prompt) return;

        try {
            const response = await fetch('/api/prompt-engineering/test', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    prompt_id: promptId,
                    model_id: selectedModel.id,
                    prompt_content: prompt.content || prompt.template
                })
            });

            const data = await response.json();
            if (data.status === 'success') {
                // Handle test results
                console.log('Test results:', data.data);
            }
        } catch (error) {
            console.error('Failed to test prompt:', error);
        }
    };

    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text);
    };

    const getPromptIcon = (type) => {
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

    const getPromptTypeLabel = (type) => {
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

    if (isLoading) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Prompt Details" />
                <div className="flex items-center justify-center h-64">
                    <Loader2 className="w-8 h-8 animate-spin" />
                </div>
            </AuthenticatedLayout>
        );
    }

    if (!prompt) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Prompt Not Found" />
                <div className="text-center py-8">
                    <AlertCircle className="w-12 h-12 mx-auto mb-4 text-muted-foreground" />
                    <h2 className="text-xl font-semibold text-foreground mb-2">Prompt Not Found</h2>
                    <p className="text-muted-foreground mb-4">The prompt you're looking for doesn't exist.</p>
                    <Link href="/prompt-engineering">
                        <Button>
                            <ArrowLeft className="w-4 h-4 mr-2" />
                            Back to Prompt Engineering
                        </Button>
                    </Link>
                </div>
            </AuthenticatedLayout>
        );
    }

    const IconComponent = getPromptIcon(prompt.type);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${prompt.name} - Prompt Details`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/prompt-engineering">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{prompt.name}</h1>
                            <p className="text-muted-foreground">Prompt Details & Optimization</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline">
                            <Edit className="w-4 h-4 mr-2" />
                            Edit
                        </Button>
                        <Button variant="outline">
                            <Share2 className="w-4 h-4 mr-2" />
                            Share
                        </Button>
                    </div>
                </div>

                {/* Tabs */}
                <div className="flex space-x-1 bg-muted p-1 rounded-lg">
                    <Button
                        variant={activeTab === 'details' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('details')}
                        className="flex-1"
                    >
                        <Eye className="w-4 h-4 mr-2" />
                        Details
                    </Button>
                    <Button
                        variant={activeTab === 'optimize' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('optimize')}
                        className="flex-1"
                    >
                        <Sparkles className="w-4 h-4 mr-2" />
                        Optimize
                    </Button>
                    <Button
                        variant={activeTab === 'test' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('test')}
                        className="flex-1"
                    >
                        <TestTube2 className="w-4 h-4 mr-2" />
                        Test
                    </Button>
                    <Button
                        variant={activeTab === 'analytics' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('analytics')}
                        className="flex-1"
                    >
                        <BarChart className="w-4 h-4 mr-2" />
                        Analytics
                    </Button>
                </div>

                {/* Details Tab */}
                {activeTab === 'details' && (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Content */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Prompt Content */}
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground flex items-center">
                                        <IconComponent className="w-5 h-5 mr-2" />
                                        Prompt Content
                                    </CardTitle>
                                    <CardDescription className="text-muted-foreground">
                                        The actual prompt template or content
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="bg-muted rounded-lg p-4 font-mono text-sm whitespace-pre-wrap">
                                        {prompt.content || prompt.template}
                                    </div>
                                    <div className="flex items-center justify-between mt-4">
                                        <div className="flex items-center space-x-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => copyToClipboard(prompt.content || prompt.template)}
                                            >
                                                <Copy className="w-4 h-4 mr-2" />
                                                Copy
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <Download className="w-4 h-4 mr-2" />
                                                Export
                                            </Button>
                                        </div>
                                        <div className="text-sm text-muted-foreground">
                                            {prompt.content ? `${prompt.content.length} characters` : `${prompt.template.length} characters`}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Description */}
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Description</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-foreground">{prompt.description}</p>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Prompt Info */}
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Prompt Information</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <label className="text-sm font-medium text-foreground">Type</label>
                                        <div className="flex items-center space-x-2 mt-1">
                                            <IconComponent className="w-4 h-4 text-primary" />
                                            <Badge variant="secondary">{getPromptTypeLabel(prompt.type)}</Badge>
                                        </div>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-foreground">Status</label>
                                        <div className="mt-1">
                                            <Badge variant={prompt.is_active ? 'default' : 'secondary'}>
                                                {prompt.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-foreground">Created</label>
                                        <p className="text-sm text-muted-foreground mt-1">
                                            {new Date(prompt.created_at).toLocaleDateString()}
                                        </p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-foreground">Last Updated</label>
                                        <p className="text-sm text-muted-foreground mt-1">
                                            {new Date(prompt.updated_at).toLocaleDateString()}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Quick Actions */}
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Quick Actions</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    <Button className="w-full" onClick={() => setActiveTab('optimize')}>
                                        <Sparkles className="w-4 h-4 mr-2" />
                                        Optimize Prompt
                                    </Button>
                                    <Button variant="outline" className="w-full" onClick={() => setActiveTab('test')}>
                                        <TestTube2 className="w-4 h-4 mr-2" />
                                        Test Prompt
                                    </Button>
                                    <Button variant="outline" className="w-full">
                                        <History className="w-4 h-4 mr-2" />
                                        View History
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                )}

                {/* Optimize Tab */}
                {activeTab === 'optimize' && (
                    <div className="space-y-6">
                        {/* Optimization Controls */}
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground flex items-center">
                                    <Sparkles className="w-5 h-5 mr-2" />
                                    AI-Powered Optimization
                                </CardTitle>
                                <CardDescription className="text-muted-foreground">
                                    Use AI models to optimize your prompt for better results
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <div>
                                        <label className="text-sm font-medium text-foreground mb-2 block">AI Model</label>
                                        <select
                                            value={selectedModel?.id || ''}
                                            onChange={(e) => {
                                                const model = models.find(m => m.id == e.target.value);
                                                setSelectedModel(model);
                                            }}
                                            className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            {models.map((model) => (
                                                <option key={model.id} value={model.id}>
                                                    {model.name} ({model.provider_name})
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-foreground mb-2 block">Optimization Type</label>
                                        <select
                                            value={optimizationType}
                                            onChange={(e) => setOptimizationType(e.target.value)}
                                            className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="effectiveness">Effectiveness</option>
                                            <option value="clarity">Clarity</option>
                                            <option value="conciseness">Conciseness</option>
                                            <option value="creativity">Creativity</option>
                                            <option value="structure">Structure</option>
                                        </select>
                                    </div>
                                    <div className="flex items-end">
                                        <Button
                                            onClick={optimizePrompt}
                                            disabled={!selectedModel || isOptimizing}
                                            className="w-full"
                                        >
                                            {isOptimizing ? (
                                                <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                            ) : (
                                                <Zap className="w-4 h-4 mr-2" />
                                            )}
                                            {isOptimizing ? 'Optimizing...' : 'Optimize Prompt'}
                                        </Button>
                                    </div>
                                </div>

                                {/* Optimization Results */}
                                {optimizedPrompt && (
                                    <div className="space-y-4">
                                        <div className="flex items-center justify-between">
                                            <h3 className="text-lg font-semibold text-foreground">Optimization Results</h3>
                                            <Badge variant="default" className="bg-green-500">
                                                <CheckCircle className="w-3 h-3 mr-1" />
                                                Optimized
                                            </Badge>
                                        </div>

                                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            {/* Original */}
                                            <div>
                                                <h4 className="text-sm font-medium text-foreground mb-2">Original Prompt</h4>
                                                <div className="bg-muted rounded-lg p-3 font-mono text-sm whitespace-pre-wrap">
                                                    {prompt.content || prompt.template}
                                                </div>
                                            </div>

                                            {/* Optimized */}
                                            <div>
                                                <h4 className="text-sm font-medium text-foreground mb-2">Optimized Prompt</h4>
                                                <div className="bg-green-50 border border-green-200 rounded-lg p-3 font-mono text-sm whitespace-pre-wrap">
                                                    {optimizedPrompt.optimized}
                                                </div>
                                            </div>
                                        </div>

                                        {/* Improvements */}
                                        <div>
                                            <h4 className="text-sm font-medium text-foreground mb-2">Improvements Made</h4>
                                            <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                                <p className="text-sm text-blue-800">{optimizedPrompt.improvements[optimizationType]}</p>
                                            </div>
                                        </div>

                                        <div className="flex items-center space-x-2">
                                            <Button
                                                variant="outline"
                                                onClick={() => copyToClipboard(optimizedPrompt.optimized)}
                                            >
                                                <Copy className="w-4 h-4 mr-2" />
                                                Copy Optimized
                                            </Button>
                                            <Button variant="outline">
                                                <Save className="w-4 h-4 mr-2" />
                                                Save as New
                                            </Button>
                                            <Button variant="outline">
                                                <RefreshCw className="w-4 h-4 mr-2" />
                                                Try Again
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Optimization History */}
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-foreground flex items-center">
                                        <History className="w-5 h-5 mr-2" />
                                        Optimization History
                                    </CardTitle>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => setShowOptimizationHistory(!showOptimizationHistory)}
                                    >
                                        {showOptimizationHistory ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
                                    </Button>
                                </div>
                            </CardHeader>
                            {showOptimizationHistory && (
                                <CardContent>
                                    {optimizationHistory.length > 0 ? (
                                        <div className="space-y-3">
                                            {optimizationHistory.map((optimization, index) => (
                                                <div key={index} className="border border-border rounded-lg p-3">
                                                    <div className="flex items-center justify-between mb-2">
                                                        <span className="text-sm font-medium text-foreground">
                                                            {optimization.optimization_type} Optimization
                                                        </span>
                                                        <span className="text-xs text-muted-foreground">
                                                            {new Date(optimization.created_at).toLocaleString()}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm text-muted-foreground">
                                                        {optimization.improvements[optimization.optimization_type]}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="text-muted-foreground text-center py-4">
                                            No optimization history yet
                                        </p>
                                    )}
                                </CardContent>
                            )}
                        </Card>
                    </div>
                )}

                {/* Test Tab */}
                {activeTab === 'test' && (
                    <div className="space-y-6">
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground flex items-center">
                                    <TestTube2 className="w-5 h-5 mr-2" />
                                    Test Prompt
                                </CardTitle>
                                <CardDescription className="text-muted-foreground">
                                    Test your prompt with different AI models and parameters
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="text-center py-8 text-muted-foreground">
                                    <TestTube2 className="w-12 h-12 mx-auto mb-4 opacity-50" />
                                    <p>Testing functionality coming soon</p>
                                    <p className="text-sm">Test your prompts with different models and parameters</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Analytics Tab */}
                {activeTab === 'analytics' && (
                    <div className="space-y-6">
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground flex items-center">
                                    <BarChart className="w-5 h-5 mr-2" />
                                    Prompt Analytics
                                </CardTitle>
                                <CardDescription className="text-muted-foreground">
                                    Track performance and usage metrics
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="text-center py-8 text-muted-foreground">
                                    <BarChart className="w-12 h-12 mx-auto mb-4 opacity-50" />
                                    <p>Analytics coming soon</p>
                                    <p className="text-sm">Track prompt performance and usage metrics</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
