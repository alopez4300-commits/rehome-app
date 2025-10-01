# ReHome v2 Frontend

React SPA with TypeScript for the ReHome v2 project management platform.

## Features

- **React 18** with TypeScript
- **React Router** for navigation
- **Tailwind CSS** for styling
- **Vite** for fast development and builds
- **React Query** for data fetching
- **Zustand** for state management
- **Storybook** for component development
- **Vitest** for testing

## Getting Started

### Prerequisites

- Node.js 20+
- npm or yarn

### Installation

```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Start Storybook
npm run storybook

# Run tests
npm run test

# Build for production
npm run build
```

## Development

### Project Structure

```
src/
├── components/          # Reusable components
│   ├── auth/           # Authentication components
│   ├── common/         # Common UI components
│   ├── layout/         # Layout components
│   └── MyHome/         # MyHome system components
├── contexts/           # React contexts
├── hooks/              # Custom React hooks
├── pages/              # Page components
├── services/           # API services
├── types/              # TypeScript type definitions
└── utils/              # Utility functions
```

### Authentication

The frontend integrates with Laravel Sanctum for authentication:

- **Login**: Email/password authentication
- **Token Management**: Automatic token storage and refresh
- **Role-based Access**: Admin, owner, member, consultant, client roles
- **Protected Routes**: Automatic redirect to login for unauthenticated users

### API Integration

All API calls go through the Laravel backend:

```typescript
// Authentication
const response = await authService.login({ email, password });

// MyHome API
const feed = await myhomeService.getFeed(workspaceId, projectId);

// Workspace API
const workspaces = await workspaceService.getWorkspaces();
```

### Role-based Features

- **Admin**: Full system access, can access all workspaces
- **Owner**: Full workspace control and project management
- **Member**: Standard team member with task and project access
- **Consultant**: Limited access to assigned projects
- **Client**: Read-only access to project progress

## Component Development

### Storybook

Each component should have a corresponding story file:

```typescript
// Button.stories.tsx
import type { Meta, StoryObj } from '@storybook/react';
import { Button } from './Button';

const meta: Meta<typeof Button> = {
  title: 'Common/Button',
  component: Button,
  parameters: {
    layout: 'centered',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Primary: Story = {
  args: {
    variant: 'primary',
    children: 'Button',
  },
};
```

### Testing

```bash
# Run tests
npm run test

# Run tests with UI
npm run test:ui

# Run tests with coverage
npm run test:coverage
```

## Deployment

### Build

```bash
# Build for production
npm run build
```

### Environment Variables

Create `.env.local`:

```env
VITE_API_URL=http://localhost:8000
VITE_ADMIN_URL=http://localhost:8000/system
VITE_APP_NAME=ReHome v2
VITE_DEBUG=true
```

## Integration with Backend

### Authentication Flow

1. User logs in via React form
2. API returns user data + Bearer token
3. Token stored in localStorage and axios headers
4. Protected routes check authentication status
5. Automatic logout on token expiration

### MyHome Integration

The MyHome system provides:

- **Activity Feed**: Chronological stream of project activity
- **Entry Types**: Notes, tasks, time logs, file uploads, AI interactions
- **Real-time Updates**: Polling-based updates (WebSockets planned)
- **Search**: Full-text search across MyHome entries

### AI Chat Integration

- **Context-aware**: AI has access to project MyHome data
- **Role-based PII**: Sensitive data redacted based on user role
- **Cost Tracking**: Token usage and cost monitoring
- **Rate Limiting**: Per-user request limits

## Key Pages

- **Dashboard**: Overview of workspaces and recent activity
- **Workspace**: Workspace details and project list
- **Project**: Project overview with navigation to features
- **Task Board**: Kanban-style task management
- **Activity Feed**: MyHome chronological activity stream
- **Time Tracking**: Time logging and reports
- **File Browser**: File management and uploads
- **AI Chat**: AI assistant for project help

## Development Commands

```bash
# Development
npm run dev              # Start dev server
npm run storybook        # Start Storybook
npm run build            # Build for production
npm run preview          # Preview production build

# Testing
npm run test             # Run tests
npm run test:ui          # Run tests with UI
npm run test:coverage    # Run tests with coverage

# Linting
npm run lint             # Check for linting errors
npm run lint:fix         # Fix linting errors
npm run typecheck        # TypeScript type checking
```

## Technologies Used

- **React 18** - UI library
- **TypeScript** - Type safety
- **React Router** - Client-side routing
- **Tailwind CSS** - Utility-first CSS
- **Vite** - Build tool and dev server
- **React Query** - Data fetching and caching
- **Zustand** - State management
- **Axios** - HTTP client
- **Storybook** - Component development
- **Vitest** - Testing framework
- **Testing Library** - React testing utilities