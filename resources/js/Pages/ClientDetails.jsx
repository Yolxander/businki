import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    ArrowLeft,
    Edit,
    Mail,
    Phone,
    Globe,
    Building,
    User,
    Calendar,
    DollarSign,
    Star,
    MapPin,
    FileText,
    MessageSquare,
    Clock,
    Target,
    TrendingUp,
    MoreHorizontal,
    Trash2,
    Copy,
    ExternalLink
} from 'lucide-react';

export default function ClientDetails({ auth, clientId }) {
    // Mock client data - in real app this would come from props
    const client = {
        id: clientId,
        name: 'Acme Corporation',
        contactPerson: 'John Smith',
        email: 'john.smith@acme.com',
        phone: '+1 (555) 123-4567',
        website: 'www.acme.com',
        company: 'Acme Corp',
        status: 'active',
        industry: 'Technology',
        address: '123 Business Ave',
        city: 'San Francisco',
        state: 'CA',
        zipCode: '94105',
        country: 'USA',
        budget: '$25,000 - $50,000',
        source: 'Referral',
        notes: 'Great client to work with. Very responsive and clear about requirements.',
        rating: 5,
        totalRevenue: 25000,
        projects: [
            {
                id: 1,
                name: 'Website Redesign',
                status: 'in-progress',
                progress: 65,
                dueDate: '2024-02-15',
                budget: 15000
            },
            {
                id: 2,
                name: 'Brand Identity',
                status: 'completed',
                progress: 100,
                dueDate: '2024-01-30',
                budget: 10000
            }
        ],
        contactHistory: [
            {
                id: 1,
                type: 'email',
                subject: 'Project Update - Website Redesign',
                date: '2024-01-15',
                status: 'sent'
            },
            {
                id: 2,
                type: 'call',
                subject: 'Initial Consultation',
                date: '2024-01-10',
                status: 'completed'
            }
        ]
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'prospect':
                return 'bg-blue-100 text-blue-800';
            case 'inactive':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getProjectStatusColor = (status) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in-progress':
                return 'bg-blue-100 text-blue-800';
            case 'planned':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const renderStars = (rating) => {
        return Array.from({ length: 5 }, (_, i) => (
            <Star
                key={i}
                className={`w-4 h-4 ${i < rating ? 'text-yellow-400 fill-current' : 'text-gray-300'}`}
            />
        ));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${client.name} - Client Details`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/clients">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Clients
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{client.name}</h1>
                            <p className="text-muted-foreground">Client Details & Management</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={`/clients/${client.id}/edit`}>
                            <Button variant="outline" size="sm">
                                <Edit className="w-4 h-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Button variant="outline" size="sm">
                            <MoreHorizontal className="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                {/* Client Overview */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Info */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>Client Information</span>
                                    <div className="flex items-center space-x-2">
                                        <Badge className={getStatusColor(client.status)}>
                                            {client.status}
                                        </Badge>
                                        <div className="flex">
                                            {renderStars(client.rating)}
                                        </div>
                                    </div>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <div className="flex items-center text-sm">
                                            <User className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Contact Person:</span>
                                            <span className="ml-2 text-muted-foreground">{client.contactPerson}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Building className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Company:</span>
                                            <span className="ml-2 text-muted-foreground">{client.company}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Building className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Industry:</span>
                                            <span className="ml-2 text-muted-foreground">{client.industry}</span>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <div className="flex items-center text-sm">
                                            <DollarSign className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Budget Range:</span>
                                            <span className="ml-2 text-muted-foreground">{client.budget}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Target className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Lead Source:</span>
                                            <span className="ml-2 text-muted-foreground">{client.source}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <TrendingUp className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Total Revenue:</span>
                                            <span className="ml-2 text-muted-foreground">${client.totalRevenue.toLocaleString()}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-4 border-t">
                                    <h4 className="font-medium mb-2">Contact Information</h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="flex items-center text-sm">
                                            <Mail className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="text-muted-foreground">{client.email}</span>
                                            <Button variant="ghost" size="sm" className="ml-2">
                                                <Copy className="w-3 h-3" />
                                            </Button>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Phone className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="text-muted-foreground">{client.phone}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Globe className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="text-muted-foreground">{client.website}</span>
                                            <Button variant="ghost" size="sm" className="ml-2">
                                                <ExternalLink className="w-3 h-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-4 border-t">
                                    <h4 className="font-medium mb-2">Address</h4>
                                    <div className="flex items-start text-sm">
                                        <MapPin className="w-4 h-4 mr-2 text-muted-foreground mt-0.5" />
                                        <span className="text-muted-foreground">
                                            {client.address}, {client.city}, {client.state} {client.zipCode}, {client.country}
                                        </span>
                                    </div>
                                </div>

                                {client.notes && (
                                    <div className="pt-4 border-t">
                                        <h4 className="font-medium mb-2">Notes</h4>
                                        <p className="text-sm text-muted-foreground">{client.notes}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quick Stats */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Stats</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-foreground">{client.projects.length}</div>
                                    <div className="text-sm text-muted-foreground">Total Projects</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-foreground">${client.totalRevenue.toLocaleString()}</div>
                                    <div className="text-sm text-muted-foreground">Total Revenue</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-foreground">{client.rating}/5</div>
                                    <div className="text-sm text-muted-foreground">Client Rating</div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button className="w-full" size="sm">
                                    <Mail className="w-4 h-4 mr-2" />
                                    Send Email
                                </Button>
                                <Button variant="outline" className="w-full" size="sm">
                                    <Phone className="w-4 h-4 mr-2" />
                                    Schedule Call
                                </Button>
                                <Button variant="outline" className="w-full" size="sm">
                                    <FileText className="w-4 h-4 mr-2" />
                                    Create Project
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Tabs Content */}
                <Tabs defaultValue="projects" className="space-y-6">
                    <TabsList>
                        <TabsTrigger value="projects">Projects</TabsTrigger>
                        <TabsTrigger value="contact">Contact History</TabsTrigger>
                        <TabsTrigger value="documents">Documents</TabsTrigger>
                    </TabsList>

                    <TabsContent value="projects" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Projects</CardTitle>
                                <CardDescription>All projects for this client</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {client.projects.map((project) => (
                                        <div key={project.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3">
                                                    <h4 className="font-medium">{project.name}</h4>
                                                    <Badge className={getProjectStatusColor(project.status)}>
                                                        {project.status.replace('-', ' ')}
                                                    </Badge>
                                                </div>
                                                <div className="flex items-center space-x-4 mt-2 text-sm text-muted-foreground">
                                                    <span>Budget: ${project.budget.toLocaleString()}</span>
                                                    <span>Due: {new Date(project.dueDate).toLocaleDateString()}</span>
                                                    <span>Progress: {project.progress}%</span>
                                                </div>
                                            </div>
                                            <Link href={`/projects/${project.id}`}>
                                                <Button variant="outline" size="sm">
                                                    View Details
                                                </Button>
                                            </Link>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="contact" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Contact History</CardTitle>
                                <CardDescription>Recent communications with this client</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {client.contactHistory.map((contact) => (
                                        <div key={contact.id} className="flex items-center space-x-4 p-4 border rounded-lg">
                                            <div className="flex-shrink-0">
                                                {contact.type === 'email' ? (
                                                    <Mail className="w-5 h-5 text-blue-500" />
                                                ) : (
                                                    <Phone className="w-5 h-5 text-green-500" />
                                                )}
                                            </div>
                                            <div className="flex-1">
                                                <h4 className="font-medium">{contact.subject}</h4>
                                                <p className="text-sm text-muted-foreground">
                                                    {new Date(contact.date).toLocaleDateString()}
                                                </p>
                                            </div>
                                            <Badge variant="outline">{contact.status}</Badge>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="documents" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Documents</CardTitle>
                                <CardDescription>Files and documents related to this client</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="text-center py-8">
                                    <FileText className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                                    <p className="text-muted-foreground">No documents uploaded yet</p>
                                    <Button className="mt-4" size="sm">
                                        Upload Document
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AuthenticatedLayout>
    );
}
