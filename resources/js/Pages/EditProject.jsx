import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ArrowLeft, Save, Building, Calendar, DollarSign, Users, Target, Plus, X, FileText, Clock, Star } from 'lucide-react';

export default function EditProject({ auth, projectId }) {
    const [newTag, setNewTag] = useState('');
    const [newPhase, setNewPhase] = useState('');

    // Mock project data - in real app this would come from props
    const project = {
        id: projectId,
        name: 'Website Redesign',
        description: 'Complete redesign of the company website with modern design, improved user experience, and better conversion optimization.',
        client: 'acme-corp',
        status: 'in-progress',
        priority: 'high',
        startDate: '2024-01-15',
        endDate: '2024-03-15',
        budget: '25000',
        actualCost: '18000',
        progress: 65,
        tags: ['Design', 'Development', 'SEO'],
        team: [
            { id: 1, name: 'John Smith', role: 'Project Manager', email: 'john@example.com' },
            { id: 2, name: 'Sarah Johnson', role: 'Designer', email: 'sarah@example.com' },
            { id: 3, name: 'Mike Wilson', role: 'Developer', email: 'mike@example.com' }
        ],
        phases: [
            { id: 1, name: 'Discovery & Planning', status: 'completed', startDate: '2024-01-15', endDate: '2024-01-30' },
            { id: 2, name: 'Design Phase', status: 'in-progress', startDate: '2024-02-01', endDate: '2024-02-15' },
            { id: 3, name: 'Development', status: 'pending', startDate: '2024-02-16', endDate: '2024-03-01' },
            { id: 4, name: 'Testing & Launch', status: 'pending', startDate: '2024-03-02', endDate: '2024-03-15' }
        ],
        milestones: [
            { id: 1, name: 'Project Kickoff', date: '2024-01-15', completed: true },
            { id: 2, name: 'Design Approval', date: '2024-02-15', completed: false },
            { id: 3, name: 'Development Complete', date: '2024-03-01', completed: false },
            { id: 4, name: 'Go Live', date: '2024-03-15', completed: false }
        ]
    };

    const { data, setData, put, processing, errors } = useForm({
        name: project.name,
        description: project.description,
        client: project.client,
        status: project.status,
        priority: project.priority,
        startDate: project.startDate,
        endDate: project.endDate,
        budget: project.budget,
        tags: project.tags,
        team: project.team,
        phases: project.phases,
        milestones: project.milestones
    });

    // Mock data for dropdowns
    const clients = [
        { id: 'acme-corp', name: 'Acme Corporation' },
        { id: 'techstart', name: 'TechStart Inc' },
        { id: 'retailplus', name: 'RetailPlus' }
    ];

    const teamMembers = [
        { id: 1, name: 'John Smith', role: 'Project Manager', email: 'john@example.com' },
        { id: 2, name: 'Sarah Johnson', role: 'Designer', email: 'sarah@example.com' },
        { id: 3, name: 'Mike Wilson', role: 'Developer', email: 'mike@example.com' },
        { id: 4, name: 'Lisa Brown', role: 'QA Tester', email: 'lisa@example.com' },
        { id: 5, name: 'David Lee', role: 'Content Writer', email: 'david@example.com' }
    ];

    const addTag = () => {
        if (newTag.trim() && !data.tags.includes(newTag.trim())) {
            setData('tags', [...data.tags, newTag.trim()]);
            setNewTag('');
        }
    };

    const removeTag = (tagToRemove) => {
        setData('tags', data.tags.filter(tag => tag !== tagToRemove));
    };

    const addPhase = () => {
        if (newPhase.trim()) {
            const newPhaseObj = {
                id: Date.now(),
                name: newPhase.trim(),
                status: 'pending',
                startDate: '',
                endDate: ''
            };
            setData('phases', [...data.phases, newPhaseObj]);
            setNewPhase('');
        }
    };

    const removePhase = (phaseId) => {
        setData('phases', data.phases.filter(phase => phase.id !== phaseId));
    };

    const updatePhase = (phaseId, field, value) => {
        setData('phases', data.phases.map(phase =>
            phase.id === phaseId ? { ...phase, [field]: value } : phase
        ));
    };

    const addTeamMember = (memberId) => {
        const member = teamMembers.find(m => m.id === memberId);
        if (member && !data.team.find(t => t.id === memberId)) {
            setData('team', [...data.team, member]);
        }
    };

    const removeTeamMember = (memberId) => {
        setData('team', data.team.filter(member => member.id !== memberId));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/projects/${projectId}`, {
            onSuccess: () => {
                console.log('Project updated successfully');
            },
            onError: (errors) => {
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
                        <TabsList className="grid w-full grid-cols-4">
                            <TabsTrigger value="overview">Overview</TabsTrigger>
                            <TabsTrigger value="team">Team</TabsTrigger>
                            <TabsTrigger value="phases">Phases</TabsTrigger>
                            <TabsTrigger value="milestones">Milestones</TabsTrigger>
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
                                                    value={data.name}
                                                    onChange={(e) => setData('name', e.target.value)}
                                                    placeholder="Enter project name"
                                                    className={errors.name ? 'border-red-500' : ''}
                                                />
                                                {errors.name && <p className="text-sm text-red-500 mt-1">{errors.name}</p>}
                                            </div>

                                            <div>
                                                <Label htmlFor="description">Description</Label>
                                                <Textarea
                                                    id="description"
                                                    value={data.description}
                                                    onChange={(e) => setData('description', e.target.value)}
                                                    placeholder="Describe the project in detail"
                                                    rows={4}
                                                    className={errors.description ? 'border-red-500' : ''}
                                                />
                                                {errors.description && <p className="text-sm text-red-500 mt-1">{errors.description}</p>}
                                            </div>

                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <Label htmlFor="client">Client</Label>
                                                    <Select value={data.client} onValueChange={(value) => setData('client', value)}>
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Select client" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {clients.map((client) => (
                                                                <SelectItem key={client.id} value={client.id}>
                                                                    {client.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </div>

                                                <div>
                                                    <Label htmlFor="priority">Priority</Label>
                                                    <Select value={data.priority} onValueChange={(value) => setData('priority', value)}>
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
                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <Label htmlFor="startDate">Start Date</Label>
                                                    <Input
                                                        id="startDate"
                                                        type="date"
                                                        value={data.startDate}
                                                        onChange={(e) => setData('startDate', e.target.value)}
                                                    />
                                                </div>

                                                <div>
                                                    <Label htmlFor="endDate">End Date</Label>
                                                    <Input
                                                        id="endDate"
                                                        type="date"
                                                        value={data.endDate}
                                                        onChange={(e) => setData('endDate', e.target.value)}
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <Label htmlFor="budget">Budget</Label>
                                                    <Input
                                                        id="budget"
                                                        value={data.budget}
                                                        onChange={(e) => setData('budget', e.target.value)}
                                                        placeholder="Enter budget amount"
                                                    />
                                                </div>

                                                <div>
                                                    <Label htmlFor="status">Status</Label>
                                                    <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="planning">Planning</SelectItem>
                                                            <SelectItem value="in-progress">In Progress</SelectItem>
                                                            <SelectItem value="on-hold">On Hold</SelectItem>
                                                            <SelectItem value="completed">Completed</SelectItem>
                                                            <SelectItem value="cancelled">Cancelled</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Tags */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Tags</CardTitle>
                                            <CardDescription>Add tags to categorize the project</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="flex flex-wrap gap-2">
                                                {data.tags.map((tag, index) => (
                                                    <Badge key={index} variant="outline" className="bg-muted/30">
                                                        {tag}
                                                        <button
                                                            type="button"
                                                            onClick={() => removeTag(tag)}
                                                            className="ml-2 hover:text-red-500"
                                                        >
                                                            <X className="w-3 h-3" />
                                                        </button>
                                                    </Badge>
                                                ))}
                                            </div>
                                            <div className="flex space-x-2">
                                                <Input
                                                    placeholder="Add new tag..."
                                                    value={newTag}
                                                    onChange={(e) => setNewTag(e.target.value)}
                                                    onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addTag())}
                                                />
                                                <Button type="button" onClick={addTag} disabled={!newTag.trim()}>
                                                    <Plus className="w-4 h-4" />
                                                </Button>
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
                                                <h3 className="font-medium text-sm text-foreground">{data.name}</h3>
                                                <p className="text-xs text-muted-foreground line-clamp-2">{data.description}</p>
                                            </div>
                                            <div className="flex items-center justify-between text-xs text-muted-foreground">
                                                <span>Start: {data.startDate}</span>
                                                <span>End: {data.endDate}</span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Badge className={getPriorityColor(data.priority)}>
                                                    {data.priority}
                                                </Badge>
                                                <Badge className={getStatusColor(data.status)}>
                                                    {data.status.replace('-', ' ')}
                                                </Badge>
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                Budget: ${data.budget}
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="team" className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Team Members</CardTitle>
                                    <CardDescription>Manage project team and roles</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-3">
                                        {data.team.map((member) => (
                                            <div key={member.id} className="flex items-center justify-between p-3 border rounded-md">
                                                <div>
                                                    <p className="font-medium text-sm">{member.name}</p>
                                                    <p className="text-xs text-muted-foreground">{member.role}</p>
                                                    <p className="text-xs text-muted-foreground">{member.email}</p>
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => removeTeamMember(member.id)}
                                                >
                                                    <X className="w-4 h-4" />
                                                </Button>
                                            </div>
                                        ))}
                                    </div>

                                    <div>
                                        <Label>Add Team Member</Label>
                                        <Select onValueChange={addTeamMember}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select team member" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {teamMembers.filter(member => !data.team.find(t => t.id === member.id)).map((member) => (
                                                    <SelectItem key={member.id} value={member.id}>
                                                        {member.name} - {member.role}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="phases" className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Project Phases</CardTitle>
                                    <CardDescription>Define project phases and timelines</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-3">
                                        {data.phases.map((phase) => (
                                            <div key={phase.id} className="p-3 border rounded-md space-y-2">
                                                <div className="flex items-center justify-between">
                                                    <Input
                                                        value={phase.name}
                                                        onChange={(e) => updatePhase(phase.id, 'name', e.target.value)}
                                                        className="font-medium"
                                                    />
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => removePhase(phase.id)}
                                                    >
                                                        <X className="w-4 h-4" />
                                                    </Button>
                                                </div>
                                                <div className="grid grid-cols-3 gap-2">
                                                    <Select value={phase.status} onValueChange={(value) => updatePhase(phase.id, 'status', value)}>
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="pending">Pending</SelectItem>
                                                            <SelectItem value="in-progress">In Progress</SelectItem>
                                                            <SelectItem value="completed">Completed</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <Input
                                                        type="date"
                                                        value={phase.startDate}
                                                        onChange={(e) => updatePhase(phase.id, 'startDate', e.target.value)}
                                                        placeholder="Start Date"
                                                    />
                                                    <Input
                                                        type="date"
                                                        value={phase.endDate}
                                                        onChange={(e) => updatePhase(phase.id, 'endDate', e.target.value)}
                                                        placeholder="End Date"
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex space-x-2">
                                        <Input
                                            placeholder="Add new phase..."
                                            value={newPhase}
                                            onChange={(e) => setNewPhase(e.target.value)}
                                            onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addPhase())}
                                        />
                                        <Button type="button" onClick={addPhase} disabled={!newPhase.trim()}>
                                            <Plus className="w-4 h-4" />
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="milestones" className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Project Milestones</CardTitle>
                                    <CardDescription>Track important project milestones</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-3">
                                        {data.milestones.map((milestone) => (
                                            <div key={milestone.id} className="flex items-center space-x-3 p-3 border rounded-md">
                                                <input
                                                    type="checkbox"
                                                    checked={milestone.completed}
                                                    onChange={(e) => {
                                                        setData('milestones', data.milestones.map(m =>
                                                            m.id === milestone.id ? { ...m, completed: e.target.checked } : m
                                                        ));
                                                    }}
                                                />
                                                <div className="flex-1">
                                                    <p className={`font-medium text-sm ${milestone.completed ? 'line-through text-muted-foreground' : 'text-foreground'}`}>
                                                        {milestone.name}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">{milestone.date}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
