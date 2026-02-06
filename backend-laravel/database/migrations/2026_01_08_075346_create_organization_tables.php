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
        // Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            // Self-referencing for sub-departemen
            $table->uuid('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();
        });

        // Positions
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->timestamps();
            $table->softDeletes();
        });

        // Levels
        Schema::create('levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('level');
            $table->timestamps();
            $table->softDeletes();
        });

        // Employees
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('employee_code')->unique()->nullable(); // NIK
            $table->string('name');
            $table->string('email')->nullable();
            
            // Relation to Department & Position
            $table->foreignUuid('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // Hierarchy Manager (Self Reference)
            // $table->foreignUuid('manager_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->date('join_date');
            $table->date('end_contract_date')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabel Pivot Karyawan <-> Departemen (Many-to-Many)
        Schema::create('employee_departments', function (Blueprint $table) {
            // $table->uuid('id')->primary();
            
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();
            
            // Menandakan mana departemen "Homebase" utamanya
            $table->boolean('is_primary')->default(false); 
            
            // Opsional: Jika di departemen A dia sebagai Staff, di departemen B sebagai Lead
            // $table->string('role_in_dept')->nullable(); 

            $table->timestamps();
            $table->softDeletes();
            
            // Mencegah duplikasi data karyawan sama di dept sama
            $table->unique(['employee_id', 'department_id']); 
        });

        // Tabel Pivot Reporting Line / Atasan (Many-to-Many)
        Schema::create('employee_reporting_lines', function (Blueprint $table) {
            // $table->uuid('id')->primary();
            
            // Bawahan
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            
            // Atasan (Manager 1, Manager 2, dll)
            $table->foreignUuid('manager_id')->constrained('employees')->cascadeOnDelete();
            
            // Tipe Pelaporan: 'direct' (Atasan Langsung), 'project' (Manager Proyek), 'functional'
            $table->string('reporting_type')->default('direct'); 
            
            $table->timestamps();
            $table->softDeletes();

            // Mencegah duplikasi
            $table->unique(['employee_id', 'manager_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_reporting_lines');
        Schema::dropIfExists('employee_departments');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
