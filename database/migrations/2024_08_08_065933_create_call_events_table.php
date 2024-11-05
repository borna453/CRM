<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallEventsTable extends Migration
{
    public function up(): void
    {
        Schema::create('call_events', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->unique();
            $table->string('tenant_id')
                ->nullable();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('answered_by')->nullable();
            $table->foreign('answered_by')->references('id')->on('users')->nullOnDelete();
            $table->string('to_number')->nullable();
            $table->string('from_number')->nullable();
            $table->dateTime('event_time');
            $table->string('call_type')->nullable();
            $table->string('call_status')->nullable();
            $table->string('duration')->nullable();
            $table->text('insights_summary')->nullable();
            $table->string('sentiment_indicator')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_events');
    }
}
