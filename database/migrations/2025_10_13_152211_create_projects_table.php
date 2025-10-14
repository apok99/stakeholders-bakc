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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_context');
            $table->string('promoting_company');
            $table->text('brief_project_description');
            $table->string('location');
            $table->string('current_phase');
            $table->string('main_objective');
            $table->text('perceived_sensitive_issues');
            $table->text('known_initial_actors');
            $table->text('next_milestones');
            $table->text('reference_links')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
