<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string("symbol");
            $table->string("priceChange");
            $table->string("priceChangePercent");
            $table->string("weightedAvgPrice");
            $table->string("prevClosePrice");
            $table->string("lastPrice");
            $table->string("prevLastPrice");
            $table->string("lastQty");
            $table->string("lastPriceVariance");
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
        Schema::dropIfExists('currencies');
    }
}
