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
        Schema::create('dossier_details', function (Blueprint $table) {
            $table->id();
            $table->integer('dossier_id');
            $table->string('request_type')->nullable();
            $table->date('received_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('status')->nullable();
            $table->json('history')->nullable();
            $table->json('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossier_details');
    }
};
