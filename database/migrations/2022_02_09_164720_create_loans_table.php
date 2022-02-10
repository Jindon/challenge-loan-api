<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            $table->string('account_no')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->string('currency_code')->default('USD');

            $table->bigInteger('amount')->comment('in lowest denomination');
            $table->integer('term')->comment('in weeks');
            $table->bigInteger('paid_amount')->default(0)->comment('in lowest denomination');
            $table->bigInteger('pending_amount')->default(0)->comment('in lowest denomination');

            $table->date('issued_on')->nullable();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();

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
        Schema::dropIfExists('loans');
    }
};
