import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Brain,
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
    Play,
    Pause,
    Trash2,
    Edit,
    Copy,
    Eye,
    Plus,
    MoreVertical
} from 'lucide-react';

export default function AISettings({ auth }) {
    const aiModels = [
        {
            id: 1,
            name: 'GPT-4',
            provider: 'OpenAI',
            status: 'active',
            apiKey: 'sk-...1234',
            usage: '85%',
            cost: '$1,234.56',
            lastUsed: '2 hours ago',
            requests: '45,234'
        },
        {
            id: 2,
            name: 'Claude-3',
            provider: 'Anthropic',
            status: 'active',
            apiKey: 'sk-ant-...5678',
            usage: '62%',
            cost: '$856.78',
            lastUsed: '1 day ago',
            requests: '23,456'
        },
        {
            id: 3,
            name: 'Gemini Pro',
            provider: 'Google',
            status: 'inactive',
            apiKey: 'AIza...9012',
            usage: '0%',
            cost: '$0.00',
            lastUsed: '1 week ago',
            requests: '1,234'
        }
    ];

    const promptTemplates = [
        {
            id: 1,
            name: 'Proposal Generator',
            description: 'Generate professional business proposals',
            category: 'Business',
            status: 'active',
            usage: '1,234 times',
            lastUpdated: '3 days ago'
        },
        {
            id: 2,
            name: 'Content Writer',
            description: 'Create engaging blog posts and articles',
            category: 'Content',
            status: 'active',
            usage: '856 times',
            lastUpdated: '1 week ago'
        },
        {
            id: 3,
            name: 'Code Assistant',
            description: 'Help with programming and debugging',
            category: 'Development',
            status: 'draft',
            usage: '0 times',
            lastUpdated: '2 days ago'
        }
    ];

    const aiConfigurations = [
        {
            id: 1,
            name: 'Temperature',
            value: '0.7',
            description: 'Controls randomness in AI responses',
            type: 'slider',
            min: 0,
            max: 1,
            step: 0.1
        },
        {
            id: 2,
            name: 'Max Tokens',
            value: '2048',
            description: 'Maximum length of AI responses',
            type: 'input',
            unit: 'tokens'
        },
        {
            id: 3,
            name: 'Top P',
            value: '0.9',
            description: 'Controls response diversity',
            type: 'slider',
            min: 0,
            max: 1,
            step: 0.1
        },
        {
            id: 4,
            name: 'Frequency Penalty',
            value: '0.0',
            description: 'Reduces repetition in responses',
            type: 'slider',
            min: 0,
            max: 2,
            step: 0.1
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
            <Head title="AI Settings" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">AI Settings</h1>
                        <p className="text-muted-foreground">Configure and manage your AI integrations</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline">
                            <TestTube className="w-4 h-4 mr-2" />
                            Test Connection
                        </Button>
                        <Button>
                            <Save className="w-4 h-4 mr-2" />
                            Save Changes
                        </Button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Active Models</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">2</div>
                            <p className="text-xs text-muted-foreground">GPT-4, Claude-3</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Requests</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">69,924</div>
                            <p className="text-xs text-muted-foreground">This month</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Cost</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">$2,091.34</div>
                            <p className="text-xs text-muted-foreground">This month</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Success Rate</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">98.7%</div>
                            <p className="text-xs text-muted-foreground">+0.3% from last month</p>
                        </CardContent>
                    </Card>
                </div>

                {/* AI Models Configuration */}
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
                        <div className="space-y-4">
                            {aiModels.map((model) => (
                                <div key={model.id} className="flex items-center justify-between p-4 border border-border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex items-center">
                                            <Brain className="w-5 h-5 mr-2 text-primary" />
                                            <div>
                                                <h3 className="font-medium text-foreground">{model.name}</h3>
                                                <p className="text-sm text-muted-foreground">{model.provider}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center">
                                            <div className={`w-2 h-2 rounded-full ${getStatusColor(model.status)} mr-2`}></div>
                                            <span className="text-sm">{getStatusText(model.status)}</span>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-6">
                                        <div className="text-right">
                                            <p className="text-sm text-foreground">Usage: {model.usage}</p>
                                            <p className="text-sm text-muted-foreground">Cost: {model.cost}</p>
                                        </div>
                                        <div className="flex space-x-2">
                                            <Button variant="ghost" size="sm">
                                                <Eye className="w-4 h-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm">
                                                <Edit className="w-4 h-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm">
                                                <TestTube className="w-4 h-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm">
                                                <MoreVertical className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* AI Configuration Parameters */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <CardTitle className="text-foreground">Model Parameters</CardTitle>
                            <CardDescription className="text-muted-foreground">Fine-tune AI response behavior</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {aiConfigurations.map((config) => (
                                <div key={config.id} className="space-y-2">
                                    <div className="flex justify-between items-center">
                                        <label className="text-sm font-medium text-foreground">{config.name}</label>
                                        <span className="text-sm text-muted-foreground">{config.value}{config.unit && ` ${config.unit}`}</span>
                                    </div>
                                    <div className="w-full bg-muted rounded-full h-2">
                                        <div
                                            className="bg-primary h-2 rounded-full"
                                            style={{ width: `${(config.value / config.max) * 100}%` }}
                                        ></div>
                                    </div>
                                    <p className="text-xs text-muted-foreground">{config.description}</p>
                                </div>
                            ))}
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader>
                            <CardTitle className="text-foreground">Prompt Templates</CardTitle>
                            <CardDescription className="text-muted-foreground">Manage your AI prompt templates</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {promptTemplates.map((template) => (
                                    <div key={template.id} className="flex items-center justify-between p-3 border border-border rounded-lg">
                                        <div className="flex-1">
                                            <div className="flex items-center space-x-2">
                                                <h3 className="font-medium text-foreground">{template.name}</h3>
                                                <Badge variant="secondary" className="text-xs">
                                                    {template.category}
                                                </Badge>
                                            </div>
                                            <p className="text-sm text-muted-foreground">{template.description}</p>
                                            <p className="text-xs text-muted-foreground">Used {template.usage} • Updated {template.lastUpdated}</p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <div className={`w-2 h-2 rounded-full ${getStatusColor(template.status)}`}></div>
                                            <Button variant="ghost" size="sm">
                                                <Edit className="w-4 h-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm">
                                                <Copy className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Security & Monitoring */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <CardTitle className="text-foreground">Security & Monitoring</CardTitle>
                        <CardDescription className="text-muted-foreground">API key management and usage monitoring</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div className="space-y-4">
                                <div className="flex items-center space-x-2">
                                    <Shield className="w-5 h-5 text-primary" />
                                    <h3 className="font-medium text-foreground">API Security</h3>
                                </div>
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Rate Limiting</span>
                                        <span className="text-foreground">Enabled</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">IP Whitelist</span>
                                        <span className="text-foreground">Disabled</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Key Rotation</span>
                                        <span className="text-foreground">30 days</span>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="flex items-center space-x-2">
                                    <BarChart3 className="w-5 h-5 text-primary" />
                                    <h3 className="font-medium text-foreground">Usage Analytics</h3>
                                </div>
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Daily Requests</span>
                                        <span className="text-foreground">2,331</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Avg Response Time</span>
                                        <span className="text-foreground">1.2s</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Error Rate</span>
                                        <span className="text-foreground">1.3%</span>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="flex items-center space-x-2">
                                    <AlertTriangle className="w-5 h-5 text-primary" />
                                    <h3 className="font-medium text-foreground">Alerts</h3>
                                </div>
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">High Usage</span>
                                        <span className="text-foreground">Enabled</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Error Threshold</span>
                                        <span className="text-foreground">5%</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Cost Alerts</span>
                                        <span className="text-foreground">$500/day</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
