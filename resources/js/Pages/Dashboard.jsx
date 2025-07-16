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

export default function Dashboard({ auth, stats }) {
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

    const recentActivity = [
        { endpoint: 'POST /api/ai/generate', user: 'john.doe@example.com', status: 'success', time: '2 min ago' },
        { endpoint: 'GET /api/users', user: 'jane.smith@example.com', status: 'success', time: '5 min ago' },
        { endpoint: 'POST /api/auth/login', user: 'admin@businki.com', status: 'success', time: '8 min ago' },
        { endpoint: 'DELETE /api/tasks/123', user: 'freelancer@example.com', status: 'error', time: '12 min ago' },
        { endpoint: 'GET /api/proposals', user: 'client@example.com', status: 'success', time: '15 min ago' }
    ];

    const getStatusColor = (status) => {
        switch (status) {
            case 'active': return 'bg-green-500';
            case 'warning': return 'bg-yellow-500';
            case 'error': return 'bg-red-500';
            default: return 'bg-gray-500';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'active': return 'Active';
            case 'warning': return 'Warning';
            case 'error': return 'Error';
            default: return 'Unknown';
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
                    {apiMetrics.map((metric, index) => (
                        <Card key={index} className="bg-card border-border">
                            <CardHeader className="pb-2">
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-sm font-medium text-muted-foreground">
                                        {metric.title}
                                    </CardTitle>
                                    <metric.icon className="w-4 h-4 text-muted-foreground" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-foreground">{metric.value}</div>
                                <div className="flex items-center space-x-2 mt-2">
                                    <div className={`flex items-center text-sm ${
                                        metric.trendDirection === 'up' ? 'text-green-500' : 'text-red-500'
                                    }`}>
                                        {metric.trendDirection === 'up' ? (
                                            <ArrowUp className="w-3 h-3 mr-1" />
                                        ) : (
                                            <ArrowDown className="w-3 h-3 mr-1" />
                                        )}
                                        {metric.trend}
                                    </div>
                                </div>
                                <p className="text-xs text-muted-foreground mt-1">{metric.description}</p>
                                <p className="text-xs text-muted-foreground">{metric.subtitle}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* API Performance Chart */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-foreground">API Performance</CardTitle>
                                <CardDescription className="text-muted-foreground">Response times and request volume over time</CardDescription>
                            </div>
                            <div className="flex space-x-2">
                                <Button variant="outline" size="sm" className="bg-muted text-muted-foreground">
                                    Last 7 days
                                </Button>
                                <Button variant="outline" size="sm" className="bg-muted text-muted-foreground">
                                    Last 30 days
                                </Button>
                                <Button variant="default" size="sm" className="bg-primary text-primary-foreground">
                                    Last 24 hours
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="h-64 bg-muted rounded-lg flex items-center justify-center">
                            <div className="text-center">
                                <BarChart3 className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                                <p className="text-muted-foreground">API performance chart would go here</p>
                                <p className="text-sm text-muted-foreground">Showing response times and request volume</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Endpoint Status Table */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-foreground">Endpoint Status</CardTitle>
                                <CardDescription className="text-muted-foreground">Real-time status of all API endpoints</CardDescription>
                            </div>
                            <div className="flex space-x-2">
                                <Button variant="outline" size="sm">
                                    <Globe className="w-4 h-4 mr-2" />
                                    Health Check
                                </Button>
                                <Button variant="outline" size="sm">
                                    <Plus className="w-4 h-4 mr-2" />
                                    Add Endpoint
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-border">
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Endpoint</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Status</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Requests (24h)</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Errors (24h)</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Uptime</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {endpointStatus.map((endpoint, index) => (
                                        <tr key={index} className="border-b border-border">
                                            <td className="py-3 px-4 text-sm text-foreground font-mono">{endpoint.name}</td>
                                            <td className="py-3 px-4">
                                                <div className="flex items-center">
                                                    <div className={`w-2 h-2 rounded-full ${getStatusColor(endpoint.status)} mr-2`}></div>
                                                    <span className="text-sm">{getStatusText(endpoint.status)}</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-4 text-sm text-foreground">{endpoint.requests}</td>
                                            <td className="py-3 px-4 text-sm text-foreground">{endpoint.errors}</td>
                                            <td className="py-3 px-4 text-sm text-foreground">{endpoint.uptime}</td>
                                            <td className="py-3 px-4">
                                                <Button variant="ghost" size="sm">
                                                    <Settings className="w-4 h-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Recent Activity */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <CardTitle className="text-foreground">Recent API Activity</CardTitle>
                        <CardDescription className="text-muted-foreground">Latest API requests and responses</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {recentActivity.map((activity, index) => (
                                <div key={index} className="flex items-center justify-between p-3 bg-muted rounded-lg">
                                    <div className="flex-1">
                                        <p className="font-medium text-foreground font-mono">{activity.endpoint}</p>
                                        <p className="text-sm text-muted-foreground">{activity.user}</p>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <Badge variant={activity.status === 'success' ? 'default' : 'destructive'}>
                                            {activity.status}
                                        </Badge>
                                        <span className="text-sm text-muted-foreground">{activity.time}</span>
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
