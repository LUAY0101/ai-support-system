<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name'); // اسم العميل
            $table->text('message'); // نص المشكلة أو الرسالة
            
            // هذه الأعمدة سيقوم الذكاء الاصطناعي بتعبئتها لاحقاً
            $table->string('department')->nullable(); // القسم 
            $table->string('priority')->nullable(); // الأولوية 
            $table->string('order_number')->nullable(); // رقم الطلب
            
            $table->string('status')->default('Yeni'); // حالة التذكرة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};