<?php

use Archilex\AdvancedTables\Support\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Database\TenantScope;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filament_filter_sets', function (Blueprint $table) {
            $userClass = Config::getUser();
            $user = new $userClass();

            $table->id();
            $table->foreignId('user_id')->references('id')->on($user->getTable())->constrained();
            $table->integer('tenant_id')->nullable();
            $table->string('name');
            $table->string('resource');
            $table->text('filters');
            $table->json('indicators');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();

            $table->boolean('is_public');
            $table->boolean('is_global_favorite');
            $table->string('status')->default('approved');
            $table->smallInteger('sort_order')->default(1);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
        Config::getUserView()::query()
            ->withoutGlobalScope(TenantScope::class)
            ->global()
            ->each(function ($view) {
                $view->userManagedUserViews()
                    ->syncWithPivotValues($view->user_id, [
                        'is_visible' => true,
                    ], false);
            });
    }

    public function down(): void
    {
        Schema::drop('filament_filter_sets');
    }
};
