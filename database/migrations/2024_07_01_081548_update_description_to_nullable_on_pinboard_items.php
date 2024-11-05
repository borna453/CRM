<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDescriptionToNullableOnPinboardItems extends Migration
{
    public function up(): void
    {
        Schema::table('pinboard_items', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pinboard_items', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
        });
    }
}
