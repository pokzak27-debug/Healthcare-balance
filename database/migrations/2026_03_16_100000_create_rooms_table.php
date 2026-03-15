<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['cabinet_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
