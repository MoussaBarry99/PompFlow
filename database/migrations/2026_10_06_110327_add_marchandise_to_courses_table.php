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
        Schema::table('courses', function (Blueprint $table) {
            //
            $table->string('type_marchandise')->nullable();
            $table->decimal('poids_kg', 8, 2)->nullable(); 
            $table->text('description_marchandise')->nullable(); 
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
            $table->dropColumn('type_marchandise');
            $table->dropColumn('poids_kg');
            $table->dropColumn('description_marchandise');
        });
    }
};
