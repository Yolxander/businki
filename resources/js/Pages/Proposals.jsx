import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Plus,
    Search,
    Filter,
    FileText,
    Calendar,
    DollarSign,
    User,
    Building,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2
} from 'lucide-react';

export default function Proposals({ auth }) {
    const [proposals, setProposals] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');

    useEffect(() => {
        // Mock data for now - in real app this would fetch from API
        const mockProposals = [
            {
                id: 1,
                title: 'Website Redesign Proposal',
                status: 'accepted',
                price: 15000,
                created_at: '2024-01-05',
                client: {
                    full_name: 'John Doe',
                    company_name: 'Acme Corporation'
                }
            },
            {
                id: 2,
                title: 'Brand Identity Package',
                status: 'sent',
                price: 8000,
                created_at: '2024-01-20',
                client: {
                    full_name: 'Jane Smith',
                    company_name: 'Tech Solutions'
                }
            },
            {
                id: 3,
                title: 'Mobile App Development',
                status: 'draft',
                price: 25000,
                created_at: '2024-01-25',
                client: {
                    full_name: 'Mike Johnson',
                    company_name: 'Startup Inc'
                }
            }
        ];

        setProposals(mockProposals);
        setLoading(false);
    }, []);

    const getStatusColor = (status) => {
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

    const filteredProposals = proposals.filter(proposal => {
        const matchesSearch = proposal.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            proposal.client.full_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            proposal.client.company_name.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || proposal.status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    if (loading) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Proposals" />
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900 mx-auto"></div>
                        <p className="mt-4 text-muted-foreground">Loading proposals...</p>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Proposals" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Proposals</h1>
                        <p className="text-muted-foreground">Manage and track all your proposals</p>
                    </div>
                    <Link href="/proposals/create">
                        <Button>
                            <Plus className="w-4 h-4 mr-2" />
                            Create Proposal
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center space-x-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                                    <Input
                                        placeholder="Search proposals..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            <Select value={statusFilter} onValueChange={setStatusFilter}>
                                <SelectTrigger className="w-48">
                                    <SelectValue placeholder="Filter by status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="sent">Sent</SelectItem>
                                    <SelectItem value="accepted">Accepted</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Proposals List */}
                <div className="space-y-4">
                    {filteredProposals.map((proposal) => (
                        <Card key={proposal.id} className="hover:shadow-md transition-shadow">
                            <CardContent className="p-6">
                                <div className="flex items-center justify-between">
                                    <div className="flex-1">
                                        <div className="flex items-center space-x-3 mb-2">
                                            <FileText className="w-5 h-5 text-blue-500" />
                                            <h3 className="font-medium text-lg">{proposal.title}</h3>
                                            <Badge className={getStatusColor(proposal.status)}>
                                                {proposal.status}
                                            </Badge>
                                        </div>
                                        <div className="flex items-center space-x-6 text-sm text-muted-foreground">
                                            <div className="flex items-center space-x-1">
                                                <User className="w-4 h-4" />
                                                <span>{proposal.client.full_name}</span>
                                            </div>
                                            <div className="flex items-center space-x-1">
                                                <Building className="w-4 h-4" />
                                                <span>{proposal.client.company_name}</span>
                                            </div>
                                            <div className="flex items-center space-x-1">
                                                <DollarSign className="w-4 h-4" />
                                                <span>${proposal.price.toLocaleString()}</span>
                                            </div>
                                            <div className="flex items-center space-x-1">
                                                <Calendar className="w-4 h-4" />
                                                <span>{new Date(proposal.created_at).toLocaleDateString()}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Link href={`/proposals/${proposal.id}`}>
                                            <Button variant="outline" size="sm">
                                                <Eye className="w-4 h-4 mr-2" />
                                                View
                                            </Button>
                                        </Link>
                                        <Button variant="outline" size="sm">
                                            <Edit className="w-4 h-4 mr-2" />
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
                </div>

                {/* Empty State */}
                {filteredProposals.length === 0 && (
                    <Card>
                        <CardContent className="p-12 text-center">
                            <FileText className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                            <h3 className="text-lg font-medium mb-2">No proposals found</h3>
                            <p className="text-muted-foreground mb-4">
                                {searchTerm || statusFilter !== 'all'
                                    ? 'Try adjusting your search or filters'
                                    : 'Get started by creating your first proposal'
                                }
                            </p>
                            <Link href="/proposals/create">
                                <Button>
                                    <Plus className="w-4 h-4 mr-2" />
                                    Create Proposal
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
