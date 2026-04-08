<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_customers', function (Blueprint $table): void {
            $table->id();
            $table->morphs('billable');
            $table->string('customer_code')->nullable()->unique();
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['billable_type', 'billable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_customers');
    }
};
