// Preset chat flow definitions
export const presetChatFlows = {
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
    }
};
