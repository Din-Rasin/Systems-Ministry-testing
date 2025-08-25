 # Workflow Diagram

This document explains how to view and use the workflow diagram in the Laravel Workflow Management System.

## Viewing the Workflow Diagram

To view the workflow diagram:

1. Navigate to `/workflow-diagram` in your browser, or
2. Click on "Workflow Diagram" in the navigation menu

## Understanding the Diagram

The workflow diagram provides a visual representation of the entire request and approval process in the system. It shows how requests flow through different roles and departments based on the type of request and the user's department.

### Key Components

-   **Authentication & Initialization** - User login and role assignment
-   **Role-Based Routing** - Different dashboards for different roles
-   **Employee Workflow** - Request creation process for leave and mission requests
-   **Workflow Engine** - Department-specific workflow routing
-   **Approval Processing** - Multi-level approval processes
-   **Role-Specific Functions** - Unique features for each role
-   **Final Processing** - Request completion and notification

### Color Coding

The diagram uses different colors to represent different types of processes:

-   ![#e3f2fd](https://via.placeholder.com/15/e3f2fd/000000?text=+) `#e3f2fd` - Start/End points
-   ![#f3e5f5](https://via.placeholder.com/15/f3e5f5/000000?text=+) `#f3e5f5` - Process steps
-   ![#fff3e0](https://via.placeholder.com/15/fff3e0/000000?text=+) `#fff3e0` - Decision points
-   ![#e8f5e8](https://via.placeholder.com/15/e8f5e8/000000?text=+) `#e8f5e8` - Success actions
-   ![#ffebee](https://via.placeholder.com/15/ffebee/000000?text=+) `#ffebee` - Error actions
-   ![#e1f5fe](https://via.placeholder.com/15/e1f5fe/000000?text=+) `#e1f5fe` - Dashboard components
-   ![#f9fbe7](https://via.placeholder.com/15/f9fbe7/000000?text=+) `#f9fbe7` - Workflow components

## Interacting with the Diagram

The diagram is interactive and allows you to:

1. Zoom in/out using your mouse wheel or touchpad
2. Pan around the diagram by clicking and dragging
3. Click on any node to highlight its connections
4. View detailed information about each process step

## Updating the Diagram

The workflow diagram is defined in `resources/views/workflows/diagram.blade.php`. To make changes to the diagram:

1. Edit the Mermaid syntax in the file
2. The changes will be reflected immediately when you refresh the page

For more information about Mermaid syntax, visit [https://mermaid-js.github.io/](https://mermaid-js.github.io/)
