import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Key,
    Settings,
    Save,
    ArrowLeft,
    Edit,
    TestTube,
    CheckCircle,
    AlertTriangle,
    Globe,
    Brain,
    BarChart3,
    Clock,
    User,
    Activity,
    Shield,
    Database,
    Zap,
    Trash2,
    Copy,
    Eye,
    MoreVertical
} from 'lucide-react';

export default function ProviderDetails({ auth, providerId }) {
    const [provider, setProvider] = useState(null);
    const [models, setModels] = useState([]);
    const [stats, setStats] = useState({});
    const [isLoading, setIsLoading] = useState(true);
    const [testResult, setTestResult] = useState(null);
    const [isEditing, setIsEditing] = useState(false);
    const [editData, setEditData] = useState({});

    useEffect(() => {
        loadProviderData();
    }, [providerId]);

    const loadProviderData = async () => {
        try {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            };

            const [providerResponse, modelsResponse, statsResponse] = await Promise.all([
                fetch(`/api/ai-settings/providers/${providerId}`, {
                    method: 'GET',
                    headers,
                    credentials: 'same-origin'
                }),
                fetch(`/api/ai-settings/providers/${providerId}/models`, {
                    method: 'GET',
                    headers,
                    credentials: 'same-origin'
                }),
                fetch(`/api/ai-settings/providers/${providerId}/stats`, {
                    method: 'GET',
                    headers,
                    credentials: 'same-origin'
                })
            ]);

            const providerData = await providerResponse.json();
            const modelsData = await modelsResponse.json();
            const statsData = await statsResponse.json();

            if (providerData.status === 'success') {
                setProvider(providerData.data);
                setEditData({
                    name: providerData.data.name,
                    provider_type: providerData.data.provider_type,
                    base_url: providerData.data.base_url,
                    status: providerData.data.status
                });
            }

            if (modelsData.status === 'success') {
                setModels(modelsData.data);
            }

            if (statsData.status === 'success') {
                setStats(statsData.data);
            }
        } catch (error) {
            console.error('Failed to load provider data:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const handleTestConnection = async () => {
        if (!provider) return;

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
                    api_key: provider.api_key,
                    base_url: provider.base_url,
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

    const handleSaveChanges = async () => {
        setIsLoading(true);

        try {
            const response = await fetch(`/api/ai-settings/providers/${providerId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(editData)
            });

            const data = await response.json();

            if (data.status === 'success') {
                setProvider(prev => ({ ...prev, ...editData }));
                setIsEditing(false);
                alert('Provider updated successfully!');
            } else {
                alert('Failed to update provider: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to update provider:', error);
            alert('Failed to update provider');
        } finally {
            setIsLoading(false);
        }
    };

    const handleDeleteProvider = async () => {
        if (!confirm('Are you sure you want to delete this provider? This action cannot be undone.')) {
            return;
        }

        setIsLoading(true);

        try {
            const response = await fetch(`/api/ai-settings/providers/${providerId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert('Provider deleted successfully!');
                router.visit('/ai-settings');
            } else {
                alert('Failed to delete provider: ' + data.message);
            }
        } catch (error) {
            console.error('Failed to delete provider:', error);
            alert('Failed to delete provider');
        } finally {
            setIsLoading(false);
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'active': return 'bg-green-500';
            case 'inactive': return 'bg-gray-500';
            case 'error': return 'bg-red-500';
            default: return 'bg-gray-500';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'active': return 'Active';
            case 'inactive': return 'Inactive';
            case 'error': return 'Error';
            default: return 'Unknown';
        }
    };

    if (isLoading && !provider) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Provider Details" />
                <div className="flex items-center justify-center py-8">
                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                    <span className="ml-2 text-muted-foreground">Loading provider details...</span>
                </div>
            </AuthenticatedLayout>
        );
    }

    if (!provider) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Provider Not Found" />
                <div className="text-center py-8">
                    <p className="text-muted-foreground">Provider not found</p>
                    <Button variant="outline" className="mt-2" onClick={() => router.visit('/ai-settings')}>
                        <ArrowLeft className="w-4 h-4 mr-2" />
                        Back to AI Settings
                    </Button>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${provider.name} - Provider Details`} />

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
                            <h1 className="text-2xl font-bold text-foreground">{provider.name}</h1>
                            <p className="text-muted-foreground">Provider Details & Configuration</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" onClick={handleTestConnection} disabled={isLoading}>
                            <TestTube className="w-4 h-4 mr-2" />
                            Test Connection
                        </Button>
                        {isEditing ? (
                            <>
                                <Button variant="outline" onClick={() => setIsEditing(false)}>
                                    Cancel
                                </Button>
                                <Button onClick={handleSaveChanges} disabled={isLoading}>
                                    <Save className="w-4 h-4 mr-2" />
                                    Save Changes
                                </Button>
                            </>
                        ) : (
                            <>
                                <Button variant="outline" onClick={() => setIsEditing(true)}>
                                    <Edit className="w-4 h-4 mr-2" />
                                    Edit
                                </Button>
                                <Button variant="outline" onClick={handleDeleteProvider} disabled={isLoading}>
                                    <Trash2 className="w-4 h-4 mr-2" />
                                    Delete
                                </Button>
                            </>
                        )}
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Models</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.total_models || 0}</div>
                            <p className="text-xs text-muted-foreground">Using this provider</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Active Models</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.active_models || 0}</div>
                            <p className="text-xs text-muted-foreground">Currently active</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Requests</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">{stats.total_requests || '0'}</div>
                            <p className="text-xs text-muted-foreground">API calls made</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Last Used</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {stats.last_used ? new Date(stats.last_used).toLocaleDateString() : 'Never'}
                            </div>
                            <p className="text-xs text-muted-foreground">Last API call</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Provider Information */}
                <Card className="bg-card border-border shadow-lg">
                    <CardHeader className="pb-6">
                        <div className="flex items-center space-x-3">
                            <div className="p-2 bg-primary/10 rounded-lg">
                                <Key className="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <CardTitle className="text-xl font-semibold text-foreground">Provider Information</CardTitle>
                                <CardDescription className="text-muted-foreground mt-1">
                                    Basic provider details and configuration
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Left Column */}
                            <div className="space-y-6">
                                <div className="space-y-3">
                                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">Provider Name</label>
                                    {isEditing ? (
                                        <input
                                            type="text"
                                            value={editData.name}
                                            onChange={(e) => setEditData(prev => ({ ...prev, name: e.target.value }))}
                                            className="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                                        />
                                    ) : (
                                        <div className="px-4 py-3 bg-muted/30 rounded-lg border border-border/50">
                                            <p className="text-base font-medium text-foreground">{provider.name}</p>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-3">
                                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">Provider Type</label>
                                    {isEditing ? (
                                        <select
                                            value={editData.provider_type}
                                            onChange={(e) => setEditData(prev => ({ ...prev, provider_type: e.target.value }))}
                                            className="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                                        >
                                            <option value="AIMLAPI">AIMLAPI - Multi-provider AI API</option>
                                            <option value="OpenAI">OpenAI - Direct OpenAI API</option>
                                            <option value="Anthropic">Anthropic - Claude API</option>
                                            <option value="Google">Google - Gemini API</option>
                                        </select>
                                    ) : (
                                        <div className="px-4 py-3 bg-muted/30 rounded-lg border border-border/50">
                                            <div className="flex items-center space-x-2">
                                                <Badge variant="secondary" className="text-xs font-medium">
                                                    {provider.provider_type}
                                                </Badge>
                                                <span className="text-sm text-muted-foreground">
                                                    {provider.provider_type === 'AIMLAPI' && 'Multi-provider AI API'}
                                                    {provider.provider_type === 'OpenAI' && 'Direct OpenAI API'}
                                                    {provider.provider_type === 'Anthropic' && 'Claude API'}
                                                    {provider.provider_type === 'Google' && 'Gemini API'}
                                                </span>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-3">
                                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">Base URL</label>
                                    {isEditing ? (
                                        <input
                                            type="url"
                                            value={editData.base_url}
                                            onChange={(e) => setEditData(prev => ({ ...prev, base_url: e.target.value }))}
                                            className="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                                        />
                                    ) : (
                                        <div className="px-4 py-3 bg-muted/30 rounded-lg border border-border/50">
                                            <div className="flex items-center space-x-2">
                                                <Globe className="w-4 h-4 text-muted-foreground" />
                                                <p className="text-sm font-mono text-foreground">{provider.base_url}</p>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-3">
                                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">Status</label>
                                    {isEditing ? (
                                        <select
                                            value={editData.status}
                                            onChange={(e) => setEditData(prev => ({ ...prev, status: e.target.value }))}
                                            className="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                                        >
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    ) : (
                                        <div className="px-4 py-3 bg-muted/30 rounded-lg border border-border/50">
                                            <div className="flex items-center space-x-3">
                                                <div className={`w-3 h-3 rounded-full ${getStatusColor(provider.status)}`}></div>
                                                <span className="text-sm font-medium">{getStatusText(provider.status)}</span>
                                                {provider.status === 'active' && (
                                                    <Badge variant="default" className="text-xs bg-green-500/20 text-green-600 border-green-500/30">
                                                        Operational
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Right Column */}
                            <div className="space-y-6">
                                <div className="space-y-3">
                                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">API Key</label>
                                    <div className="px-4 py-3 bg-muted/30 rounded-lg border border-border/50">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center space-x-2">
                                                <Shield className="w-4 h-4 text-muted-foreground" />
                                                <p className="text-sm font-mono text-foreground">{provider.masked_api_key}</p>
                                            </div>
                                            <Button variant="ghost" size="sm" className="hover:bg-muted/50">
                                                <Copy className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">Created By</label>
                                    <div className="px-4 py-3 bg-muted/30 rounded-lg border border-border/50">
                                        <div className="flex items-center space-x-2">
                                            <User className="w-4 h-4 text-muted-foreground" />
                                            <p className="text-sm text-foreground">{provider.user?.name || 'Unknown'}</p>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">Created At</label>
                                    <div className="px-4 py-3 bg-muted/30 rounded-lg border border-border/50">
                                        <div className="flex items-center space-x-2">
                                            <Clock className="w-4 h-4 text-muted-foreground" />
                                            <p className="text-sm text-foreground">
                                                {new Date(provider.created_at).toLocaleString()}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    <label className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">Available Models</label>
                                    <div className="px-4 py-3 bg-muted/30 rounded-lg border border-border/50">
                                        <div className="flex flex-wrap gap-2">
                                            {provider.available_models?.slice(0, 6).map(model => (
                                                <Badge key={model} variant="outline" className="text-xs">
                                                    {model}
                                                </Badge>
                                            )) || []}
                                            {provider.available_models?.length > 6 && (
                                                <Badge variant="outline" className="text-xs">
                                                    +{provider.available_models.length - 6} more
                                                </Badge>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Associated Models */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-foreground">Associated Models</CardTitle>
                                <CardDescription className="text-muted-foreground">
                                    AI models that use this provider
                                </CardDescription>
                            </div>
                            <Button variant="outline" size="sm" onClick={() => router.visit('/ai-settings/models/new')}>
                                <Brain className="w-4 h-4 mr-2" />
                                Add Model
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {models.length === 0 ? (
                                <div className="text-center py-8">
                                    <p className="text-muted-foreground">No models using this provider</p>
                                    <Button variant="outline" size="sm" className="mt-2" onClick={() => router.visit('/ai-settings/models/new')}>
                                        <Brain className="w-4 h-4 mr-2" />
                                        Add Your First Model
                                    </Button>
                                </div>
                            ) : (
                                models.map((model) => (
                                    <div
                                        key={model.id}
                                        className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 cursor-pointer transition-colors"
                                        onClick={() => router.visit(`/ai-settings/models/${model.id}`)}
                                    >
                                        <div className="flex items-center space-x-4">
                                            <div className="flex items-center">
                                                <Brain className="w-5 h-5 mr-2 text-primary" />
                                                <div>
                                                    <h3 className="font-medium text-foreground">{model.name}</h3>
                                                    <p className="text-sm text-muted-foreground">{model.model}</p>
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
                                                <p className="text-sm text-foreground">Usage: {model.usage_count || 0}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    Last used: {model.last_used_at ? new Date(model.last_used_at).toLocaleDateString() : 'Never'}
                                                </p>
                                            </div>
                                            <div className="flex space-x-2" onClick={(e) => e.stopPropagation()}>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => router.visit(`/ai-settings/models/${model.id}`)}
                                                >
                                                    <Eye className="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
