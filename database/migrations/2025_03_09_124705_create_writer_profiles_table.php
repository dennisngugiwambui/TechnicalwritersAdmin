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
        Schema::create('writer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('writer_id')->unique();
            $table->string('phone_number')->nullable();
            $table->string('national_id')->nullable();
            $table->string('national_id_image')->nullable();
            $table->enum('id_verification_status', ['not-verified', 'pending', 'verified', 'rejected'])->default('not-verified');
            $table->text('id_rejection_reason')->nullable();
            $table->string('country')->nullable();
            $table->string('county')->nullable();
            $table->string('native_language')->nullable();
            $table->string('profile_picture')->nullable();
            $table->enum('education_level', ['high_school', 'bachelor', 'master', 'phd'])->nullable();
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->json('subjects')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('night_calls')->default(false);
            $table->boolean('force_assign')->default(false);
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->enum('payment_method', ['mpesa', 'bank', 'paypal'])->nullable();
            $table->string('payment_details')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedInteger('jobs_completed')->default(0);
            $table->decimal('earnings', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('writer_profiles');
    }
};
