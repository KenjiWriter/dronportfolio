<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('email')->after('name');
            $table->dropColumn(['company_name', 'location']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->string('company_name')->nullable()->after('name');
            $table->string('location')->after('company_name');
        });
    }
};
