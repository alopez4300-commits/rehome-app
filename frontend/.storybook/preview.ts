import type { Preview } from '@storybook/react';
import { withThemeByClassName } from '@storybook/addon-themes';
import { withRouter } from 'storybook-addon-react-router-v6';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
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
          <div style={{ padding: '1rem' }}>
            <Story />
          </div>
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
