import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Brain,
    Save,
    ArrowLeft,
    Plus,
    TestTube,
    CheckCircle,
    AlertTriangle,
    Key,
    Globe,
    Settings
} from 'lucide-react';

export default function AddModel({ auth }) {
    const [formData, setFormData] = useState({
        name: '',
        provider_type: 'AIMLAPI',
        model: 'gpt-4o',
        provider_id: null,
        status: 'active'
    });
    const [providers, setProviders] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [testResult, setTestResult] = useState(null);

    const providerTypes = [
        { value: 'AIMLAPI', label: 'AIMLAPI', description: 'Multi-provider AI API' },
        { value: 'OpenAI', label: 'OpenAI', description: 'Direct OpenAI API' },
        { value: 'Anthropic', label: 'Anthropic', description: 'Claude API' },
        { value: 'Google', label: 'Google', description: 'Gemini API' }
    ];

    const models = {
        'AIMLAPI': [
            { value: 'gpt-4o', label: 'GPT-4o' },
            { value: 'gpt-4o-mini', label: 'GPT-4o Mini' },
            { value: 'claude-3-opus', label: 'Claude-3 Opus' },
            { value: 'claude-3-sonnet', label: 'Claude-3 Sonnet' },
            { value: 'gemini-pro', label: 'Gemini Pro' }
        ],
        'OpenAI': [
            { value: 'gpt-4o', label: 'GPT-4o' },
            { value: 'gpt-4o-mini', label: 'GPT-4o Mini' },
            { value: 'gpt-4-turbo', label: 'GPT-4 Turbo' }
        ],
        'Anthropic': [
            { value: 'claude-3-opus', label: 'Claude-3 Opus' },
            { value: 'claude-3-sonnet', label: 'Claude-3 Sonnet' },
            { value: 'claude-3-haiku', label: 'Claude-3 Haiku' }
        ],
        'Google': [
            { value: 'gemini-pro', label: 'Gemini Pro' },
            { value: 'gemini-pro-vision', label: 'Gemini Pro Vision' }
        ]
    };

    // Load providers on component mount
    useEffect(() => {
        const loadProviders = async () => {
            try {
                const response = await fetch('/ai-settings/providers', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    setProviders(data.data || []);
                }
            } catch (error) {
                console.error('Failed to load providers:', error);
            }
        };

        loadProviders();
    }, []);

    const handleInputChange = (field, value) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));

        // Reset model when provider changes
        if (field === 'provider_id') {
            const selectedProvider = providers.find(p => p.id == value);
            if (selectedProvider) {
                const availableModels = models[selectedProvider.provider_type];
                if (availableModels && availableModels.length > 0) {
                    setFormData(prev => ({
                        ...prev,
                        provider_id: value,
                        model: availableModels[0].value
                    }));
                }
            }
        }
    };

    const handleTestConnection = async () => {
        if (!formData.apiKey.trim()) {
            alert('Please enter an API key first');
            return;
        }

        setIsLoading(true);
        setTestResult(null);

        try {
            const response = await fetch('/api/ai-settings/test-aimlapi-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    api_key: formData.apiKey,
                    base_url: formData.baseUrl,
                    model: formData.model
                })
            });

            const data = await response.json();
            setTestResult(data);
        } catch (error) {
            console.error('Failed to test connection:', error);
            setTestResult({
                status: 'error',
                message: 'Failed to test connection'
            });
        } finally {
            setIsLoading(false);
        }
    };

    const handleSaveModel = async () => {
        if (!formData.name.trim()) {
            alert('Please enter a model name');
            return;
        }

        if (!formData.provider_id) {
            alert('Please select a provider');
            return;
        }

        setIsLoading(true);

        try {
            const response = await fetch('/api/ai-settings/models', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: formData.name,
                    ai_provider_id: formData.provider_id,
                    model: formData.model,
                    status: formData.status,
                    is_default: false
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert('Model saved successfully!');
                router.visit('/ai-settings');
            } else {
                alert('Failed to save model: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to save model:', error);
            alert('Failed to save model: ' + error.message);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Add New AI Model" />

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
                            <h1 className="text-2xl font-bold text-foreground">Add New AI Model</h1>
                            <p className="text-muted-foreground">Configure a new AI model integration</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" onClick={handleTestConnection} disabled={isLoading}>
                            <TestTube className="w-4 h-4 mr-2" />
                            Test Connection
                        </Button>
                        <Button onClick={handleSaveModel} disabled={isLoading}>
                            <Save className="w-4 h-4 mr-2" />
                            {isLoading ? 'Saving...' : 'Save Model'}
                        </Button>
                    </div>
                </div>

                {/* Model Configuration Form */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Basic Configuration */}
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex items-center space-x-2">
                                <Settings className="w-5 h-5 text-primary" />
                                <CardTitle className="text-foreground">Basic Configuration</CardTitle>
                            </div>
                            <CardDescription className="text-muted-foreground">
                                Set up the basic model information
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">Model Name *</label>
                                <input
                                    type="text"
                                    value={formData.name}
                                    onChange={(e) => handleInputChange('name', e.target.value)}
                                    placeholder="e.g., My GPT-4 Model"
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">Provider *</label>
                                <select
                                    value={formData.provider_id || ''}
                                    onChange={(e) => handleInputChange('provider_id', e.target.value)}
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                >
                                    <option value="">Select existing provider...</option>
                                    {providers.map(provider => (
                                        <option key={provider.id} value={provider.id}>
                                            {provider.name} ({provider.provider_type})
                                        </option>
                                    ))}
                                </select>
                                {providers.length === 0 && (
                                    <p className="text-xs text-muted-foreground">
                                        No providers available. <a href="/ai-settings/providers/new" className="text-primary hover:underline">Create a provider first</a>.
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">Model *</label>
                                <select
                                    value={formData.model}
                                    onChange={(e) => handleInputChange('model', e.target.value)}
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                    disabled={!formData.provider_id}
                                >
                                    {(() => {
                                        const selectedProvider = providers.find(p => p.id == formData.provider_id);
                                        const providerType = selectedProvider?.provider_type;
                                        return models[providerType]?.map(model => (
                                            <option key={model.value} value={model.value}>
                                                {model.label}
                                            </option>
                                        )) || [];
                                    })()}
                                </select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">Status</label>
                                <select
                                    value={formData.status}
                                    onChange={(e) => handleInputChange('status', e.target.value)}
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Provider Information */}
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex items-center space-x-2">
                                <Key className="w-5 h-5 text-primary" />
                                <CardTitle className="text-foreground">Provider Information</CardTitle>
                            </div>
                            <CardDescription className="text-muted-foreground">
                                Select an existing provider for this model
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {formData.provider_id && (() => {
                                const selectedProvider = providers.find(p => p.id == formData.provider_id);
                                return selectedProvider ? (
                                    <div className="p-4 border border-border rounded-lg bg-muted/50">
                                        <h4 className="font-medium mb-2">{selectedProvider.name}</h4>
                                        <p className="text-sm text-muted-foreground">
                                            Type: {selectedProvider.provider_type}<br/>
                                            Base URL: {selectedProvider.base_url}<br/>
                                            Status: {selectedProvider.status}<br/>
                                            Added by: {selectedProvider.user?.name}
                                        </p>
                                    </div>
                                ) : null;
                            })()}

                            {!formData.provider_id && (
                                <div className="p-4 border border-border rounded-lg bg-muted/50">
                                    <p className="text-sm text-muted-foreground">
                                        Please select a provider above to see its details here.
                                    </p>
                                </div>
                            )}

                            {/* Test Result */}
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
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Model Preview */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center space-x-2">
                            <Brain className="w-5 h-5 text-primary" />
                            <CardTitle className="text-foreground">Model Preview</CardTitle>
                        </div>
                        <CardDescription className="text-muted-foreground">
                            Preview of how your model will appear
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center justify-between p-4 border border-border rounded-lg">
                            <div className="flex items-center space-x-4">
                                <div className="flex items-center">
                                    <Brain className="w-5 h-5 mr-2 text-primary" />
                                    <div>
                                        <h3 className="font-medium text-foreground">
                                            {formData.name || 'Model Name'}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            {(() => {
                                                const selectedProvider = providers.find(p => p.id == formData.provider_id);
                                                const providerType = selectedProvider?.provider_type;
                                                return providerType ? `${providerType} • ${formData.model}` : 'Select a provider first';
                                            })()}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-center">
                                    <div className={`w-2 h-2 rounded-full ${
                                        formData.status === 'active' ? 'bg-green-500' : 'bg-gray-500'
                                    } mr-2`}></div>
                                    <span className="text-sm capitalize">{formData.status}</span>
                                </div>
                            </div>
                            <div className="text-right">
                                <p className="text-sm text-foreground">
                                    Provider: {(() => {
                                        const selectedProvider = providers.find(p => p.id == formData.provider_id);
                                        return selectedProvider ? selectedProvider.name : 'Not selected';
                                    })()}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Status: {formData.status}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
