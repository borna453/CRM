<?php

use App\Enums\OnboardingTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnboardingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('onboardings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')
                ->nullable();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')->cascadeOnDelete();            
            $table->string('step');
            $table->boolean('is_complete')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboardings');
    }
}
