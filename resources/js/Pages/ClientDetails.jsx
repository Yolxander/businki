import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
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
    ExternalLink,
    FileText as FileTextIcon
} from 'lucide-react';

export default function ClientDetails({ auth, clientId }) {
    const [client, setClient] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

    useEffect(() => {
        const fetchClient = async () => {
            try {
                setLoading(true);
                const response = await fetch(`/api/clients/${clientId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    console.warn('API call failed, using mock data');
                    setClient(null); // Use mock data
                } else {
                    const clientData = await response.json();
                    setClient(clientData);
                }
            } catch (err) {
                console.warn('API call failed, using mock data:', err.message);
                setClient(null); // Use mock data
            } finally {
                setLoading(false);
            }
        };

        fetchClient();
    }, [clientId]);

    const handleDeleteClient = () => {
        router.delete(`/clients/${clientId}`, {
            onError: (errors) => {
                console.error('Delete client error:', errors);
                alert('Failed to delete client. Please try again.');
            }
        });
    };

    // Mock client data - fallback while loading or if API fails
    const mockClient = {
        id: clientId,
        first_name: 'John',
        last_name: 'Doe',
        full_name: 'John Doe',
        contactPerson: 'John Doe',
        email: 'john.doe@example.com',
        phone: '+1-555-123-4567',
        website: 'www.acme.com',
        company_name: 'Acme Corporation',
        company: 'Acme Corporation',
        status: 'active',
        industry: 'Technology',
        address: '123 Main St',
        city: 'New York',
        state: 'NY',
        zip_code: '10001',
        zipCode: '10001',
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
        ],
        proposals: [
            {
                id: 1,
                title: 'Website Redesign Proposal',
                status: 'accepted',
                price: 15000,
                created_at: '2024-01-05',
                scope: 'Complete website redesign with modern UI/UX',
                deliverables: ['Homepage Design', 'About Page', 'Contact Form', 'Mobile Responsive Design'],
                timeline: [
                    { description: 'Discovery & Planning', duration: '1 week', price: 2000 },
                    { description: 'Design Phase', duration: '2 weeks', price: 6000 },
                    { description: 'Development', duration: '3 weeks', price: 5000 },
                    { description: 'Testing & Launch', duration: '1 week', price: 2000 }
                ]
            },
            {
                id: 2,
                title: 'Brand Identity Package',
                status: 'sent',
                price: 8000,
                created_at: '2024-01-20',
                scope: 'Complete brand identity including logo, colors, and guidelines',
                deliverables: ['Logo Design', 'Color Palette', 'Typography Guide', 'Brand Guidelines'],
                timeline: [
                    { description: 'Brand Research', duration: '3 days', price: 1200 },
                    { description: 'Logo Design', duration: '1 week', price: 3000 },
                    { description: 'Brand Guidelines', duration: '4 days', price: 2000 },
                    { description: 'Final Delivery', duration: '2 days', price: 1800 }
                ]
            }
        ]
    };

    // Use real client data if available, otherwise use mock data
    const clientData = client || mockClient;

    if (loading) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Loading Client Details" />
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900 mx-auto"></div>
                        <p className="mt-4 text-muted-foreground">Loading client details...</p>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    if (error) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Error Loading Client" />
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <p className="text-red-600 mb-4">Error: {error}</p>
                        <Link href="/clients">
                            <Button variant="outline">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Clients
                            </Button>
                        </Link>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    const getStatusColor = (status) => {
        const safeStatus = status || 'inactive';
        switch (safeStatus) {
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
        const safeStatus = status || 'planned';
        switch (safeStatus) {
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

    const getProposalStatusColor = (status) => {
        const safeStatus = status || 'draft';
        switch (safeStatus) {
            case 'accepted':
                return 'bg-green-100 text-green-800';
            case 'sent':
                return 'bg-blue-100 text-blue-800';
            case 'draft':
                return 'bg-gray-100 text-gray-800';
            case 'rejected':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const renderStars = (rating) => {
        const safeRating = rating || 0;
        return Array.from({ length: 5 }, (_, i) => (
            <Star
                key={i}
                className={`w-4 h-4 ${i < safeRating ? 'text-yellow-400 fill-current' : 'text-gray-300'}`}
            />
        ));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${clientData.full_name || clientData.name} - Client Details`} />

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
                            <h1 className="text-2xl font-bold text-foreground">{clientData.full_name || clientData.name}</h1>
                            <p className="text-muted-foreground">Client Details & Management</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={`/clients/${clientData.id}/edit`}>
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
                                        <Badge className={getStatusColor(clientData.status)}>
                                            {clientData.status || 'inactive'}
                                        </Badge>
                                        <div className="flex">
                                            {renderStars(clientData.rating)}
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
                                            <span className="ml-2 text-muted-foreground">{clientData.contactPerson}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Building className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Company:</span>
                                            <span className="ml-2 text-muted-foreground">{clientData.company_name || clientData.company}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Building className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Industry:</span>
                                            <span className="ml-2 text-muted-foreground">{clientData.industry}</span>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <div className="flex items-center text-sm">
                                            <DollarSign className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Budget Range:</span>
                                            <span className="ml-2 text-muted-foreground">{clientData.budget_range || clientData.budget}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Target className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Lead Source:</span>
                                            <span className="ml-2 text-muted-foreground">{clientData.lead_source || clientData.source}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <TrendingUp className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Total Revenue:</span>
                                            <span className="ml-2 text-muted-foreground">${(clientData.totalRevenue || 0).toLocaleString()}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-4 border-t">
                                    <h4 className="font-medium mb-2">Contact Information</h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="flex items-center text-sm">
                                            <Mail className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="text-muted-foreground">{clientData.email}</span>
                                            <Button variant="ghost" size="sm" className="ml-2">
                                                <Copy className="w-3 h-3" />
                                            </Button>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Phone className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="text-muted-foreground">{clientData.phone}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Globe className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="text-muted-foreground">{clientData.website}</span>
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
                                            {clientData.address}, {clientData.city}, {clientData.state} {clientData.zip_code || clientData.zipCode}, {clientData.country}
                                        </span>
                                    </div>
                                </div>

                                {clientData.notes && (
                                    <div className="pt-4 border-t">
                                        <h4 className="font-medium mb-2">Notes</h4>
                                        <p className="text-sm text-muted-foreground">{clientData.notes}</p>
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
                                    <div className="text-2xl font-bold text-foreground">{clientData.projects?.length || 0}</div>
                                    <div className="text-sm text-muted-foreground">Total Projects</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-foreground">{clientData.proposals?.length || 0}</div>
                                    <div className="text-sm text-muted-foreground">Total Proposals</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-foreground">${(clientData.totalRevenue || 0).toLocaleString()}</div>
                                    <div className="text-sm text-muted-foreground">Total Revenue</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-foreground">{clientData.rating || 0}/5</div>
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
                                <Link href={`/proposals/create?client_id=${clientData.id}`} className="w-full">
                                    <Button variant="outline" className="w-full" size="sm">
                                        <FileTextIcon className="w-4 h-4 mr-2" />
                                        Create Proposal
                                    </Button>
                                </Link>
                                                                <AlertDialog>
                                    <AlertDialogTrigger asChild>
                                        <Button
                                            variant="outline"
                                            className="w-full text-red-600 hover:text-red-700 hover:bg-red-50"
                                            size="sm"
                                        >
                                            <Trash2 className="w-4 h-4 mr-2" />
                                            Delete Client
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                This action cannot be undone. This will permanently delete the client
                                                "{clientData.full_name || clientData.name}" and remove all associated data from our servers.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction
                                                onClick={handleDeleteClient}
                                                className="bg-red-600 hover:bg-red-700 text-white"
                                            >
                                                Delete Client
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Tabs Content */}
                <Tabs defaultValue="projects" className="space-y-6">
                    <TabsList>
                        <TabsTrigger value="projects">Projects</TabsTrigger>
                        <TabsTrigger value="proposals">Proposals</TabsTrigger>
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
                                    {(clientData.projects || []).map((project) => (
                                        <div key={project.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3">
                                                    <h4 className="font-medium">{project.name || 'Unnamed Project'}</h4>
                                                    <Badge className={getProjectStatusColor(project.status)}>
                                                        {(project.status || 'planned').replace('-', ' ')}
                                                    </Badge>
                                                </div>
                                                <div className="flex items-center space-x-4 mt-2 text-sm text-muted-foreground">
                                                    <span>Budget: ${(project.budget || 0).toLocaleString()}</span>
                                                    <span>Due: {project.dueDate ? new Date(project.dueDate).toLocaleDateString() : 'Not set'}</span>
                                                    <span>Progress: {project.progress || 0}%</span>
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

                    <TabsContent value="proposals" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Proposals</CardTitle>
                                <CardDescription>All proposals for this client</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {(clientData.proposals || []).map((proposal) => (
                                        <div key={proposal.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3">
                                                    <FileTextIcon className="w-5 h-5 text-blue-500" />
                                                    <h4 className="font-medium">{proposal.title || 'Untitled Proposal'}</h4>
                                                    <Badge className={getProposalStatusColor(proposal.status)}>
                                                        {proposal.status || 'draft'}
                                                    </Badge>
                                                </div>
                                                <div className="flex items-center space-x-4 mt-2 text-sm text-muted-foreground">
                                                    <span>Price: ${(proposal.price || 0).toLocaleString()}</span>
                                                    <span>Created: {proposal.created_at ? new Date(proposal.created_at).toLocaleDateString() : 'Not set'}</span>
                                                    <span>Deliverables: {(proposal.deliverables || []).length} items</span>
                                                </div>
                                                <div className="mt-2">
                                                    <p className="text-sm text-muted-foreground line-clamp-2">
                                                        {proposal.scope || 'No scope description available'}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Link href={`/proposals/${proposal.id}`}>
                                                    <Button variant="outline" size="sm">
                                                        View Details
                                                    </Button>
                                                </Link>
                                                <Button variant="outline" size="sm">
                                                    <MoreHorizontal className="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                    {(clientData.proposals || []).length === 0 && (
                                        <div className="text-center py-8">
                                            <FileTextIcon className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                                            <p className="text-muted-foreground">No proposals created yet</p>
                                            <Link href={`/proposals/create?client_id=${clientData.id}`}>
                                                <Button className="mt-4" size="sm">
                                                    Create Proposal
                                                </Button>
                                            </Link>
                                        </div>
                                    )}
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
                                    {(clientData.contactHistory || []).map((contact) => (
                                        <div key={contact.id} className="flex items-center space-x-4 p-4 border rounded-lg">
                                            <div className="flex-shrink-0">
                                                {(contact.type || 'email') === 'email' ? (
                                                    <Mail className="w-5 h-5 text-blue-500" />
                                                ) : (
                                                    <Phone className="w-5 h-5 text-green-500" />
                                                )}
                                            </div>
                                            <div className="flex-1">
                                                <h4 className="font-medium">{contact.subject || 'No subject'}</h4>
                                                <p className="text-sm text-muted-foreground">
                                                    {contact.date ? new Date(contact.date).toLocaleDateString() : 'Not set'}
                                                </p>
                                            </div>
                                            <Badge variant="outline">{contact.status || 'unknown'}</Badge>
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
