import React, { useState } from 'react';
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
        provider: 'AIMLAPI',
        model: 'gpt-4o',
        apiKey: '',
        baseUrl: 'https://api.aimlapi.com/v1',
        status: 'active'
    });
    const [isLoading, setIsLoading] = useState(false);
    const [testResult, setTestResult] = useState(null);

    const providers = [
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

    const handleInputChange = (field, value) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));

        // Reset model when provider changes
        if (field === 'provider') {
            const availableModels = models[value];
            if (availableModels && availableModels.length > 0) {
                setFormData(prev => ({
                    ...prev,
                    provider: value,
                    model: availableModels[0].value
                }));
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
        if (!formData.name.trim() || !formData.apiKey.trim()) {
            alert('Please fill in all required fields');
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
                    provider: formData.provider,
                    model: formData.model,
                    api_key: formData.apiKey,
                    base_url: formData.baseUrl,
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
            alert('Failed to save model');
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
                                    value={formData.provider}
                                    onChange={(e) => handleInputChange('provider', e.target.value)}
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                >
                                    {providers.map(provider => (
                                        <option key={provider.value} value={provider.value}>
                                            {provider.label} - {provider.description}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">Model *</label>
                                <select
                                    value={formData.model}
                                    onChange={(e) => handleInputChange('model', e.target.value)}
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                >
                                    {models[formData.provider]?.map(model => (
                                        <option key={model.value} value={model.value}>
                                            {model.label}
                                        </option>
                                    ))}
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

                    {/* API Configuration */}
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex items-center space-x-2">
                                <Key className="w-5 h-5 text-primary" />
                                <CardTitle className="text-foreground">API Configuration</CardTitle>
                            </div>
                            <CardDescription className="text-muted-foreground">
                                Configure API credentials and endpoints
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">API Key *</label>
                                <input
                                    type="password"
                                    value={formData.apiKey}
                                    onChange={(e) => handleInputChange('apiKey', e.target.value)}
                                    placeholder="sk-..."
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Your API key will be encrypted and stored securely
                                </p>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">Base URL</label>
                                <input
                                    type="url"
                                    value={formData.baseUrl}
                                    onChange={(e) => handleInputChange('baseUrl', e.target.value)}
                                    placeholder="https://api.aimlapi.com/v1"
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Leave default for standard providers
                                </p>
                            </div>

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
                                            {formData.provider} • {formData.model}
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
                                <p className="text-sm text-foreground">API Key: {formData.apiKey ? '••••••••' : 'Not set'}</p>
                                <p className="text-sm text-muted-foreground">Base URL: {formData.baseUrl}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
