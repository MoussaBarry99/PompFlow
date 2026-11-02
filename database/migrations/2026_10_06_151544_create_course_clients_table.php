<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->decimal('montant_facture', 10, 2)->default(0);
            $table->string('type_marchandise')->nullable();
            $table->decimal('poids_kg', 8, 2)->nullable();
            $table->text('description_marchandise')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'client_id']);
        });

        DB::statement('INSERT INTO course_clients (course_id, client_id, montant_facture, type_marchandise, poids_kg, description_marchandise, created_at, updated_at) SELECT id, client_id, COALESCE(montant_facture, 0), type_marchandise, poids_kg, description_marchandise, NOW(), NOW() FROM courses WHERE client_id IS NOT NULL');

        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn(['montant_facture', 'type_marchandise', 'poids_kg', 'description_marchandise']);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('montant_facture', 10, 2)->nullable();
            $table->string('type_marchandise')->nullable();
            $table->decimal('poids_kg', 8, 2)->nullable();
            $table->text('description_marchandise')->nullable();
        });

        Schema::dropIfExists('course_clients');
    }
};