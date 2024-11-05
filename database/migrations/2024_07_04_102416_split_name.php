<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('company_id');
            $table->string('first_name')->nullable()->after('company_id');
        });

        // first name is the first word in the name column
        // last name is the rest of the name column
        if (! Schema::getConnection() instanceof \Illuminate\Database\SQLiteConnection) {
            User::query()
                ->update([
                    'first_name' => DB::raw('SUBSTRING_INDEX(TRIM(name), " ", 1)'),
                    'last_name' => DB::raw('SUBSTRING(TRIM(name), LENGTH(SUBSTRING_INDEX(TRIM(name), " ", 1)) + 2)'),
                ]);
        } else {
            User::query()
                ->update([
                    'first_name' => DB::raw('SUBSTR(TRIM(name), 0, INSTR(TRIM(name), " "))'),
                    'last_name' => DB::raw('SUBSTR(TRIM(name), INSTR(TRIM(name), " ") + 1)'),
                ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::getConnection() instanceof \Illuminate\Database\SQLiteConnection) {
                $table->string('name')->virtualAs('concat(coalesce(first_name, ""), " ", coalesce(last_name, ""))')->after('company_id');
            } else {
                $table->string('name')->virtualAs('coalesce(first_name, "") || " " || coalesce(last_name, "")')->after('company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('company_id');
        });

        // Combine first and last name into the name column
        User::query()
            ->update([
                'name' => DB::raw('CONCAT_WS(" ", first_name, last_name)'),
            ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
        });
    }
};
