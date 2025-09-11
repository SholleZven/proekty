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
            $table->string('project_number')->nullable(false);
            $table->string('egrz_number')->nullable(false);
            $table->text('project_name')->nullable(true);
            $table->string('object_type')->nullable(true);
            $table->string('functional_purpose',1000)->nullable(true);
            $table->text('service_type')->nullable(true);
            $table->date('start_date')->nullable(true);
            $table->string('name', 1000)->nullable(true);
            $table->string('inn')->nullable(true);
            $table->date('contract_date')->nullable(true);
            $table->date('conclusion_date')->nullable(true);
            $table->string('conclusion_result')->nullable(true);
            $table->string('project_status')->nullable(true);
            $table->decimal('cost_declared', 20, 5)->nullable(true);
            $table->decimal('cost_adjusted', 20, 5)->nullable(true);
            $table->string('stage_construction_works')->nullable(true);
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
