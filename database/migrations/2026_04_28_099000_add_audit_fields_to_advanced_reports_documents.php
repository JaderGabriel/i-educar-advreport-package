<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advanced_reports_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('issued_by_user_id')->nullable()->index();
            $table->string('issued_ip', 45)->nullable();
            $table->string('issued_user_agent', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('advanced_reports_documents', function (Blueprint $table) {
            $table->dropColumn(['issued_by_user_id', 'issued_ip', 'issued_user_agent']);
        });
    }
};

