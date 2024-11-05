<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_id')->nullable()->constrained();
            $table->string('title');
            $table->string('location')->nullable();
            $table->string('online_url')->nullable();
            $table->string('other_location')->nullable();
            $table->text('description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->dateTime('dt_start');
            $table->dateTime('dt_end');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
