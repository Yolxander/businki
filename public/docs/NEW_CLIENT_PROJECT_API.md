# New Client Project API Documentation

## Endpoint

```
POST /api/projects/new-client-project
```

## Description

This endpoint creates a complete client project workflow in a single request. It creates:
1. A new **Client** with contact information
2. An **IntakeResponse** with project requirements
3. A **Proposal** with scope, deliverables, timeline, and pricing
4. A **Project** with execution details

All entities are created in a database transaction to ensure data consistency.

## Authentication

This endpoint requires authentication. Include your Bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

## Request Structure

The request body should be a JSON object with the following structure:

```json
{
  "client": {
    "first_name": "string (required)",
    "last_name": "string (required)",
    "email": "string (required, unique)",
    "phone": "string (optional)",
    "address": "string (optional)",
    "city": "string (optional)",
    "state": "string (optional)",
    "zip_code": "string (optional)"
  },
  "intake_response": {
    "full_name": "string (required)",
    "company_name": "string (required)",
    "email": "string (required)",
    "project_description": "string (required)",
    "budget_range": "string (required)",
    "deadline": "date (required, YYYY-MM-DD)",
    "project_type": "string (required)",
    "project_examples": "array (optional)"
  },
  "proposal": {
    "scope": "string (required)",
    "deliverables": "array (required)",
    "timeline": "array (required)",
    "price": "number (required, min: 0)",
    "status": "string (optional, default: 'draft')"
  },
  "project": {
    "title": "string (required)",
    "status": "string (optional, default: 'not_started')",
    "current_phase": "string (required)",
    "kickoff_date": "date (required, YYYY-MM-DD)",
    "expected_delivery": "date (required, YYYY-MM-DD, must be after kickoff_date)",
    "notes": "string (optional)"
  }
}
```

## Validation Rules

### Client
- `first_name`: Required, max 255 characters
- `last_name`: Required, max 255 characters
- `email`: Required, valid email format, must be unique in clients table
- `phone`: Optional, max 20 characters
- `address`: Optional, max 255 characters
- `city`: Optional, max 255 characters
- `state`: Optional, max 255 characters
- `zip_code`: Optional, max 10 characters

### Intake Response
- `full_name`: Required, max 255 characters
- `company_name`: Required, max 255 characters
- `email`: Required, valid email format
- `project_description`: Required
- `budget_range`: Required, max 255 characters
- `deadline`: Required, valid date format (YYYY-MM-DD)
- `project_type`: Required, max 255 characters
- `project_examples`: Optional, array format

### Proposal
- `title`: Optional, max 255 characters (auto-generated if not provided)
- `scope`: Required
- `deliverables`: Required, array format
- `timeline`: Required, array format
- `price`: Required, numeric value >= 0
- `status`: Optional, must be one of: 'draft', 'sent', 'accepted', 'rejected'

### Project
- `title`: Required, max 255 characters
- `status`: Optional, must be one of: 'not_started', 'in_progress', 'paused', 'done', 'draft'
- `current_phase`: Required, max 255 characters
- `kickoff_date`: Required, valid date format (YYYY-MM-DD)
- `expected_delivery`: Required, valid date format (YYYY-MM-DD), must be after kickoff_date
- `notes`: Optional

## Example Request

```json
{
  "client": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1-555-123-4567",
    "address": "123 Main Street",
    "city": "New York",
    "state": "NY",
    "zip_code": "10001"
  },
  "intake_response": {
    "full_name": "John Doe",
    "company_name": "Acme Corporation",
    "email": "john.doe@example.com",
    "project_description": "We need a modern e-commerce website with payment processing, inventory management, and customer analytics.",
    "budget_range": "$10,000 - $25,000",
    "deadline": "2024-12-31",
    "project_type": "E-commerce Website",
    "project_examples": [
      "Shopify store",
      "WooCommerce site",
      "Custom e-commerce platform"
    ]
  },
  "proposal": {
    "title": "Acme Corporation E-commerce Website Proposal",
    "scope": "Full-stack e-commerce website development including frontend, backend, database design, payment integration, and deployment.",
    "deliverables": [
      "Responsive website design",
      "Product catalog with search and filtering",
      "Shopping cart and checkout system",
      "Payment gateway integration (Stripe)",
      "Admin dashboard for inventory management",
      "Customer analytics and reporting",
      "SEO optimization",
      "Mobile app (optional)"
    ],
    "timeline": [
      {
        "phase": "Discovery & Planning",
        "duration": "2 weeks",
        "deliverables": ["Requirements document", "Wireframes", "Technical specification"]
      },
      {
        "phase": "Design",
        "duration": "3 weeks",
        "deliverables": ["UI/UX design", "Prototype", "Design system"]
      },
      {
        "phase": "Development",
        "duration": "8 weeks",
        "deliverables": ["Frontend development", "Backend API", "Database setup"]
      },
      {
        "phase": "Testing & Deployment",
        "duration": "2 weeks",
        "deliverables": ["Testing", "Bug fixes", "Production deployment"]
      }
    ],
    "price": 15000.00,
    "status": "draft"
  },
  "project": {
    "title": "Acme Corporation E-commerce Website",
    "status": "not_started",
    "current_phase": "Discovery & Planning",
    "kickoff_date": "2024-01-15",
    "expected_delivery": "2024-04-15",
    "notes": "Client prefers React frontend and Node.js backend. Priority on mobile responsiveness and fast loading times."
  }
}
```

## Response

