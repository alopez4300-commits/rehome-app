<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all projects
        if ($user->isAdmin()) {
            return true;
        }
        
        // Users can view projects if they belong to any workspace
        return $user->workspaces()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Admin can view all projects
        if ($user->isAdmin()) {
            return true;
        }
        
        // Users can view projects in workspaces they belong to
        return $user->workspaces()
            ->where('workspaces.id', $project->workspace_id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Users can create projects if they own or are members of any workspace
        return $user->workspaces()
            ->whereIn('workspace_user.role', ['owner', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // Admin can update all projects
        if ($user->isAdmin()) {
            return true;
        }
        
        // Users can update projects if they are owner or member of the workspace
        return $user->workspaces()
            ->where('workspaces.id', $project->workspace_id)
            ->whereIn('workspace_user.role', ['owner', 'member'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only workspace owners can delete projects
        return $user->workspaces()
            ->where('workspaces.id', $project->workspace_id)
            ->where('workspace_user.role', 'owner')
            ->exists();
    }

    /**
     * Determine whether the user can access MyHome for this project.
     */
    public function accessMyHome(User $user, Project $project): bool
    {
        // Same as view permission - users can access MyHome for projects they can view
        return $this->view($user, $project);
    }

    /**
     * Determine whether the user can add entries to MyHome for this project.
     */
    public function addToMyHome(User $user, Project $project): bool
    {
        // Users can add to MyHome if they are members of the workspace (not just clients)
        return $user->workspaces()
            ->where('workspaces.id', $project->workspace_id)
            ->whereIn('workspace_user.role', ['owner', 'member', 'consultant'])
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }
}
