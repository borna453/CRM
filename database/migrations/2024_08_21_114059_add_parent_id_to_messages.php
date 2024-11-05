<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToMessages extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->after('tenant_id')->nullable();
            $table->foreign('parent_id')
                ->references('id')
                ->on('messages')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('conversation_id')->nullable()->after('parent_id');
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');

            $table->timestamp('seen_at')->nullable()->after('content');
            $table->string('title')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropColumn('seen_at');
            $table->string('title')->change();
            $table->dropForeign(['conversation_id']);
            $table->dropColumn('conversation_id');
        });
    }
}
