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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('instructions');
            $table->decimal('price', 10, 2);
            $table->datetime('deadline');
            $table->string('task_size')->nullable();
            $table->string('type_of_service');
            $table->string('discipline')->nullable();
            $table->string('software')->nullable();
            $table->string('status')->default('available');
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('writer_id')->nullable()->constrained('users');
            $table->text('customer_comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
