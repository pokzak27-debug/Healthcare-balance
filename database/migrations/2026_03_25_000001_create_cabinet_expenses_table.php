<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabinet_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // supply | charge
            $table->string('category', 80);
            $table->string('description', 500)->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabinet_expenses');
    }
};
