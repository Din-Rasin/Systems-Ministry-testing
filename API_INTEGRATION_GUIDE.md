# Laravel Workflow Management System - API Integration Guide

This guide provides comprehensive information about integrating with the Laravel Workflow Management System API, including available endpoints, authentication methods, data formats, and implementation examples.

## API Overview

The Laravel Workflow Management System provides a RESTful API that allows external systems to interact with the workflow management functionality. The API supports operations for managing users, departments, roles, workflows, requests, approvals, and notifications.

## Base URL

All API endpoints are relative to the base URL of your Laravel application:

```
http://your-domain.com/api/
```

## Authentication

The API uses Laravel Sanctum for authentication. There are two ways to authenticate:

### 1. Session Authentication (for web applications)

For web applications that make requests from the same domain, you can use the existing session authentication.

### 2. API Token Authentication (for external applications)

For external applications, you need to generate an API token:

1. Create a personal access token for a user:

    ```php
    $token = $user->createToken('api-token')->plainTextToken;
    ```

2. Include the token in the Authorization header:
    ```
    Authorization: Bearer YOUR_API_TOKEN
    ```

### 3. Login Endpoint

To obtain an authentication token, use the login endpoint:

**POST** `/api/login`

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

Response:

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com"
        },
        "token": "1|abcdefghijklmnopqrstuvwxyz123456"
    }
}
```

## API Endpoints

### Authentication Endpoints

#### POST `/api/login`

Authenticate a user and obtain an API token.

**Request Body:**

```json
{
    "email": "string",
    "password": "string"
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "string",
            "email": "string"
        },
        "token": "string"
    }
}
```

#### POST `/api/logout`

Logout the authenticated user.

**Response:**

```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

### User Endpoints

#### GET `/api/user`

Get the authenticated user's profile.

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "string",
        "email": "string",
        "department": {
            "id": 1,
            "name": "string"
        },
        "roles": [
            {
                "id": 1,
                "name": "string"
            }
        ]
    }
}
```

#### PUT `/api/user`

Update the authenticated user's profile.

**Request Body:**

```json
{
    "name": "string",
    "email": "string"
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "string",
        "email": "string"
    },
    "message": "Profile updated successfully"
}
```

### Request Endpoints

#### GET `/api/requests`

Get all requests (filtered by user role and permissions).

**Query Parameters:**

-   `type`: Filter by request type (leave|mission)
-   `status`: Filter by status (pending|approved|rejected)
-   `page`: Page number for pagination

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "type": "leave",
            "status": "pending",
            "workflow_id": 1,
            "current_step_id": 1,
            "submitted_at": "2023-01-01T00:00:00.000000Z",
            "data": {
                "start_date": "2023-01-15",
                "end_date": "2023-01-20",
                "reason": "string"
            },
            "user": {
                "id": 1,
                "name": "string"
            },
            "workflow": {
                "id": 1,
                "name": "string"
            }
        }
    ],
    "links": {},
    "meta": {}
}
```

#### GET `/api/requests/{id}`

