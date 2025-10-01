# ReHome v2 - Storybook 8.3 Integration Guide

## Overview

This guide covers integrating Storybook 8.3 with the ReHome v2 React SPA, including authentication, MyHome system components, and AI agent interfaces.

## Storybook 8.3 Features

### Key Features
- **Vite 5 support** - Faster builds and HMR
- **React 18 support** - Concurrent features and Suspense
- **TypeScript support** - Built-in TypeScript configuration
- **Addon ecosystem** - Rich addon library
- **Performance improvements** - Faster startup and builds

### Installation

```bash
# Install Storybook 8.3
npx storybook@8.3 init

# Or upgrade existing Storybook
npx storybook@8.3 upgrade
```

## Project Structure

```
frontend/
├── src/
│   ├── components/
│   │   ├── auth/
│   │   │   ├── LoginForm.stories.tsx
│   │   │   ├── ProtectedRoute.stories.tsx
│   │   │   └── UserProfile.stories.tsx
│   │   ├── myhome/
│   │   │   ├── ActivityFeed.stories.tsx
│   │   │   ├── NoteEditor.stories.tsx
│   │   │   ├── TaskCard.stories.tsx
│   │   │   └── TimeLogForm.stories.tsx
│   │   ├── ai/
│   │   │   ├── ChatInterface.stories.tsx
│   │   │   ├── MessageBubble.stories.tsx
│   │   │   └── AIStats.stories.tsx
│   │   └── common/
│   │       ├── Button.stories.tsx
│   │       ├── Modal.stories.tsx
│   │       └── LoadingSpinner.stories.tsx
│   ├── hooks/
│   │   ├── useAuth.stories.tsx
│   │   └── useMyHome.stories.tsx
│   └── pages/
│       ├── Dashboard.stories.tsx
│       ├── ProjectOverview.stories.tsx
│       └── TaskBoard.stories.tsx
├── .storybook/
│   ├── main.ts
│   ├── preview.ts
│   └── manager.ts
└── package.json
```

## Configuration

### .storybook/main.ts

```typescript
import type { StorybookConfig } from '@storybook/react-vite';

const config: StorybookConfig = {
  stories: ['../src/**/*.stories.@(js|jsx|ts|tsx|mdx)'],
  addons: [
    '@storybook/addon-essentials',
    '@storybook/addon-interactions',
    '@storybook/addon-a11y',
    '@storybook/addon-docs',
    '@storybook/addon-controls',
    '@storybook/addon-viewport',
    '@storybook/addon-backgrounds',
    '@storybook/addon-measure',
    '@storybook/addon-outline',
    '@storybook/addon-toolbars',
    '@storybook/addon-actions',
    '@storybook/addon-links',
    '@storybook/addon-storysource',
    '@storybook/addon-coverage',
    '@storybook/addon-jest',
    '@storybook/addon-design-tokens',
    '@storybook/addon-themes',
  ],
  framework: {
    name: '@storybook/react-vite',
    options: {},
  },
  typescript: {
    check: false,
    reactDocgen: 'react-docgen-typescript',
    reactDocgenTypescriptOptions: {
      shouldExtractLiteralValuesFromEnum: true,
      propFilter: (prop) => (prop.parent ? !/node_modules/.test(prop.parent.fileName) : true),
    },
  },
  viteFinal: async (config) => {
    // Add any custom Vite configuration here
    return config;
  },
  docs: {
    autodocs: 'tag',
  },
  staticDirs: ['../public'],
};

export default config;
```

### .storybook/preview.ts

