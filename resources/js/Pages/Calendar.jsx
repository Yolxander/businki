import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    ChevronLeft,
    ChevronRight,
    Plus,
    Calendar as CalendarIcon,
    Clock,
    Target,
    Users,
    CheckCircle,
    AlertCircle,
    FileText,
    DollarSign,
    Eye,
    EyeOff
} from 'lucide-react';

export default function Calendar({ auth, events, upcomingEvents }) {
    // Ensure events and upcomingEvents are arrays
    const safeEvents = Array.isArray(events) ? events : [];
    const safeUpcomingEvents = Array.isArray(upcomingEvents) ? upcomingEvents : [];
    const [currentDate, setCurrentDate] = useState(new Date());
    const [selectedDate, setSelectedDate] = useState(null);

    // Initialize showMonthSummary from localStorage, default to false (hidden)
    const [showMonthSummary, setShowMonthSummary] = useState(() => {
        try {
            const saved = localStorage.getItem('calendar-show-summary');
            return saved !== null ? JSON.parse(saved) : false;
        } catch (error) {
            // Fallback to false if localStorage is not available
            return false;
        }
    });

    const getDaysInMonth = (date) => {
        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        return { daysInMonth, startingDay };
    };

    const getEventsForDate = (date) => {
        const dateString = date.toISOString().split('T')[0];
        return safeEvents.filter(event => event.date === dateString);
    };

    const getEventIcon = (type) => {
        switch (type) {
            case 'project':
                return <FileText className="w-3 h-3" />;
            case 'task':
                return <Target className="w-3 h-3" />;
            case 'meeting':
                return <Users className="w-3 h-3" />;
            case 'proposal':
                return <DollarSign className="w-3 h-3" />;
            default:
                return <CalendarIcon className="w-3 h-3" />;
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

    const getStatusIcon = (status) => {
        switch (status) {
            case 'completed':
            case 'done':
                return <CheckCircle className="w-3 h-3 text-green-600" />;
            case 'in-progress':
            case 'in_progress':
                return <Clock className="w-3 h-3 text-blue-600" />;
            case 'pending':
            case 'todo':
                return <AlertCircle className="w-3 h-3 text-orange-600" />;
            default:
                return <AlertCircle className="w-3 h-3 text-gray-600" />;
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
            case 'done':
                return 'bg-green-100 text-green-800';
            case 'in-progress':
            case 'in_progress':
                return 'bg-blue-100 text-blue-800';
            case 'pending':
            case 'todo':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const navigateMonth = (direction) => {
        const newDate = new Date(currentDate);
        if (direction === 'prev') {
            newDate.setMonth(newDate.getMonth() - 1);
        } else {
            newDate.setMonth(newDate.getMonth() + 1);
        }
        setCurrentDate(newDate);
    };

    const formatDate = (date) => {
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long'
        });
    };

    const isToday = (date) => {
        if (!date) return false;
        const today = new Date();
        return date.toDateString() === today.toDateString();
    };

    const isSelected = (date) => {
        if (!date || !selectedDate) return false;
        return date.toDateString() === selectedDate.toDateString();
    };

    const handleToggleSummary = () => {
        const newState = !showMonthSummary;
        setShowMonthSummary(newState);
        try {
            localStorage.setItem('calendar-show-summary', JSON.stringify(newState));
        } catch (error) {
            // Silently fail if localStorage is not available
            console.warn('Could not save calendar preference to localStorage');
        }
    };

    const { daysInMonth, startingDay } = getDaysInMonth(currentDate);
    const days = [];

    // Add empty cells for days before the first day of the month
    for (let i = 0; i < startingDay; i++) {
        days.push(null);
    }

    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
        days.push(date);
    }

    const selectedDateEvents = selectedDate ? getEventsForDate(selectedDate) : [];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Calendar" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">Calendar</h1>
                        <p className="text-muted-foreground">Manage your schedule and deadlines</p>
                    </div>
                    <div className="flex items-center space-x-2">
                                                                        <Button
                            variant="outline"
                            onClick={handleToggleSummary}
                        >
                            {showMonthSummary ? <EyeOff className="w-4 h-4 mr-2" /> : <Eye className="w-4 h-4 mr-2" />}
                            {showMonthSummary ? 'Hide' : 'Show'} Summary
                        </Button>
                        <Link href="/tasks/create">
                            <Button variant="outline">
                                <Plus className="w-4 h-4 mr-2" />
                                Add Task
                            </Button>
                        </Link>
                        <Button onClick={() => setCurrentDate(new Date())}>
                            <CalendarIcon className="w-4 h-4 mr-2" />
                            Today
                        </Button>
                    </div>
                </div>

                                {/* Month Summary */}
                <div className={`transition-all duration-300 ease-in-out ${showMonthSummary ? 'opacity-100 max-h-96' : 'opacity-0 max-h-0 overflow-hidden'}`}>
                    <Card className="bg-card border-border">
                        <CardContent className="p-4">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                                <div>
                                    <div className="text-2xl font-bold text-foreground">
                                        {safeEvents.filter(e => new Date(e.date).getMonth() === currentDate.getMonth() && new Date(e.date).getFullYear() === currentDate.getFullYear()).length}
                                    </div>
                                    <div className="text-sm text-muted-foreground">Total Events</div>
                                </div>
                                <div>
                                    <div className="text-2xl font-bold text-blue-600">
                                        {safeEvents.filter(e => e.type === 'project' && new Date(e.date).getMonth() === currentDate.getMonth() && new Date(e.date).getFullYear() === currentDate.getFullYear()).length}
                                    </div>
                                    <div className="text-sm text-muted-foreground">Projects</div>
                                </div>
                                <div>
                                    <div className="text-2xl font-bold text-orange-600">
                                        {safeEvents.filter(e => e.type === 'task' && new Date(e.date).getMonth() === currentDate.getMonth() && new Date(e.date).getFullYear() === currentDate.getFullYear()).length}
                                    </div>
                                    <div className="text-sm text-muted-foreground">Tasks</div>
                                </div>
                                <div>
                                    <div className="text-2xl font-bold text-green-600">
                                        {safeEvents.filter(e => e.type === 'proposal' && new Date(e.date).getMonth() === currentDate.getMonth() && new Date(e.date).getFullYear() === currentDate.getFullYear()).length}
                                    </div>
                                    <div className="text-sm text-muted-foreground">Proposals</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Calendar Navigation */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-4">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    onClick={() => navigateMonth('prev')}
                                >
                                    <ChevronLeft className="w-4 h-4" />
                                </Button>
                                <h2 className="text-xl font-semibold text-foreground">
                                    {formatDate(currentDate)}
                                </h2>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    onClick={() => navigateMonth('next')}
                                >
                                    <ChevronRight className="w-4 h-4" />
                                </Button>
                            </div>
                            <div className="flex items-center space-x-4">
                                {/* Legend */}
                                <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                                    <div className="flex items-center space-x-1">
                                        <FileText className="w-3 h-3" />
                                        <span>Projects</span>
                                    </div>
                                    <div className="flex items-center space-x-1">
                                        <Target className="w-3 h-3" />
                                        <span>Tasks</span>
                                    </div>
                                    <div className="flex items-center space-x-1">
                                        <DollarSign className="w-3 h-3" />
                                        <span>Proposals</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {/* Calendar Grid */}
                        <div className="grid grid-cols-7 gap-1">
                            {/* Day headers */}
                            {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(day => (
                                <div key={day} className="p-2 text-center text-sm font-medium text-muted-foreground">
                                    {day}
                                </div>
                            ))}

                            {/* Calendar days */}
                            {days.map((date, index) => (
                                <div
                                    key={index}
                                    className={`min-h-[100px] p-2 border border-border transition-colors ${
                                        date ? 'hover:bg-muted/50 cursor-pointer' : ''
                                    } ${
                                        isToday(date) ? 'bg-primary/10 border-primary' : ''
                                    } ${
                                        isSelected(date) ? 'ring-2 ring-primary' : ''
                                    }`}
                                    onClick={() => date && setSelectedDate(date)}
                                >
                                    {date ? (
                                        <>
                                            <div className="text-sm font-medium text-foreground mb-1">
                                                {date.getDate()}
                                            </div>
                                            <div className="space-y-1">
                                                {getEventsForDate(date).slice(0, 2).map(event => (
                                                    <div
                                                        key={event.id}
                                                        className="flex items-center space-x-1 p-1 rounded text-xs bg-muted"
                                                    >
                                                        {getEventIcon(event.type)}
                                                        <span className="truncate">{event.title}</span>
                                                    </div>
                                                ))}
                                                {getEventsForDate(date).length > 2 && (
                                                    <div className="text-xs text-muted-foreground">
                                                        +{getEventsForDate(date).length - 2} more
                                                    </div>
                                                )}
                                            </div>
                                        </>
                                    ) : (
                                        <div className="text-sm text-muted-foreground/30">
                                            {/* Empty cell */}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Selected Date Events */}
                {selectedDate && (
                    <Card className="bg-card border-border">
                        <CardHeader>
                            <CardTitle className="text-foreground">
                                Events for {selectedDate.toLocaleDateString('en-US', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                })}
                            </CardTitle>
                            <CardDescription className="text-muted-foreground">
                                {selectedDateEvents.length} events scheduled
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {selectedDateEvents.length === 0 ? (
                                <div className="text-center py-8 text-muted-foreground">
                                    No events scheduled for this date
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {selectedDateEvents.map(event => (
                                        <Link key={event.id} href={event.url}>
                                            <div className="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                                <div className="flex items-center space-x-3">
                                                    {getEventIcon(event.type)}
                                                    <div>
                                                        <h3 className="font-medium text-foreground">{event.title}</h3>
                                                        <p className="text-sm text-muted-foreground">{event.client}</p>
                                                        {event.description && (
                                                            <p className="text-xs text-muted-foreground mt-1 line-clamp-1">
                                                                {event.description}
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="flex items-center space-x-2">
                                                    <Badge className={getPriorityColor(event.priority)} size="sm">
                                                        {event.priority}
                                                    </Badge>
                                                    <Badge className={getStatusColor(event.status)} size="sm">
                                                        {event.status}
                                                    </Badge>
                                                    {getStatusIcon(event.status)}
                                                    <Button variant="outline" size="sm">
                                                        View
                                                    </Button>
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Upcoming Events */}
                <Card className="bg-card border-border">
                    <CardHeader>
                        <CardTitle className="text-foreground">Upcoming Events</CardTitle>
                        <CardDescription className="text-muted-foreground">
                            Next 7 days
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {safeUpcomingEvents.length === 0 ? (
                            <div className="text-center py-8 text-muted-foreground">
                                No upcoming events in the next 7 days
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {safeUpcomingEvents.map(event => (
                                    <Link key={event.id} href={event.url}>
                                        <div className="flex items-center justify-between p-3 border border-border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                            <div className="flex items-center space-x-3">
                                                {getEventIcon(event.type)}
                                                <div>
                                                    <h3 className="font-medium text-foreground">{event.title}</h3>
                                                    <p className="text-sm text-muted-foreground">
                                                        {event.client} • {new Date(event.date).toLocaleDateString()}
                                                    </p>
                                                    {event.description && (
                                                        <p className="text-xs text-muted-foreground mt-1 line-clamp-1">
                                                            {event.description}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Badge className={getPriorityColor(event.priority)} size="sm">
                                                    {event.priority}
                                                </Badge>
                                                <Badge className={getStatusColor(event.status)} size="sm">
                                                    {event.status}
                                                </Badge>
                                                {getStatusIcon(event.status)}
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
