<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_clients', function (Blueprint $table) {
            $table->decimal('montant_facture', 15, 2)->default(0)->change();
        });

        Schema::table('course_expenses', function (Blueprint $table) {
            $table->decimal('montant', 15, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('course_clients', function (Blueprint $table) {
            $table->decimal('montant_facture', 10, 2)->default(0)->change();
        });

        Schema::table('course_expenses', function (Blueprint $table) {
            $table->decimal('montant', 10, 2)->change();
        });
    }
};