```typescript
import type { Preview } from '@storybook/react';
import { withThemeByClassName } from '@storybook/addon-themes';
import { withRouter } from 'storybook-addon-react-router-v6';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from '../src/contexts/AuthContext';
import '../src/index.css';

// Mock API for Storybook
const mockApi = {
  get: async (url: string) => {
    console.log(`Mock API GET: ${url}`);
    return { data: {} };
  },
  post: async (url: string, data: any) => {
    console.log(`Mock API POST: ${url}`, data);
    return { data: {} };
  },
};

// Mock user for stories
const mockUser = {
  id: 1,
  name: 'John Doe',
  email: 'john@example.com',
  has_admin_role: false,
  role: 'member',
  workspaces: [
    {
      id: 1,
      name: 'Demo Workspace',
      pivot: { role: 'member' }
    }
  ]
};

const mockAdminUser = {
  ...mockUser,
  name: 'Admin User',
  email: 'admin@rehome.com',
  has_admin_role: true,
  role: 'admin'
};

const preview: Preview = {
  parameters: {
    actions: { argTypesRegex: '^on[A-Z].*' },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
    backgrounds: {
      default: 'light',
      values: [
        { name: 'light', value: '#ffffff' },
        { name: 'dark', value: '#1a1a1a' },
        { name: 'gray', value: '#f5f5f5' },
      ],
    },
    viewport: {
      viewports: {
        mobile: {
          name: 'Mobile',
          styles: { width: '375px', height: '667px' },
        },
        tablet: {
          name: 'Tablet',
          styles: { width: '768px', height: '1024px' },
        },
        desktop: {
          name: 'Desktop',
          styles: { width: '1024px', height: '768px' },
        },
      },
    },
    docs: {
      toc: true,
    },
  },
  decorators: [
    withThemeByClassName({
      themes: {
        light: 'light',
        dark: 'dark',
      },
      defaultTheme: 'light',
    }),
    withRouter({
      initialEntries: ['/'],
    }),
    (Story) => {
      const queryClient = new QueryClient({
        defaultOptions: {
          queries: { retry: false },
          mutations: { retry: false },
        },
      });

      return (
        <QueryClientProvider client={queryClient}>
          <AuthProvider>
            <div style={{ padding: '1rem' }}>
              <Story />
            </div>
          </AuthProvider>
        </QueryClientProvider>
      );
    },
  ],
  globalTypes: {
    user: {
      description: 'User type for stories',
      defaultValue: 'member',
      toolbar: {
        title: 'User Type',
        icon: 'user',
        items: [
          { value: 'member', title: 'Member' },
          { value: 'admin', title: 'Admin' },
          { value: 'client', title: 'Client' },
        ],
        dynamicTitle: true,
      },
    },
  },
};

export default preview;
```

## Component Stories

### Authentication Components

#### LoginForm.stories.tsx

```typescript
import type { Meta, StoryObj } from '@storybook/react';
import { LoginForm } from '../LoginForm';

const meta: Meta<typeof LoginForm> = {
  title: 'Auth/LoginForm',
  component: LoginForm,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'Login form component for user authentication.',
      },
    },
  },
  argTypes: {
    onSubmit: { action: 'submitted' },
    onForgotPassword: { action: 'forgot-password' },
    loading: { control: 'boolean' },
    error: { control: 'text' },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    loading: false,
    error: null,
  },
};

export const Loading: Story = {
  args: {
    loading: true,
    error: null,
  },
};

export const WithError: Story = {
  args: {
    loading: false,
    error: 'Invalid email or password',
  },
};

export const Mobile: Story = {
  parameters: {
    viewport: { defaultViewport: 'mobile' },
  },
};
```

#### ProtectedRoute.stories.tsx

```typescript
import type { Meta, StoryObj } from '@storybook/react';
import { ProtectedRoute } from '../ProtectedRoute';
import { Dashboard } from '../../pages/Dashboard';

const meta: Meta<typeof ProtectedRoute> = {
  title: 'Auth/ProtectedRoute',
  component: ProtectedRoute,
  parameters: {
    layout: 'fullscreen',
  },
  argTypes: {
    requireAdmin: { control: 'boolean' },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const MemberAccess: Story = {
  args: {
    requireAdmin: false,
    children: <Dashboard />,
  },
  parameters: {
    user: 'member',
  },
};

export const AdminAccess: Story = {
  args: {
    requireAdmin: true,
    children: <Dashboard />,
  },
  parameters: {
    user: 'admin',
  },
};

export const Unauthorized: Story = {
  args: {
    requireAdmin: true,
    children: <Dashboard />,
  },
  parameters: {
    user: 'client',
  },
};
```

