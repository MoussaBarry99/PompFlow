<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_client_id')->unique()->constrained('course_clients')->cascadeOnDelete();
            $table->string('numero');
            $table->date('emise_le');
            $table->decimal('montant', 15, 2);
            $table->timestamps();

            $table->unique(['company_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
