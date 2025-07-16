import React from 'react';
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
    Code
} from 'lucide-react';

export default function Playground({ auth }) {
    const promptTemplates = [
        {
            id: 1,
            name: 'Proposal Generator',
            description: 'Generate professional business proposals',
            category: 'Business',
            status: 'active',
            usage: '1,234 times',
            lastUpdated: '3 days ago',
            icon: FileText
        },
        {
            id: 2,
            name: 'Content Writer',
            description: 'Create engaging blog posts and articles',
            category: 'Content',
            status: 'active',
            usage: '856 times',
            lastUpdated: '1 week ago',
            icon: MessageSquare
        },
        {
            id: 3,
            name: 'Code Assistant',
            description: 'Help with programming and debugging',
            category: 'Development',
            status: 'draft',
            usage: '0 times',
            lastUpdated: '2 days ago',
            icon: Code
        }
    ];

    const aiModels = [
        {
            id: 1,
            name: 'GPT-4',
            provider: 'OpenAI',
            status: 'active',
            usage: '85%',
            cost: '$1,234.56'
        },
        {
            id: 2,
            name: 'Claude-3',
            provider: 'Anthropic',
            status: 'active',
            usage: '62%',
            cost: '$856.78'
        },
        {
            id: 3,
            name: 'Gemini Pro',
            provider: 'Google',
            status: 'inactive',
            usage: '0%',
            cost: '$0.00'
        }
    ];

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
                        <Button variant="outline">
                            <TestTube className="w-4 h-4 mr-2" />
                            Test Connection
                        </Button>
                        <Button>
                            <Play className="w-4 h-4 mr-2" />
                            Run Test
                        </Button>
                    </div>
                </div>

                {/* Main Playground Area */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left Panel - Prompt Templates */}
                    <div className="lg:col-span-1">
                        <Card className="bg-card border-border">
                            <CardHeader>
                                <CardTitle className="text-foreground">Prompt Templates</CardTitle>
                                <CardDescription className="text-muted-foreground">Manage your AI prompt templates</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {promptTemplates.map((template) => (
                                        <div key={template.id} className="flex items-center justify-between p-3 border border-border rounded-lg hover:bg-muted/50 cursor-pointer transition-colors">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-2">
                                                    <template.icon className="w-4 h-4 text-primary" />
                                                    <h3 className="font-medium text-foreground">{template.name}</h3>
                                                    <Badge variant="secondary" className="text-xs">
                                                        {template.category}
                                                    </Badge>
                                                </div>
                                                <p className="text-sm text-muted-foreground mt-1">{template.description}</p>
                                                <p className="text-xs text-muted-foreground mt-1">Used {template.usage} • Updated {template.lastUpdated}</p>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <div className={`w-2 h-2 rounded-full ${getStatusColor(template.status)}`}></div>
                                                <Button variant="ghost" size="sm">
                                                    <Play className="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
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
                                        <CardTitle className="text-foreground">Chat Interface</CardTitle>
                                        <CardDescription className="text-muted-foreground">Test your AI models and prompts</CardDescription>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Button variant="outline" size="sm">
                                            <Settings className="w-4 h-4" />
                                        </Button>
                                        <Button variant="outline" size="sm">
                                            <RefreshCw className="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="flex-1 flex flex-col">
                                {/* Chat Messages Area */}
                                <div className="flex-1 bg-muted rounded-lg p-4 mb-4 overflow-y-auto">
                                    <div className="space-y-4">
                                        <div className="flex items-start space-x-3">
                                            <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                                                <span className="text-xs text-primary-foreground">AI</span>
                                            </div>
                                            <div className="flex-1">
                                                <p className="text-sm text-foreground">Hello! I'm your AI assistant. How can I help you today?</p>
                                                <p className="text-xs text-muted-foreground mt-1">2 minutes ago</p>
                                            </div>
                                        </div>
                                        <div className="flex items-start space-x-3">
                                            <div className="w-8 h-8 rounded-full bg-muted-foreground flex items-center justify-center">
                                                <span className="text-xs text-muted">U</span>
                                            </div>
                                            <div className="flex-1">
                                                <p className="text-sm text-foreground">Can you help me write a business proposal?</p>
                                                <p className="text-xs text-muted-foreground mt-1">1 minute ago</p>
                                            </div>
                                        </div>
                                        <div className="flex items-start space-x-3">
                                            <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                                                <span className="text-xs text-primary-foreground">AI</span>
                                            </div>
                                            <div className="flex-1">
                                                <p className="text-sm text-foreground">I'd be happy to help you write a business proposal! I can use the Proposal Generator template to create a professional document. What type of business proposal do you need?</p>
                                                <p className="text-xs text-muted-foreground mt-1">Just now</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Input Area */}
                                <div className="flex space-x-2">
                                    <div className="flex-1 relative">
                                        <input
                                            type="text"
                                            placeholder="Type your message here..."
                                            className="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        />
                                    </div>
                                    <Button>
                                        <Send className="w-4 h-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* AI Models Panel */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-foreground">AI Models</CardTitle>
                                <CardDescription className="text-muted-foreground">Configure your AI model integrations</CardDescription>
                            </div>
                            <Button variant="outline" size="sm">
                                <Plus className="w-4 h-4 mr-2" />
                                Add Model
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {aiModels.map((model) => (
                                <div key={model.id} className="flex items-center justify-between p-4 border border-border rounded-lg">
                                    <div className="flex items-center space-x-3">
                                        <Brain className="w-5 h-5 text-primary" />
                                        <div>
                                            <h3 className="font-medium text-foreground">{model.name}</h3>
                                            <p className="text-sm text-muted-foreground">{model.provider}</p>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <div className="flex items-center">
                                            <div className={`w-2 h-2 rounded-full ${getStatusColor(model.status)} mr-2`}></div>
                                            <span className="text-sm">{getStatusText(model.status)}</span>
                                        </div>
                                        <p className="text-sm text-foreground">Usage: {model.usage}</p>
                                        <p className="text-sm text-muted-foreground">Cost: {model.cost}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
