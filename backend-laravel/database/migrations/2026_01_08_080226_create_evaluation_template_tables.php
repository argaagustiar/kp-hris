<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Periods MonthYear-MonthYear (Ex: March 2026 - August 2026)
        Schema::create('periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Attendance Record Periods
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->constrained('periods')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('sick', 8, 2)->default(0);
            $table->decimal('work_accident', 8, 2)->default(0);
            $table->decimal('permit', 8, 2)->default(0);
            $table->decimal('awol', 8, 2)->default(0);
            $table->decimal('late_permit', 8, 2)->default(0);
            $table->decimal('early_leave', 8, 2)->default(0);
            $table->decimal('annual_leave', 8, 2)->default(0);
            $table->decimal('late', 8, 2)->default(0);
            $table->decimal('warning_letter_1', 8, 2)->default(0);
            $table->decimal('warning_letter_2', 8, 2)->default(0);
            $table->decimal('warning_letter_3', 8, 2)->default(0);
            $table->decimal('subordinate_late', 8, 2)->default(0);
            $table->decimal('subordinate_awol', 8, 2)->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Template Header (Ex: Form 2026)
        Schema::create('evaluation_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->constrained('periods')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Sections (A. Attitude, B. Attendance)
        Schema::create('template_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained('evaluation_templates')->cascadeOnDelete();
            $table->string('name');
            $table->text('description_en')->nullable();
            $table->text('description_jp')->nullable();
            $table->integer('sequence_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Questions (Questions & Poin)
        Schema::create('template_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')->constrained('template_sections')->cascadeOnDelete();
            
            $table->text('label_en');
            $table->text('description_en')->nullable();
            $table->text('description_jp')->nullable();
            
            // Identifier for mapping Vue (ex: 'att_sick', 'question_1')
            $table->string('key_identifier'); 
            
            // Input type: radio_1_5, number_qty, text
            $table->string('input_type'); 
            
            // Value (Misal: Sick = -0.10)
            $table->decimal('weight_point', 8, 2)->default(0);
            
            $table->integer('sequence_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS template_questions CASCADE');
        DB::statement('DROP TABLE IF EXISTS template_sections CASCADE');
        DB::statement('DROP TABLE IF EXISTS evaluation_templates CASCADE');
        DB::statement('DROP TABLE IF EXISTS attendance_records CASCADE');
        DB::statement('DROP TABLE IF EXISTS periods CASCADE');
    }
};