### MyHome Components

#### ActivityFeed.stories.tsx

```typescript
import type { Meta, StoryObj } from '@storybook/react';
import { ActivityFeed } from '../ActivityFeed';

const mockEntries = [
  {
    id: 1,
    ts: '2025-01-15T14:23:10Z',
    author: 1,
    author_name: 'John Doe',
    kind: 'note',
    text: 'Project kickoff meeting completed successfully.',
  },
  {
    id: 2,
    ts: '2025-01-15T14:24:15Z',
    author: 1,
    author_name: 'John Doe',
    kind: '/task',
    title: 'Setup development environment',
    status: 'completed',
  },
  {
    id: 3,
    ts: '2025-01-15T14:30:00Z',
    author: 2,
    author_name: 'Jane Smith',
    kind: '/time',
    hours: 2.5,
    task: 'Development setup',
  },
];

const meta: Meta<typeof ActivityFeed> = {
  title: 'MyHome/ActivityFeed',
  component: ActivityFeed,
  parameters: {
    layout: 'padded',
    docs: {
      description: {
        component: 'Activity feed displaying MyHome entries in chronological order.',
      },
    },
  },
  argTypes: {
    entries: { control: 'object' },
    loading: { control: 'boolean' },
    onLoadMore: { action: 'load-more' },
    onEntryClick: { action: 'entry-click' },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    entries: mockEntries,
    loading: false,
  },
};

export const Loading: Story = {
  args: {
    entries: [],
    loading: true,
  },
};

export const Empty: Story = {
  args: {
    entries: [],
    loading: false,
  },
};

export const LongList: Story = {
  args: {
    entries: Array.from({ length: 50 }, (_, i) => ({
      ...mockEntries[0],
      id: i + 1,
      text: `Entry ${i + 1}: This is a longer entry to test scrolling behavior.`,
    })),
    loading: false,
  },
};
```

#### TaskCard.stories.tsx

```typescript
import type { Meta, StoryObj } from '@storybook/react';
import { TaskCard } from '../TaskCard';

const meta: Meta<typeof TaskCard> = {
  title: 'MyHome/TaskCard',
  component: TaskCard,
  parameters: {
    layout: 'centered',
  },
  argTypes: {
    task: { control: 'object' },
    onStatusChange: { action: 'status-change' },
    onAssign: { action: 'assign' },
    onEdit: { action: 'edit' },
    onDelete: { action: 'delete' },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Pending: Story = {
  args: {
    task: {
      id: 1,
      title: 'Setup development environment',
      description: 'Configure Laravel, database, and development tools',
      status: 'pending',
      due_date: '2025-01-20',
      assigned_to: [],
    },
  },
};

export const InProgress: Story = {
  args: {
    task: {
      id: 2,
      title: 'Implement MyHome system',
      description: 'Build append-only NDJSON activity logging system',
      status: 'in_progress',
      due_date: '2025-01-25',
      assigned_to: [
        { id: 1, name: 'John Doe' },
        { id: 2, name: 'Jane Smith' },
      ],
    },
  },
};

export const Completed: Story = {
  args: {
    task: {
      id: 3,
      title: 'Create API endpoints',
      description: 'Build REST API for MyHome operations',
      status: 'completed',
      due_date: '2025-01-30',
      assigned_to: [{ id: 1, name: 'John Doe' }],
    },
  },
};

export const Overdue: Story = {
  args: {
    task: {
      id: 4,
      title: 'Overdue task',
      description: 'This task is past its due date',
      status: 'pending',
      due_date: '2025-01-10',
      assigned_to: [],
    },
  },
};
```

### AI Agent Components

#### ChatInterface.stories.tsx

