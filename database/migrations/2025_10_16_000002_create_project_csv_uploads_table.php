<?php

use App\Models\FormDefinition;
use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_csv_uploads', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Project::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(FormDefinition::class)->nullable()->constrained()->nullOnDelete();
            $table->string('storage_disk');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('status')->default('pending');
            $table->json('processing_payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_csv_uploads');
    }
};
