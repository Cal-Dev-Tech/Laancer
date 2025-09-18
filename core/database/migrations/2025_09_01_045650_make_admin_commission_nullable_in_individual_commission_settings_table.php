<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('individual_commission_settings', function (Blueprint $table) {
            $table->string('admin_commission_type')->nullable()->change();
            $table->double('admin_commission_charge')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('individual_commission_settings', function (Blueprint $table) {
            $table->string('admin_commission_type')->nullable(false)->change();
            $table->double('admin_commission_charge')->nullable(false)->change();
        });
    }
};
