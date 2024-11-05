<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')
                ->nullable();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')->cascadeOnDelete();
            $table->string('recipient_type')->nullable();
            $table->json('recipient_ids')->nullable();
            $table->string('title');
            $table->text('content');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
}
