<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create regular users if they don't exist
        $user1 = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => bcrypt('password'),
                'has_admin_role' => false,
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'password' => bcrypt('password'),
                'has_admin_role' => false,
            ]
        );

        // Create workspaces
        $workspace1 = Workspace::create([
            'name' => 'Demo Workspace',
            'owner_id' => $user1->id,
        ]);

        $workspace2 = Workspace::create([
            'name' => 'Client Project Hub',
            'owner_id' => $user2->id,
        ]);

        // Attach users to workspaces
        $workspace1->users()->attach([
            $user1->id => ['role' => 'owner'],
            $user2->id => ['role' => 'member'],
        ]);

        $workspace2->users()->attach([
            $user2->id => ['role' => 'owner'],
        ]);

        // Create projects
        Project::create([
            'name' => 'Website Redesign',
            'workspace_id' => $workspace1->id,
            'status' => 'active',
        ]);

        Project::create([
            'name' => 'Mobile App Development',
            'workspace_id' => $workspace1->id,
            'status' => 'active',
        ]);

        Project::create([
            'name' => 'Brand Identity',
            'workspace_id' => $workspace2->id,
            'status' => 'completed',
        ]);
    }
}
