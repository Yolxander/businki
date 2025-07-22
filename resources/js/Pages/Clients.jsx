import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Plus,
    Search,
    Filter,
    Mail,
    Phone,
    Globe,
    Building,
    User,
    Calendar,
    DollarSign,
    Star,
    MoreHorizontal,
    Edit,
    Trash2
} from 'lucide-react';

export default function Clients({ auth, clients = [], error }) {

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
            <Head title="Clients" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Clients</h1>
                        <p className="text-muted-foreground">Manage your client relationships and contact information</p>
                    </div>
                    <Link href="/clients/create">
                        <Button>
                            <Plus className="w-4 h-4 mr-2" />
                            New Client
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                                    <input
                                        type="text"
                                        placeholder="Search clients..."
                                        className="w-full pl-10 pr-4 py-2 border border-input rounded-md bg-background text-foreground placeholder:text-muted-foreground"
                                    />
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button variant="outline" size="sm">
                                    <Filter className="w-4 h-4 mr-2" />
                                    Filter
                                </Button>
                                <Button variant="outline" size="sm">
                                    <Calendar className="w-4 h-4 mr-2" />
                                    Sort
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Error Message */}
                {error && (
                    <Card className="border-red-200 bg-red-50">
                        <CardContent className="pt-6">
                            <div className="text-center text-red-600">
                                <p>{error}</p>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Clients Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {clients.map((client) => (
                        <Card key={client.id} className="hover:shadow-lg transition-shadow">
                            <CardHeader>
                                <div className="flex justify-between items-start">
                                    <div className="flex-1">
                                        <CardTitle className="text-lg">{client.name}</CardTitle>
                                        <CardDescription className="flex items-center mt-1">
                                            <Building className="w-4 h-4 mr-1" />
                                            {client.company || 'No company'}
                                        </CardDescription>
                                    </div>
                                    <div className="flex flex-col items-end space-y-1">
                                        <Badge className={getStatusColor(client.status)}>
                                            {client.status}
                                        </Badge>
                                        <div className="flex">
                                            {renderStars(client.rating || 3)}
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {/* Contact Information */}
                                <div className="space-y-2">
                                    <div className="flex items-center text-sm">
                                        <User className="w-4 h-4 mr-2 text-muted-foreground" />
                                        <span className="text-foreground">{client.contactPerson || 'No contact person'}</span>
                                    </div>
                                    <div className="flex items-center text-sm">
                                        <Mail className="w-4 h-4 mr-2 text-muted-foreground" />
                                        <span className="text-muted-foreground">{client.email || 'No email'}</span>
                                    </div>
                                    <div className="flex items-center text-sm">
                                        <Phone className="w-4 h-4 mr-2 text-muted-foreground" />
                                        <span className="text-muted-foreground">{client.phone || 'No phone'}</span>
                                    </div>
                                    <div className="flex items-center text-sm">
                                        <Globe className="w-4 h-4 mr-2 text-muted-foreground" />
                                        <span className="text-muted-foreground">{client.website || 'No website'}</span>
                                    </div>
                                </div>

                                {/* Business Information */}
                                {(client.industry || client.budget_range || client.lead_source) && (
                                    <div className="space-y-2 pt-2 border-t">
                                        {client.industry && (
                                            <div className="flex items-center text-sm">
                                                <Building className="w-4 h-4 mr-2 text-muted-foreground" />
                                                <span className="text-muted-foreground">Industry: </span>
                                                <span className="text-foreground ml-1">{client.industry}</span>
                                            </div>
                                        )}
                                        {client.budget_range && (
                                            <div className="flex items-center text-sm">
                                                <DollarSign className="w-4 h-4 mr-2 text-muted-foreground" />
                                                <span className="text-muted-foreground">Budget: </span>
                                                <span className="text-foreground ml-1">{client.budget_range}</span>
                                            </div>
                                        )}
                                        {client.lead_source && (
                                            <div className="flex items-center text-sm">
                                                <User className="w-4 h-4 mr-2 text-muted-foreground" />
                                                <span className="text-muted-foreground">Source: </span>
                                                <span className="text-foreground ml-1">{client.lead_source}</span>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Stats */}
                                <div className="grid grid-cols-2 gap-4 pt-2 border-t">
                                    <div className="text-center">
                                        <div className="text-lg font-semibold text-foreground">{client.projects || 0}</div>
                                        <div className="text-xs text-muted-foreground">Projects</div>
                                    </div>
                                    <div className="text-center">
                                        <div className="text-lg font-semibold text-foreground">
                                            ${(client.totalRevenue || 0).toLocaleString()}
                                        </div>
                                        <div className="text-xs text-muted-foreground">Revenue</div>
                                    </div>
                                </div>

                                {/* Last Contact */}
                                <div className="flex items-center justify-between text-sm pt-2 border-t">
                                    <div className="flex items-center">
                                        <Calendar className="w-4 h-4 mr-1 text-muted-foreground" />
                                        <span className="text-muted-foreground">Last contact:</span>
                                    </div>
                                    <span className="text-foreground">
                                        {client.lastContact ? new Date(client.lastContact).toLocaleDateString() : 'Never'}
                                    </span>
                                </div>

                                {/* Actions */}
                                <div className="flex gap-2 pt-2">
                                    <Link href={`/clients/${client.id}`}>
                                        <Button variant="outline" size="sm" className="flex-1">
                                            View Details
                                        </Button>
                                    </Link>
                                    <Link href={`/clients/${client.id}/edit`}>
                                        <Button variant="outline" size="sm">
                                            <Edit className="w-4 h-4" />
                                        </Button>
                                    </Link>
                                    <Button variant="outline" size="sm">
                                        <Trash2 className="w-4 h-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Empty State */}
                {clients.length === 0 && (
                    <Card>
                        <CardContent className="pt-12 pb-12">
                            <div className="text-center">
                                <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                                    <User className="w-8 h-8 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-medium text-foreground mb-2">No clients yet</h3>
                                <p className="text-muted-foreground mb-4">
                                    Get started by adding your first client
                                </p>
                                <Link href="/clients/create">
                                    <Button>
                                        <Plus className="w-4 h-4 mr-2" />
                                        Add Client
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
