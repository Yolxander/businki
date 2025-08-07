// Preset chat flow definitions
export const presetChatFlows = {
    'Plan my day': {
        steps: [
            {
                message: "Let's plan your day! What's your main focus for today?",
                field: 'daily_focus',
                validation: (value) => value.trim().length > 0 ? null : 'Please describe your main focus for today'
            },
            {
                message: "How many hours do you have available to work today?",
                field: 'available_hours',
                validation: (value) => {
                    const hours = parseInt(value);
                    return !isNaN(hours) && hours > 0 && hours <= 24 ? null : 'Please enter a valid number of hours (1-24)';
                }
            },
            {
                message: "What's your energy level today? (This helps me prioritize tasks)",
                field: 'energy_level',
                validation: (value) => ['high', 'medium', 'low'].includes(value.toLowerCase()) ? null : 'Please select an energy level',
                options: ['High', 'Medium', 'Low']
            },
            {
                message: "Perfect! I'll create a personalized daily plan for you. Here's what I'm considering:\n\n" +
                         "Main Focus: {daily_focus}\n" +
                         "Available Time: {available_hours} hours\n" +
                         "Energy Level: {energy_level}\n\n" +
                         "Creating your daily plan...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Review priorities': {
        steps: [
            {
                message: "Let's review your priorities! What time period would you like to focus on?",
                field: 'priority_period',
                validation: (value) => ['today', 'this week', 'this month', 'all'].includes(value.toLowerCase()) ? null : 'Please select a time period',
                options: ['Today', 'This Week', 'This Month', 'All Time']
            },
            {
                message: "What type of priorities would you like to review?",
                field: 'priority_type',
                validation: (value) => ['tasks', 'projects', 'both'].includes(value.toLowerCase()) ? null : 'Please select a priority type',
                options: ['Tasks Only', 'Projects Only', 'Both Tasks & Projects']
            },
            {
                message: "Great! I'll review your {priority_type} priorities for {priority_period}. This will show you:\n\n" +
                         "• High priority items that need attention\n" +
                         "• Overdue tasks and projects\n" +
                         "• Upcoming deadlines\n" +
                         "• Suggested focus areas\n\n" +
                         "Analyzing your priorities...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Check deadlines': {
        steps: [
            {
                message: "Let's check your deadlines! What time range would you like to review?",
                field: 'deadline_range',
                validation: (value) => ['today', 'this week', 'next week', 'this month'].includes(value.toLowerCase()) ? null : 'Please select a time range',
                options: ['Today', 'This Week', 'Next Week', 'This Month']
            },
            {
                message: "Would you like to see overdue items as well?",
                field: 'include_overdue',
                validation: (value) => ['yes', 'no'].includes(value.toLowerCase()) ? null : 'Please select yes or no',
                options: ['Yes', 'No']
            },
            {
                message: "Perfect! I'll check your deadlines for {deadline_range} {include_overdue === 'yes' ? 'including overdue items' : 'excluding overdue items'}. This will show you:\n\n" +
                         "• Upcoming deadlines\n" +
                         "• Task and project due dates\n" +
                         "• Priority levels\n" +
                         "• Suggested actions\n\n" +
                         "Checking your deadlines...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Start new project': {
        steps: [
            {
                message: "Let's start a new project! What would you like to call this project?",
                field: 'project_name',
                validation: (value) => value.trim().length > 0 ? null : 'Project name is required'
            },
            {
                message: "What's the main goal or purpose of this project?",
                field: 'project_goal',
                validation: (value) => value.trim().length > 0 ? null : 'Please describe the project goal'
            },
            {
                message: "What's the priority level for this project?",
                field: 'project_priority',
                validation: (value) => ['high', 'medium', 'low'].includes(value.toLowerCase()) ? null : 'Please select a priority level',
                options: ['High', 'Medium', 'Low']
            },
            {
                message: "When would you like to complete this project? (YYYY-MM-DD):",
                field: 'project_deadline',
                validation: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime()) ? null : 'Please enter a valid date (YYYY-MM-DD)';
                }
            },
            {
                message: "Perfect! I'll start your new project. Here's what I'm creating:\n\n" +
                         "Project Name: {project_name}\n" +
                         "Goal: {project_goal}\n" +
                         "Priority: {project_priority}\n" +
                         "Deadline: {project_deadline}\n\n" +
                         "Starting your new project...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Create Project': {
        steps: [
            {
                message: "Let's create a new project! Please enter the project name:",
                field: 'project_name',
                validation: (value) => value.trim().length > 0 ? null : 'Project name is required'
            },
            {
                message: "Great! Now please enter a brief description of the project:",
                field: 'project_description',
                validation: (value) => value.trim().length > 0 ? null : 'Project description is required'
            },
            {
                message: "What's the priority level for this project?",
                field: 'project_priority',
                validation: (value) => ['high', 'medium', 'low'].includes(value.toLowerCase()) ? null : 'Please select a priority level',
                options: ['High', 'Medium', 'Low']
            },
            {
                message: "When is the project deadline? (YYYY-MM-DD):",
                field: 'project_deadline',
                validation: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime()) ? null : 'Please enter a valid date (YYYY-MM-DD)';
                }
            },
            {
                message: "Perfect! I'll create the project for you. Here's what I'm creating:\n\n" +
                         "Project Name: {project_name}\n" +
                         "Description: {project_description}\n" +
                         "Priority: {project_priority}\n" +
                         "Deadline: {project_deadline}\n\n" +
                         "Creating project...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Create Task': {
        steps: [
            {
                message: "Let's create a new task! Please enter the task title:",
                field: 'task_title',
                validation: (value) => value.trim().length > 0 ? null : 'Task title is required'
            },
            {
                message: "Now please enter a description of what needs to be done:",
                field: 'task_description',
                validation: (value) => value.trim().length > 0 ? null : 'Task description is required'
            },
            {
                message: "What's the priority level for this task?",
                field: 'task_priority',
                validation: (value) => ['high', 'medium', 'low'].includes(value.toLowerCase()) ? null : 'Please select a priority level',
                options: ['High', 'Medium', 'Low']
            },
            {
                message: "When is this task due? (YYYY-MM-DD):",
                field: 'task_due_date',
                validation: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime()) ? null : 'Please enter a valid date (YYYY-MM-DD)';
                }
            },
            {
                message: "Perfect! I'll create the task for you. Here's what I'm creating:\n\n" +
                         "Task Title: {task_title}\n" +
                         "Description: {task_description}\n" +
                         "Priority: {task_priority}\n" +
                         "Due Date: {task_due_date}\n\n" +
                         "Creating task...",
                field: null,
                isFinal: true
            }
        ]
    },
    'View All Projects': {
        steps: [
            {
                message: "I'll show you all your projects. What would you like to see?",
                field: 'view_type',
                options: ['All Projects', 'Active Projects', 'Completed Projects', 'Overdue Projects']
            },
            {
                message: "Perfect! I'm fetching your {view_type} for you. This will show you:\n\n" +
                         "• Project names and descriptions\n" +
                         "• Current status and progress\n" +
                         "• Due dates and priorities\n" +
                         "• Team members assigned\n\n" +
                         "Loading your projects...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Create New Project': {
        steps: [
            {
                message: "Let's create a new project! Please enter the project name:",
                field: 'project_name',
                validation: (value) => value.trim().length > 0 ? null : 'Project name is required'
            },
            {
                message: "Great! Now please enter a brief description of the project:",
                field: 'project_description',
                validation: (value) => value.trim().length > 0 ? null : 'Project description is required'
            },
            {
                message: "What's the priority level for this project?",
                field: 'project_priority',
                validation: (value) => ['high', 'medium', 'low'].includes(value.toLowerCase()) ? null : 'Please select a priority level',
                options: ['High', 'Medium', 'Low']
            },
            {
                message: "When is the project deadline? (YYYY-MM-DD):",
                field: 'project_deadline',
                validation: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime()) ? null : 'Please enter a valid date (YYYY-MM-DD)';
                }
            },
            {
                message: "Perfect! I'll create the project for you. Here's what I'm creating:\n\n" +
                         "Project Name: {project_name}\n" +
                         "Description: {project_description}\n" +
                         "Priority: {project_priority}\n" +
                         "Deadline: {project_deadline}\n\n" +
                         "Creating project...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Update Project Status': {
        steps: [
            {
                message: "Which project would you like to update?",
                field: 'project_name',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter a project name'
            },
            {
                message: "What's the new status for this project?",
                field: 'project_status',
                options: ['Planning', 'In Progress', 'Review', 'Completed', 'On Hold']
            },
            {
                message: "Would you like to add any notes about this status change?",
                field: 'status_notes',
                validation: (value) => value.trim().length > 0 ? null : 'Notes are optional, you can skip this step'
            },
            {
                message: "Perfect! I'm updating the status for {project_name} to {project_status}.\n\n" +
                         "Status: {project_status}\n" +
                         "Notes: {status_notes}\n\n" +
                         "Updating project status...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Create Project Tasks': {
        steps: [
            {
                message: "Which project would you like to create tasks for?",
                field: 'project_name',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter a project name'
            },
            {
                message: "What type of task are you creating?",
                field: 'task_type',
                options: ['Development Task', 'Design Task', 'Content Task', 'Review Task', 'Testing Task', 'Documentation Task']
            },
            {
                message: "Enter the task title:",
                field: 'task_title',
                validation: (value) => value.trim().length > 0 ? null : 'Task title is required'
            },
            {
                message: "Enter a description of what needs to be done:",
                field: 'task_description',
                validation: (value) => value.trim().length > 0 ? null : 'Task description is required'
            },
            {
                message: "What's the priority level for this task?",
                field: 'task_priority',
                validation: (value) => ['high', 'medium', 'low'].includes(value.toLowerCase()) ? null : 'Please select a priority level',
                options: ['High', 'Medium', 'Low']
            },
            {
                message: "When is this task due? (YYYY-MM-DD):",
                field: 'task_due_date',
                validation: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime()) ? null : 'Please enter a valid date (YYYY-MM-DD)';
                }
            },
            {
                message: "Who should be assigned to this task?",
                field: 'assigned_to',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter an assignee name'
            },
            {
                message: "Perfect! I'm creating a task for {project_name}.\n\n" +
                         "Project: {project_name}\n" +
                         "Task Type: {task_type}\n" +
                         "Title: {task_title}\n" +
                         "Description: {task_description}\n" +
                         "Priority: {task_priority}\n" +
                         "Due Date: {task_due_date}\n" +
                         "Assigned To: {assigned_to}\n\n" +
                         "Creating project task...",
                field: null,
                isFinal: true
            }
        ]
    },
    'View All Clients': {
        steps: [
            {
                message: "I'll show you all your clients. What would you like to see?",
                field: 'view_type',
                options: ['All Clients', 'Active Clients', 'Recent Clients', 'Clients with Outstanding Invoices']
            },
            {
                message: "Perfect! I'm fetching your {view_type} for you. This will show you:\n\n" +
                         "• Client names and contact information\n" +
                         "• Company details and industry\n" +
                         "• Project history and status\n" +
                         "• Outstanding invoices and payment status\n\n" +
                         "Loading your clients...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Add New Client': {
        steps: [
            {
                message: "Let's add a new client! Please enter the client's name:",
                field: 'client_name',
                validation: (value) => value.trim().length > 0 ? null : 'Client name is required'
            },
            {
                message: "What's the company name?",
                field: 'company_name',
                validation: (value) => value.trim().length > 0 ? null : 'Company name is required'
            },
            {
                message: "Enter the client's email address:",
                field: 'client_email',
                validation: (value) => {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(value) ? null : 'Please enter a valid email address';
                }
            },
            {
                message: "Enter the client's phone number:",
                field: 'client_phone',
                validation: (value) => value.trim().length > 0 ? null : 'Phone number is required'
            },
            {
                message: "What industry is the client in?",
                field: 'client_industry',
                options: ['Technology', 'Healthcare', 'Finance', 'Education', 'Retail', 'Manufacturing', 'Consulting', 'Other']
            },
            {
                message: "Perfect! I'm adding {client_name} to your client list. Here's what I'm creating:\n\n" +
                         "Client Name: {client_name}\n" +
                         "Company: {company_name}\n" +
                         "Email: {client_email}\n" +
                         "Phone: {client_phone}\n" +
                         "Industry: {client_industry}\n\n" +
                         "Adding new client...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Update Client Information': {
        steps: [
            {
                message: "Which client would you like to update?",
                field: 'client_name',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter a client name'
            },
            {
                message: "What information would you like to update?",
                field: 'update_type',
                options: ['Contact Information', 'Company Details', 'Billing Information', 'Project Status']
            },
            {
                message: "Enter the new value:",
                field: 'new_value',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter the new value'
            },
            {
                message: "Would you like to add any notes about this update?",
                field: 'update_notes',
                validation: (value) => value.trim().length > 0 ? null : 'Notes are optional, you can skip this step'
            },
            {
                message: "Perfect! I'm updating {client_name}'s {update_type}.\n\n" +
                         "Client: {client_name}\n" +
                         "Field: {update_type}\n" +
                         "New Value: {new_value}\n" +
                         "Notes: {update_notes}\n\n" +
                         "Updating client information...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Generate Client Report': {
        steps: [
            {
                message: "What type of client report would you like to generate?",
                field: 'report_type',
                options: ['Client Satisfaction Report', 'Revenue Report', 'Project Progress Report', 'Payment Status Report']
            },
            {
                message: "For which time period?",
                field: 'time_period',
                options: ['Last 30 Days', 'Last Quarter', 'Last 6 Months', 'Last Year', 'All Time']
            },
            {
                message: "Would you like to include specific clients or all clients?",
                field: 'client_scope',
                options: ['All Clients', 'Specific Clients', 'Active Clients Only']
            },
            {
                message: "Perfect! I'm generating a {report_type} for {time_period}.\n\n" +
                         "Report Type: {report_type}\n" +
                         "Time Period: {time_period}\n" +
                         "Scope: {client_scope}\n\n" +
                         "This report will include:\n" +
                         "• Client performance metrics\n" +
                         "• Project completion rates\n" +
                         "• Revenue and payment data\n" +
                         "• Satisfaction scores\n\n" +
                         "Generating report...",
                field: null,
                isFinal: true
            }
        ]
    },
    'View This Week\'s Schedule': {
        steps: [
            {
                message: "I'll show you this week's schedule. What would you like to see?",
                field: 'schedule_type',
                options: ['All Events', 'Meetings Only', 'Deadlines Only', 'Client Meetings', 'Team Meetings']
            },
            {
                message: "Perfect! I'm fetching your {schedule_type} for this week. This will show you:\n\n" +
                         "• Event dates and times\n" +
                         "• Meeting locations and participants\n" +
                         "• Project deadlines and priorities\n" +
                         "• Client appointments and details\n\n" +
                         "Loading your schedule...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Schedule New Meeting': {
        steps: [
            {
                message: "Let's schedule a new meeting! What type of meeting is this?",
                field: 'meeting_type',
                options: ['Client Meeting', 'Team Meeting', 'Project Review', 'Sales Call', 'Strategy Session', 'Other']
            },
            {
                message: "What's the meeting title?",
                field: 'meeting_title',
                validation: (value) => value.trim().length > 0 ? null : 'Meeting title is required'
            },
            {
                message: "What date would you like to schedule it for? (YYYY-MM-DD):",
                field: 'meeting_date',
                validation: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime()) ? null : 'Please enter a valid date (YYYY-MM-DD)';
                }
            },
            {
                message: "What time should the meeting start? (HH:MM):",
                field: 'meeting_time',
                validation: (value) => {
                    const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
                    return timeRegex.test(value) ? null : 'Please enter a valid time (HH:MM)';
                }
            },
            {
                message: "How long should the meeting be?",
                field: 'meeting_duration',
                options: ['30 minutes', '1 hour', '1.5 hours', '2 hours', 'Half day', 'Full day']
            },
            {
                message: "Who should attend this meeting?",
                field: 'meeting_participants',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter at least one participant'
            },
            {
                message: "Where will the meeting take place?",
                field: 'meeting_location',
                options: ['Office', 'Video Call', 'Client Office', 'Conference Room', 'Coffee Shop', 'Other']
            },
            {
                message: "Perfect! I'm scheduling your meeting. Here's what I'm creating:\n\n" +
                         "Meeting Type: {meeting_type}\n" +
                         "Title: {meeting_title}\n" +
                         "Date: {meeting_date}\n" +
                         "Time: {meeting_time}\n" +
                         "Duration: {meeting_duration}\n" +
                         "Participants: {meeting_participants}\n" +
                         "Location: {meeting_location}\n\n" +
                         "Scheduling meeting...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Check Deadlines': {
        steps: [
            {
                message: "I'll help you check deadlines. What would you like to see?",
                field: 'deadline_type',
                options: ['All Deadlines', 'This Week', 'This Month', 'Overdue', 'Upcoming']
            },
            {
                message: "For which projects or tasks?",
                field: 'deadline_scope',
                options: ['All Projects', 'Active Projects', 'Specific Project', 'Client Projects', 'Personal Tasks']
            },
            {
                message: "Perfect! I'm checking your {deadline_type} for {deadline_scope}. This will show you:\n\n" +
                         "• Project deadlines and due dates\n" +
                         "• Task completion status\n" +
                         "• Priority levels and urgency\n" +
                         "• Overdue items and alerts\n\n" +
                         "Checking deadlines...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Generate Calendar Report': {
        steps: [
            {
                message: "What type of calendar report would you like to generate?",
                field: 'report_type',
                options: ['Weekly Summary', 'Monthly Overview', 'Meeting Analytics', 'Time Allocation Report']
            },
            {
                message: "For which time period?",
                field: 'time_period',
                options: ['This Week', 'Last Week', 'This Month', 'Last Month', 'This Quarter', 'Custom Range']
            },
            {
                message: "What should the report include?",
                field: 'report_content',
                options: ['All Events', 'Meetings Only', 'Deadlines Only', 'Client Activities', 'Team Activities']
            },
            {
                message: "Perfect! I'm generating a {report_type} for {time_period}.\n\n" +
                         "Report Type: {report_type}\n" +
                         "Time Period: {time_period}\n" +
                         "Content: {report_content}\n\n" +
                         "This report will include:\n" +
                         "• Meeting statistics and attendance\n" +
                         "• Time allocation and productivity\n" +
                         "• Deadline tracking and completion rates\n" +
                         "• Calendar efficiency metrics\n\n" +
                         "Generating calendar report...",
                field: null,
                isFinal: true
            }
        ]
    },
    'View All Tasks': {
        steps: [
            {
                message: "I'll show you all your tasks. What would you like to see?",
                field: 'task_view',
                options: ['All Tasks', 'Active Tasks', 'Completed Tasks', 'Overdue Tasks', 'Tasks with Subtasks']
            },
            {
                message: "Perfect! I'm fetching your {task_view} for you. This will show you:\n\n" +
                         "• Task titles and descriptions\n" +
                         "• Current status and progress\n" +
                         "• Due dates and priorities\n" +
                         "• Assigned team members\n" +
                         "• Related subtasks\n\n" +
                         "Loading your tasks...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Create New Task': {
        steps: [
            {
                message: "Let's create a new task! What type of task is this?",
                field: 'task_type',
                options: ['Development Task', 'Design Task', 'Content Task', 'Review Task', 'Testing Task', 'Documentation Task']
            },
            {
                message: "Enter the task title:",
                field: 'task_title',
                validation: (value) => value.trim().length > 0 ? null : 'Task title is required'
            },
            {
                message: "Enter a description of what needs to be done:",
                field: 'task_description',
                validation: (value) => value.trim().length > 0 ? null : 'Task description is required'
            },
            {
                message: "What's the priority level for this task?",
                field: 'task_priority',
                validation: (value) => ['high', 'medium', 'low'].includes(value.toLowerCase()) ? null : 'Please select a priority level',
                options: ['High', 'Medium', 'Low']
            },
            {
                message: "When is this task due? (YYYY-MM-DD):",
                field: 'task_due_date',
                validation: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime()) ? null : 'Please enter a valid date (YYYY-MM-DD)';
                }
            },
            {
                message: "Who should be assigned to this task?",
                field: 'task_assignee',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter an assignee name'
            },
            {
                message: "Perfect! I'm creating a new task for you. Here's what I'm creating:\n\n" +
                         "Task Type: {task_type}\n" +
                         "Title: {task_title}\n" +
                         "Description: {task_description}\n" +
                         "Priority: {task_priority}\n" +
                         "Due Date: {task_due_date}\n" +
                         "Assigned To: {task_assignee}\n\n" +
                         "Creating task...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Update Task Status': {
        steps: [
            {
                message: "Which task would you like to update?",
                field: 'task_name',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter a task name'
            },
            {
                message: "What's the new status for this task?",
                field: 'task_status',
                options: ['Not Started', 'In Progress', 'Under Review', 'Completed', 'On Hold', 'Cancelled']
            },
            {
                message: "What's the current progress percentage?",
                field: 'task_progress',
                options: ['0%', '25%', '50%', '75%', '90%', '100%']
            },
            {
                message: "Would you like to add any notes about this update?",
                field: 'task_notes',
                validation: (value) => value.trim().length > 0 ? null : 'Notes are optional, you can skip this step'
            },
            {
                message: "Perfect! I'm updating the status for {task_name}.\n\n" +
                         "Task: {task_name}\n" +
                         "New Status: {task_status}\n" +
                         "Progress: {task_progress}\n" +
                         "Notes: {task_notes}\n\n" +
                         "Updating task status...",
                field: null,
                isFinal: true
            }
        ]
    },
    'Manage Subtasks': {
        steps: [
            {
                message: "Which task would you like to manage subtasks for?",
                field: 'parent_task',
                validation: (value) => value.trim().length > 0 ? null : 'Please enter a task name'
            },
            {
                message: "What would you like to do with subtasks?",
                field: 'subtask_action',
                options: ['View Subtasks', 'Add New Subtask', 'Update Subtask', 'Mark Subtask Complete']
            },
            {
                message: "Enter the subtask title:",
                field: 'subtask_title',
                validation: (value) => value.trim().length > 0 ? null : 'Subtask title is required'
            },
            {
                message: "Enter a description for this subtask:",
                field: 'subtask_description',
                validation: (value) => value.trim().length > 0 ? null : 'Subtask description is required'
            },
            {
                message: "What's the priority for this subtask?",
                field: 'subtask_priority',
                validation: (value) => ['high', 'medium', 'low'].includes(value.toLowerCase()) ? null : 'Please select a priority level',
                options: ['High', 'Medium', 'Low']
            },
            {
                message: "Perfect! I'm managing subtasks for {parent_task}.\n\n" +
                         "Parent Task: {parent_task}\n" +
                         "Action: {subtask_action}\n" +
                         "Subtask Title: {subtask_title}\n" +
                         "Description: {subtask_description}\n" +
                         "Priority: {subtask_priority}\n\n" +
                         "Managing subtasks...",
                field: null,
                isFinal: true
            }
        ]
    }
};
