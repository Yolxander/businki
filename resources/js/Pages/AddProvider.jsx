import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Key,
    Save,
    ArrowLeft,
    Plus,
    TestTube,
    CheckCircle,
    AlertTriangle,
    Globe,
    Settings,
    Shield
} from 'lucide-react';

export default function AddProvider({ auth }) {
    const [formData, setFormData] = useState({
        name: '',
        provider_type: 'AIMLAPI',
        api_key: '',
        base_url: 'https://api.aimlapi.com/v1',
        status: 'active'
    });
    const [isLoading, setIsLoading] = useState(false);
    const [testResult, setTestResult] = useState(null);

    const providerTypes = [
        { value: 'AIMLAPI', label: 'AIMLAPI', description: 'Multi-provider AI API' },
        { value: 'OpenAI', label: 'OpenAI', description: 'Direct OpenAI API' },
        { value: 'Anthropic', label: 'Anthropic', description: 'Claude API' },
        { value: 'Google', label: 'Google', description: 'Gemini API' }
    ];

    const defaultUrls = {
        'AIMLAPI': 'https://api.aimlapi.com/v1',
        'OpenAI': 'https://api.openai.com/v1',
        'Anthropic': 'https://api.anthropic.com',
        'Google': 'https://generativelanguage.googleapis.com'
    };

    const handleInputChange = (field, value) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));

        // Update base URL when provider type changes
        if (field === 'provider_type') {
            setFormData(prev => ({
                ...prev,
                provider_type: value,
                base_url: defaultUrls[value] || prev.base_url
            }));
        }
    };

    const handleTestConnection = async () => {
        if (!formData.api_key.trim()) {
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
                    api_key: formData.api_key,
                    base_url: formData.base_url,
                    model: 'gpt-4o'
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

    const handleSaveProvider = async () => {
        if (!formData.name.trim() || !formData.api_key.trim()) {
            alert('Please fill in all required fields');
            return;
        }

        setIsLoading(true);

        try {
            const response = await fetch('/api/ai-settings/providers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert('Provider saved successfully!');
                router.visit('/ai-settings');
            } else {
                alert('Failed to save provider: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to save provider:', error);
            alert('Failed to save provider');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Add New AI Provider" />

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
                            <h1 className="text-2xl font-bold text-foreground">Add New AI Provider</h1>
                            <p className="text-muted-foreground">Configure a new AI service provider account</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" onClick={handleTestConnection} disabled={isLoading}>
                            <TestTube className="w-4 h-4 mr-2" />
                            Test Connection
                        </Button>
                        <Button onClick={handleSaveProvider} disabled={isLoading}>
                            <Save className="w-4 h-4 mr-2" />
                            {isLoading ? 'Saving...' : 'Save Provider'}
                        </Button>
                    </div>
                </div>

                {/* Provider Configuration Form */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Basic Configuration */}
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <div className="flex items-center space-x-2">
                                <Settings className="w-5 h-5 text-primary" />
                                <CardTitle className="text-foreground">Basic Configuration</CardTitle>
                            </div>
                            <CardDescription className="text-muted-foreground">
                                Set up the basic provider information
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">Provider Name *</label>
                                <input
                                    type="text"
                                    value={formData.name}
                                    onChange={(e) => handleInputChange('name', e.target.value)}
                                    placeholder="e.g., My OpenAI Account"
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-foreground">Provider Type *</label>
                                <select
                                    value={formData.provider_type}
                                    onChange={(e) => handleInputChange('provider_type', e.target.value)}
                                    className="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground"
                                >
                                    {providerTypes.map(provider => (
                                        <option key={provider.value} value={provider.value}>
                                            {provider.label} - {provider.description}
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
                                    value={formData.api_key}
                                    onChange={(e) => handleInputChange('api_key', e.target.value)}
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
                                    value={formData.base_url}
                                    onChange={(e) => handleInputChange('base_url', e.target.value)}
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

                {/* Provider Preview */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center space-x-2">
                            <Key className="w-5 h-5 text-primary" />
                            <CardTitle className="text-foreground">Provider Preview</CardTitle>
                        </div>
                        <CardDescription className="text-muted-foreground">
                            Preview of how your provider will appear
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center justify-between p-4 border border-border rounded-lg">
                            <div className="flex items-center space-x-4">
                                <div className="flex items-center">
                                    <Key className="w-5 h-5 mr-2 text-primary" />
                                    <div>
                                        <h3 className="font-medium text-foreground">
                                            {formData.name || 'Provider Name'}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            {formData.provider_type} • {formData.base_url}
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
                                <p className="text-sm text-foreground">API Key: {formData.api_key ? '••••••••' : 'Not set'}</p>
                                <p className="text-sm text-muted-foreground">Type: {formData.provider_type}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
