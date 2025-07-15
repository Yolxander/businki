import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Users,
    FileText,
    Calendar,
    TrendingUp,
    DollarSign,
    CheckCircle,
    Clock,
    AlertCircle,
    ArrowUp,
    ArrowDown,
    BarChart3,
    Plus
} from 'lucide-react';

export default function Dashboard({ auth, stats }) {
    const defaultStats = {
        totalClients: 0,
        totalProposals: 0,
        totalProjects: 0,
        totalTasks: 0,
        recentProposals: [],
        recentTasks: [],
        revenue: 0,
        pendingTasks: 0
    };

    const statsData = stats || defaultStats;

    const metricCards = [
        {
            title: 'Total Revenue',
            value: '$1,250.00',
            trend: '+12.5%',
            trendDirection: 'up',
            description: 'Trending up this month',
            subtitle: 'Visitors for the last 6 months',
            icon: DollarSign
        },
        {
            title: 'New Customers',
            value: '1,234',
            trend: '-20%',
            trendDirection: 'down',
            description: 'Down 20% this period',
            subtitle: 'Acquisition needs attention',
            icon: Users
        },
        {
            title: 'Active Accounts',
            value: '45,678',
            trend: '+12.5%',
            trendDirection: 'up',
            description: 'Strong user retention',
            subtitle: 'Engagement exceed targets',
            icon: CheckCircle
        },
        {
            title: 'Growth Rate',
            value: '4.5%',
            trend: '+4.5%',
            trendDirection: 'up',
            description: 'Steady performance',
            subtitle: 'Meets growth projections',
            icon: TrendingUp
        }
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Documents</h1>
                        <p className="text-muted-foreground">Manage your business documents and analytics</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" size="icon">
                            <FileText className="w-4 h-4" />
                        </Button>
                        <Button variant="outline" size="icon">
                            <BarChart3 className="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                {/* Metrics Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {metricCards.map((metric, index) => (
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

                {/* Total Visitors Chart */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-foreground">Total Visitors</CardTitle>
                                <CardDescription className="text-muted-foreground">Total for the last 3 months</CardDescription>
                            </div>
                            <div className="flex space-x-2">
                                <Button variant="outline" size="sm" className="bg-muted text-muted-foreground">
                                    Last 7 days
                                </Button>
                                <Button variant="outline" size="sm" className="bg-muted text-muted-foreground">
                                    Last 30 days
                                </Button>
                                <Button variant="default" size="sm" className="bg-primary text-primary-foreground">
                                    Last 3 months
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="h-64 bg-muted rounded-lg flex items-center justify-center">
                            <div className="text-center">
                                <BarChart3 className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                                <p className="text-muted-foreground">Chart visualization would go here</p>
                                <p className="text-sm text-muted-foreground">Showing visitor data over time</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Documents Table */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex space-x-4">
                                <Button variant="default" className="bg-primary text-primary-foreground">
                                    Outline
                                </Button>
                                <Button variant="outline" className="bg-muted text-muted-foreground">
                                    Past Performance
                                    <Badge variant="secondary" className="ml-2">3</Badge>
                                </Button>
                                <Button variant="outline" className="bg-muted text-muted-foreground">
                                    Key Personnel
                                    <Badge variant="secondary" className="ml-2">2</Badge>
                                </Button>
                                <Button variant="outline" className="bg-muted text-muted-foreground">
                                    Focus Documents
                                </Button>
                            </div>
                            <div className="flex space-x-2">
                                <Button variant="outline" size="sm">
                                    Customize Columns
                                </Button>
                                <Button variant="outline" size="sm">
                                    <Plus className="w-4 h-4 mr-2" />
                                    Add Section
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-border">
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Header</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Section Type</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Target</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Limit</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Reviewer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr className="border-b border-border">
                                        <td className="py-3 px-4 text-sm text-foreground">Executive Summary</td>
                                        <td className="py-3 px-4 text-sm text-muted-foreground">Overview</td>
                                        <td className="py-3 px-4 text-sm text-foreground">Q4 2024</td>
                                        <td className="py-3 px-4 text-sm text-muted-foreground">2 pages</td>
                                        <td className="py-3 px-4 text-sm text-foreground">John Doe</td>
                                    </tr>
                                    <tr className="border-b border-border">
                                        <td className="py-3 px-4 text-sm text-foreground">Market Analysis</td>
                                        <td className="py-3 px-4 text-sm text-muted-foreground">Research</td>
                                        <td className="py-3 px-4 text-sm text-foreground">Q4 2024</td>
                                        <td className="py-3 px-4 text-sm text-muted-foreground">5 pages</td>
                                        <td className="py-3 px-4 text-sm text-foreground">Jane Smith</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
