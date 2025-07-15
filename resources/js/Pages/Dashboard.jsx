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
    AlertCircle
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

    const statCards = [
        {
            title: 'Total Clients',
            value: statsData.totalClients,
            icon: Users,
            description: 'Active clients',
            color: 'text-blue-600'
        },
        {
            title: 'Total Proposals',
            value: statsData.totalProposals,
            icon: FileText,
            description: 'Proposals created',
            color: 'text-green-600'
        },
        {
            title: 'Active Projects',
            value: statsData.totalProjects,
            icon: Calendar,
            description: 'Ongoing projects',
            color: 'text-purple-600'
        },
        {
            title: 'Total Tasks',
            value: statsData.totalTasks,
            icon: CheckCircle,
            description: 'Completed tasks',
            color: 'text-orange-600'
        }
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />

            <div className="space-y-6">
                {/* Quick Actions */}
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-2xl font-bold text-gray-900">Welcome back, {auth.user.name}!</h2>
                        <p className="text-gray-600">Here's what's happening with your business today.</p>
                    </div>
                    <div className="flex space-x-3">
                        <Button variant="outline">
                            <Calendar className="w-4 h-4 mr-2" />
                            View Calendar
                        </Button>
                        <Button>
                            <FileText className="w-4 h-4 mr-2" />
                            New Proposal
                        </Button>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {statCards.map((stat, index) => (
                        <Card key={index} className="hover:shadow-lg transition-shadow">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-gray-600">
                                    {stat.title}
                                </CardTitle>
                                <stat.icon className={`w-4 h-4 ${stat.color}`} />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stat.value}</div>
                                <p className="text-xs text-gray-500">{stat.description}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Revenue and Pending Tasks */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <DollarSign className="w-5 h-5 mr-2 text-green-600" />
                                Revenue Overview
                            </CardTitle>
                            <CardDescription>Your earnings this month</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-green-600">
                                ${statsData.revenue.toLocaleString()}
                            </div>
                            <div className="flex items-center text-sm text-gray-500 mt-2">
                                <TrendingUp className="w-4 h-4 mr-1 text-green-500" />
                                +12% from last month
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Clock className="w-5 h-5 mr-2 text-orange-600" />
                                Pending Tasks
                            </CardTitle>
                            <CardDescription>Tasks requiring attention</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-orange-600">
                                {statsData.pendingTasks}
                            </div>
                            <div className="flex items-center text-sm text-gray-500 mt-2">
                                <AlertCircle className="w-4 h-4 mr-1 text-orange-500" />
                                {statsData.pendingTasks > 0 ? 'Needs attention' : 'All caught up!'}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Activity */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Proposals</CardTitle>
                            <CardDescription>Latest proposals created</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {statsData.recentProposals && statsData.recentProposals.length > 0 ? (
                                <div className="space-y-4">
                                    {statsData.recentProposals.slice(0, 5).map((proposal, index) => (
                                        <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <p className="font-medium">{proposal.title}</p>
                                                <p className="text-sm text-gray-500">{proposal.client_name}</p>
                                            </div>
                                            <Badge variant={proposal.status === 'approved' ? 'default' : 'secondary'}>
                                                {proposal.status}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8 text-gray-500">
                                    <FileText className="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                    <p>No recent proposals</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Tasks</CardTitle>
                            <CardDescription>Latest task updates</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {statsData.recentTasks && statsData.recentTasks.length > 0 ? (
                                <div className="space-y-4">
                                    {statsData.recentTasks.slice(0, 5).map((task, index) => (
                                        <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <p className="font-medium">{task.title}</p>
                                                <p className="text-sm text-gray-500">{task.project_name}</p>
                                            </div>
                                            <Badge variant={task.status === 'completed' ? 'default' : 'secondary'}>
                                                {task.status}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8 text-gray-500">
                                    <CheckCircle className="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                    <p>No recent tasks</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
