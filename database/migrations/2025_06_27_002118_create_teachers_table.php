<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {

    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('staff_number')->nullable();
            $table->string('surname')->nullable();
            $table->string('firstname')->nullable();
            $table->string('email')->nullable();
            $table->string('othername')->nullable();
            $table->string('initials')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('registration_number')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('national_id')->nullable();
            $table->string('address')->nullable();
            $table->string('employee_number')->nullable();
            $table->tinyInteger('group_teacher')->nullable(); 
            $table->string('teacher_profile')->nullable();

            $table->string('password')
                ->default(Hash::make('123456789'));

            $table->boolean('must_change_password')
                ->default(true);

            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();

            $table->foreign('school_id')
                  ->references('id')
                  ->on('schools')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('teachers');
    }
};
