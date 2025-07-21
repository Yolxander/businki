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
    Trash2,
    Pause,
    Play,
    Calendar,
    DollarSign,
    Users,
    FileText,
    CheckCircle,
    Clock,
    AlertCircle,
    Download,
    Send,
    MoreHorizontal,
    Plus
} from 'lucide-react';

export default function SubscriptionDetails({ auth, subscriptionId }) {
    const [subscription, setSubscription] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Mock subscription data
        const mockSubscription = {
            id: subscriptionId,
            title: 'Website Maintenance - Monthly',
            client: {
                id: 1,
                name: 'Acme Corp',
                company: 'Acme Corporation',
                email: 'contact@acmecorp.com',
                phone: '+1 (555) 123-4567'
            },
            service_type: 'Website Maintenance',
            billing_cycle: 'monthly',
            amount: 299,
            description: 'Monthly website maintenance including updates, security patches, and content updates. This includes regular backups, performance monitoring, and 24/7 support.',
            start_date: '2024-01-01',
            end_date: '2024-12-31',
            status: 'active',
            next_billing: '2024-02-01',
            total_billed: 299,
            payments_received: 1,
            billing_history: [
                {
                    id: 1,
                    date: '2024-01-01',
                    amount: 299,
                    status: 'paid',
                    invoice_number: 'INV-2024-001'
                },
                {
                    id: 2,
                    date: '2024-02-01',
                    amount: 299,
                    status: 'pending',
                    invoice_number: 'INV-2024-002'
                }
            ],
            deliverables: [
                'Monthly security updates',
                'Performance optimization',
                'Content updates (up to 5 pages)',
                '24/7 technical support',
                'Monthly backup',
                'Analytics reporting'
            ],
            notes: [
                {
                    id: 1,
                    date: '2024-01-15',
                    content: 'Client requested additional SEO optimization for their blog section.',
                    author: 'John Doe'
                },
                {
                    id: 2,
                    date: '2024-01-10',
                    content: 'Completed monthly security update and performance optimization.',
                    author: 'Jane Smith'
                }
            ]
        };

        setSubscription(mockSubscription);
        setLoading(false);
    }, [subscriptionId]);

    if (loading) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <div className="flex items-center justify-center h-64">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>
                        <p className="text-muted-foreground">Loading subscription details...</p>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    const getStatusColor = (status) => {
        switch (status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'paused': return 'bg-yellow-100 text-yellow-800';
            case 'cancelled': return 'bg-red-100 text-red-800';
            case 'expired': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getBillingCycleColor = (cycle) => {
        switch (cycle) {
            case 'monthly': return 'bg-blue-100 text-blue-800';
            case 'quarterly': return 'bg-purple-100 text-purple-800';
            case 'yearly': return 'bg-indigo-100 text-indigo-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getPaymentStatusColor = (status) => {
        switch (status) {
            case 'paid': return 'bg-green-100 text-green-800';
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'overdue': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${subscription.title} - Subscription Details`} />
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/subscriptions">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{subscription.title}</h1>
                            <p className="text-muted-foreground">Subscription Details</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline">
                            <Edit className="w-4 h-4 mr-2" />
                            Edit
                        </Button>
                        <Button variant="outline">
                            <Pause className="w-4 h-4 mr-2" />
                            Pause
                        </Button>
                        <Button variant="outline">
                            <MoreHorizontal className="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                {/* Top Row: Client Info + Subscription Status + Quick Actions */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Client Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Client Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Client Name</label>
                                <p className="text-sm text-foreground">{subscription.client.name}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Company</label>
                                <p className="text-sm text-foreground">{subscription.client.company}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Email</label>
                                <p className="text-sm text-foreground">{subscription.client.email}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Phone</label>
                                <p className="text-sm text-foreground">{subscription.client.phone}</p>
                            </div>
                            <div className="pt-4">
                                <Link href={`/clients/${subscription.client.id}`}>
                                    <Button variant="outline" className="w-full">
                                        <Users className="w-4 h-4 mr-2" />
                                        View Client Profile
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                    {/* Subscription Status */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Subscription Status</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">Status</span>
                                <Badge className={getStatusColor(subscription.status)}>
                                    {subscription.status}
                                </Badge>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">Billing Cycle</span>
                                <Badge className={getBillingCycleColor(subscription.billing_cycle)}>
                                    {subscription.billing_cycle}
                                </Badge>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">Payments Received</span>
                                <span className="text-sm font-medium text-foreground">{subscription.payments_received}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">Next Billing</span>
                                <span className="text-sm font-medium text-foreground">
                                    {new Date(subscription.next_billing).toLocaleDateString()}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                    {/* Quick Actions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <Button variant="outline" className="w-full justify-start">
                                <Send className="w-4 h-4 mr-2" />
                                Send Invoice
                            </Button>
                            <Button variant="outline" className="w-full justify-start">
                                <Edit className="w-4 h-4 mr-2" />
                                Edit Subscription
                            </Button>
                            <Button variant="outline" className="w-full justify-start">
                                <Pause className="w-4 h-4 mr-2" />
                                Pause Subscription
                            </Button>
                            <Button variant="outline" className="w-full justify-start text-red-600 hover:text-red-700">
                                <Trash2 className="w-4 h-4 mr-2" />
                                Cancel Subscription
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Tabbed Content: Full width below */}
                <Tabs defaultValue="overview" className="space-y-6">
                    <TabsList>
                        <TabsTrigger value="overview">Overview</TabsTrigger>
                        <TabsTrigger value="billing">Billing History</TabsTrigger>
                        <TabsTrigger value="deliverables">Deliverables</TabsTrigger>
                        <TabsTrigger value="notes">Notes</TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Subscription Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Service Type</label>
                                        <p className="text-sm text-foreground">{subscription.service_type}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Billing Cycle</label>
                                        <Badge className={getBillingCycleColor(subscription.billing_cycle)}>
                                            {subscription.billing_cycle}
                                        </Badge>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Start Date</label>
                                        <p className="text-sm text-foreground">
                                            {new Date(subscription.start_date).toLocaleDateString()}
                                        </p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">End Date</label>
                                        <p className="text-sm text-foreground">
                                            {new Date(subscription.end_date).toLocaleDateString()}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Description</label>
                                    <p className="text-sm text-foreground mt-1">{subscription.description}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="billing" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Billing History</CardTitle>
                                <CardDescription>Payment history and upcoming invoices</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {subscription.billing_history.map((payment) => (
                                        <div key={payment.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex items-center space-x-4">
                                                <div>
                                                    <p className="font-medium text-foreground">{payment.invoice_number}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {new Date(payment.date).toLocaleDateString()}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-4">
                                                <span className="font-semibold text-foreground">${payment.amount}</span>
                                                <Badge className={getPaymentStatusColor(payment.status)}>
                                                    {payment.status}
                                                </Badge>
                                                <Button variant="outline" size="sm">
                                                    <Download className="w-4 h-4 mr-1" />
                                                    Invoice
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="deliverables" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Service Deliverables</CardTitle>
                                <CardDescription>What's included in this subscription</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {subscription.deliverables.map((deliverable, index) => (
                                        <div key={index} className="flex items-center space-x-3">
                                            <CheckCircle className="w-5 h-5 text-green-500" />
                                            <span className="text-sm text-foreground">{deliverable}</span>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="notes" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Notes & Updates</CardTitle>
                                <CardDescription>Internal notes and client communications</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {subscription.notes.map((note) => (
                                        <div key={note.id} className="p-4 border rounded-lg">
                                            <div className="flex items-center justify-between mb-2">
                                                <span className="text-sm font-medium text-foreground">{note.author}</span>
                                                <span className="text-sm text-muted-foreground">
                                                    {new Date(note.date).toLocaleDateString()}
                                                </span>
                                            </div>
                                            <p className="text-sm text-foreground">{note.content}</p>
                                        </div>
                                    ))}
                                    <Button variant="outline" className="w-full">
                                        <Plus className="w-4 h-4 mr-2" />
                                        Add Note
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
