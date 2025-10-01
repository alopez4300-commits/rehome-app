import { api } from './api';

export interface Workspace {
  id: number;
  name: string;
  description?: string;
  pivot: {
    role: string;
  };
  projects_count?: number;
  members_count?: number;
  created_at: string;
  updated_at: string;
}

export interface Project {
  id: number;
  name: string;
  description?: string;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface WorkspaceResponse {
  success: boolean;
  data: {
    workspace: Workspace;
  };
}

export interface ProjectsResponse {
  success: boolean;
  data: {
    projects: Project[];
  };
}

export const workspaceService = {
  async getWorkspace(workspaceId: number): Promise<WorkspaceResponse> {
    const response = await api.get(`/api/workspaces/${workspaceId}`);
    return response.data;
  },

  async getProjects(workspaceId: number): Promise<ProjectsResponse> {
    const response = await api.get(`/api/workspaces/${workspaceId}/projects`);
    return response.data;
  },

  async getProject(
    workspaceId: number,
    projectId: number
  ): Promise<{ success: boolean; data: { project: Project } }> {
    const response = await api.get(
      `/api/workspaces/${workspaceId}/projects/${projectId}`
    );
    return response.data;
  },
};
