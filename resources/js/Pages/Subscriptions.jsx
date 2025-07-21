import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import {
    Plus,
    Search,
    Filter,
    Calendar,
    DollarSign,
    Users,
    TrendingUp,
    AlertCircle,
    CheckCircle,
    Clock,
    Edit,
    Trash2,
    Eye,
    MoreHorizontal,
    CreditCard
} from 'lucide-react';

export default function Subscriptions({ auth }) {
    const [subscriptions, setSubscriptions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [showCreateDialog, setShowCreateDialog] = useState(false);
    const [newSubscription, setNewSubscription] = useState({
        title: '',
        client_id: '',
        service_type: '',
        billing_cycle: 'monthly',
        amount: '',
        description: '',
        start_date: '',
        end_date: '',
        status: 'active'
    });

    useEffect(() => {
        // Mock data for subscriptions
        const mockSubscriptions = [
            {
                id: 1,
                title: 'Website Maintenance - Monthly',
                client: {
                    id: 1,
                    name: 'Acme Corp',
                    company: 'Acme Corporation'
                },
                service_type: 'Website Maintenance',
                billing_cycle: 'monthly',
                amount: 299,
                description: 'Monthly website maintenance including updates, security patches, and content updates',
                start_date: '2024-01-01',
                end_date: '2024-12-31',
                status: 'active',
                next_billing: '2024-02-01',
                total_billed: 299,
                payments_received: 1
            },
            {
                id: 2,
                title: 'SEO Services - Quarterly',
                client: {
                    id: 2,
                    name: 'TechStart Inc',
                    company: 'TechStart Inc'
                },
                service_type: 'SEO Services',
                billing_cycle: 'quarterly',
                amount: 1500,
                description: 'Quarterly SEO optimization and reporting services',
                start_date: '2024-01-15',
                end_date: '2024-12-31',
                status: 'active',
                next_billing: '2024-04-15',
                total_billed: 1500,
                payments_received: 1
            },
            {
                id: 3,
                title: 'Cloud Hosting - Yearly',
                client: {
                    id: 3,
                    name: 'Global Solutions',
                    company: 'Global Solutions Ltd'
                },
                service_type: 'Cloud Hosting',
                billing_cycle: 'yearly',
                amount: 2400,
                description: 'Annual cloud hosting and server management services',
                start_date: '2024-01-01',
                end_date: '2024-12-31',
                status: 'paused',
                next_billing: '2025-01-01',
                total_billed: 2400,
                payments_received: 1
            }
        ];

        setSubscriptions(mockSubscriptions);
        setLoading(false);
    }, []);

    const filteredSubscriptions = subscriptions.filter(subscription => {
        const matchesSearch = subscription.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            subscription.client.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            subscription.client.company.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || subscription.status === statusFilter;
        return matchesSearch && matchesStatus;
    });

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

    const handleCreateSubscription = () => {
        const subscription = {
            id: subscriptions.length + 1,
            ...newSubscription,
            client: {
                id: parseInt(newSubscription.client_id),
                name: 'New Client',
                company: 'New Company'
            },
            next_billing: newSubscription.start_date,
            total_billed: 0,
            payments_received: 0
        };

        setSubscriptions([...subscriptions, subscription]);
        setNewSubscription({
            title: '',
            client_id: '',
            service_type: '',
            billing_cycle: 'monthly',
            amount: '',
            description: '',
            start_date: '',
            end_date: '',
            status: 'active'
        });
        setShowCreateDialog(false);
    };

    const totalRevenue = subscriptions.reduce((sum, sub) => sum + sub.total_billed, 0);
    const activeSubscriptions = subscriptions.filter(sub => sub.status === 'active').length;
    const totalSubscriptions = subscriptions.length;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Subscriptions" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Subscriptions</h1>
                        <p className="text-muted-foreground">Manage recurring services and maintenance plans</p>
                    </div>
                    <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="w-4 h-4 mr-2" />
                                New Subscription
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-2xl">
                            <DialogHeader>
                                <DialogTitle>Create New Subscription</DialogTitle>
                                <DialogDescription>
                                    Set up a new recurring service for your client
                                </DialogDescription>
                            </DialogHeader>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="col-span-2">
                                    <Label htmlFor="title">Subscription Title</Label>
                                    <Input
                                        id="title"
                                        value={newSubscription.title}
                                        onChange={(e) => setNewSubscription({...newSubscription, title: e.target.value})}
                                        placeholder="e.g., Website Maintenance - Monthly"
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="client">Client</Label>
                                    <Select value={newSubscription.client_id} onValueChange={(value) => setNewSubscription({...newSubscription, client_id: value})}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select client" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">Acme Corp</SelectItem>
                                            <SelectItem value="2">TechStart Inc</SelectItem>
                                            <SelectItem value="3">Global Solutions</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <Label htmlFor="service_type">Service Type</Label>
                                    <Select value={newSubscription.service_type} onValueChange={(value) => setNewSubscription({...newSubscription, service_type: value})}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select service" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Website Maintenance">Website Maintenance</SelectItem>
                                            <SelectItem value="SEO Services">SEO Services</SelectItem>
                                            <SelectItem value="Cloud Hosting">Cloud Hosting</SelectItem>
                                            <SelectItem value="Content Management">Content Management</SelectItem>
                                            <SelectItem value="Technical Support">Technical Support</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <Label htmlFor="billing_cycle">Billing Cycle</Label>
                                    <Select value={newSubscription.billing_cycle} onValueChange={(value) => setNewSubscription({...newSubscription, billing_cycle: value})}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="monthly">Monthly</SelectItem>
                                            <SelectItem value="quarterly">Quarterly</SelectItem>
                                            <SelectItem value="yearly">Yearly</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <Label htmlFor="amount">Amount ($)</Label>
                                    <Input
                                        id="amount"
                                        type="number"
                                        value={newSubscription.amount}
                                        onChange={(e) => setNewSubscription({...newSubscription, amount: e.target.value})}
                                        placeholder="299"
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="start_date">Start Date</Label>
                                    <Input
                                        id="start_date"
                                        type="date"
                                        value={newSubscription.start_date}
                                        onChange={(e) => setNewSubscription({...newSubscription, start_date: e.target.value})}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="end_date">End Date</Label>
                                    <Input
                                        id="end_date"
                                        type="date"
                                        value={newSubscription.end_date}
                                        onChange={(e) => setNewSubscription({...newSubscription, end_date: e.target.value})}
                                    />
                                </div>
                                <div className="col-span-2">
                                    <Label htmlFor="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        value={newSubscription.description}
                                        onChange={(e) => setNewSubscription({...newSubscription, description: e.target.value})}
                                        placeholder="Describe the services included in this subscription..."
                                        rows={3}
                                    />
                                </div>
                            </div>
                            <div className="flex justify-end space-x-2 mt-6">
                                <Button variant="outline" onClick={() => setShowCreateDialog(false)}>
                                    Cancel
                                </Button>
                                <Button onClick={handleCreateSubscription}>
                                    Create Subscription
                                </Button>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-green-100 rounded-lg">
                                    <TrendingUp className="w-6 h-6 text-green-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Revenue</p>
                                    <p className="text-2xl font-bold text-foreground">${totalRevenue.toLocaleString()}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-blue-100 rounded-lg">
                                    <CheckCircle className="w-6 h-6 text-blue-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Active Subscriptions</p>
                                    <p className="text-2xl font-bold text-foreground">{activeSubscriptions}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-purple-100 rounded-lg">
                                    <Users className="w-6 h-6 text-purple-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Subscriptions</p>
                                    <p className="text-2xl font-bold text-foreground">{totalSubscriptions}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-orange-100 rounded-lg">
                                    <Clock className="w-6 h-6 text-orange-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Due This Month</p>
                                    <p className="text-2xl font-bold text-foreground">3</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="p-6">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                                    <Input
                                        placeholder="Search subscriptions..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-40">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="paused">Paused</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                        <SelectItem value="expired">Expired</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button variant="outline">
                                    <Filter className="w-4 h-4 mr-2" />
                                    More Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Subscriptions List */}
                <Tabs defaultValue="all" className="space-y-6">
                    <TabsList>
                        <TabsTrigger value="all">All Subscriptions ({filteredSubscriptions.length})</TabsTrigger>
                        <TabsTrigger value="active">Active ({subscriptions.filter(s => s.status === 'active').length})</TabsTrigger>
                        <TabsTrigger value="paused">Paused ({subscriptions.filter(s => s.status === 'paused').length})</TabsTrigger>
                        <TabsTrigger value="cancelled">Cancelled ({subscriptions.filter(s => s.status === 'cancelled').length})</TabsTrigger>
                    </TabsList>

                    <TabsContent value="all" className="space-y-4">
                        {filteredSubscriptions.map((subscription) => (
                            <Card key={subscription.id}>
                                <CardContent className="p-6">
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center space-x-3 mb-2">
                                                <h3 className="text-lg font-semibold text-foreground">{subscription.title}</h3>
                                                <Badge className={getStatusColor(subscription.status)}>
                                                    {subscription.status}
                                                </Badge>
                                                <Badge className={getBillingCycleColor(subscription.billing_cycle)}>
                                                    {subscription.billing_cycle}
                                                </Badge>
                                            </div>
                                            <div className="flex items-center space-x-4 text-sm text-muted-foreground mb-2">
                                                <span className="flex items-center">
                                                    <Users className="w-4 h-4 mr-1" />
                                                    {subscription.client.name} ({subscription.client.company})
                                                </span>
                                                <span className="flex items-center">
                                                    <DollarSign className="w-4 h-4 mr-1" />
                                                    ${subscription.amount}/{subscription.billing_cycle}
                                                </span>
                                                <span className="flex items-center">
                                                    <Calendar className="w-4 h-4 mr-1" />
                                                    Next: {new Date(subscription.next_billing).toLocaleDateString()}
                                                </span>
                                            </div>
                                            <p className="text-sm text-muted-foreground line-clamp-2">
                                                {subscription.description}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Link href={`/subscriptions/${subscription.id}`}>
                                                <Button variant="outline" size="sm">
                                                    <Eye className="w-4 h-4 mr-1" />
                                                    View
                                                </Button>
                                            </Link>
                                            <Button variant="outline" size="sm">
                                                <Edit className="w-4 h-4 mr-1" />
                                                Edit
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <MoreHorizontal className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}

                        {filteredSubscriptions.length === 0 && (
                            <Card>
                                <CardContent className="p-12 text-center">
                                    <div className="mx-auto w-12 h-12 bg-muted rounded-full flex items-center justify-center mb-4">
                                        <AlertCircle className="w-6 h-6 text-muted-foreground" />
                                    </div>
                                    <h3 className="text-lg font-medium text-foreground mb-2">No subscriptions found</h3>
                                    <p className="text-muted-foreground mb-4">
                                        {searchTerm || statusFilter !== 'all'
                                            ? 'Try adjusting your search or filters'
                                            : 'Get started by creating your first subscription'
                                        }
                                    </p>
                                    {!searchTerm && statusFilter === 'all' && (
                                        <Button onClick={() => setShowCreateDialog(true)}>
                                            <Plus className="w-4 h-4 mr-2" />
                                            Create Subscription
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </TabsContent>

                    <TabsContent value="active" className="space-y-4">
                        {filteredSubscriptions.filter(s => s.status === 'active').map((subscription) => (
                            <Card key={subscription.id}>
                                <CardContent className="p-6">
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center space-x-3 mb-2">
                                                <h3 className="text-lg font-semibold text-foreground">{subscription.title}</h3>
                                                <Badge className={getStatusColor(subscription.status)}>
                                                    {subscription.status}
                                                </Badge>
                                                <Badge className={getBillingCycleColor(subscription.billing_cycle)}>
                                                    {subscription.billing_cycle}
                                                </Badge>
                                            </div>
                                            <div className="flex items-center space-x-4 text-sm text-muted-foreground mb-2">
                                                <span className="flex items-center">
                                                    <Users className="w-4 h-4 mr-1" />
                                                    {subscription.client.name} ({subscription.client.company})
                                                </span>
                                                <span className="flex items-center">
                                                    <DollarSign className="w-4 h-4 mr-1" />
                                                    ${subscription.amount}/{subscription.billing_cycle}
                                                </span>
                                                <span className="flex items-center">
                                                    <Calendar className="w-4 h-4 mr-1" />
                                                    Next: {new Date(subscription.next_billing).toLocaleDateString()}
                                                </span>
                                            </div>
                                            <p className="text-sm text-muted-foreground line-clamp-2">
                                                {subscription.description}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Link href={`/subscriptions/${subscription.id}`}>
                                                <Button variant="outline" size="sm">
                                                    <Eye className="w-4 h-4 mr-1" />
                                                    View
                                                </Button>
                                            </Link>
                                            <Button variant="outline" size="sm">
                                                <Edit className="w-4 h-4 mr-1" />
                                                Edit
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <MoreHorizontal className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </TabsContent>

                    <TabsContent value="paused" className="space-y-4">
                        {filteredSubscriptions.filter(s => s.status === 'paused').map((subscription) => (
                            <Card key={subscription.id}>
                                <CardContent className="p-6">
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center space-x-3 mb-2">
                                                <h3 className="text-lg font-semibold text-foreground">{subscription.title}</h3>
                                                <Badge className={getStatusColor(subscription.status)}>
                                                    {subscription.status}
                                                </Badge>
                                                <Badge className={getBillingCycleColor(subscription.billing_cycle)}>
                                                    {subscription.billing_cycle}
                                                </Badge>
                                            </div>
                                            <div className="flex items-center space-x-4 text-sm text-muted-foreground mb-2">
                                                <span className="flex items-center">
                                                    <Users className="w-4 h-4 mr-1" />
                                                    {subscription.client.name} ({subscription.client.company})
                                                </span>
                                                <span className="flex items-center">
                                                    <DollarSign className="w-4 h-4 mr-1" />
                                                    ${subscription.amount}/{subscription.billing_cycle}
                                                </span>
                                                <span className="flex items-center">
                                                    <Calendar className="w-4 h-4 mr-1" />
                                                    Next: {new Date(subscription.next_billing).toLocaleDateString()}
                                                </span>
                                            </div>
                                            <p className="text-sm text-muted-foreground line-clamp-2">
                                                {subscription.description}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Link href={`/subscriptions/${subscription.id}`}>
                                                <Button variant="outline" size="sm">
                                                    <Eye className="w-4 h-4 mr-1" />
                                                    View
                                                </Button>
                                            </Link>
                                            <Button variant="outline" size="sm">
                                                <Edit className="w-4 h-4 mr-1" />
                                                Edit
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <MoreHorizontal className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </TabsContent>

                    <TabsContent value="cancelled" className="space-y-4">
                        {filteredSubscriptions.filter(s => s.status === 'cancelled').map((subscription) => (
                            <Card key={subscription.id}>
                                <CardContent className="p-6">
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center space-x-3 mb-2">
                                                <h3 className="text-lg font-semibold text-foreground">{subscription.title}</h3>
                                                <Badge className={getStatusColor(subscription.status)}>
                                                    {subscription.status}
                                                </Badge>
                                                <Badge className={getBillingCycleColor(subscription.billing_cycle)}>
                                                    {subscription.billing_cycle}
                                                </Badge>
                                            </div>
                                            <div className="flex items-center space-x-4 text-sm text-muted-foreground mb-2">
                                                <span className="flex items-center">
                                                    <Users className="w-4 h-4 mr-1" />
                                                    {subscription.client.name} ({subscription.client.company})
                                                </span>
                                                <span className="flex items-center">
                                                    <DollarSign className="w-4 h-4 mr-1" />
                                                    ${subscription.amount}/{subscription.billing_cycle}
                                                </span>
                                                <span className="flex items-center">
                                                    <Calendar className="w-4 h-4 mr-1" />
                                                    Next: {new Date(subscription.next_billing).toLocaleDateString()}
                                                </span>
                                            </div>
                                            <p className="text-sm text-muted-foreground line-clamp-2">
                                                {subscription.description}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Link href={`/subscriptions/${subscription.id}`}>
                                                <Button variant="outline" size="sm">
                                                    <Eye className="w-4 h-4 mr-1" />
                                                    View
                                                </Button>
                                            </Link>
                                            <Button variant="outline" size="sm">
                                                <Edit className="w-4 h-4 mr-1" />
                                                Edit
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <MoreHorizontal className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </TabsContent>
                </Tabs>
            </div>
        </AuthenticatedLayout>
    );
}
