<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractCostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('contract_costs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')
                ->nullable();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->float('cost_estimate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_costs');
    }
}