```typescript
import type { Meta, StoryObj } from '@storybook/react';
import { ChatInterface } from '../ChatInterface';

const mockMessages = [
  {
    id: 1,
    role: 'user',
    content: 'What are the key requirements for the MyHome system?',
    timestamp: '2025-01-15T15:30:00Z',
  },
  {
    id: 2,
    role: 'assistant',
    content: 'The MyHome system should provide append-only activity logging with NDJSON format, support for multiple entry types (notes, tasks, time logs, files, AI interactions), and efficient querying capabilities.',
    timestamp: '2025-01-15T15:30:05Z',
    metadata: {
      provider: 'claude',
      model: 'claude-3-sonnet-20240229',
      tokens_used: 150,
      response_time: 1250,
    },
  },
];

const meta: Meta<typeof ChatInterface> = {
  title: 'AI/ChatInterface',
  component: ChatInterface,
  parameters: {
    layout: 'padded',
    docs: {
      description: {
        component: 'AI chat interface for project assistance and insights.',
      },
    },
  },
  argTypes: {
    messages: { control: 'object' },
    loading: { control: 'boolean' },
    onSendMessage: { action: 'send-message' },
    onClearHistory: { action: 'clear-history' },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    messages: mockMessages,
    loading: false,
  },
};

export const Loading: Story = {
  args: {
    messages: mockMessages,
    loading: true,
  },
};

export const Empty: Story = {
  args: {
    messages: [],
    loading: false,
  },
};

export const LongConversation: Story = {
  args: {
    messages: Array.from({ length: 20 }, (_, i) => ({
      id: i + 1,
      role: i % 2 === 0 ? 'user' : 'assistant',
      content: `Message ${i + 1}: This is a longer conversation to test scrolling behavior.`,
      timestamp: new Date(Date.now() - (20 - i) * 60000).toISOString(),
    })),
    loading: false,
  },
};
```

## Mock Data and Services

### Mock API Service

```typescript
// src/mocks/api.ts
export const mockApi = {
  auth: {
    getCurrentUser: async () => ({
      id: 1,
      name: 'John Doe',
      email: 'john@example.com',
      has_admin_role: false,
      role: 'member',
      workspaces: [
        {
          id: 1,
          name: 'Demo Workspace',
          pivot: { role: 'member' }
        }
      ]
    }),
    login: async (email: string, password: string) => {
      console.log('Mock login:', { email, password });
      return { success: true };
    },
  },
  
  myhome: {
    getFeed: async (projectId: number) => ({
      entries: mockEntries,
      pagination: {
        total: 100,
        limit: 50,
        offset: 0,
        has_more: true,
      }
    }),
    createNote: async (projectId: number, text: string) => ({
      id: Date.now(),
      ts: new Date().toISOString(),
      author: 1,
      author_name: 'John Doe',
      kind: 'note',
      text,
    }),
  },
  
  ai: {
    sendMessage: async (projectId: number, query: string) => ({
      success: true,
      content: `Mock AI response to: ${query}`,
      provider: 'claude',
      model: 'claude-3-sonnet-20240229',
      tokens_used: 100,
      response_time: 1000,
    }),
  },
};
```

### Mock Context Providers

```typescript
// src/mocks/contexts.tsx
import React from 'react';
import { AuthContext } from '../contexts/AuthContext';
import { mockApi } from './api';

export const MockAuthProvider: React.FC<{ children: React.ReactNode; user?: any }> = ({ 
  children, 
  user = mockApi.auth.getCurrentUser() 
}) => {
  const [currentUser, setCurrentUser] = React.useState(user);
  
  const value = {
    user: currentUser,
    loading: false,
    isAuthenticated: !!currentUser,
    isAdmin: currentUser?.has_admin_role || false,
    login: async (email: string, password: string) => {
      await mockApi.auth.login(email, password);
      setCurrentUser(await mockApi.auth.getCurrentUser());
    },
    logout: () => setCurrentUser(null),
  };
  
  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
```

## Addon Configuration

### Design Tokens

