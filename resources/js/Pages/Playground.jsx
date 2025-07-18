import React, { useState, useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Brain,
    Play,
    Settings,
    Save,
    TestTube,
    Zap,
    Shield,
    Database,
    Key,
    Globe,
    BarChart3,
    AlertTriangle,
    CheckCircle,
    Clock,
    RefreshCw,
    Send,
    Pause,
    Trash2,
    Edit,
    Copy,
    Eye,
    Plus,
    MoreVertical,
    MessageSquare,
    FileText,
    Code,
    User,
    Sparkles,
    Target,
    Layers,
    Cpu,
    Activity,
    TrendingUp,
    Loader2,
    ChevronDown,
    ChevronUp,
    Sliders,
    BookOpen,
    Palette,
    Lightbulb,
    Code2,
    Variable,
    FileCode
} from 'lucide-react';

export default function Playground({ auth }) {
    const [selectedModel, setSelectedModel] = useState(null);
    const [selectedTemplate, setSelectedTemplate] = useState(null);
    const [promptInput, setPromptInput] = useState('');
    const [messages, setMessages] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [models, setModels] = useState([]);
    const [templates, setTemplates] = useState([]);
    const [modelParameters, setModelParameters] = useState({
        temperature: 0.7,
        maxTokens: 2000,
        topP: 1.0,
        frequencyPenalty: 0.0,
        presencePenalty: 0.0
    });
    const [showParameters, setShowParameters] = useState(false);
    const [testResults, setTestResults] = useState([]);
    const [activeTab, setActiveTab] = useState('chat');
    const [templateFilter, setTemplateFilter] = useState('all');

    // Request Interface State
    const [selectedRequestTemplate, setSelectedRequestTemplate] = useState(null);
    const [templateVariables, setTemplateVariables] = useState({});
    const [renderedPrompt, setRenderedPrompt] = useState('');
    const [requestResponse, setRequestResponse] = useState(null);
    const [isRequestLoading, setIsRequestLoading] = useState(false);

    const messagesEndRef = useRef(null);

    // Load models and templates on component mount
    useEffect(() => {
        loadModels();
        loadTemplates();
    }, []);

    // Auto-scroll to bottom of messages
    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    // Extract variables from template when selected
    useEffect(() => {
        if (selectedRequestTemplate) {
            const variables = extractVariablesFromTemplate(selectedRequestTemplate.template);
            const initialVariables = {};
            variables.forEach(variable => {
                initialVariables[variable] = '';
            });
            setTemplateVariables(initialVariables);
            setRenderedPrompt(selectedRequestTemplate.template);
        }
    }, [selectedRequestTemplate]);

    // Update rendered prompt when variables change
    useEffect(() => {
        if (selectedRequestTemplate) {
            let prompt = selectedRequestTemplate.template;
            Object.keys(templateVariables).forEach(variable => {
                const regex = new RegExp(`{${variable}}`, 'g');
                prompt = prompt.replace(regex, templateVariables[variable] || `{${variable}}`);
            });
            setRenderedPrompt(prompt);
        }
    }, [templateVariables, selectedRequestTemplate]);

    const extractVariablesFromTemplate = (template) => {
        const variables = [];
        const regex = /{(\w+)}/g;
        let match;
        while ((match = regex.exec(template)) !== null) {
            if (!variables.includes(match[1])) {
                variables.push(match[1]);
            }
        }
        return variables;
    };

    const getCsrfToken = () => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            console.error('CSRF token not found in meta tag');
            return null;
        }
        return token;
    };

    const refreshCsrfToken = async () => {
        try {
            const response = await fetch('/api/csrf-token', {
                method: 'GET',
                credentials: 'include',
            });

            if (response.ok) {
                const data = await response.json();
                // Update the meta tag with new token
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag && data.token) {
                    metaTag.setAttribute('content', data.token);
                    return data.token;
                }
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
        }
        return null;
    };

    const loadModels = async () => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            const response = await fetch('/api/ai-settings/models', {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

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

    const loadTemplates = async () => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            const response = await fetch('/api/playground/templates', {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data.status === 'success') {
                setTemplates(data.data);
            }
        } catch (error) {
            console.error('Failed to load templates:', error);
        }
    };

    const sendMessage = async () => {
        if (!promptInput.trim() || !selectedModel) return;

        const userMessage = {
            id: Date.now(),
            role: 'user',
            content: promptInput,
            timestamp: new Date().toISOString()
        };

        setMessages(prev => [...prev, userMessage]);
        setPromptInput('');
        setIsLoading(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }

            const response = await fetch('/api/playground/generate', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    model_id: selectedModel.id,
                    prompt: promptInput,
                    template_id: selectedTemplate?.id,
                    parameters: modelParameters
                })
            });

            if (!response.ok) {
                if (response.status === 419) {
                    throw new Error('CSRF token mismatch. Please refresh the page and try again.');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.status === 'success') {
                const aiMessage = {
                    id: Date.now() + 1,
                    role: 'assistant',
                    content: data.data.response,
                    timestamp: new Date().toISOString(),
                    model: selectedModel.name,
                    tokens: data.data.tokens,
                    cost: data.data.cost
                };
                setMessages(prev => [...prev, aiMessage]);
            } else {
                const errorMessage = {
                    id: Date.now() + 1,
                    role: 'error',
                    content: data.message || 'An error occurred',
                    timestamp: new Date().toISOString()
                };
                setMessages(prev => [...prev, errorMessage]);
            }
        } catch (error) {
            console.error('Send message failed:', error);
            const errorMessage = {
                id: Date.now() + 1,
                role: 'error',
                content: error.message || 'Failed to generate response',
                timestamp: new Date().toISOString()
            };
            setMessages(prev => [...prev, errorMessage]);
        } finally {
            setIsLoading(false);
        }
    };

    const loadTemplate = (template) => {
        setSelectedTemplate(template);
        setPromptInput(template.template);
    };

    const testTemplate = async (template) => {
        setIsLoading(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            const response = await fetch('/api/playground/test-template', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    template_id: template.id,
                    model_id: selectedModel?.id,
                    parameters: modelParameters
                })
            });

            if (!response.ok) {
                if (response.status === 419) {
                    console.error('CSRF token mismatch. Please refresh the page and try again.');
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.status === 'success') {
                setTestResults(prev => [...prev, {
                    id: Date.now(),
                    template: template.name,
                    response: data.data.response,
                    tokens: data.data.tokens,
                    cost: data.data.cost,
                    timestamp: new Date().toISOString()
                }]);
            }
        } catch (error) {
            console.error('Template test failed:', error);
        } finally {
            setIsLoading(false);
        }
    };

        const testRequestTemplate = async () => {
        if (!selectedRequestTemplate || !selectedModel) return;

        setIsRequestLoading(true);
        setRequestResponse(null);

        try {
            let csrfToken = getCsrfToken();

            if (!csrfToken) {
                // Try to refresh the token
                csrfToken = await refreshCsrfToken();
                if (!csrfToken) {
                    throw new Error('CSRF token not found. Please refresh the page and try again.');
                }
            }

            const response = await fetch('/api/playground/generate', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    model_id: selectedModel.id,
                    prompt: renderedPrompt,
                    template_id: selectedRequestTemplate.id,
                    parameters: modelParameters
                })
            });

            if (!response.ok) {
                if (response.status === 419) {
                    // Try to refresh token and retry once
                    const newToken = await refreshCsrfToken();
                    if (newToken) {
                        const retryResponse = await fetch('/api/playground/generate', {
                            method: 'POST',
                            credentials: 'include',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': newToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                model_id: selectedModel.id,
                                prompt: renderedPrompt,
                                template_id: selectedRequestTemplate.id,
                                parameters: modelParameters
                            })
                        });

                        if (!retryResponse.ok) {
                            throw new Error(`HTTP error! status: ${retryResponse.status}`);
                        }

                        const retryData = await retryResponse.json();
                        if (retryData.status === 'success') {
                            setRequestResponse({
                                success: true,
                                data: {
                                    response: retryData.data.response,
                                    tokens: retryData.data.tokens,
                                    cost: retryData.data.cost,
                                    model: retryData.data.model,
                                    provider: retryData.data.provider,
                                    timestamp: new Date().toISOString()
                                }
                            });
                            return;
                        } else {
                            throw new Error(retryData.message || 'An error occurred');
                        }
                    } else {
                        throw new Error('CSRF token mismatch. Please refresh the page and try again.');
                    }
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.status === 'success') {
                setRequestResponse({
                    success: true,
                    data: {
                        response: data.data.response,
                        tokens: data.data.tokens,
                        cost: data.data.cost,
                        model: data.data.model,
                        provider: data.data.provider,
                        timestamp: new Date().toISOString()
                    }
                });
            } else {
                setRequestResponse({
                    success: false,
                    error: data.message || 'An error occurred'
                });
            }
        } catch (error) {
            console.error('Request failed:', error);
            setRequestResponse({
                success: false,
                error: error.message || 'Failed to generate response'
            });
        } finally {
            setIsRequestLoading(false);
        }
    };

    const clearChat = () => {
        setMessages([]);
    };

    const clearRequestInterface = () => {
        setSelectedRequestTemplate(null);
        setTemplateVariables({});
        setRenderedPrompt('');
        setRequestResponse(null);
    };

    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text);
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'active': return 'bg-green-500';
            case 'inactive': return 'bg-gray-500';
            case 'draft': return 'bg-yellow-500';
            case 'error': return 'bg-red-500';
            default: return 'bg-gray-500';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'active': return 'Active';
            case 'inactive': return 'Inactive';
            case 'draft': return 'Draft';
            case 'error': return 'Error';
            default: return 'Unknown';
        }
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

    const filteredTemplates = templates.filter(template =>
        templateFilter === 'all' || template.type === templateFilter
    );

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="AI Playground" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">AI Playground</h1>
                        <p className="text-muted-foreground">Test and experiment with AI models and prompt templates</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" onClick={loadModels}>
                            <RefreshCw className="w-4 h-4 mr-2" />
                            Refresh Models
                        </Button>
                        <Button variant="outline" onClick={clearChat}>
                            <Trash2 className="w-4 h-4 mr-2" />
                            Clear Chat
                        </Button>
                        <Button
                            variant="outline"
                            onClick={async () => {
                                const newToken = await refreshCsrfToken();
                                if (newToken) {
                                    console.log('CSRF token refreshed successfully');
                                } else {
                                    console.error('Failed to refresh CSRF token');
                                }
                            }}
                            title="Refresh CSRF Token"
                        >
                            <Shield className="w-4 h-4 mr-2" />
                            Refresh Token
                        </Button>
                    </div>
                </div>

                {/* Model Selection */}
                <Card className="bg-card border-border">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-foreground flex items-center text-lg">
                            <Cpu className="w-4 h-4 mr-2" />
                            AI Model Configuration
                        </CardTitle>
                        <CardDescription className="text-muted-foreground text-sm">
                            Select an AI model and configure parameters
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="pt-0">
                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                            {models.map((model) => (
                                <div
                                    key={model.id}
                                    className={`p-3 border rounded-md cursor-pointer transition-all ${
                                        selectedModel?.id === model.id
                                            ? 'border-primary bg-primary/5'
                                            : 'border-border hover:border-primary/50'
                                    }`}
                                    onClick={() => setSelectedModel(model)}
                                >
                                    <div className="flex items-center justify-between mb-1">
                                        <div className="flex items-center space-x-1">
                                            <Brain className="w-3 h-3 text-primary" />
                                            <span className="font-medium text-foreground text-sm">{model.name}</span>
                                        </div>
                                        <div className={`w-1.5 h-1.5 rounded-full ${getStatusColor(model.status)}`}></div>
                                    </div>
                                    <p className="text-xs text-muted-foreground mb-1">{model.provider_name}</p>
                                    <div className="flex items-center justify-between text-xs text-muted-foreground">
                                        <span>Used {model.usage}</span>
                                        <span>{model.cost}</span>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Model Parameters */}
                        <div className="mt-4">
                            <Button
                                variant="outline"
                                onClick={() => setShowParameters(!showParameters)}
                                className="w-full text-sm py-2"
                            >
                                <Sliders className="w-3 h-3 mr-2" />
                                Model Parameters
                                {showParameters ? <ChevronUp className="w-3 h-3 ml-2" /> : <ChevronDown className="w-3 h-3 ml-2" />}
                            </Button>

                            {showParameters && (
                                <div className="mt-3 p-3 border border-border rounded-md bg-muted/30">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label className="text-xs font-medium text-foreground">Temperature</label>
                                            <input
                                                type="range"
                                                min="0"
                                                max="2"
                                                step="0.1"
                                                value={modelParameters.temperature}
                                                onChange={(e) => setModelParameters(prev => ({ ...prev, temperature: parseFloat(e.target.value) }))}
                                                className="w-full"
                                            />
                                            <span className="text-xs text-muted-foreground">{modelParameters.temperature}</span>
                                        </div>
                                        <div>
                                            <label className="text-xs font-medium text-foreground">Max Tokens</label>
                                            <input
                                                type="range"
                                                min="100"
                                                max="8000"
                                                step="100"
                                                value={modelParameters.maxTokens}
                                                onChange={(e) => setModelParameters(prev => ({ ...prev, maxTokens: parseInt(e.target.value) }))}
                                                className="w-full"
                                            />
                                            <span className="text-xs text-muted-foreground">{modelParameters.maxTokens}</span>
                                        </div>
                                        <div>
                                            <label className="text-xs font-medium text-foreground">Top P</label>
                                            <input
                                                type="range"
                                                min="0"
                                                max="1"
                                                step="0.1"
                                                value={modelParameters.topP}
                                                onChange={(e) => setModelParameters(prev => ({ ...prev, topP: parseFloat(e.target.value) }))}
                                                className="w-full"
                                            />
                                            <span className="text-xs text-muted-foreground">{modelParameters.topP}</span>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Tab Navigation */}
                <div className="flex space-x-1 bg-muted p-1 rounded-lg">
                    <Button
                        variant={activeTab === 'chat' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('chat')}
                        className="flex-1"
                    >
                        <MessageSquare className="w-4 h-4 mr-2" />
                        Chat Interface
                    </Button>
                    <Button
                        variant={activeTab === 'request' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setActiveTab('request')}
                        className="flex-1"
                    >
                        <Code2 className="w-4 h-4 mr-2" />
                        Request Interface
                    </Button>
                </div>

                {/* Main Playground Area */}
                {activeTab === 'chat' && (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Left Panel - Prompt Templates */}
                        <div className="lg:col-span-1">
                            <Card className="bg-card border-border">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-foreground flex items-center text-lg">
                                        <BookOpen className="w-4 h-4 mr-2" />
                                        Prompt Templates
                                    </CardTitle>
                                    <CardDescription className="text-muted-foreground text-sm">
                                        Select and test prompt templates
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="pt-0">
                                    {/* Filter */}
                                    <div className="mb-3">
                                        <select
                                            value={templateFilter}
                                            onChange={(e) => setTemplateFilter(e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="all">All Categories</option>
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

                                    {/* Templates List */}
                                    <div className="space-y-2 max-h-96 overflow-y-auto">
                                        {filteredTemplates.map((template) => {
                                            const IconComponent = getTemplateIcon(template.type);
                                            return (
                                                <div key={template.id} className="border border-border rounded-md p-2 hover:bg-muted/50 transition-colors">
                                                    <div className="flex items-center justify-between mb-1">
                                                        <div className="flex items-center space-x-1">
                                                            <IconComponent className="w-3 h-3 text-primary" />
                                                            <h3 className="font-medium text-foreground text-sm">{template.name}</h3>
                                                            <Badge variant="secondary" className="text-xs">
                                                                {getTemplateTypeLabel(template.type)}
                                                            </Badge>
                                                        </div>
                                                        <div className="flex items-center space-x-1">
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() => loadTemplate(template)}
                                                                title="Load Template"
                                                                className="h-6 w-6 p-0"
                                                            >
                                                                <Eye className="w-3 h-3" />
                                                            </Button>
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() => testTemplate(template)}
                                                                disabled={!selectedModel || isLoading}
                                                                title="Test Template"
                                                                className="h-6 w-6 p-0"
                                                            >
                                                                {isLoading ? <Loader2 className="w-3 h-3 animate-spin" /> : <Play className="w-3 h-3" />}
                                                            </Button>
                                                        </div>
                                                    </div>
                                                    <p className="text-xs text-muted-foreground mb-1">{template.description}</p>
                                                    <div className="flex items-center justify-between text-xs text-muted-foreground">
                                                        <span>{template.is_active ? 'Active' : 'Inactive'}</span>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Center Panel - Chat Interface */}
                        <div className="lg:col-span-2">
                            <Card className="bg-card border-border h-[600px] flex flex-col">
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <CardTitle className="text-foreground flex items-center">
                                                <MessageSquare className="w-5 h-5 mr-2" />
                                                Chat Interface
                                            </CardTitle>
                                            <CardDescription className="text-muted-foreground">
                                                {selectedModel ? `Using ${selectedModel.name} (${selectedModel.provider_name})` : 'Select a model to start'}
                                            </CardDescription>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Button variant="outline" size="sm" onClick={clearChat}>
                                                <Trash2 className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="flex-1 flex flex-col">
                                    {/* Chat Messages Area */}
                                    <div className="flex-1 bg-muted rounded-lg p-4 mb-4 overflow-y-auto">
                                        {messages.length === 0 ? (
                                            <div className="flex items-center justify-center h-full text-muted-foreground">
                                                <div className="text-center">
                                                    <Sparkles className="w-12 h-12 mx-auto mb-4 opacity-50" />
                                                    <p>Start a conversation with your AI assistant</p>
                                                    <p className="text-sm">Select a model and type your message below</p>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="space-y-4">
                                                {messages.map((message) => (
                                                    <div key={message.id} className="flex items-start space-x-3">
                                                        <div className={`w-8 h-8 rounded-full flex items-center justify-center ${
                                                            message.role === 'user'
                                                                ? 'bg-primary text-primary-foreground'
                                                                : message.role === 'error'
                                                                ? 'bg-red-500 text-white'
                                                                : 'bg-muted-foreground text-muted'
                                                        }`}>
                                                            <span className="text-xs">
                                                                {message.role === 'user' ? 'U' : message.role === 'error' ? '!' : 'AI'}
                                                            </span>
                                                        </div>
                                                        <div className="flex-1">
                                                            <div className="flex items-center justify-between mb-1">
                                                                <p className="text-sm text-foreground">{message.content}</p>
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => copyToClipboard(message.content)}
                                                                    className="ml-2"
                                                                >
                                                                    <Copy className="w-3 h-3" />
                                                                </Button>
                                                            </div>
                                                            <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                                                                <span>{new Date(message.timestamp).toLocaleTimeString()}</span>
                                                                {message.model && <span>• {message.model}</span>}
                                                                {message.tokens && <span>• {message.tokens} tokens</span>}
                                                                {message.cost && <span>• {message.cost}</span>}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                                {isLoading && (
                                                    <div className="flex items-start space-x-3">
                                                        <div className="w-8 h-8 rounded-full bg-muted-foreground flex items-center justify-center">
                                                            <span className="text-xs text-muted">AI</span>
                                                        </div>
                                                        <div className="flex-1">
                                                            <div className="flex items-center space-x-2">
                                                                <Loader2 className="w-4 h-4 animate-spin" />
                                                                <span className="text-sm text-muted-foreground">Generating response...</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                                <div ref={messagesEndRef} />
                                            </div>
                                        )}
                                    </div>

                                    {/* Input Area */}
                                    <div className="flex space-x-2">
                                        <div className="flex-1 relative">
                                            <input
                                                type="text"
                                                value={promptInput}
                                                onChange={(e) => setPromptInput(e.target.value)}
                                                onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
                                                placeholder={selectedModel ? "Type your message here..." : "Select a model first..."}
                                                disabled={!selectedModel || isLoading}
                                                className="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50"
                                            />
                                        </div>
                                        <Button
                                            onClick={sendMessage}
                                            disabled={!selectedModel || !promptInput.trim() || isLoading}
                                        >
                                            {isLoading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                )}

                                {/* Request Interface */}
                {activeTab === 'request' && (
                    <div className="space-y-6">
                        {/* Top Row - Template Selection/Variables and Rendered Prompt */}
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Left Panel - Template Selection and Variables */}
                            <div className="space-y-6 h-[600px]">
                                {/* Template Selection */}
                                <Card className="bg-card border-border h-[280px] flex flex-col">
                                    <CardHeader className="pb-3 flex-shrink-0">
                                        <CardTitle className="text-foreground flex items-center text-lg">
                                            <FileCode className="w-4 h-4 mr-2" />
                                            Template Selection
                                        </CardTitle>
                                        <CardDescription className="text-muted-foreground text-sm">
                                            Select a prompt template to test
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="pt-0 flex-1 flex flex-col overflow-hidden">
                                        <Select
                                            value={selectedRequestTemplate?.id?.toString() || ''}
                                            onValueChange={(value) => {
                                                const template = templates.find(t => t.id.toString() === value);
                                                setSelectedRequestTemplate(template);
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a template..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {filteredTemplates.map((template) => {
                                                    const IconComponent = getTemplateIcon(template.type);
                                                    return (
                                                        <SelectItem key={template.id} value={template.id.toString()}>
                                                            <div className="flex items-center space-x-2">
                                                                <IconComponent className="w-4 h-4" />
                                                                <span>{template.name}</span>
                                                                <Badge variant="secondary" className="text-xs">
                                                                    {getTemplateTypeLabel(template.type)}
                                                                </Badge>
                                                            </div>
                                                        </SelectItem>
                                                    );
                                                })}
                                            </SelectContent>
                                        </Select>

                                        {selectedRequestTemplate && (
                                            <div className="mt-4 p-3 bg-muted/30 rounded-md flex-shrink-0">
                                                <h4 className="font-medium text-foreground mb-2">{selectedRequestTemplate.name}</h4>
                                                <p className="text-sm text-muted-foreground mb-2">{selectedRequestTemplate.description}</p>
                                                <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                                                    <Variable className="w-3 h-3" />
                                                    <span>{Object.keys(templateVariables).length} variables</span>
                                                </div>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>

                                {/* Template Variables */}
                                {selectedRequestTemplate && Object.keys(templateVariables).length > 0 && (
                                    <Card className="bg-card border-border flex-1 flex flex-col">
                                        <CardHeader className="pb-3 flex-shrink-0">
                                            <CardTitle className="text-foreground flex items-center text-lg">
                                                <Variable className="w-4 h-4 mr-2" />
                                                Template Variables
                                            </CardTitle>
                                            <CardDescription className="text-muted-foreground text-sm">
                                                Fill in the template variables
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent className="pt-0 flex-1 overflow-y-auto">
                                            <div className="space-y-3">
                                                {Object.keys(templateVariables).map((variable) => (
                                                    <div key={variable}>
                                                        <Label htmlFor={variable} className="text-sm font-medium text-foreground">
                                                            {variable.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                                        </Label>
                                                        <Input
                                                            id={variable}
                                                            value={templateVariables[variable]}
                                                            onChange={(e) => setTemplateVariables(prev => ({
                                                                ...prev,
                                                                [variable]: e.target.value
                                                            }))}
                                                            placeholder={`Enter ${variable.replace(/_/g, ' ')}`}
                                                            className="mt-1"
                                                        />
                                                    </div>
                                                ))}
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}
                            </div>

                            {/* Right Panel - Rendered Prompt */}
                            <div className="h-[600px]">
                                <Card className="bg-card border-border h-full flex flex-col">
                                    <CardHeader className="pb-3 flex-shrink-0">
                                        <CardTitle className="text-foreground flex items-center text-lg">
                                            <Code2 className="w-4 h-4 mr-2" />
                                            Rendered Prompt
                                        </CardTitle>
                                        <CardDescription className="text-muted-foreground text-sm">
                                            Preview the prompt with filled variables
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="pt-0 flex-1 flex flex-col">
                                        <div className="relative flex-1">
                                            <Textarea
                                                value={renderedPrompt}
                                                readOnly
                                                className="font-mono text-sm bg-muted/30 h-full resize-none"
                                                placeholder="Select a template and fill in variables to see the rendered prompt..."
                                            />
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => copyToClipboard(renderedPrompt)}
                                                className="absolute top-2 right-2"
                                            >
                                                <Copy className="w-3 h-3" />
                                            </Button>
                                        </div>

                                        <div className="mt-4 flex space-x-2 flex-shrink-0">
                                            <Button
                                                onClick={testRequestTemplate}
                                                disabled={!selectedRequestTemplate || !selectedModel || isRequestLoading}
                                                className="flex-1"
                                            >
                                                {isRequestLoading ? (
                                                    <>
                                                        <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                                        Testing...
                                                    </>
                                                ) : (
                                                    <>
                                                        <Play className="w-4 h-4 mr-2" />
                                                        Test Prompt
                                                    </>
                                                )}
                                            </Button>
                                            <Button
                                                variant="outline"
                                                onClick={clearRequestInterface}
                                                disabled={isRequestLoading}
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>

                        {/* Bottom Row - API Response (Full Width) */}
                        {requestResponse && (
                            <Card className="bg-card border-border">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-foreground flex items-center text-lg">
                                        {requestResponse.success ? (
                                            <CheckCircle className="w-5 h-5 mr-2 text-green-500" />
                                        ) : (
                                            <AlertTriangle className="w-5 h-5 mr-2 text-red-500" />
                                        )}
                                        API Response
                                    </CardTitle>
                                    <CardDescription className="text-muted-foreground text-sm">
                                        {requestResponse.success ? 'Success response from AI model' : 'Error occurred during generation'}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="pt-0">
                                    {requestResponse.success ? (
                                        <div className="space-y-4">
                                            {/* Response Content */}
                                            <div>
                                                <Label className="text-sm font-medium text-foreground mb-2 block">Response</Label>
                                                <div className="relative">
                                                    <Textarea
                                                        value={requestResponse.data.response}
                                                        readOnly
                                                        rows={6}
                                                        className="font-mono text-sm bg-muted/30"
                                                    />
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => copyToClipboard(requestResponse.data.response)}
                                                        className="absolute top-2 right-2"
                                                    >
                                                        <Copy className="w-3 h-3" />
                                                    </Button>
                                                </div>
                                            </div>

                                            {/* Response Metadata */}
                                            <div className="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span className="text-muted-foreground">Model:</span>
                                                    <span className="ml-2 text-foreground">{requestResponse.data.model}</span>
                                                </div>
                                                <div>
                                                    <span className="text-muted-foreground">Provider:</span>
                                                    <span className="ml-2 text-foreground">{requestResponse.data.provider}</span>
                                                </div>
                                                <div>
                                                    <span className="text-muted-foreground">Tokens:</span>
                                                    <span className="ml-2 text-foreground">{requestResponse.data.tokens}</span>
                                                </div>
                                                <div>
                                                    <span className="text-muted-foreground">Cost:</span>
                                                    <span className="ml-2 text-foreground">{requestResponse.data.cost}</span>
                                                </div>
                                            </div>

                                            {/* JSON Response Format */}
                                            <div>
                                                <Label className="text-sm font-medium text-foreground mb-2 block">JSON Response Format</Label>
                                                <div className="relative">
                                                    <Textarea
                                                        value={JSON.stringify({
                                                            status: 'success',
                                                            data: {
                                                                response: requestResponse.data.response,
                                                                tokens: requestResponse.data.tokens,
                                                                cost: requestResponse.data.cost,
                                                                model: requestResponse.data.model,
                                                                provider: requestResponse.data.provider,
                                                                timestamp: requestResponse.data.timestamp
                                                            }
                                                        }, null, 2)}
                                                        readOnly
                                                        rows={8}
                                                        className="font-mono text-sm bg-muted/30"
                                                    />
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => copyToClipboard(JSON.stringify({
                                                            status: 'success',
                                                            data: {
                                                                response: requestResponse.data.response,
                                                                tokens: requestResponse.data.tokens,
                                                                cost: requestResponse.data.cost,
                                                                model: requestResponse.data.model,
                                                                provider: requestResponse.data.provider,
                                                                timestamp: requestResponse.data.timestamp
                                                            }
                                                        }, null, 2))}
                                                        className="absolute top-2 right-2"
                                                    >
                                                        <Copy className="w-3 h-3" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="p-4 bg-red-50 border border-red-200 rounded-md">
                                            <div className="flex items-center space-x-2">
                                                <AlertTriangle className="w-4 h-4 text-red-500" />
                                                <span className="text-red-700 font-medium">Error</span>
                                            </div>
                                            <p className="text-red-600 mt-2">{requestResponse.error}</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>
                )}

                {/* Test Results Panel */}
                {testResults.length > 0 && activeTab === 'chat' && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <CardTitle className="text-foreground flex items-center">
                                <TestTube className="w-5 h-5 mr-2" />
                                Template Test Results
                            </CardTitle>
                            <CardDescription className="text-muted-foreground">
                                Results from template testing
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {testResults.map((result) => (
                                    <div key={result.id} className="border border-border rounded-lg p-4">
                                        <div className="flex items-center justify-between mb-2">
                                            <h3 className="font-medium text-foreground">{result.template}</h3>
                                            <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                                                <span>{result.tokens} tokens</span>
                                                <span>•</span>
                                                <span>{result.cost}</span>
                                                <span>•</span>
                                                <span>{new Date(result.timestamp).toLocaleTimeString()}</span>
                                            </div>
                                        </div>
                                        <p className="text-sm text-foreground mb-2">{result.response}</p>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => copyToClipboard(result.response)}
                                        >
                                            <Copy className="w-3 h-3 mr-1" />
                                            Copy Response
                                        </Button>
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
