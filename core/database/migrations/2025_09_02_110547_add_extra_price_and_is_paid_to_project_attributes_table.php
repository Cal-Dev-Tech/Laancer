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
        Schema::table('project_attributes', function (Blueprint $table) {
            $table->tinyInteger('is_paid')
                ->default(0)
                ->after('type')
                ->comment('0 = free, 1 = paid');

            $table->decimal('basic_extra_price', 10, 2)
                ->nullable()
                ->after('basic_check_numeric');
            $table->decimal('standard_extra_price', 10, 2)
                ->nullable()
                ->after('standard_check_numeric');
            $table->decimal('premium_extra_price', 10, 2)
                ->nullable()
                ->after('premium_check_numeric');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_attributes', function (Blueprint $table) {
            $table->dropColumn([
                'is_paid',
                'basic_extra_price',
                'standard_extra_price',
                'premium_extra_price',
            ]);
        });
    }
};
