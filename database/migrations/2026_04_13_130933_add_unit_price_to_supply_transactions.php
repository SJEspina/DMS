<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('supply_transactions', 'unit_price')) {
            Schema::table('supply_transactions', function (Blueprint $table) {
                $table->decimal('unit_price', 10, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('supply_transactions', 'unit_price')) {
            Schema::table('supply_transactions', function (Blueprint $table) {
                $table->dropColumn('unit_price');
            });
        }
    }
};