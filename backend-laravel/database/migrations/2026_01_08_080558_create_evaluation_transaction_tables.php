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
        // Evaluation Header (One form for each employees)
        Schema::create('evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->constrained('periods')->cascadeOnDelete();
            // $table->foreignUuid('template_id')->constrained('evaluation_templates');
            $table->foreignUuid('employee_id')->constrained('employees'); // Yang dinilai
            $table->foreignUuid('evaluator_id')->constrained('employees'); // Penilai
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();  
            $table->date('end_contract_date')->nullable();          
            $table->string('evaluation_purpose')->nullable();
            
            $table->integer('question_1')->default(0);
            $table->integer('question_2')->default(0);
            $table->integer('question_3')->default(0);
            $table->integer('question_4')->default(0);
            $table->integer('question_5')->default(0);
            $table->integer('question_6')->default(0);
            $table->integer('question_7')->default(0);
            $table->integer('question_8')->default(0);
            $table->integer('question_9')->default(0);
            $table->integer('question_10')->default(0);
            
            // $table->timestamp('evaluation_date')->useCurrent();
            $table->text('comments')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
