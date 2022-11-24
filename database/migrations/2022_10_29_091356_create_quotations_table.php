<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companies_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('coins_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('customers_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('voucher_number');
            $table->decimal('exchange_rate', 5, 3)->nullable();
            $table->string('igv');
            $table->decimal('transporte', 12, 2)->nullable();
            $table->decimal('descuento', 12, 2)->nullable();
            $table->decimal('total', 12, 2);
            $table->date('date');
            $table->string('description', 250)->nullable();
            $table->boolean('state')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotations');
    }
}