Get a specific request by ID.

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "type": "leave",
        "status": "pending",
        "workflow_id": 1,
        "current_step_id": 1,
        "submitted_at": "2023-01-01T00:00:00.000000Z",
        "data": {
            "start_date": "2023-01-15",
            "end_date": "2023-01-20",
            "reason": "string"
        },
        "user": {
            "id": 1,
            "name": "string"
        },
        "workflow": {
            "id": 1,
            "name": "string"
        },
        "currentStep": {
            "id": 1,
            "step_number": 1,
            "role": {
                "id": 1,
                "name": "Team Leader"
            }
        }
    }
}
```

#### POST `/api/requests/leave`

Create a new leave request.

**Request Body:**

```json
{
    "leave_type_id": 1,
    "start_date": "2023-01-15",
    "end_date": "2023-01-20",
    "reason": "string",
    "supporting_document": "base64_encoded_file"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Leave request submitted successfully.",
    "data": {
        "id": 1,
        "user_id": 1,
        "type": "leave",
        "status": "pending",
        "workflow_id": 1,
        "current_step_id": 1,
        "submitted_at": "2023-01-01T00:00:00.000000Z",
        "data": {
            "start_date": "2023-01-15",
            "end_date": "2023-01-20",
            "reason": "string"
        }
    }
}
```

#### POST `/api/requests/mission`

Create a new mission request.

**Request Body:**

```json
{
    "destination": "string",
    "purpose": "string",
    "start_date": "2023-01-15",
    "end_date": "2023-01-20",
    "budget": 1000.0,
    "supporting_document": "base64_encoded_file"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Mission request submitted successfully.",
    "data": {
        "id": 1,
        "user_id": 1,
        "type": "mission",
        "status": "pending",
        "workflow_id": 1,
        "current_step_id": 1,
        "submitted_at": "2023-01-01T00:00:00.000000Z",
        "data": {
            "destination": "string",
            "purpose": "string",
            "start_date": "2023-01-15",
            "end_date": "2023-01-20",
            "budget": 1000.0
        }
    }
}
```

#### PUT `/api/requests/{id}`

Update an existing request.

**Request Body:**

```json
{
    "start_date": "2023-01-16",
    "end_date": "2023-01-21",
    "reason": "Updated reason"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Request updated successfully.",
    "data": {
        "id": 1,
        "user_id": 1,
        "type": "leave",
        "status": "pending",
        "workflow_id": 1,
        "current_step_id": 1,
        "data": {
            "start_date": "2023-01-16",
            "end_date": "2023-01-21",
            "reason": "Updated reason"
        }
    }
}
```

#### DELETE `/api/requests/{id}`

Delete a draft request.

**Response:**

```json
{
    "success": true,
    "message": "Request deleted successfully."
}
```

#### POST `/api/requests/{id}/submit`

Submit a draft request to start the workflow.

**Response:**

```json
{
    "success": true,
    "message": "Request submitted successfully.",
    "data": {
        "id": 1,
        "user_id": 1,
        "type": "leave",
        "status": "pending",
        "workflow_id": 1,
        "current_step_id": 1
    }
}
```

### Approval Endpoints

#### GET `/api/approvals/pending`

Get pending approvals for the authenticated user.

**Query Parameters:**

-   `page`: Page number for pagination

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "request_id": 1,
            "approver_id": 1,
            "step_id": 1,
            "status": "pending",
            "comments": null,
            "approved_at": null,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "request": {
                "id": 1,
                "user_id": 2,
                "type": "leave",
                "status": "pending",
                "data": {
                    "start_date": "2023-01-15",
                    "end_date": "2023-01-20",
                    "reason": "string"
                }
            },
            "user": {
                "id": 2,
                "name": "string"
            }
        }
    ],
    "links": {},
    "meta": {}
}
```

#### POST `/api/approvals/{id}`

Process an approval (approve/reject).

**Request Body:**

```json
{
    "decision": "approve", // or "reject"
    "comments": "string"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Request approved successfully.", // or "Request rejected successfully."
    "data": {
        "id": 1,
        "request_id": 1,
        "approver_id": 1,
        "step_id": 1,
        "status": "approved", // or "rejected"
        "comments": "string",
        "approved_at": "2023-01-01T00:00:00.000000Z"
    }
}
```

### Department Endpoints

#### GET `/api/departments`

Get all departments.

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "IT Department",
            "description": "Information Technology Department"
        }
    ]
}
```

#### GET `/api/departments/{id}`

Get a specific department.

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "IT Department",
        "description": "Information Technology Department",
        "users": [
            {
                "id": 1,
                "name": "string",
                "email": "string"
            }
        ]
    }
}
```

### Role Endpoints

#### GET `/api/roles`

Get all roles.

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Employee",
            "description": "Regular employee"
        }
    ]
}
```

### Workflow Endpoints

#### GET `/api/workflows`

Get all workflows.

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "IT Leave Workflow",
            "department_id": 1,
            "type": "leave",
            "description": "Leave workflow for IT department",
            "is_active": true
        }
    ]
}
```

#### GET `/api/workflows/{id}`

