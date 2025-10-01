<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SPATestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'alice@admin.com'],
            [
                'name' => 'Alice Admin',
                'password' => bcrypt('password'),
                'has_admin_role' => true,
                'role' => 'admin',
            ]
        );

        // Create Team Member
        $teamMember = User::firstOrCreate(
            ['email' => 'bob@team.com'],
            [
                'name' => 'Bob Team',
                'password' => bcrypt('password'),
                'has_admin_role' => false,
                'role' => 'member',
            ]
        );

        // Create Consultant
        $consultant = User::firstOrCreate(
            ['email' => 'john@consulting.com'],
            [
                'name' => 'John Consultant',
                'password' => bcrypt('password'),
                'has_admin_role' => false,
                'role' => 'consultant',
            ]
        );

        // Create Client
        $client = User::firstOrCreate(
            ['email' => 'jane@client.com'],
            [
                'name' => 'Jane Client',
                'password' => bcrypt('password'),
                'has_admin_role' => false,
                'role' => 'client',
            ]
        );

        // Create Demo Workspace
        $workspace = Workspace::firstOrCreate(
            ['name' => 'Demo Workspace'],
            [
                'owner_id' => $admin->id,
                'description' => 'A demonstration workspace for testing the ReHome v2 platform',
            ]
        );

        // Attach users to workspace with appropriate roles
        $workspace->users()->syncWithoutDetaching([
            $admin->id => ['role' => 'owner'],
            $teamMember->id => ['role' => 'member'],
            $consultant->id => ['role' => 'consultant'],
            $client->id => ['role' => 'client'],
        ]);

        // Create Demo Projects
        $project1 = Project::firstOrCreate(
            ['name' => 'Website Redesign'],
            [
                'workspace_id' => $workspace->id,
                'status' => 'in_progress',
            ]
        );

        $project2 = Project::firstOrCreate(
            ['name' => 'Mobile App Development'],
            [
                'workspace_id' => $workspace->id,
                'status' => 'planning',
            ]
        );

        $project3 = Project::firstOrCreate(
            ['name' => 'Brand Identity'],
            [
                'workspace_id' => $workspace->id,
                'status' => 'completed',
            ]
        );

        $this->command->info('SPA Test Users created successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('  • Admin: alice@admin.com / password');
        $this->command->info('  • Team: bob@team.com / password');
        $this->command->info('  • Consultant: john@consulting.com / password');
        $this->command->info('  • Client: jane@client.com / password');
        $this->command->info("Workspace ID: {$workspace->id}");
        $this->command->info("Projects: {$project1->id}, {$project2->id}, {$project3->id}");
    }
}
