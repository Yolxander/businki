import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
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
    MoreVertical,
    ArrowLeft,
    Sliders,
    Target,
    Gauge,
    Activity
} from 'lucide-react';

export default function ModelDetails({ auth, modelId }) {
    const [model, setModel] = useState(null);
    const [configurations, setConfigurations] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [testResult, setTestResult] = useState(null);
    const [testMessage, setTestMessage] = useState('');

    useEffect(() => {
        loadModelData();
        loadConfiguration();
    }, [modelId]);

    const loadModelData = async () => {
        try {
            const response = await fetch(`/api/ai-settings/models`);
            const data = await response.json();
            if (data.status === 'success') {
                const foundModel = data.data.find(m => m.id == modelId);
                setModel(foundModel);

                // If model has settings, convert them to configuration format
                if (foundModel && foundModel.settings) {
                    const modelConfigs = [
                        {
                            id: 1,
                            name: 'Temperature',
                            value: foundModel.settings.temperature?.toString() || '0.7',
                            description: 'Controls randomness in AI responses',
                            type: 'slider',
                            min: 0,
                            max: 1,
                            step: 0.1
                        },
                        {
                            id: 2,
                            name: 'Max Tokens',
                            value: foundModel.settings.max_tokens?.toString() || '2048',
                            description: 'Maximum length of AI responses',
                            type: 'input',
                            unit: 'tokens'
                        },
                        {
                            id: 3,
                            name: 'Top P',
                            value: foundModel.settings.top_p?.toString() || '0.9',
                            description: 'Controls response diversity',
                            type: 'slider',
                            min: 0,
                            max: 1,
                            step: 0.1
                        },
                        {
                            id: 4,
                            name: 'Frequency Penalty',
                            value: foundModel.settings.frequency_penalty?.toString() || '0.0',
                            description: 'Reduces repetition in responses',
                            type: 'slider',
                            min: 0,
                            max: 2,
                            step: 0.1
                        },
                        {
                            id: 5,
                            name: 'Presence Penalty',
                            value: foundModel.settings.presence_penalty?.toString() || '0.0',
                            description: 'Encourages new topics in responses',
                            type: 'slider',
                            min: 0,
                            max: 2,
                            step: 0.1
                        }
                    ];
                    setConfigurations(modelConfigs);
                }
            }
        } catch (error) {
            console.error('Failed to load model data:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const loadConfiguration = async () => {
        try {
            const response = await fetch(`/api/ai-settings/configuration`);
            const data = await response.json();
            if (data.status === 'success') {
                setConfigurations(data.data);
            }
        } catch (error) {
            console.error('Failed to load configuration:', error);
        }
    };

    const handleConfigurationChange = (configId, newValue) => {
        setConfigurations(prev =>
            prev.map(config =>
                config.id === configId
                    ? { ...config, value: newValue }
                    : config
            )
        );
    };

    const handleSaveConfiguration = async () => {
        setIsSaving(true);
        try {
            const configData = {};
            configurations.forEach(config => {
                const key = config.name.toLowerCase().replace(/\s+/g, '_');
                configData[key] = parseFloat(config.value);
            });

            const response = await fetch('/api/ai-settings/configuration', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(configData)
            });

            const data = await response.json();
            if (data.status === 'success') {
                // Show success message
                alert('Configuration saved successfully!');
            } else {
                alert('Failed to save configuration: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to save configuration:', error);
            alert('Failed to save configuration');
        } finally {
            setIsSaving(false);
        }
    };

    const handleTestConnection = async () => {
        try {
            const response = await fetch('/api/ai-settings/test-aimlapi-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            setTestResult(data);
        } catch (error) {
            console.error('Failed to test connection:', error);
            setTestResult({
                status: 'error',
                message: 'Failed to test connection'
            });
        }
    };

    const handleTestCompletion = async () => {
        if (!testMessage.trim()) {
            alert('Please enter a test message');
            return;
        }

        try {
            const response = await fetch('/api/ai-settings/test-completion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: testMessage,
                    model: model?.name || 'gpt-4o'
                })
            });

            const data = await response.json();
            setTestResult(data);
        } catch (error) {
            console.error('Failed to test completion:', error);
            setTestResult({
                status: 'error',
                message: 'Failed to test completion'
            });
        }
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

    if (isLoading) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <div className="flex items-center justify-center h-64">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
            </AuthenticatedLayout>
        );
    }

    if (!model) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <div className="text-center py-8">
                    <p className="text-muted-foreground">Model not found</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${model.name} - Model Details`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div className="flex items-center space-x-4">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => router.visit('/ai-settings')}
                        >
                            <ArrowLeft className="w-4 h-4 mr-2" />
                            Back to AI Settings
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{model.name}</h1>
                            <p className="text-muted-foreground">Model configuration and parameters</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" onClick={handleTestConnection}>
                            <TestTube className="w-4 h-4 mr-2" />
                            Test Connection
                        </Button>
                        <Button onClick={handleSaveConfiguration} disabled={isSaving}>
                            <Save className="w-4 h-4 mr-2" />
                            {isSaving ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </div>
                </div>

                {/* Model Info Card */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-3">
                                <Brain className="w-6 h-6 text-primary" />
                                <div>
                                    <CardTitle className="text-foreground">{model.name}</CardTitle>
                                    <CardDescription className="text-muted-foreground">
                                        {model.provider} • {model.model} • {model.status}
                                    </CardDescription>
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                <div className={`w-3 h-3 rounded-full ${getStatusColor(model.status)}`}></div>
                                <Badge variant="secondary">{getStatusText(model.status)}</Badge>
                                {model.is_default && (
                                    <Badge variant="outline">Default</Badge>
                                )}
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div className="space-y-2">
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Provider</span>
                                    <span className="text-foreground">{model.provider}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Model</span>
                                    <span className="text-foreground">{model.model}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Base URL</span>
                                    <span className="text-foreground font-mono text-xs">{model.base_url}</span>
                                </div>
                            </div>
                            <div className="space-y-2">
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Usage Count</span>
                                    <span className="text-foreground">{model.usage_count || 0}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Last Used</span>
                                    <span className="text-foreground">
                                        {model.last_used_at ? new Date(model.last_used_at).toLocaleDateString() : 'Never'}
                                    </span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Created</span>
                                    <span className="text-foreground">
                                        {model.created_at ? new Date(model.created_at).toLocaleDateString() : 'Unknown'}
                                    </span>
                                </div>
                            </div>
                            <div className="space-y-2">
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Status</span>
                                    <div className="flex items-center">
                                        <div className={`w-2 h-2 rounded-full ${getStatusColor(model.status)} mr-2`}></div>
                                        <span className="text-foreground">{getStatusText(model.status)}</span>
                                    </div>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Default Model</span>
                                    <span className="text-foreground">{model.is_default ? 'Yes' : 'No'}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Created By</span>
                                    <span className="text-foreground">{model.user?.name || 'Unknown'}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">User Email</span>
                                    <span className="text-foreground">{model.user?.email || 'Unknown'}</span>
                                </div>
                            </div>
                        </div>

                        {model.settings?.description && (
                            <div className="pt-4 border-t border-border">
                                <label className="text-sm font-medium text-muted-foreground">Description</label>
                                <p className="text-foreground mt-1">{model.settings.description}</p>
                            </div>
                        )}

                        {model.settings?.use_cases && model.settings.use_cases.length > 0 && (
                            <div className="pt-4 border-t border-border">
                                <label className="text-sm font-medium text-muted-foreground">Use Cases</label>
                                <div className="flex flex-wrap gap-2 mt-1">
                                    {model.settings.use_cases.map((useCase, index) => (
                                        <Badge key={index} variant="outline" className="text-xs">
                                            {useCase.replace(/_/g, ' ')}
                                        </Badge>
                                    ))}
                                </div>
                            </div>
                        )}

                        {model.settings?.tags && model.settings.tags.length > 0 && (
                            <div className="pt-4 border-t border-border">
                                <label className="text-sm font-medium text-muted-foreground">Tags</label>
                                <div className="flex flex-wrap gap-2 mt-1">
                                    {model.settings.tags.map((tag, index) => (
                                        <Badge key={index} variant="secondary" className="text-xs">
                                            {tag}
                                        </Badge>
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* User Information */}
                {model.user && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex items-center space-x-2">
                                <Key className="w-5 h-5 text-primary" />
                                <CardTitle className="text-foreground">User Information</CardTitle>
                            </div>
                            <CardDescription className="text-muted-foreground">
                                Model creator and ownership details
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Created By</span>
                                        <span className="text-foreground font-medium">{model.user.name}</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">User Email</span>
                                        <span className="text-foreground">{model.user.email}</span>
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">User ID</span>
                                        <span className="text-foreground font-mono text-xs">{model.user.id}</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Ownership</span>
                                        <Badge variant={model.user.id === auth.user.id ? "default" : "secondary"}>
                                            {model.user.id === auth.user.id ? "You" : "Other User"}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Model Parameters */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center space-x-2">
                            <Sliders className="w-5 h-5 text-primary" />
                            <CardTitle className="text-foreground">Model Parameters</CardTitle>
                        </div>
                        <CardDescription className="text-muted-foreground">
                            Fine-tune AI response behavior
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {configurations.map((config) => (
                            <div key={config.id} className="space-y-3">
                                <div className="flex justify-between items-center">
                                    <label className="text-sm font-medium text-foreground">{config.name}</label>
                                    <span className="text-sm text-muted-foreground">
                                        {config.value}{config.unit && ` ${config.unit}`}
                                    </span>
                                </div>
                                {config.type === 'slider' ? (
                                    <div className="space-y-2">
                                        <input
                                            type="range"
                                            min={config.min}
                                            max={config.max}
                                            step={config.step}
                                            value={config.value}
                                            onChange={(e) => handleConfigurationChange(config.id, e.target.value)}
                                            className="w-full h-2 bg-muted rounded-lg appearance-none cursor-pointer slider"
                                        />
                                        <div className="flex justify-between text-xs text-muted-foreground">
                                            <span>{config.min}</span>
                                            <span>{config.max}</span>
                                        </div>
                                    </div>
                                ) : (
                                    <input
                                        type="number"
                                        value={config.value}
                                        onChange={(e) => handleConfigurationChange(config.id, e.target.value)}
                                        className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                        placeholder={`Enter ${config.name.toLowerCase()}`}
                                    />
                                )}
                                <p className="text-xs text-muted-foreground">{config.description}</p>
                            </div>
                        ))}
                    </CardContent>
                </Card>

                {/* Test AI Completion */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center space-x-2">
                            <TestTube className="w-5 h-5 text-primary" />
                            <CardTitle className="text-foreground">Test AI Completion</CardTitle>
                        </div>
                        <CardDescription className="text-muted-foreground">
                            Test the AI model with a custom message
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-foreground">Test Message</label>
                            <textarea
                                value={testMessage}
                                onChange={(e) => setTestMessage(e.target.value)}
                                placeholder="Enter a test message..."
                                className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground min-h-[100px]"
                            />
                        </div>
                        <Button onClick={handleTestCompletion} disabled={!testMessage.trim()}>
                            <Play className="w-4 h-4 mr-2" />
                            Test Completion
                        </Button>

                        {testResult && (
                            <div className={`p-4 rounded-lg border ${
                                testResult.status === 'success'
                                    ? 'border-green-200 bg-green-50'
                                    : 'border-red-200 bg-red-50'
                            }`}>
                                <div className="flex items-center space-x-2">
                                    {testResult.status === 'success' ? (
                                        <CheckCircle className="w-4 h-4 text-green-600" />
                                    ) : (
                                        <AlertTriangle className="w-4 h-4 text-red-600" />
                                    )}
                                    <span className={`font-medium ${
                                        testResult.status === 'success' ? 'text-green-800' : 'text-red-800'
                                    }`}>
                                        {testResult.message}
                                    </span>
                                </div>
                                {testResult.content && (
                                    <div className="mt-3 p-3 bg-white rounded border">
                                        <p className="text-sm text-gray-700">{testResult.content}</p>
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Performance Metrics */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center space-x-2">
                            <BarChart3 className="w-5 h-5 text-primary" />
                            <CardTitle className="text-foreground">Performance Metrics</CardTitle>
                        </div>
                        <CardDescription className="text-muted-foreground">
                            Model performance and usage statistics
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div className="space-y-2">
                                <div className="flex items-center space-x-2">
                                    <Activity className="w-4 h-4 text-primary" />
                                    <span className="text-sm font-medium text-foreground">Response Time</span>
                                </div>
                                <p className="text-2xl font-bold text-foreground">1.2s</p>
                                <p className="text-xs text-muted-foreground">Average</p>
                            </div>
                            <div className="space-y-2">
                                <div className="flex items-center space-x-2">
                                    <Target className="w-4 h-4 text-primary" />
                                    <span className="text-sm font-medium text-foreground">Success Rate</span>
                                </div>
                                <p className="text-2xl font-bold text-foreground">98.7%</p>
                                <p className="text-xs text-muted-foreground">Last 24h</p>
                            </div>
                            <div className="space-y-2">
                                <div className="flex items-center space-x-2">
                                    <Gauge className="w-4 h-4 text-primary" />
                                    <span className="text-sm font-medium text-foreground">Token Usage</span>
                                </div>
                                <p className="text-2xl font-bold text-foreground">2.3M</p>
                                <p className="text-xs text-muted-foreground">This month</p>
                            </div>
                            <div className="space-y-2">
                                <div className="flex items-center space-x-2">
                                    <Clock className="w-4 h-4 text-primary" />
                                    <span className="text-sm font-medium text-foreground">Uptime</span>
                                </div>
                                <p className="text-2xl font-bold text-foreground">99.9%</p>
                                <p className="text-xs text-muted-foreground">Last 30 days</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
