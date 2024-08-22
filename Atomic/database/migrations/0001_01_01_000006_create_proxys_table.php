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
        Schema::create('proxys', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->string('port');
            $table->string('type'); //http, https, socks4, socks5
            $table->boolean('working')->default(0);
            $table->boolean('working_waf')->default(0);
            $table->timestamp('tested_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(['ip', 'port', 'type']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proxys');
    }
};
