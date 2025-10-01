# ReHome v2 Frontend

React SPA with Storybook 8.3 for the ReHome v2 project management platform.

## Features

- **React 18** with TypeScript
- **Storybook 8.3** for component development
- **Tailwind CSS** for styling
- **Vite** for fast development and builds
- **Vitest** for testing
- **React Query** for data fetching
- **React Router** for navigation

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
│   ├── myhome/         # MyHome system components
│   ├── ai/             # AI agent components
│   └── common/         # Common UI components
├── hooks/              # Custom React hooks
├── pages/              # Page components
├── contexts/           # React contexts
├── services/           # API services
├── types/              # TypeScript type definitions
└── utils/              # Utility functions
```

### Component Development

1. Create component in `src/components/`
2. Add Storybook story in `*.stories.tsx`
3. Test component in Storybook
4. Add to main application

### Storybook

Storybook provides an isolated environment for developing and testing components:

```bash
# Start Storybook
npm run storybook

# Build Storybook
npm run build-storybook
```

#### Available Addons

- **Essentials** - Controls, actions, viewport, backgrounds
- **A11y** - Accessibility testing
- **Interactions** - User interaction testing
- **Docs** - Component documentation
- **Themes** - Light/dark theme support
- **Viewport** - Responsive design testing

### Testing

```bash
# Run tests
npm run test

# Run tests with UI
npm run test:ui

# Run tests with coverage
npm run test:coverage
```

### Linting

```bash
# Check for linting errors
npm run lint

# Fix linting errors
npm run lint:fix

# Type checking
npm run typecheck
```

## Integration with Backend

### Authentication

The frontend integrates with Laravel Sanctum for authentication:

```typescript
// Get CSRF cookie
await axios.get('/sanctum/csrf-cookie');

// Get current user
const user = await axios.get('/api/user');
```

### API Integration

All API calls go through the Laravel backend:

```typescript
// MyHome API
const feed = await api.get('/api/projects/1/myhome/feed');
const note = await api.post('/api/projects/1/myhome/notes', { text: 'Hello' });

// AI Agent API
const response = await api.post('/api/projects/1/agent/chat', { query: 'Help me' });
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
REACT_APP_API_URL=http://localhost:8000
REACT_APP_ADMIN_URL=http://localhost:8000/admin
```

## Storybook Integration

### Component Stories

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
  argTypes: {
    variant: {
      control: { type: 'select' },
      options: ['primary', 'secondary', 'danger'],
    },
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

### Mock Data

Use mock data for stories:

```typescript
const mockUser = {
  id: 1,
  name: 'John Doe',
  email: 'john@example.com',
  has_admin_role: false,
};
```

### Testing in Storybook

Add interaction tests:

```typescript
import { userEvent, within } from '@storybook/test';

export const FilledForm: Story = {
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement);
    await userEvent.type(canvas.getByLabelText('Email'), 'test@example.com');
  },
};
```

## Best Practices

### Component Design

1. **Single Responsibility** - Each component should have one clear purpose
2. **Composition** - Build complex components from simple ones
3. **Props Interface** - Define clear TypeScript interfaces
4. **Accessibility** - Use semantic HTML and ARIA attributes

### Storybook

1. **Story Organization** - Group related components
2. **Documentation** - Add descriptions and examples
3. **Testing** - Include interaction tests
4. **Accessibility** - Use a11y addon

### Performance

1. **Code Splitting** - Use dynamic imports for large components
2. **Memoization** - Use React.memo for expensive renders
3. **Bundle Analysis** - Monitor bundle size

## Troubleshooting

### Common Issues

1. **Storybook not starting** - Check Node.js version
2. **TypeScript errors** - Verify tsconfig.json
3. **Build failures** - Check for missing dependencies
4. **Hot reload not working** - Restart development server

### Performance Issues

1. **Slow Storybook** - Reduce addon count
2. **Large bundle size** - Use dynamic imports
3. **Memory leaks** - Check component cleanup

## Resources

- [React Documentation](https://react.dev/)
- [Storybook 8.3 Documentation](https://storybook.js.org/docs/8.3)
- [Tailwind CSS](https://tailwindcss.com/)
- [Vite Documentation](https://vitejs.dev/)
- [ReHome v2 Backend](../README.md)
