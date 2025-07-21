import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Save, Send } from 'lucide-react';

export default function CreateProposal({ auth }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Proposal" />

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
                            <h1 className="text-2xl font-bold text-foreground">Create Proposal</h1>
                            <p className="text-muted-foreground">Create a new proposal for your client</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" size="sm">
                            <Save className="w-4 h-4 mr-2" />
                            Save Draft
                        </Button>
                        <Button size="sm">
                            <Send className="w-4 h-4 mr-2" />
                            Send to Client
                        </Button>
                    </div>
                </div>

                {/* Proposal Form */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Form */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Proposal Details</CardTitle>
                                <CardDescription>Basic information about the proposal</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="title">Proposal Title</Label>
                                    <Input
                                        id="title"
                                        placeholder="Enter proposal title"
                                        defaultValue="Website Redesign Proposal"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="scope">Project Scope</Label>
                                    <Textarea
                                        id="scope"
                                        placeholder="Describe the project scope and objectives"
                                        rows={4}
                                        defaultValue="Complete website redesign with modern UI/UX design, responsive layout, and enhanced user experience. The project includes comprehensive research, design, development, and testing phases."
                                    />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="price">Total Price ($)</Label>
                                        <Input
                                            id="price"
                                            type="number"
                                            placeholder="0.00"
                                            defaultValue="15000"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="status">Status</Label>
                                        <Select defaultValue="draft">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="draft">Draft</SelectItem>
                                                <SelectItem value="sent">Sent</SelectItem>
                                                <SelectItem value="accepted">Accepted</SelectItem>
                                                <SelectItem value="rejected">Rejected</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Deliverables</CardTitle>
                                <CardDescription>List all items included in this proposal</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label>Deliverables List</Label>
                                    <div className="space-y-2">
                                        {[
                                            'Homepage Design',
                                            'About Page',
                                            'Contact Form',
                                            'Mobile Responsive Design',
                                            'SEO Optimization',
                                            'Content Management System',
                                            'Analytics Integration',
                                            'Performance Optimization'
                                        ].map((deliverable, index) => (
                                            <div key={index} className="flex items-center space-x-2 p-2 border rounded">
                                                <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                <span className="text-sm">{deliverable}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Timeline & Phases</CardTitle>
                                <CardDescription>Break down the project into phases</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-4">
                                    {[
                                        { description: 'Discovery & Planning', duration: '1 week', price: 2000 },
                                        { description: 'Design Phase', duration: '2 weeks', price: 6000 },
                                        { description: 'Development', duration: '3 weeks', price: 5000 },
                                        { description: 'Testing & Launch', duration: '1 week', price: 2000 }
                                    ].map((phase, index) => (
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
                                                    <span>Price: ${phase.price.toLocaleString()}</span>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Client Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="client">Select Client</Label>
                                    <Select defaultValue="1">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Choose a client" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">John Doe - Acme Corporation</SelectItem>
                                            <SelectItem value="2">Jane Smith - Tech Solutions</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="intake">Intake Response</Label>
                                    <Select defaultValue="1">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select intake response" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">Website Redesign Request</SelectItem>
                                            <SelectItem value="2">Brand Identity Package</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button className="w-full" size="sm">
                                    <Save className="w-4 h-4 mr-2" />
                                    Save Draft
                                </Button>
                                <Button variant="outline" className="w-full" size="sm">
                                    <Send className="w-4 h-4 mr-2" />
                                    Send to Client
                                </Button>
                                <Button variant="outline" className="w-full" size="sm">
                                    Preview
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
