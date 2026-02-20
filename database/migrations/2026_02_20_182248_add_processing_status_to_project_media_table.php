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
        Schema::table('project_media', function (Blueprint $table) {
            // Track compression pipeline state
            $table->string('processing_status')->default('ready')->after('sort_order');
            // Quality chosen by the admin in the UI (e.g. "720p")
            $table->string('video_quality')->nullable()->after('processing_status');
            // Holds the raw/original upload path while compression is ongoing
            $table->string('original_file_path')->nullable()->after('video_quality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_media', function (Blueprint $table) {
            $table->dropColumn(['processing_status', 'video_quality', 'original_file_path']);
        });
    }
};
