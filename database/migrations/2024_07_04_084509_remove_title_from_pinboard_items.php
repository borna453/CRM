<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTitleFromPinboardItems extends Migration
{
    public function up(): void
    {
        Schema::table('pinboard_items', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }

    public function down(): void
    {
        Schema::table('pinboard_items', function (Blueprint $table) {
            $table->string('title')->nullable();
        });
    }
}
