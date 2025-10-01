<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'workspace_id'];

    /**
     * The workspace this project belongs to
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
