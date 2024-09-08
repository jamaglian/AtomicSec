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
        Schema::create('applications_analysis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unsigned();
            $table->foreign('application_id')->references('id')->on('applications');
            $table->json('analysis')->nullable();
            $table->longText('log')->nullable();
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
        Schema::dropIfExists('applications_analysis');
    }
};
