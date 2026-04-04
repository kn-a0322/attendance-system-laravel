<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')->constrained('correction_requests')->cascadeOnDelete();
            $table->time('clock_in');
            $table->time('clock_out');
            $table->text('remark');
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
        Schema::dropIfExists('correction_request_details');
    }
}
