# User-Client Relationship API Documentation

## Overview

The system now supports a many-to-many relationship between users and clients through a pivot table (`user_client`). This allows multiple users to be associated with the same client and vice versa.

## Database Structure

### Pivot Table: `user_client`

```sql
CREATE TABLE user_client (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_user_client (user_id, client_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

### Key Features

- **Unique Constraint**: Prevents duplicate relationships between the same user and client
- **Cascade Delete**: When a user or client is deleted, the relationship is automatically removed
- **Timestamps**: Tracks when relationships are created and updated

## Model Relationships

### User Model
```php
public function clients()
{
    return $this->belongsToMany(Client::class, 'user_client')
                ->withTimestamps();
}
```

### Client Model
```php
public function users()
{
    return $this->belongsToMany(User::class, 'user_client')
                ->withTimestamps();
}
```

## API Endpoints

### 1. Create Client (Auto-Connect)

**Endpoint**: `POST /api/clients`

**Description**: Creates a new client and automatically connects it to the logged-in user.

**Request Body**:
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1-555-123-4567",
    "company_name": "Acme Corporation",
    "address": "123 Main Street",
    "city": "New York",
    "state": "NY",
    "zip_code": "10001"
}
```

**Response**:
```json
{
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1-555-123-4567",
    "company_name": "Acme Corporation",
    "address": "123 Main Street",
    "city": "New York",
    "state": "NY",
    "zip_code": "10001",
    "created_at": "2025-07-15T03:59:35.000000Z",
    "updated_at": "2025-07-15T03:59:35.000000Z",
    "users": [
        {
            "id": 2,
            "name": "User Name",
            "email": "user@example.com",
            "pivot": {
                "user_id": 2,
                "client_id": 1,
                "created_at": "2025-07-15T03:59:35.000000Z",
                "updated_at": "2025-07-15T03:59:35.000000Z"
            }
        }
    ]
}
```

### 2. Get User's Clients

**Endpoint**: `GET /api/clients/user/me`

**Description**: Retrieves all clients connected to the logged-in user.

**Response**:
```json
[
    {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "users": [
            {
                "id": 2,
                "name": "User Name",
                "email": "user@example.com",
                "pivot": {
                    "user_id": 2,
                    "client_id": 1,
                    "created_at": "2025-07-15T03:59:35.000000Z",
                    "updated_at": "2025-07-15T03:59:35.000000Z"
                }
            }
        ],
        "intakes": [...]
    }
]
```

### 3. Connect Existing Client

**Endpoint**: `POST /api/clients/{id}/connect`

**Description**: Connects an existing client to the logged-in user.

**Response**:
```json
{
    "message": "Client connected successfully",
    "client": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "users": [...]
    }
}
```

**Note**: If the relationship already exists, returns:
```json
{
    "message": "Client already connected",
    "client": {...}
}
```

### 4. Disconnect Client

**Endpoint**: `DELETE /api/clients/{id}/disconnect`

**Description**: Removes the connection between the logged-in user and the specified client.

**Response**:
```json
{
    "message": "Client disconnected successfully"
}
```

## Integration with New Client Project

The `newClientProject` function in `ProjectController` also automatically connects the user to the client when creating a new client project:

```php
// Connect the logged-in user to the client via pivot table
$userId = auth()->id();
$existingRelationship = $client->users()->where('user_id', $userId)->exists();

if (!$existingRelationship) {
    $client->users()->attach($userId);
    Log::info("User connected to client successfully");
} else {
    Log::info("User-client relationship already exists, skipping");
}
```

### 5. Connect Client for Project Creation

**Endpoint**: `POST /api/projects/connect-client`

**Description**: Connects an existing client to the logged-in user specifically for project creation workflow.

**Request Body**:
```json
{
    "client_id": 1
}
```

**Response**:
```json
{
    "message": "Client connected successfully for project creation",
    "client": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "users": [
            {
                "id": 2,
                "name": "User Name",
                "email": "user@example.com",
                "pivot": {
                    "user_id": 2,
                    "client_id": 1,
                    "created_at": "2025-07-15T03:59:35.000000Z",
                    "updated_at": "2025-07-15T03:59:35.000000Z"
                }
            }
        ]
    },
    "request_id": "connect_client_project_abc123"
}
```

**Note**: If the relationship already exists, returns:
```json
{
    "message": "Client already connected for project creation",
    "client": {...}
}
```

## Frontend Implementation Examples

### JavaScript/Fetch API

```javascript
// Create a new client (auto-connects to user)
async function createClient(clientData) {
    try {
        const response = await fetch('/api/clients', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify(clientData)
        });

        if (!response.ok) {
            throw new Error('Failed to create client');
        }

        const client = await response.json();
        console.log('Client created and connected:', client);
        return client;
    } catch (error) {
        console.error('Error creating client:', error);
        throw error;
    }
}

// Get user's clients
async function getUserClients() {
    try {
        const response = await fetch('/api/clients/user/me', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch clients');
        }

        const clients = await response.json();
        console.log('User clients:', clients);
        return clients;
    } catch (error) {
        console.error('Error fetching clients:', error);
        throw error;
    }
}

// Connect to existing client
async function connectToClient(clientId) {
    try {
        const response = await fetch(`/api/clients/${clientId}/connect`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to connect to client');
        }

        const result = await response.json();
        console.log('Connection result:', result);
        return result;
    } catch (error) {
        console.error('Error connecting to client:', error);
        throw error;
    }
}

// Disconnect from client
async function disconnectFromClient(clientId) {
    try {
        const response = await fetch(`/api/clients/${clientId}/disconnect`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to disconnect from client');
        }

        const result = await response.json();
        console.log('Disconnection result:', result);
        return result;
    } catch (error) {
        console.error('Error disconnecting from client:', error);
        throw error;
    }
}

// Connect client for project creation
async function connectClientForProject(clientId) {
    try {
        const response = await fetch('/api/projects/connect-client', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ client_id: clientId })
        });

        if (!response.ok) {
            throw new Error('Failed to connect client for project');
        }

        const result = await response.json();
        console.log('Project client connection result:', result);
        return result;
    } catch (error) {
        console.error('Error connecting client for project:', error);
        throw error;
    }
}
```

### React Hook Example

```javascript
import { useState } from 'react';

function useUserClientRelationship() {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const createClient = async (clientData) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/api/clients', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(clientData)
            });

            if (!response.ok) {
                throw new Error('Failed to create client');
            }

            const client = await response.json();
            return client;
        } catch (err) {
            setError(err.message);
            throw err;
        } finally {
            setLoading(false);
        }
    };

    const getUserClients = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/api/clients/user/me', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch clients');
            }

            const clients = await response.json();
            return clients;
        } catch (err) {
            setError(err.message);
            throw err;
        } finally {
            setLoading(false);
        }
    };

    return { createClient, getUserClients, loading, error };
}
```

## Benefits

1. **Flexible Relationships**: Multiple users can work with the same client
2. **Data Integrity**: Unique constraints prevent duplicate relationships
3. **Automatic Cleanup**: Cascade deletes ensure data consistency
4. **Audit Trail**: Timestamps track when relationships are created/modified
5. **Easy Management**: Simple API endpoints for connecting/disconnecting clients

## Logging

All user-client relationship operations are logged with detailed information:

- User ID and client ID for each operation
- Success/failure status
- Timestamps for debugging
- Error details when operations fail

## Security Considerations

- All endpoints require authentication
- Users can only manage their own client relationships
- No unauthorized access to other users' client connections 