Get a specific workflow.

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "IT Leave Workflow",
        "department_id": 1,
        "type": "leave",
        "description": "Leave workflow for IT department",
        "is_active": true,
        "steps": [
            {
                "id": 1,
                "step_number": 1,
                "role_id": 2,
                "role": {
                    "id": 2,
                    "name": "Team Leader"
                }
            }
        ]
    }
}
```

## Data Formats

### Date Format

All dates are in ISO 8601 format:

```
YYYY-MM-DD
```

### DateTime Format

All timestamps are in ISO 8601 format:

```
YYYY-MM-DDTHH:MM:SS.ssssssZ
```

### File Uploads

Files should be base64 encoded when sent in JSON requests:

```json
{
    "supporting_document": "data:application/pdf;base64,JVBERi0xLjQKJcOkw7zDtsO..."
}
```

## Error Handling

The API returns standardized error responses:

### Validation Errors (422)

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### Authentication Errors (401)

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

### Authorization Errors (403)

```json
{
    "success": false,
    "message": "This action is unauthorized."
}
```

### Not Found Errors (404)

```json
{
    "success": false,
    "message": "The requested resource was not found."
}
```

### Server Errors (500)

```json
{
    "success": false,
    "message": "An error occurred while processing your request."
}
```

## Rate Limiting

The API implements rate limiting to prevent abuse:

-   60 requests per minute for authenticated users
-   10 requests per minute for unauthenticated users

When rate limited, the API returns a 429 status code:

```json
{
    "success": false,
    "message": "Too many attempts. Please try again later."
}
```

## Implementation Examples

### JavaScript/Node.js Example

```javascript
// Login and get token
async function login() {
    const response = await fetch("http://your-domain.com/api/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            email: "user@example.com",
            password: "password",
        }),
    });

    const data = await response.json();
    return data.data.token;
}

// Create a leave request
async function createLeaveRequest(token, requestData) {
    const response = await fetch("http://your-domain.com/api/requests/leave", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(requestData),
    });

    return await response.json();
}

// Usage
(async () => {
    try {
        const token = await login();
        const result = await createLeaveRequest(token, {
            leave_type_id: 1,
            start_date: "2023-01-15",
            end_date: "2023-01-20",
            reason: "Annual leave",
        });
        console.log(result);
    } catch (error) {
        console.error("Error:", error);
    }
})();
```

### Python Example

```python
import requests
import json

class WorkflowAPIClient:
    def __init__(self, base_url):
        self.base_url = base_url
        self.token = None

    def login(self, email, password):
        response = requests.post(
            f'{self.base_url}/api/login',
            json={'email': email, 'password': password}
        )
        data = response.json()
        if data['success']:
            self.token = data['data']['token']
            return True
        return False

    def create_leave_request(self, request_data):
        headers = {
            'Content-Type': 'application/json',
            'Authorization': f'Bearer {self.token}'
        }
        response = requests.post(
            f'{self.base_url}/api/requests/leave',
            headers=headers,
            json=request_data
        )
        return response.json()

# Usage
client = WorkflowAPIClient('http://your-domain.com')
if client.login('user@example.com', 'password'):
    result = client.create_leave_request({
        'leave_type_id': 1,
        'start_date': '2023-01-15',
        'end_date': '2023-01-20',
        'reason': 'Annual leave'
    })
    print(result)
```

## Webhook Integration

The system can send webhook notifications for various events:

### Available Webhook Events

1. `request.created` - When a new request is created
2. `request.approved` - When a request is approved
3. `request.rejected` - When a request is rejected
4. `approval.pending` - When an approval is pending
5. `user.registered` - When a new user is registered

### Webhook Payload Format

```json
{
    "event": "request.created",
    "timestamp": "2023-01-01T00:00:00.000000Z",
    "data": {
        "request": {
            "id": 1,
            "type": "leave",
            "status": "pending",
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john.doe@example.com"
            }
        }
    }
}
```

## CORS Configuration

The API is configured to allow cross-origin requests from the frontend domain. If you need to configure additional domains, update the `cors.php` configuration file.

## Security Considerations

1. All API requests should be made over HTTPS in production
2. API tokens should be stored securely and rotated regularly
3. Input validation is performed on all endpoints
4. Rate limiting prevents abuse
5. Authentication is required for all protected endpoints

## Testing

You can test the API using tools like Postman or curl:

```bash
# Login
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get requests
curl -X GET http://your-domain.com/api/requests \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

## Support

For additional help with API integration, please refer to:

-   Laravel Documentation: https://laravel.com/docs
-   API Testing Tools: https://www.postman.com/
-   REST API Best Practices: https://restfulapi.net/
