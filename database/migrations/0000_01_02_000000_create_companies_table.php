<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')
                ->nullable();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')->cascadeOnDelete();
            $table->boolean('is_main')->default(false);
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('city')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('coc_number')->nullable();
            $table->unique(['name', 'tenant_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
