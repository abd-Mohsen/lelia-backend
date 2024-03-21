<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('size');
            $table->string('neighborhood');
            $table->string('street');
            $table->string('landline_number');
            $table->string('mobile_number');
            $table->decimal('longitude', 9, 6);
            $table->decimal('latitude', 9, 6);
            $table->timestamp('issue_date'); // must be iso806
            $table->string('status')->nullable();
            $table->string('notes')->nullable();
            $table->foreignIdFor(User::class);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
