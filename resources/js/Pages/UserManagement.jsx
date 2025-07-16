import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Users,
    UserPlus,
    Search,
    Filter,
    MoreVertical,
    Mail,
    Phone,
    Calendar,
    Building,
    Star,
    Edit,
    Trash2,
    Eye,
    Shield,
    UserCheck
} from 'lucide-react';

export default function UserManagement({ auth }) {
    const clients = [
        {
            id: 1,
            name: 'Acme Corporation',
            contact: 'john.smith@acme.com',
            phone: '+1 (555) 123-4567',
            status: 'active',
            projects: 12,
            totalSpent: '$45,000',
            lastActive: '2 days ago',
            type: 'client'
        },
        {
            id: 2,
            name: 'TechStart Inc',
            contact: 'sarah.jones@techstart.com',
            phone: '+1 (555) 987-6543',
            status: 'active',
            projects: 8,
            totalSpent: '$28,500',
            lastActive: '1 week ago',
            type: 'client'
        },
        {
            id: 3,
            name: 'Global Solutions',
            contact: 'mike.wilson@globalsolutions.com',
            phone: '+1 (555) 456-7890',
            status: 'inactive',
            projects: 3,
            totalSpent: '$12,000',
            lastActive: '3 weeks ago',
            type: 'client'
        }
    ];

    const freelancers = [
        {
            id: 1,
            name: 'Alex Johnson',
            email: 'alex.johnson@example.com',
            phone: '+1 (555) 111-2222',
            status: 'active',
            skills: ['React', 'Node.js', 'Python'],
            rating: 4.8,
            completedProjects: 25,
            hourlyRate: '$75',
            lastActive: '1 day ago',
            type: 'freelancer'
        },
        {
            id: 2,
            name: 'Maria Garcia',
            email: 'maria.garcia@example.com',
            phone: '+1 (555) 333-4444',
            status: 'active',
            skills: ['UI/UX Design', 'Figma', 'Adobe Creative Suite'],
            rating: 4.9,
            completedProjects: 18,
            hourlyRate: '$85',
            lastActive: '3 days ago',
            type: 'freelancer'
        },
        {
            id: 3,
            name: 'David Chen',
            email: 'david.chen@example.com',
            phone: '+1 (555) 555-6666',
            status: 'pending',
            skills: ['Laravel', 'Vue.js', 'MySQL'],
            rating: 4.6,
            completedProjects: 12,
            hourlyRate: '$65',
            lastActive: '1 week ago',
            type: 'freelancer'
        }
    ];

    const getStatusColor = (status) => {
        switch (status) {
            case 'active': return 'bg-green-500';
            case 'inactive': return 'bg-gray-500';
            case 'pending': return 'bg-yellow-500';
            default: return 'bg-gray-500';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'active': return 'Active';
            case 'inactive': return 'Inactive';
            case 'pending': return 'Pending';
            default: return 'Unknown';
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="User Management" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">User Management</h1>
                        <p className="text-muted-foreground">Manage clients and freelancers</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline">
                            <Filter className="w-4 h-4 mr-2" />
                            Filter
                        </Button>
                        <Button variant="outline">
                            <Search className="w-4 h-4 mr-2" />
                            Search
                        </Button>
                        <Button>
                            <UserPlus className="w-4 h-4 mr-2" />
                            Add User
                        </Button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Clients</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">156</div>
                            <p className="text-xs text-muted-foreground">+12% from last month</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Active Freelancers</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">89</div>
                            <p className="text-xs text-muted-foreground">+8% from last month</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Pending Approvals</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">23</div>
                            <p className="text-xs text-muted-foreground">5 new this week</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-border">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Revenue</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">$2.4M</div>
                            <p className="text-xs text-muted-foreground">+15% from last month</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Clients Section */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-foreground">Clients</CardTitle>
                                <CardDescription className="text-muted-foreground">Manage your client relationships</CardDescription>
                            </div>
                            <Button variant="outline" size="sm">
                                <UserPlus className="w-4 h-4 mr-2" />
                                Add Client
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-border">
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Company</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Contact</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Status</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Projects</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Total Spent</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Last Active</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {clients.map((client) => (
                                        <tr key={client.id} className="border-b border-border">
                                            <td className="py-3 px-4">
                                                <div className="flex items-center">
                                                    <Building className="w-4 h-4 mr-2 text-muted-foreground" />
                                                    <span className="text-sm font-medium text-foreground">{client.name}</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div>
                                                    <p className="text-sm text-foreground">{client.contact}</p>
                                                    <p className="text-xs text-muted-foreground">{client.phone}</p>
                                                </div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="flex items-center">
                                                    <div className={`w-2 h-2 rounded-full ${getStatusColor(client.status)} mr-2`}></div>
                                                    <span className="text-sm">{getStatusText(client.status)}</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-4 text-sm text-foreground">{client.projects}</td>
                                            <td className="py-3 px-4 text-sm text-foreground">{client.totalSpent}</td>
                                            <td className="py-3 px-4 text-sm text-muted-foreground">{client.lastActive}</td>
                                            <td className="py-3 px-4">
                                                <div className="flex space-x-1">
                                                    <Button variant="ghost" size="sm">
                                                        <Eye className="w-4 h-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm">
                                                        <Edit className="w-4 h-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm">
                                                        <MoreVertical className="w-4 h-4" />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Freelancers Section */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-foreground">Freelancers</CardTitle>
                                <CardDescription className="text-muted-foreground">Manage your freelance talent pool</CardDescription>
                            </div>
                            <Button variant="outline" size="sm">
                                <UserPlus className="w-4 h-4 mr-2" />
                                Add Freelancer
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-border">
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Name</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Contact</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Skills</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Rating</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Rate</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Status</th>
                                        <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {freelancers.map((freelancer) => (
                                        <tr key={freelancer.id} className="border-b border-border">
                                            <td className="py-3 px-4">
                                                <div className="flex items-center">
                                                    <Users className="w-4 h-4 mr-2 text-muted-foreground" />
                                                    <span className="text-sm font-medium text-foreground">{freelancer.name}</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div>
                                                    <p className="text-sm text-foreground">{freelancer.email}</p>
                                                    <p className="text-xs text-muted-foreground">{freelancer.phone}</p>
                                                </div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="flex flex-wrap gap-1">
                                                    {freelancer.skills.map((skill, index) => (
                                                        <Badge key={index} variant="secondary" className="text-xs">
                                                            {skill}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="flex items-center">
                                                    <Star className="w-4 h-4 text-yellow-500 mr-1" />
                                                    <span className="text-sm text-foreground">{freelancer.rating}</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-4 text-sm text-foreground">{freelancer.hourlyRate}/hr</td>
                                            <td className="py-3 px-4">
                                                <div className="flex items-center">
                                                    <div className={`w-2 h-2 rounded-full ${getStatusColor(freelancer.status)} mr-2`}></div>
                                                    <span className="text-sm">{getStatusText(freelancer.status)}</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="flex space-x-1">
                                                    <Button variant="ghost" size="sm">
                                                        <Eye className="w-4 h-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm">
                                                        <Edit className="w-4 h-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm">
                                                        <UserCheck className="w-4 h-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm">
                                                        <MoreVertical className="w-4 h-4" />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
