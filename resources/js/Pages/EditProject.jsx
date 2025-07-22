import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ArrowLeft, Save, Building, Calendar, DollarSign, Users, Target, Plus, X, FileText, Clock, Star } from 'lucide-react';


export default function EditProject({ auth, project, clients, projectId }) {
    // Use real project data from props
    const projectData = project || {
        id: projectId,
        name: 'Loading...',
        description: '',
        status: 'not_started',
        priority: 'medium',
        kickoff_date: '',
        due_date: '',
        progress: 0,
        current_phase: ''
    };

    const [formData, setFormData] = useState({
        name: projectData.name,
        description: projectData.description,
        client_id: projectData.client_id,
        status: projectData.status,
        priority: projectData.priority,
        kickoff_date: projectData.kickoff_date ? projectData.kickoff_date.split('T')[0] : '',
        start_date: projectData.start_date ? projectData.start_date.split('T')[0] : '',
        due_date: projectData.due_date ? projectData.due_date.split('T')[0] : '',
        notes: projectData.notes,
        color: projectData.color,
        progress: projectData.progress || 0,
        current_phase: projectData.current_phase
    });
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState({});

    // Use real clients data from props
    const clientsData = clients || [];



    const handleSubmit = (e) => {
        e.preventDefault();
        setProcessing(true);
        setErrors({});

        router.put(`/projects/${projectId}`, formData, {
            preserveScroll: true,
            onError: (errors) => {
                toast.error('Failed to update project. Please try again.');
                setErrors(errors);
                setProcessing(false);
                console.error('Project update failed', errors);
            }
        });
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in-progress':
                return 'bg-blue-100 text-blue-800';
            case 'pending':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getPriorityColor = (priority) => {
        switch (priority) {
            case 'high':
                return 'bg-red-100 text-red-800';
            case 'medium':
                return 'bg-yellow-100 text-yellow-800';
            case 'low':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Edit Project" />

            <div className="max-w-6xl mx-auto">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center space-x-4">
                        <Link href={`/projects/${projectId}`}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Project
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">Edit Project</h1>
                            <p className="text-sm text-muted-foreground">Update project details and settings</p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <Tabs defaultValue="overview" className="space-y-6">
                        <TabsList className="grid w-full grid-cols-1">
                            <TabsTrigger value="overview">Overview</TabsTrigger>
                        </TabsList>

                        <TabsContent value="overview" className="space-y-6">
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                {/* Main Form */}
                                <div className="lg:col-span-2 space-y-6">
                                    {/* Basic Information */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Basic Information</CardTitle>
                                            <CardDescription>Update the core project details</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div>
                                                <Label htmlFor="name">Project Name</Label>
                                                <Input
                                                    id="name"
                                                    value={formData.name}
                                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                                    placeholder="Enter project name"
                                                    className={errors.name ? 'border-red-500' : ''}
                                                />
                                                {errors.name && <p className="text-sm text-red-500 mt-1">{errors.name}</p>}
                                            </div>

                                            <div>
                                                <Label htmlFor="description">Description</Label>
                                                <Textarea
                                                    id="description"
                                                    value={formData.description}
                                                    onChange={(e) => setFormData({...formData, description: e.target.value})}
                                                    placeholder="Describe the project in detail"
                                                    rows={4}
                                                    className={errors.description ? 'border-red-500' : ''}
                                                />
                                                {errors.description && <p className="text-sm text-red-500 mt-1">{errors.description}</p>}
                                            </div>

                                            <div>
                                                <Label htmlFor="notes">Notes</Label>
                                                <Textarea
                                                    id="notes"
                                                    value={formData.notes}
                                                    onChange={(e) => setFormData({...formData, notes: e.target.value})}
                                                    placeholder="Additional notes about the project"
                                                    rows={3}
                                                    className={errors.notes ? 'border-red-500' : ''}
                                                />
                                                {errors.notes && <p className="text-sm text-red-500 mt-1">{errors.notes}</p>}
                                            </div>

                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <Label htmlFor="client_id">Client</Label>
                                                    <Select value={formData.client_id} onValueChange={(value) => setFormData({...formData, client_id: value})}>
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Select client" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {clientsData.map((client) => (
                                                                <SelectItem key={client.id} value={client.id}>
                                                                    {client.company_name || `${client.first_name} ${client.last_name}`}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </div>

                                                <div>
                                                    <Label htmlFor="priority">Priority</Label>
                                                    <Select value={formData.priority} onValueChange={(value) => setFormData({...formData, priority: value})}>
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="low">Low</SelectItem>
                                                            <SelectItem value="medium">Medium</SelectItem>
                                                            <SelectItem value="high">High</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Project Settings */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Project Settings</CardTitle>
                                            <CardDescription>Configure timeline, budget, and status</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="grid grid-cols-3 gap-4">
                                                <div>
                                                    <Label htmlFor="kickoff_date">Kickoff Date</Label>
                                                    <Input
                                                        id="kickoff_date"
                                                        type="date"
                                                        value={formData.kickoff_date}
                                                        onChange={(e) => setFormData({...formData, kickoff_date: e.target.value})}
                                                    />
                                                </div>

                                                <div>
                                                    <Label htmlFor="start_date">Start Date</Label>
                                                    <Input
                                                        id="start_date"
                                                        type="date"
                                                        value={formData.start_date}
                                                        onChange={(e) => setFormData({...formData, start_date: e.target.value})}
                                                    />
                                                </div>

                                                <div>
                                                    <Label htmlFor="due_date">Due Date</Label>
                                                    <Input
                                                        id="due_date"
                                                        type="date"
                                                        value={formData.due_date}
                                                        onChange={(e) => setFormData({...formData, due_date: e.target.value})}
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-3 gap-4">
                                                <div>
                                                    <Label htmlFor="current_phase">Current Phase</Label>
                                                    <Input
                                                        id="current_phase"
                                                        value={formData.current_phase}
                                                        onChange={(e) => setFormData({...formData, current_phase: e.target.value})}
                                                        placeholder="Enter current phase"
                                                    />
                                                </div>

                                                <div>
                                                    <Label htmlFor="status">Status</Label>
                                                    <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="not_started">Not Started</SelectItem>
                                                            <SelectItem value="in_progress">In Progress</SelectItem>
                                                            <SelectItem value="paused">Paused</SelectItem>
                                                            <SelectItem value="completed">Completed</SelectItem>
                                                            <SelectItem value="planned">Planned</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>

                                                <div>
                                                    <Label htmlFor="progress">Progress (%)</Label>
                                                    <Input
                                                        id="progress"
                                                        type="number"
                                                        min="0"
                                                        max="100"
                                                        value={formData.progress}
                                                        onChange={(e) => setFormData({...formData, progress: parseInt(e.target.value) || 0})}
                                                        placeholder="0-100"
                                                    />
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>


                                </div>

                                {/* Sidebar */}
                                <div className="space-y-6">
                                    {/* Save Actions */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Save Changes</CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-3">
                                            <Button type="submit" className="w-full" disabled={processing}>
                                                <Save className="w-4 h-4 mr-2" />
                                                {processing ? 'Saving...' : 'Save Changes'}
                                            </Button>
                                            <Link href={`/projects/${projectId}`}>
                                                <Button variant="outline" className="w-full">
                                                    Cancel
                                                </Button>
                                            </Link>
                                        </CardContent>
                                    </Card>

                                    {/* Project Preview */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Project Preview</CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-3">
                                            <div>
                                                <h3 className="font-medium text-sm text-foreground">{formData.name}</h3>
                                                <p className="text-xs text-muted-foreground line-clamp-2">{formData.description}</p>
                                            </div>
                                            <div className="flex items-center justify-between text-xs text-muted-foreground">
                                                <span>Kickoff: {formData.kickoff_date}</span>
                                                <span>Due: {formData.due_date}</span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Badge className={getPriorityColor(formData.priority)}>
                                                    {formData.priority}
                                                </Badge>
                                                <Badge className={getStatusColor(formData.status)}>
                                                    {formData.status.replace('_', ' ')}
                                                </Badge>
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                Progress: {formData.progress}%
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                Phase: {formData.current_phase}
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
