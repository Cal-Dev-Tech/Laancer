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
        Schema::create('can_contact_freelancers', function (Blueprint $table) {
            $table->id();
            $table->integer('can_contact_freelancer')->default(0)->comment('0:no, 1:yes');
            $table->integer('show_contact_me_before_login')->default(0)->comment('0:no, 1:yes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('can_contact_freelancers');
    }
};
