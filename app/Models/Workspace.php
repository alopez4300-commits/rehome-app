<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    /**
     * The workspace owner
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Users that belong to this workspace
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Projects in this workspace
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