### Success Response (201 Created)

```json
{
  "message": "Client, proposal, and project created successfully",
  "data": {
    "client": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "phone": "+1-555-123-4567",
      "address": "123 Main Street",
      "city": "New York",
      "state": "NY",
      "zip_code": "10001",
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z"
    },
    "intake_response": {
      "id": 1,
      "intake_id": 1,
      "full_name": "John Doe",
      "company_name": "Acme Corporation",
      "email": "john.doe@example.com",
      "project_description": "We need a modern e-commerce website...",
      "budget_range": "$10,000 - $25,000",
      "deadline": "2024-12-31",
      "project_type": "E-commerce Website",
      "project_examples": ["Shopify store", "WooCommerce site", "Custom e-commerce platform"],
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z"
    },
    "proposal": {
      "id": 1,
      "intake_response_id": 1,
      "title": "Acme Corporation E-commerce Website Proposal",
      "scope": "Full-stack e-commerce website development...",
      "deliverables": ["Responsive website design", "Product catalog..."],
      "timeline": [{"phase": "Discovery & Planning", "duration": "2 weeks"...}],
      "price": "15000.00",
      "status": "draft",
      "user_id": 1,
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z"
    },
    "project": {
      "id": 1,
      "proposal_id": 1,
      "title": "Acme Corporation E-commerce Website",
      "status": "not_started",
      "current_phase": "Discovery & Planning",
      "kickoff_date": "2024-01-15T00:00:00.000000Z",
      "expected_delivery": "2024-04-15T00:00:00.000000Z",
      "notes": "Client prefers React frontend and Node.js backend...",
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z",
      "proposal": {
        // Full proposal object
      },
      "tasks": []
    }
  }
}
```

### Error Responses

#### Validation Error (422 Unprocessable Entity)

```json
{
  "error": "Validation failed",
  "errors": {
    "client.email": ["The client.email field is required."],
    "project.expected_delivery": ["The project.expected_delivery must be a date after project.kickoff_date."]
  }
}
```

#### Server Error (500 Internal Server Error)

```json
{
  "error": "Failed to create new client project"
}
```

## Frontend Implementation Example

### JavaScript/Fetch API

```javascript
async function createNewClientProject(projectData) {
  try {
    const response = await fetch('/api/projects/new-client-project', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(projectData)
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || 'Failed to create project');
    }

    const result = await response.json();
    return result.data;
  } catch (error) {
    console.error('Error creating new client project:', error);
    throw error;
  }
}

// Usage example
const projectData = {
  client: {
    first_name: "John",
    last_name: "Doe",
    email: "john.doe@example.com",
    // ... other client fields
  },
  intake_response: {
    full_name: "John Doe",
    company_name: "Acme Corporation",
    // ... other intake response fields
  },
  proposal: {
    scope: "Full-stack e-commerce website development...",
    // ... other proposal fields
  },
  project: {
    title: "Acme Corporation E-commerce Website",
    // ... other project fields
  }
};

createNewClientProject(projectData)
  .then(data => {
    console.log('Project created successfully:', data);
    // Handle success (redirect, show notification, etc.)
  })
  .catch(error => {
    console.error('Failed to create project:', error);
    // Handle error (show error message, etc.)
  });
```

### React Hook Example

```javascript
import { useState } from 'react';

function useNewClientProject() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const createProject = async (projectData) => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/projects/new-client-project', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Accept': 'application/json'
        },
        body: JSON.stringify(projectData)
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to create project');
      }

      const result = await response.json();
      return result.data;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  return { createProject, loading, error };
}
```

## Notes

1. **Database Transaction**: All entities are created within a database transaction. If any part fails, all changes are rolled back.

2. **Intake Creation**: The system automatically creates a new intake form for the user with a unique link and 30-day expiration.

3. **User Association**: The proposal is automatically associated with the authenticated user.

4. **Data Relationships**: The function maintains proper relationships between all entities:
   - Intake → User (creator)
   - Intake → Client (after client creation)
   - IntakeResponse → Intake
   - Proposal → IntakeResponse
   - Project → Proposal

5. **Validation**: Comprehensive validation ensures data integrity and provides clear error messages.

6. **Logging**: All operations are logged for debugging and audit purposes.

7. **Data Sanitization**: The system automatically sanitizes data before validation:
   - Zip codes longer than 10 characters are truncated
   - All validation errors are logged with detailed context

8. **Project Status**: The 'draft' status is now accepted for projects to match proposal workflow.

## Troubleshooting

### Common Validation Errors

1. **Zip Code Too Long**: If `client.zip_code` exceeds 10 characters, it will be automatically truncated and logged.

2. **Invalid Project Status**: Ensure project status is one of: 'not_started', 'in_progress', 'paused', 'done', 'draft'.

3. **Date Format Issues**: Use ISO date format (YYYY-MM-DD) for all date fields.

4. **Email Uniqueness**: Client email must be unique in the database.

### Debugging with Logs

Use the unique request ID in logs to track specific requests:

```bash
# Find logs for a specific request
grep "new_client_project_6874597e5046b" storage/logs/laravel.log

# Monitor all new client project requests
tail -f storage/logs/laravel.log | grep "new_client_project_"
```

### Database Structure Notes

- **Intake**: Each intake has a unique link, user association, and optional client association
- **Intake Response**: Links to intake and contains project requirements
- **Proposal**: Links to intake response and contains project scope/pricing
- **Project**: Links to proposal and contains execution details 
