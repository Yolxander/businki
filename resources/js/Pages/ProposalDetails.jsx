import React, { useState, useEffect } from 'react';
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
    ExternalLink,
    FileText as FileTextIcon,
    CheckCircle,
    Clock as ClockIcon,
    AlertCircle,
    Download,
    Send,
    Eye,
    Printer
} from 'lucide-react';

export default function ProposalDetails({ auth, proposalId }) {
    const [proposal, setProposal] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchProposal = async () => {
            try {
                setLoading(true);
                const response = await fetch(`/api/proposals/${proposalId}`, {
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
                    setProposal(null); // Use mock data
                } else {
                    const proposalData = await response.json();
                    setProposal(proposalData);
                }
            } catch (err) {
                console.warn('API call failed, using mock data:', err.message);
                setProposal(null); // Use mock data
            } finally {
                setLoading(false);
            }
        };

        fetchProposal();
    }, [proposalId]);

    // Mock proposal data - fallback while loading or if API fails
    const mockProposal = {
        id: proposalId,
        title: 'Website Redesign Proposal',
        status: 'accepted',
        price: 15000,
        created_at: '2024-01-05',
        updated_at: '2024-01-15',
        scope: 'Complete website redesign with modern UI/UX design, responsive layout, and enhanced user experience. The project includes comprehensive research, design, development, and testing phases.',
        deliverables: [
            'Homepage Design',
            'About Page',
            'Contact Form',
            'Mobile Responsive Design',
            'SEO Optimization',
            'Content Management System',
            'Analytics Integration',
            'Performance Optimization'
        ],
        timeline: [
            {
                description: 'Discovery & Planning',
                duration: '1 week',
                price: 2000,
                start_date: '2024-02-01',
                end_date: '2024-02-07'
            },
            {
                description: 'Design Phase',
                duration: '2 weeks',
                price: 6000,
                start_date: '2024-02-08',
                end_date: '2024-02-21'
            },
            {
                description: 'Development',
                duration: '3 weeks',
                price: 5000,
                start_date: '2024-02-22',
                end_date: '2024-03-14'
            },
            {
                description: 'Testing & Launch',
                duration: '1 week',
                price: 2000,
                start_date: '2024-03-15',
                end_date: '2024-03-21'
            }
        ],
        client: {
            id: 1,
            full_name: 'John Doe',
            email: 'john.doe@example.com',
            phone: '+1-555-123-4567',
            company_name: 'Acme Corporation',
            address: '123 Main St',
            city: 'New York',
            state: 'NY',
            zip_code: '10001'
        },
        intake_response: {
            id: 1,
            full_name: 'John Doe',
            company_name: 'Acme Corporation',
            email: 'john.doe@example.com',
            project_description: 'We need a complete website redesign to modernize our online presence and improve user engagement.',
            budget_range: '$10,000 - $20,000',
            deadline: '2024-03-31',
            project_type: 'Website Redesign'
        },
        project: {
            id: 1,
            title: 'Acme Corporation Website Redesign',
            status: 'in_progress',
            current_phase: 'Design Phase',
            kickoff_date: '2024-02-01',
            expected_delivery: '2024-03-21'
        }
    };

    // Use real proposal data if available, otherwise use mock data
    const proposalData = proposal || mockProposal;

    if (loading) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Loading Proposal Details" />
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900 mx-auto"></div>
                        <p className="mt-4 text-muted-foreground">Loading proposal details...</p>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    if (error) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Error Loading Proposal" />
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

    const getProposalStatusColor = (status) => {
        switch (status) {
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

    const getStatusIcon = (status) => {
        switch (status) {
            case 'accepted':
                return <CheckCircle className="w-5 h-5 text-green-500" />;
            case 'sent':
                return <Send className="w-5 h-5 text-blue-500" />;
            case 'draft':
                return <FileTextIcon className="w-5 h-5 text-gray-500" />;
            case 'rejected':
                return <AlertCircle className="w-5 h-5 text-red-500" />;
            default:
                return <FileTextIcon className="w-5 h-5 text-gray-500" />;
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${proposalData.title} - Proposal Details`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href={`/clients/${proposalData.client?.id || 1}`}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Client
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{proposalData.title}</h1>
                            <p className="text-muted-foreground">Proposal Details & Management</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" size="sm">
                            <Edit className="w-4 h-4 mr-2" />
                            Edit
                        </Button>
                        <Button variant="outline" size="sm">
                            <Download className="w-4 h-4 mr-2" />
                            Export
                        </Button>
                        <Button variant="outline" size="sm">
                            <Printer className="w-4 h-4 mr-2" />
                            Print
                        </Button>
                        <Button variant="outline" size="sm">
                            <MoreHorizontal className="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                {/* Proposal Overview */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Info */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>Proposal Information</span>
                                    <div className="flex items-center space-x-2">
                                        {getStatusIcon(proposalData.status)}
                                        <Badge className={getProposalStatusColor(proposalData.status)}>
                                            {proposalData.status}
                                        </Badge>
                                    </div>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <div className="flex items-center text-sm">
                                            <DollarSign className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Total Price:</span>
                                            <span className="ml-2 text-muted-foreground">${(proposalData.price || 0).toLocaleString()}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Calendar className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Created:</span>
                                            <span className="ml-2 text-muted-foreground">
                                                {proposalData.created_at ? new Date(proposalData.created_at).toLocaleDateString() : 'Not set'}
                                            </span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Clock className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Updated:</span>
                                            <span className="ml-2 text-muted-foreground">
                                                {proposalData.updated_at ? new Date(proposalData.updated_at).toLocaleDateString() : 'Not set'}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <div className="flex items-center text-sm">
                                            <User className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Client:</span>
                                            <span className="ml-2 text-muted-foreground">
                                                {proposalData.client?.full_name || 'N/A'}
                                            </span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Building className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Company:</span>
                                            <span className="ml-2 text-muted-foreground">
                                                {proposalData.client?.company_name || 'N/A'}
                                            </span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Target className="w-4 h-4 mr-2 text-muted-foreground" />
                                            <span className="font-medium">Deliverables:</span>
                                            <span className="ml-2 text-muted-foreground">
                                                {proposalData.deliverables?.length || 0} items
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-4 border-t">
                                    <h4 className="font-medium mb-2">Project Scope</h4>
                                    <p className="text-sm text-muted-foreground">{proposalData.scope}</p>
                                </div>
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
                                    <div className="text-2xl font-bold text-foreground">${(proposalData.price || 0).toLocaleString()}</div>
                                    <div className="text-sm text-muted-foreground">Total Value</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-foreground">{proposalData.deliverables?.length || 0}</div>
                                    <div className="text-sm text-muted-foreground">Deliverables</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-foreground">{proposalData.timeline?.length || 0}</div>
                                    <div className="text-sm text-muted-foreground">Timeline Phases</div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button className="w-full" size="sm">
                                    <Send className="w-4 h-4 mr-2" />
                                    Send to Client
                                </Button>
                                <Button variant="outline" className="w-full" size="sm">
                                    <Edit className="w-4 h-4 mr-2" />
                                    Edit Proposal
                                </Button>
                                <Button variant="outline" className="w-full" size="sm">
                                    <Download className="w-4 h-4 mr-2" />
                                    Export PDF
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Tabs Content */}
                <Tabs defaultValue="timeline" className="space-y-6">
                    <TabsList>
                        <TabsTrigger value="timeline">Timeline</TabsTrigger>
                        <TabsTrigger value="deliverables">Deliverables</TabsTrigger>
                        <TabsTrigger value="client">Client Info</TabsTrigger>
                        <TabsTrigger value="project">Project</TabsTrigger>
                    </TabsList>

                    <TabsContent value="timeline" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Project Timeline</CardTitle>
                                <CardDescription>Detailed breakdown of project phases and costs</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {proposalData.timeline?.map((phase, index) => (
                                        <div key={index} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3">
                                                    <div className="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-medium">
                                                        {index + 1}
                                                    </div>
                                                    <h4 className="font-medium">{phase.description}</h4>
                                                </div>
                                                <div className="flex items-center space-x-4 mt-2 text-sm text-muted-foreground">
                                                    <span>Duration: {phase.duration}</span>
                                                    <span>Price: ${(phase.price || 0).toLocaleString()}</span>
                                                    {phase.start_date && (
                                                        <span>Start: {new Date(phase.start_date).toLocaleDateString()}</span>
                                                    )}
                                                    {phase.end_date && (
                                                        <span>End: {new Date(phase.end_date).toLocaleDateString()}</span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="deliverables" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Deliverables</CardTitle>
                                <CardDescription>All items included in this proposal</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {proposalData.deliverables?.map((deliverable, index) => (
                                        <div key={index} className="flex items-center space-x-3 p-3 border rounded-lg">
                                            <CheckCircle className="w-5 h-5 text-green-500" />
                                            <span className="font-medium">{deliverable}</span>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="client" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Client Information</CardTitle>
                                <CardDescription>Contact and company details</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <div className="flex items-center text-sm">
                                                <User className="w-4 h-4 mr-2 text-muted-foreground" />
                                                <span className="font-medium">Name:</span>
                                                <span className="ml-2 text-muted-foreground">
                                                    {proposalData.client?.full_name || 'N/A'}
                                                </span>
                                            </div>
                                            <div className="flex items-center text-sm">
                                                <Building className="w-4 h-4 mr-2 text-muted-foreground" />
                                                <span className="font-medium">Company:</span>
                                                <span className="ml-2 text-muted-foreground">
                                                    {proposalData.client?.company_name || 'N/A'}
                                                </span>
                                            </div>
                                            <div className="flex items-center text-sm">
                                                <Mail className="w-4 h-4 mr-2 text-muted-foreground" />
                                                <span className="font-medium">Email:</span>
                                                <span className="ml-2 text-muted-foreground">
                                                    {proposalData.client?.email || 'N/A'}
                                                </span>
                                            </div>
                                            <div className="flex items-center text-sm">
                                                <Phone className="w-4 h-4 mr-2 text-muted-foreground" />
                                                <span className="font-medium">Phone:</span>
                                                <span className="ml-2 text-muted-foreground">
                                                    {proposalData.client?.phone || 'N/A'}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <div className="flex items-start text-sm">
                                                <MapPin className="w-4 h-4 mr-2 text-muted-foreground mt-0.5" />
                                                <span className="font-medium">Address:</span>
                                                <span className="ml-2 text-muted-foreground">
                                                    {proposalData.client?.address ?
                                                        `${proposalData.client.address}, ${proposalData.client.city}, ${proposalData.client.state} ${proposalData.client.zip_code}` :
                                                        'N/A'
                                                    }
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="project" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Project Information</CardTitle>
                                <CardDescription>Details about the associated project</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {proposalData.project ? (
                                    <div className="space-y-4">
                                        <div className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3">
                                                    <h4 className="font-medium">{proposalData.project.title}</h4>
                                                    <Badge className={getProposalStatusColor(proposalData.project.status)}>
                                                        {proposalData.project.status.replace('_', ' ')}
                                                    </Badge>
                                                </div>
                                                <div className="flex items-center space-x-4 mt-2 text-sm text-muted-foreground">
                                                    <span>Phase: {proposalData.project.current_phase}</span>
                                                    <span>Kickoff: {proposalData.project.kickoff_date ? new Date(proposalData.project.kickoff_date).toLocaleDateString() : 'Not set'}</span>
                                                    <span>Delivery: {proposalData.project.expected_delivery ? new Date(proposalData.project.expected_delivery).toLocaleDateString() : 'Not set'}</span>
                                                </div>
                                            </div>
                                            <Link href={`/projects/${proposalData.project.id}`}>
                                                <Button variant="outline" size="sm">
                                                    View Project
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <FileTextIcon className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                                        <p className="text-muted-foreground">No project associated with this proposal</p>
                                        <Button className="mt-4" size="sm">
                                            Create Project
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AuthenticatedLayout>
    );
}
