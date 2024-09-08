<?php
use App\Models\User;
use App\Models\Companies;
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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_owner_id')->unsigned();
            $table->foreign('company_owner_id')->references('id')->on('users');
            $table->string('name')->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
        Companies::unguarded(function () {
            $userId = User::where('email', 'teste@atomicsec.com.br')->value('id');
            Companies::create([
                'company_owner_id' => $userId,
                'name' => 'Compania Teste'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
