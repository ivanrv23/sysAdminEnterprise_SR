<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountReceivableDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_receivable_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companies_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('account_receivables_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->decimal('amount', 11, 2);
            $table->date('date');
            $table->string('description', 250)->nullable();
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
        Schema::dropIfExists('account_receivable_details');
    }
}
