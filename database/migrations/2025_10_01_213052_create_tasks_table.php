<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');  // pending, in_progress, completed
            $table->integer('priority')->default(0);
            $table->date('due_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('visible_to_client')->default(true);
            $table->integer('order')->default(0);  // For manual sorting
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
