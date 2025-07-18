import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Activity,
    Server,
    Users,
    Zap,
    CheckCircle,
    AlertCircle,
    Clock,
    ArrowUp,
    ArrowDown,
    BarChart3,
    Plus,
    Settings,
    Globe,
    Database
} from 'lucide-react';

export default function ApiDashboard({ auth, stats }) {
    const defaultStats = {
        totalEndpoints: 0,
        activeEndpoints: 0,
        totalRequests: 0,
        errorRate: 0,
        recentRequests: [],
        endpointStatus: []
    };

    const statsData = stats || defaultStats;

    const apiMetrics = [
        {
            title: 'Total Endpoints',
            value: '24',
            trend: '+2',
            trendDirection: 'up',
            description: 'Active API endpoints',
            subtitle: '3 new endpoints this month',
            icon: Server,
            status: 'success'
        },
        {
            title: 'Request Volume',
            value: '2.4M',
            trend: '+12.5%',
            trendDirection: 'up',
            description: 'Requests this month',
            subtitle: 'Average 80K daily requests',
            icon: Activity,
            status: 'success'
        },
        {
            title: 'Error Rate',
            value: '0.8%',
            trend: '-0.2%',
            trendDirection: 'down',
            description: 'Down from last month',
            subtitle: 'Excellent uptime maintained',
            icon: AlertCircle,
            status: 'warning'
        },
        {
            title: 'Response Time',
            value: '145ms',
            trend: '-12ms',
            trendDirection: 'down',
            description: 'Average response time',
            subtitle: 'Performance improved',
            icon: Clock,
            status: 'success'
        }
    ];

    const endpointStatus = [
        { name: 'GET /api/users', status: 'active', requests: '45.2K', errors: '12', uptime: '99.9%' },
        { name: 'POST /api/auth/login', status: 'active', requests: '23.1K', errors: '8', uptime: '99.8%' },
        { name: 'GET /api/proposals', status: 'active', requests: '18.7K', errors: '5', uptime: '99.9%' },
        { name: 'POST /api/ai/generate', status: 'warning', requests: '12.3K', errors: '45', uptime: '98.5%' },
        { name: 'GET /api/clients', status: 'active', requests: '8.9K', errors: '3', uptime: '99.9%' },
        { name: 'DELETE /api/tasks/{id}', status: 'error', requests: '2.1K', errors: '156', uptime: '92.1%' }
    ];

    const getStatusColor = (status) => {
        switch (status) {
            case 'active':
                return 'bg-green-500';
            case 'warning':
                return 'bg-yellow-500';
            case 'error':
                return 'bg-red-500';
            default:
                return 'bg-gray-500';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'active':
                return 'Active';
            case 'warning':
                return 'Warning';
            case 'error':
                return 'Error';
            default:
                return 'Unknown';
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="API Dashboard" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">API Dashboard</h1>
                        <p className="text-muted-foreground">Monitor and manage your API endpoints</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" size="icon">
                            <Settings className="w-4 h-4" />
                        </Button>
                        <Button variant="outline" size="icon">
                            <BarChart3 className="w-4 h-4" />
                        </Button>
                        <Button>
                            <Plus className="w-4 h-4 mr-2" />
                            New Endpoint
                        </Button>
                    </div>
                </div>

                {/* API Metrics Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {apiMetrics.map((metric) => (
                        <Card key={metric.title} className="border-l-4 border-l-primary">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    {metric.title}
                                </CardTitle>
                                <metric.icon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-foreground">{metric.value}</div>
                                <div className="flex items-center space-x-2">
                                    <span className={`text-xs font-medium ${
                                        metric.trendDirection === 'up' ? 'text-green-600' : 'text-red-600'
                                    }`}>
                                        {metric.trend}
                                    </span>
                                    <span className="text-xs text-muted-foreground">{metric.description}</span>
                                </div>
                                <p className="text-xs text-muted-foreground mt-1">{metric.subtitle}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Endpoint Status Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Endpoint Status</CardTitle>
                        <CardDescription>
                            Real-time status of all API endpoints
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {endpointStatus.map((endpoint) => (
                                <div key={endpoint.name} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className={`w-3 h-3 rounded-full ${getStatusColor(endpoint.status)}`}></div>
                                        <div>
                                            <h3 className="font-medium text-foreground">{endpoint.name}</h3>
                                            <p className="text-sm text-muted-foreground">
                                                {endpoint.requests} requests • {endpoint.errors} errors
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <Badge variant={endpoint.status === 'active' ? 'default' : 'destructive'}>
                                            {getStatusText(endpoint.status)}
                                        </Badge>
                                        <span className="text-sm text-muted-foreground">{endpoint.uptime} uptime</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Recent Activity */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Requests</CardTitle>
                            <CardDescription>Latest API activity</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {[1, 2, 3, 4, 5].map((i) => (
                                    <div key={i} className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-foreground">GET /api/users</p>
                                            <p className="text-xs text-muted-foreground">2 minutes ago</p>
                                        </div>
                                        <Badge variant="outline">200 OK</Badge>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>System Health</CardTitle>
                            <CardDescription>Overall system status</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-foreground">Database</span>
                                    <Badge variant="default">Healthy</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-foreground">Cache</span>
                                    <Badge variant="default">Healthy</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-foreground">Queue</span>
                                    <Badge variant="default">Healthy</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-foreground">Storage</span>
                                    <Badge variant="default">Healthy</Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
