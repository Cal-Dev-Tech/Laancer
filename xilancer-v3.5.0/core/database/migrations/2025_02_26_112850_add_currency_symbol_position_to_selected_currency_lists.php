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
        if(Schema::hasTable('selected_currency_lists')) {
            Schema::table('selected_currency_lists', function (Blueprint $table) {
                $table->string('currency_symbol_position')->after('symbol')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('selected_currency_lists', function (Blueprint $table) {
            //
        });
    }
};
