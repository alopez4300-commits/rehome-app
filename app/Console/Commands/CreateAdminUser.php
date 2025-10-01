<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for ReHome v2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@rehome.com',
            'password' => Hash::make('password'),
            'has_admin_role' => true,
        ]);

        $this->info('Admin user created successfully!');
        $this->info('Email: admin@rehome.com');
        $this->info('Password: password');
        
        return 0;
    }
}
