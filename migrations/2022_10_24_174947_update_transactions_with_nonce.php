<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransactionsWithNonce extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'mesomb_transactions',
            function (Blueprint $table) {
                $table->string('nonce', 50)->nullable();
                $table->json('products')->nullable();
                $table->dropColumn('product');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'mesomb_transactions',
            function (Blueprint $table) {
                $table->dropColumn('nonce');
                $table->dropColumn('products');
                $table->json('product')->nullable();
            }
        );
    }
};
