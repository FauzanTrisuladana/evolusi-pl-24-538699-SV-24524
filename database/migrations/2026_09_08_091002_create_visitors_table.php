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
        Schema::create('visitors', function (Blueprint $table) {
            $table->bigIncrements('id_visitor');
            $table->unsignedBigInteger('id_link')->nullable()->index();
            $table->string('ip_address');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('user_agent');
            $table->timestamp('timestamp')->useCurrent();

            $table->index('country');
            $table->index('city');
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
