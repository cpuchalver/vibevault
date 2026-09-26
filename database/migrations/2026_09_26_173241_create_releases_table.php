<?php

declare(strict_types=1);

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
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type');
            $table->string('status')->default('draft');
            $table->string('upc', 14)->nullable();
            $table->string('catalog_number')->nullable();
            $table->date('release_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['label_id', 'upc']);
            $table->unique(['label_id', 'catalog_number']);
            $table->index(['label_id', 'status']);
            $table->index(['artist_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
