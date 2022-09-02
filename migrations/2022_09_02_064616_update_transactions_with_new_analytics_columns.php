<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransactionsWithNewAnalyticsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            'mesomb_transactions',
            function (Blueprint $table) {
                $table->json('customer')->nullable();
                $table->json('product')->nullable();
                $table->json('location')->nullable();
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
                $table->dropColumn('customer');
                $table->dropColumn('product');
                $table->dropColumn('location');
            }
        );
    }
};
