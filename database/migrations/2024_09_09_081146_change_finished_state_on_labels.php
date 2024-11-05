<?php

use App\Models\Label;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFinishedStateOnLabels extends Migration
{
    public function up(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            Tenant::each(function (Tenant $tenant) {
                $tenant->run(function () use ($tenant) {
                    Label::where('tenant_id', $tenant->id)
                        ->where('name', 'Done')
                        ->update(['finished_state' => true]);
                });
            });
        });
    }

    public function down(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            Tenant::each(function (Tenant $tenant) {
                $tenant->run(function () use ($tenant) {
                    Label::where('tenant_id', $tenant->id)
                        ->where('name', 'Done')
                        ->update(['finished_state' => false]);
                });
            });
        });
    }
}
