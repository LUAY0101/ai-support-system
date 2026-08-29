<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->after('order_number');
            $table->date('document_date')->nullable()->after('amount');
            $table->string('invoice_number')->nullable()->after('document_date');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['amount', 'document_date', 'invoice_number']);
        });
    }
};