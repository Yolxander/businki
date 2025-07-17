import React, { useState, useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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
    Lightbulb
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
            const response = await fetch('/api/playground/generate', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    model_id: selectedModel.id,
                    prompt: promptInput,
                    template_id: selectedTemplate?.id,
                    parameters: modelParameters
                })
            });

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
            const errorMessage = {
                id: Date.now() + 1,
                role: 'error',
                content: 'Failed to generate response',
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
            const response = await fetch('/api/playground/test-template', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    template_id: template.id,
                    model_id: selectedModel?.id,
                    parameters: modelParameters
                })
            });

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

    const clearChat = () => {
        setMessages([]);
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

                {/* Main Playground Area */}
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

                {/* Test Results Panel */}
                {testResults.length > 0 && (
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
