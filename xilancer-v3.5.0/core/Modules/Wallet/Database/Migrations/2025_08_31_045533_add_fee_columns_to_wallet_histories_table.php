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
        Schema::table('wallet_histories', function (Blueprint $table) {
            $table->decimal('transaction_fee', 10, 2)->default(0)->after('amount');
            $table->decimal('total', 10, 2)->default(0)->after('transaction_fee');
            $table->text('note')->nullable()->after('manual_payment_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_histories', function (Blueprint $table) {
            $table->dropColumn(['transaction_fee', 'total', 'note']);
        });
    }
};
