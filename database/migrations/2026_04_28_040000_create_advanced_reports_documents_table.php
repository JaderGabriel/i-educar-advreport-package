<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advanced_reports_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 32)->unique();
            $table->string('type', 40); // diploma|certificate|declaration
            $table->timestamp('issued_at');
            $table->string('mac', 64)->nullable()->index();
            $table->unsignedSmallInteger('version')->default(1);
            $table->jsonb('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advanced_reports_documents');
    }
};

