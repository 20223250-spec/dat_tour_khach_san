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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Khách hàng đặt
            $table->foreignId('tour_id')->constrained()->onDelete('cascade'); // Tour được đặt
            $table->integer('number_of_people'); // Số lượng hành khách
            $table->decimal('total_price', 12, 2); // Tổng tiền
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending'); // Trạng thái
            $table->string('customer_name'); // Tên người đi
            $table->string('customer_phone'); // SĐT liên hệ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};