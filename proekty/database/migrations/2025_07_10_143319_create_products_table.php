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
            $table->date('registration_date');
            $table->string('project_number')->unique()->nullable(false);
            $table->string('egrz_number')->unique()->nullable(false);
            $table->text('project_name');
            $table->string('object_type');
            $table->string('functional_purpose',500);
            $table->text('service_type');
            $table->date('start_date');
            $table->string('name', 500);
            $table->string('inn');
            $table->date('contract_date');
            $table->date('conclusion_date');
            $table->string('conclusion_result');
            $table->string('project_status');
            $table->decimal('cost_declared', 6, 5);
            $table->decimal('cost_adjusted', 6, 5);
            $table->string('stage_construction_works');
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
