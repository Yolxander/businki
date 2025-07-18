import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    BarChart3,
    TrendingUp,
    Users,
    Activity,
    Calendar,
    Download
} from 'lucide-react';

export default function Analytics({ auth }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Analytics" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Analytics</h1>
                        <p className="text-muted-foreground">System performance and usage analytics</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline">
                            <Calendar className="w-4 h-4 mr-2" />
                            Date Range
                        </Button>
                        <Button>
                            <Download className="w-4 h-4 mr-2" />
                            Export Report
                        </Button>
                    </div>
                </div>

                {/* Analytics Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total Users
                            </CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">1,234</div>
                            <p className="text-xs text-muted-foreground">+12% from last month</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Active Sessions
                            </CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">89</div>
                            <p className="text-xs text-muted-foreground">Currently online</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                API Requests
                            </CardTitle>
                            <BarChart3 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">45.2K</div>
                            <p className="text-xs text-muted-foreground">+8% from last week</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Growth Rate
                            </CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">+23%</div>
                            <p className="text-xs text-muted-foreground">Monthly growth</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Charts Placeholder */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>User Activity</CardTitle>
                            <CardDescription>Daily active users over time</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-64 bg-muted rounded-lg flex items-center justify-center">
                                <div className="text-center">
                                    <BarChart3 className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                                    <p className="text-muted-foreground">User activity chart would go here</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>System Performance</CardTitle>
                            <CardDescription>Response times and throughput</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-64 bg-muted rounded-lg flex items-center justify-center">
                                <div className="text-center">
                                    <Activity className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                                    <p className="text-muted-foreground">Performance chart would go here</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Detailed Analytics */}
                <Card>
                    <CardHeader>
                        <CardTitle>Detailed Analytics</CardTitle>
                        <CardDescription>Comprehensive system analytics and insights</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="p-4 border rounded-lg">
                                    <h3 className="font-medium text-foreground mb-2">Top Endpoints</h3>
                                    <div className="space-y-2">
                                        <div className="flex justify-between text-sm">
                                            <span>GET /api/users</span>
                                            <span className="text-muted-foreground">45.2K</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>POST /api/auth/login</span>
                                            <span className="text-muted-foreground">23.1K</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>GET /api/proposals</span>
                                            <span className="text-muted-foreground">18.7K</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="p-4 border rounded-lg">
                                    <h3 className="font-medium text-foreground mb-2">Error Rates</h3>
                                    <div className="space-y-2">
                                        <div className="flex justify-between text-sm">
                                            <span>Overall</span>
                                            <span className="text-green-600">0.8%</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>API Errors</span>
                                            <span className="text-orange-600">1.2%</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>Auth Errors</span>
                                            <span className="text-red-600">2.1%</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="p-4 border rounded-lg">
                                    <h3 className="font-medium text-foreground mb-2">Performance</h3>
                                    <div className="space-y-2">
                                        <div className="flex justify-between text-sm">
                                            <span>Avg Response Time</span>
                                            <span className="text-muted-foreground">145ms</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>P95 Response Time</span>
                                            <span className="text-muted-foreground">320ms</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>Uptime</span>
                                            <span className="text-green-600">99.9%</span>
                                        </div>
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
