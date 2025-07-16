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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->date('registration_date')->nullable(true);
            $table->string('project_number')->unique()->nullable(false);
            $table->string('egrz_number')->unique()->nullable(false);
            $table->text('project_name')->nullable(true);
            $table->string('object_type')->nullable(true);
            $table->string('functional_purpose',500)->nullable(true);
            $table->text('service_type')->nullable(true);
            $table->date('start_date')->nullable(true);
            $table->string('name', 500)->nullable(true);
            $table->string('inn')->nullable(true);
            $table->date('contract_date')->nullable(true);
            $table->date('conclusion_date')->nullable(true);
            $table->string('conclusion_result')->nullable(true);
            $table->string('project_status')->nullable(true);
            $table->decimal('cost_declared', 10, 5)->nullable(true);
            $table->decimal('cost_adjusted', 10, 5)->nullable(true);
            $table->string('stage_construction_works')->nullable(true);
            // $table->string('rating');
            // $table->decimal('rating', 2, 1);
            // $table->integer('positive_conclusion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
