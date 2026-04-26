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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên tour
            $table->text('description')->nullable(); // Mô tả
            $table->string('destination'); // Điểm đến
            $table->decimal('price', 12, 2); // Giá mỗi người
            $table->integer('duration_days'); // Thời gian (ngày)
            $table->integer('available_seats'); // Số chỗ còn trống
            $table->date('start_date'); // Ngày khởi hành
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};