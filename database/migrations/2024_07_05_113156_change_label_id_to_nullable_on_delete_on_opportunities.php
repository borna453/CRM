<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLabelIdToNullableOnDeleteOnOpportunities extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            if(\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite'){
                $table->dropForeign(['label_id']);
            }

            $table->foreign('label_id')->references('id')->on('labels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['label_id']);
            $table->foreign('label_id')->references('id')->on('labels')->cascadeOnDelete();
        });
    }
}
