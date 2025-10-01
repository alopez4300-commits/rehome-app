# React SPA Login Credentials

## Test Users Created

The following test users have been created in the database for testing the React SPA:

### **Admin User**
- **Email**: `alice@admin.com`
- **Password**: `password`
- **Role**: `admin`
- **Admin Role**: `true`
- **Access**: Full system access, can access all workspaces

### **Team Member**
- **Email**: `bob@team.com`
- **Password**: `password`
- **Role**: `member`
- **Admin Role**: `false`
- **Access**: Standard team member with task and project access

### **Consultant**
- **Email**: `john@consulting.com`
- **Password**: `password`
- **Role**: `consultant`
- **Admin Role**: `false`
- **Access**: Limited access to assigned projects

### **Client**
- **Email**: `jane@client.com`
- **Password**: `password`
- **Role**: `client`
- **Admin Role**: `false`
- **Access**: Read-only access to project progress

## Demo Data Created

### **Workspace**
- **Name**: Demo Workspace
- **ID**: 1
- **Owner**: Alice Admin
- **Description**: A demonstration workspace for testing the ReHome v2 platform

### **Projects**
1. **Website Redesign** (ID: 2)
   - Status: `in_progress`
   - Workspace: Demo Workspace

2. **Mobile App Development** (ID: 3)
   - Status: `planning`
   - Workspace: Demo Workspace

3. **Brand Identity** (ID: 4)
   - Status: `completed`
   - Workspace: Demo Workspace

### **Workspace Memberships**
- **Alice Admin**: Owner
- **Bob Team**: Member
- **John Consultant**: Consultant
- **Jane Client**: Client

## Quick Login Buttons

The React SPA includes quick login buttons for development:

- **Admin** (Red button) - `alice@admin.com`
- **Team** (Green button) - `bob@team.com`
- **Consultant** (Yellow button) - `john@consulting.com`
- **Client** (Blue button) - `jane@client.com`

All use password: `password`

## Access Points

- **React SPA**: http://localhost:8000/app
- **Laravel API**: http://localhost:8000/api
- **Admin Panel**: http://localhost:8000/system (if Filament is installed)

## Testing the Login

1. Navigate to http://localhost:8000/app
2. Use the quick login buttons or enter credentials manually
3. Test different user roles and their access levels
4. Verify workspace and project access based on user roles

## Seeder Command

To recreate the test users:

```bash
php artisan db:seed --class=SPATestUsersSeeder
```

Or run all seeders:

```bash
php artisan db:seed
```
