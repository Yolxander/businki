import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { DatePicker } from '@/components/ui/date-picker';
import { toast } from 'sonner';
import {
    ArrowLeft,
    Save,
    Calendar,
    Clock,
    User,
    Building,
    Tag,
    Target,
    AlertCircle,
    DollarSign,
    FileText,
    Target as TargetIcon,
    AlertCircle as AlertCircleIcon,
    Plus,
    Users
} from 'lucide-react';

export default function CreateProject({ auth, clients }) {
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        client_id: '',
        status: 'planned',
        priority: 'medium',
        start_date: null,
        due_date: null,
        budget: '',
        team_members: []
    });

    const [errors, setErrors] = useState({});

    // Use real clients data from props
    const clientsData = clients || [];

    // Sample team members data
    const teamMembers = [
        { id: 1, name: 'Alex Johnson', role: 'UI/UX Designer', email: 'alex@example.com' },
        { id: 2, name: 'Maria Garcia', role: 'Frontend Developer', email: 'maria@example.com' },
        { id: 3, name: 'David Chen', role: 'Backend Developer', email: 'david@example.com' },
        { id: 4, name: 'Sarah Wilson', role: 'Project Manager', email: 'sarah@example.com' }
    ];

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));

        // Clear error when user starts typing
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };

    const handleDateChange = (name, date) => {
        setFormData(prev => ({
            ...prev,
            [name]: date
        }));

        // Clear error when user selects a date
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };

    const handleSubmit = (e) => {
        console.log('handleSubmit called!');
        if (e) e.preventDefault();

        // Basic validation
        const newErrors = {};
        if (!formData.name.trim()) newErrors.name = 'Project name is required';
        if (!formData.description.trim()) newErrors.description = 'Description is required';
        if (!formData.client_id) newErrors.client_id = 'Please select a client';
        if (!formData.start_date) newErrors.start_date = 'Start date is required';
        if (!formData.due_date) newErrors.due_date = 'Due date is required';
        if (formData.start_date && formData.due_date && formData.start_date > formData.due_date) {
            newErrors.due_date = 'Due date must be after start date';
        }

        console.log('Validation errors:', newErrors);
        console.log('Form data:', formData);

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            console.log('Validation failed, returning early');
            return;
        }

        // Prepare the data for submission
        const projectData = {
            name: formData.name,
            description: formData.description,
            client_id: formData.client_id,
            status: formData.status,
            priority: formData.priority,
            kickoff_date: formData.start_date ? formData.start_date.toISOString().split('T')[0] : null,
            start_date: formData.start_date ? formData.start_date.toISOString().split('T')[0] : null,
            due_date: formData.due_date ? formData.due_date.toISOString().split('T')[0] : null,
            current_phase: 'Planning', // Default phase
            progress: 0, // Default progress
            notes: '', // Default notes
            color: null // Default color
        };

        console.log('Submitting project data:', projectData);

        // Submit to the ProjectController@store method
        router.post('/projects', projectData, {
            onSuccess: () => {
                console.log('Project created successfully!');
                toast.success("Project created successfully!");
            },
            onError: (errors) => {
                console.log('Project creation failed:', errors);
                setErrors(errors);
                // Show error toast
                toast.error("Failed to create project. Please check the form and try again.");
            }
        });
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in_progress':
                return 'bg-blue-100 text-blue-800';
            case 'planned':
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
            <Head title="Create New Project" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div className="flex items-center space-x-4">
                        <Link href="/projects">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">Create New Project</h1>
                            <p className="text-muted-foreground">Set up a new project for your client</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href="/projects">
                            <Button variant="outline">Cancel</Button>
                        </Link>
                        <Button onClick={handleSubmit}>
                            <Save className="w-4 h-4 mr-2" />
                            Create Project
                        </Button>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Form */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Basic Information */}
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Basic Information</CardTitle>
                                    <CardDescription className="text-muted-foreground">
                                        Essential project details
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-foreground mb-2">
                                            Project Name *
                                        </label>
                                        <input
                                            type="text"
                                            name="name"
                                            value={formData.name}
                                            onChange={handleInputChange}
                                            className={`w-full px-3 py-2 border rounded-md bg-background text-foreground placeholder:text-muted-foreground ${
                                                errors.name ? 'border-red-500' : 'border-input'
                                            }`}
                                            placeholder="Enter project name"
                                        />
                                        {errors.name && (
                                            <p className="text-sm text-red-600 mt-1">{errors.name}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-foreground mb-2">
                                            Description *
                                        </label>
                                        <textarea
                                            name="description"
                                            value={formData.description}
                                            onChange={handleInputChange}
                                            rows={4}
                                            className={`w-full px-3 py-2 border rounded-md bg-background text-foreground placeholder:text-muted-foreground ${
                                                errors.description ? 'border-red-500' : 'border-input'
                                            }`}
                                            placeholder="Describe the project scope and objectives"
                                        />
                                        {errors.description && (
                                            <p className="text-sm text-red-600 mt-1">{errors.description}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-foreground mb-2">
                                            Client *
                                        </label>
                                        <select
                                            name="client_id"
                                            value={formData.client_id}
                                            onChange={handleInputChange}
                                            className={`w-full px-3 py-2 border rounded-md bg-background text-foreground ${
                                                errors.client_id ? 'border-red-500' : 'border-input'
                                            }`}
                                        >
                                            <option value="">Select a client</option>
                                            {clientsData.map(client => (
                                                <option key={client.id} value={client.id}>
                                                    {client.first_name} {client.last_name}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.client_id && (
                                            <p className="text-sm text-red-600 mt-1">{errors.client_id}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Project Settings */}
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Project Settings</CardTitle>
                                    <CardDescription className="text-muted-foreground">
                                        Configure project parameters
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-foreground mb-2">
                                                Status
                                            </label>
                                            <select
                                                name="status"
                                                value={formData.status}
                                                onChange={handleInputChange}
                                                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                                            >
                                                <option value="planned">Planned</option>
                                                <option value="in_progress">In Progress</option>
                                                <option value="completed">Completed</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-foreground mb-2">
                                                Priority
                                            </label>
                                            <select
                                                name="priority"
                                                value={formData.priority}
                                                onChange={handleInputChange}
                                                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                                            >
                                                <option value="low">Low</option>
                                                <option value="medium">Medium</option>
                                                <option value="high">High</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-foreground mb-2">
                                                Start Date *
                                            </label>
                                            <DatePicker
                                                date={formData.start_date}
                                                onDateChange={(date) => handleDateChange('start_date', date)}
                                                placeholder="Select start date"
                                                className={errors.start_date ? 'border-red-500' : ''}
                                            />
                                            {errors.start_date && (
                                                <p className="text-sm text-red-600 mt-1">{errors.start_date}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-foreground mb-2">
                                                Due Date *
                                            </label>
                                            <DatePicker
                                                date={formData.due_date}
                                                onDateChange={(date) => handleDateChange('due_date', date)}
                                                placeholder="Select due date"
                                                className={errors.due_date ? 'border-red-500' : ''}
                                            />
                                            {errors.due_date && (
                                                <p className="text-sm text-red-600 mt-1">{errors.due_date}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-foreground mb-2">
                                            Budget (USD)
                                        </label>
                                        <input
                                            type="number"
                                            name="budget"
                                            value={formData.budget}
                                            onChange={handleInputChange}
                                            className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                                            placeholder="Enter project budget"
                                            min="0"
                                            step="0.01"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Project Preview */}
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Project Preview</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {formData.name && (
                                        <div>
                                            <h3 className="font-medium text-foreground">{formData.name}</h3>
                                            {formData.description && (
                                                <p className="text-sm text-muted-foreground mt-1">
                                                    {formData.description.length > 100
                                                        ? formData.description.substring(0, 100) + '...'
                                                        : formData.description
                                                    }
                                                </p>
                                            )}
                                        </div>
                                    )}

                                    <div className="space-y-2">
                                        {formData.client_id && (
                                            <div className="flex items-center space-x-2">
                                                <Users className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-sm text-muted-foreground">
                                                    {clientsData.find(c => c.id == formData.client_id) ?
                                                        `${clientsData.find(c => c.id == formData.client_id).first_name} ${clientsData.find(c => c.id == formData.client_id).last_name}` :
                                                        'Unknown Client'
                                                    }
                                                </span>
                                            </div>
                                        )}

                                        {formData.start_date && formData.due_date && (
                                            <div className="flex items-center space-x-2">
                                                <Calendar className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-sm text-muted-foreground">
                                                    {formData.start_date.toLocaleDateString()} - {formData.due_date.toLocaleDateString()}
                                                </span>
                                            </div>
                                        )}

                                        {formData.budget && (
                                            <div className="flex items-center space-x-2">
                                                <DollarSign className="w-4 h-4 text-muted-foreground" />
                                                <span className="text-sm text-muted-foreground">
                                                    ${parseFloat(formData.budget).toLocaleString()}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex space-x-2">
                                        {formData.status && (
                                            <Badge className={getStatusColor(formData.status)}>
                                                {formData.status.replace('-', ' ')}
                                            </Badge>
                                        )}
                                        {formData.priority && (
                                            <Badge className={getPriorityColor(formData.priority)}>
                                                {formData.priority}
                                            </Badge>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Quick Actions */}
                            <Card className="bg-card border-border">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Quick Actions</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <Button className="w-full justify-start" variant="outline">
                                        <Plus className="w-4 h-4 mr-2" />
                                        Add Initial Tasks
                                    </Button>
                                    <Button className="w-full justify-start" variant="outline">
                                        <Users className="w-4 h-4 mr-2" />
                                        Assign Team Members
                                    </Button>
                                    <Button className="w-full justify-start" variant="outline">
                                        <FileText className="w-4 h-4 mr-2" />
                                        Create Proposal
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    </div>


                </form>
            </div>
        </AuthenticatedLayout>
    );
}
