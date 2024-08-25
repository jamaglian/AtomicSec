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
        Schema::create('attacks_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('params')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
        // Insert a default row
        \App\Models\AttackType::create([
            'name' => 'HTTP Keep-Alive',
            'params' => json_encode(['url' => 'url', 'proxys_virgulados' => 'proxys', 'threads' => 0]),
        ]);
        Schema::create('application_attacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unsigned();
            $table->foreign('application_id')->references('id')->on('applications');
            $table->foreignId('attacks_types_id')->unsigned();
            $table->foreign('attacks_types_id')->references('id')->on('attacks_types');
            $table->json('attack_params')->nullable();
            $table->json('attack_analysis')->nullable();
            $table->text('log')->nullable();
            $table->string('status')->default('Pendente');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finish_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_attacks');
        Schema::dropIfExists('attacks_types');
    }
};