```typescript
// .storybook/design-tokens.ts
export const designTokens = {
  colors: {
    primary: {
      50: '#fefce8',
      500: '#eab308',
      900: '#713f12',
    },
    gray: {
      50: '#f9fafb',
      500: '#6b7280',
      900: '#111827',
    },
  },
  spacing: {
    xs: '0.25rem',
    sm: '0.5rem',
    md: '1rem',
    lg: '1.5rem',
    xl: '2rem',
  },
  typography: {
    fontFamily: {
      sans: ['Inter', 'system-ui', 'sans-serif'],
    },
    fontSize: {
      sm: '0.875rem',
      base: '1rem',
      lg: '1.125rem',
      xl: '1.25rem',
    },
  },
};
```

### Custom Addons

```typescript
// .storybook/addons/rehome-addon.ts
import { addons } from '@storybook/manager-api';

addons.register('rehome/addon', () => {
  addons.add('rehome/panel', {
    title: 'ReHome Context',
    type: 'panel',
    match: ({ viewMode }) => viewMode === 'story',
    render: () => {
      // Custom panel implementation
      return document.createElement('div');
    },
  });
});
```

## Testing with Storybook

### Visual Testing

```typescript
// .storybook/test-runner.ts
import { test, expect } from '@playwright/test';

test('LoginForm visual regression', async ({ page }) => {
  await page.goto('http://localhost:6006/?path=/story/auth-loginform--default');
  await expect(page).toHaveScreenshot('login-form-default.png');
});
```

### Interaction Testing

```typescript
// src/components/LoginForm.stories.tsx
import { userEvent, within } from '@storybook/test';

export const FilledForm: Story = {
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement);
    
    await userEvent.type(canvas.getByLabelText('Email'), 'test@example.com');
    await userEvent.type(canvas.getByLabelText('Password'), 'password123');
    await userEvent.click(canvas.getByRole('button', { name: 'Sign In' }));
  },
};
```

## Build and Deployment

### Build Configuration

```json
// package.json
{
  "scripts": {
    "storybook": "storybook dev -p 6006",
    "build-storybook": "storybook build",
    "test-storybook": "test-storybook",
    "chromatic": "chromatic --project-token=your-token"
  }
}
```

### CI/CD Integration

```yaml
# .github/workflows/storybook.yml
name: Storybook
on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '20'
      - run: npm ci
      - run: npm run build-storybook
      - uses: actions/upload-artifact@v3
        with:
          name: storybook-static
          path: storybook-static
```

## Best Practices

### 1. Component Organization
- Group related components in folders
- Use consistent naming conventions
- Include both simple and complex examples

### 2. Story Structure
- Start with basic examples
- Add variations for different states
- Include edge cases and error states

### 3. Documentation
- Use JSDoc comments for props
- Add descriptions to stories
- Include usage examples

### 4. Testing
- Write interaction tests for complex components
- Use visual regression testing
- Test accessibility with addon-a11y

### 5. Performance
- Lazy load heavy components
- Use React.memo for expensive renders
- Optimize bundle size

## Integration with ReHome v2

### Authentication Flow
1. Mock authentication context
2. Test different user roles
3. Verify protected routes

### MyHome System
1. Mock MyHome API responses
2. Test activity feed components
3. Verify real-time updates

### AI Agent
1. Mock AI responses
2. Test chat interface
3. Verify context building

### Project Management
1. Mock project data
2. Test task management
3. Verify workspace permissions

## Troubleshooting

### Common Issues
1. **Vite configuration conflicts** - Check vite.config.ts
2. **TypeScript errors** - Verify tsconfig.json
3. **Addon compatibility** - Check version compatibility
4. **Build failures** - Check Node.js version

### Performance Issues
1. **Slow startup** - Optimize addon configuration
2. **Memory leaks** - Check component cleanup
3. **Bundle size** - Use dynamic imports

## Resources

- [Storybook 8.3 Documentation](https://storybook.js.org/docs/8.3)
- [React Integration Guide](https://storybook.js.org/docs/react/get-started/introduction)
- [Addon Ecosystem](https://storybook.js.org/addons)
- [Testing Guide](https://storybook.js.org/docs/react/writing-tests/introduction)
- [ReHome v2 SPA Auth Guide](./CODESPACE_SPA_AUTH.md)
