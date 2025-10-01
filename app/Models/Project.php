<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'workspace_id',
        'created_by',
        'status',
        'start_date',
        'end_date',
    ];

    /**
     * The workspace this project belongs to
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * The user who created this project
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
