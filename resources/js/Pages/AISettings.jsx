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
    MoreVertical
} from 'lucide-react';

export default function AISettings({ auth }) {
    const [aiModels, setAiModels] = useState([]);
    const [aiProviders, setAiProviders] = useState([]);
    const [aiConfigurations, setAiConfigurations] = useState([]);
    const [stats, setStats] = useState({});
    const [isLoading, setIsLoading] = useState(true);
    const [testResult, setTestResult] = useState(null);

    useEffect(() => {
        loadData();
    }, []);



            const loadData = async () => {
        try {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            };

            const [modelsResponse, providersResponse, configResponse, statsResponse] = await Promise.all([
                fetch('/api/ai-settings/models', {
                    method: 'GET',
                    headers,
                    credentials: 'same-origin'
                }),
                fetch('/api/ai-settings/providers', {
                    method: 'GET',
                    headers,
                    credentials: 'same-origin'
                }),
                fetch('/api/ai-settings/configuration', {
                    method: 'GET',
                    headers,
                    credentials: 'same-origin'
                }),
                fetch('/api/ai-settings/stats', {
                    method: 'GET',
                    headers,
                    credentials: 'same-origin'
                })
            ]);

            const modelsData = await modelsResponse.json();
            const providersData = await providersResponse.json();
            const configData = await configResponse.json();
            const statsData = await statsResponse.json();

            console.log('API Responses:', {
                models: modelsData,
                config: configData,
                stats: statsData
            });

            if (modelsData.status === 'success' && Array.isArray(modelsData.data)) {
                setAiModels(modelsData.data);
                console.log('Successfully set AI models:', modelsData.data.length, 'models');
            } else {
                console.error('Models API failed or invalid data:', modelsData);
            }

            if (providersData.status === 'success' && Array.isArray(providersData.data)) {
                setAiProviders(providersData.data);
                console.log('Successfully set AI providers:', providersData.data.length, 'providers');
            } else {
                console.error('Providers API failed or invalid data:', providersData);
            }

            if (configData.status === 'success') {
                setAiConfigurations(configData.data);
            }

            if (statsData.status === 'success') {
                setStats(statsData.data);
            }
        } catch (error) {
            console.error('Failed to load data:', error);
        } finally {
            setIsLoading(false);
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

            if (data.status === 'success') {
                alert('Connection test successful!');
            } else {
                alert('Connection test failed: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to test connection:', error);
            alert('Failed to test connection');
        }
    };

    const handleSaveChanges = async () => {
        try {
            const configData = {};
            aiConfigurations.forEach(config => {
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
                alert('Changes saved successfully!');
            } else {
                alert('Failed to save changes: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to save changes:', error);
            alert('Failed to save changes');
        }
    };

    const handleModelAction = (modelId, action) => {
        switch (action) {
            case 'view':
                router.visit(`/ai-settings/models/${modelId}`);
                break;
            case 'edit':
                router.visit(`/ai-settings/models/${modelId}/edit`);
                break;
            case 'test':
                // Test specific model
                break;
            default:
                break;
        }
    };

    const handleProviderAction = (providerId, action) => {
        switch (action) {
            case 'view':
                router.visit(`/ai-settings/providers/${providerId}`);
                break;
            case 'edit':
                router.visit(`/ai-settings/providers/${providerId}/edit`);
                break;
            case 'test':
                // Test specific provider
                break;
            default:
                break;
        }
    };

    const handleAddModel = () => {
        router.visit('/ai-settings/models/new');
    };

    const handleAddProvider = () => {
        router.visit('/ai-settings/providers/new');
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
                        <Button variant="outline" onClick={handleTestConnection}>
                            <TestTube className="w-4 h-4 mr-2" />
                            Test Connection
                        </Button>
                        <Button onClick={handleSaveChanges}>
                            <Save className="w-4 h-4 mr-2" />
                            Save Changes
                        </Button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-6 gap-6">
                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Models</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.total_models || 0}</div>
                            <p className="text-xs text-muted-foreground">All AI Models</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Active Models</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.active_models || 0}</div>
                            <p className="text-xs text-muted-foreground">Currently Active</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Users</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.total_users || 0}</div>
                            <p className="text-xs text-muted-foreground">Model Creators</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Requests</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.total_requests || '0'}</div>
                            <p className="text-xs text-muted-foreground">API Calls</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Success Rate</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.success_rate || '0%'}</div>
                            <p className="text-xs text-muted-foreground">Last 24h</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Active Providers</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.active_providers || 0}</div>
                            <p className="text-xs text-muted-foreground">of {stats.total_providers || 0} total</p>
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
                            <Button variant="outline" size="sm" onClick={handleAddModel}>
                                <Plus className="w-4 h-4 mr-2" />
                                Add Model
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {isLoading ? (
                                <div className="flex items-center justify-center py-8">
                                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                    <span className="ml-2 text-muted-foreground">Loading models...</span>
                                </div>
                            ) : aiModels.length === 0 ? (
                                <div className="text-center py-8">
                                    <p className="text-muted-foreground">No AI models configured</p>
                                    <Button variant="outline" size="sm" className="mt-2" onClick={handleAddModel}>
                                        <Plus className="w-4 h-4 mr-2" />
                                        Add Your First Model
                                    </Button>
                                </div>
                            ) : (
                                aiModels.map((model) => (
                                <div
                                    key={model.id}
                                    className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 cursor-pointer transition-colors"
                                    onClick={() => handleModelAction(model.id, 'view')}
                                >
                                    <div className="flex items-center space-x-4">
                                        <div className="flex items-center">
                                            <Brain className="w-5 h-5 mr-2 text-primary" />
                                            <div>
                                                <h3 className="font-medium text-foreground">{model.name}</h3>
                                                <p className="text-sm text-muted-foreground">{model.provider} • {model.model}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    Provider: {model.provider_name} • Added by {model.user?.name || 'Unknown'}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center">
                                            <div className={`w-2 h-2 rounded-full ${getStatusColor(model.status)} mr-2`}></div>
                                            <span className="text-sm">{getStatusText(model.status)}</span>
                                            {model.is_default && (
                                                <Badge variant="secondary" className="ml-2 text-xs">Default</Badge>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-6">
                                        <div className="text-right">
                                            <p className="text-sm text-foreground">Usage: {model.usage}</p>
                                            <p className="text-sm text-muted-foreground">Cost: {model.cost}</p>
                                        </div>
                                        <div className="flex space-x-2" onClick={(e) => e.stopPropagation()}>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleModelAction(model.id, 'view')}
                                            >
                                                <Eye className="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleModelAction(model.id, 'edit')}
                                            >
                                                <Edit className="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleModelAction(model.id, 'test')}
                                            >
                                                <TestTube className="w-4 h-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm">
                                                <MoreVertical className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            ))
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* AI Providers Configuration */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-foreground">AI Providers</CardTitle>
                                <CardDescription className="text-muted-foreground">Manage your AI service provider accounts</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" onClick={handleAddProvider}>
                                <Plus className="w-4 h-4 mr-2" />
                                Add Provider
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {isLoading ? (
                                <div className="flex items-center justify-center py-8">
                                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                    <span className="ml-2 text-muted-foreground">Loading providers...</span>
                                </div>
                            ) : aiProviders.length === 0 ? (
                                <div className="text-center py-8">
                                    <p className="text-muted-foreground">No AI providers configured</p>
                                    <Button variant="outline" size="sm" className="mt-2" onClick={handleAddProvider}>
                                        <Plus className="w-4 h-4 mr-2" />
                                        Add Your First Provider
                                    </Button>
                                </div>
                            ) : (
                                aiProviders.map((provider) => (
                                <div
                                    key={provider.id}
                                    className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 cursor-pointer transition-colors"
                                    onClick={() => handleProviderAction(provider.id, 'view')}
                                >
                                    <div className="flex items-center space-x-4">
                                        <div className="flex items-center">
                                            <Key className="w-5 h-5 mr-2 text-primary" />
                                            <div>
                                                <h3 className="font-medium text-foreground">{provider.name}</h3>
                                                <p className="text-sm text-muted-foreground">{provider.provider_type} • {provider.base_url}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    API Key: {provider.masked_api_key} • Added by {provider.user?.name || 'Unknown'}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center">
                                            <div className={`w-2 h-2 rounded-full ${getStatusColor(provider.status)} mr-2`}></div>
                                            <span className="text-sm">{getStatusText(provider.status)}</span>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-6">
                                        <div className="text-right">
                                            <p className="text-sm text-foreground">Type: {provider.provider_type}</p>
                                            <p className="text-sm text-muted-foreground">Created: {new Date(provider.created_at).toLocaleDateString()}</p>
                                        </div>
                                        <div className="flex space-x-2" onClick={(e) => e.stopPropagation()}>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleProviderAction(provider.id, 'view')}
                                            >
                                                <Eye className="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleProviderAction(provider.id, 'edit')}
                                            >
                                                <Edit className="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleProviderAction(provider.id, 'test')}
                                            >
                                                <TestTube className="w-4 h-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm">
                                                <MoreVertical className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            ))
                            )}
                        </div>
                    </CardContent>
                </Card>

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
