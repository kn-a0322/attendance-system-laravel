<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestRestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_request_rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')->constrained('correction_requests')->cascadeOnDelete();
            $table->time('rest_start');
            $table->time('rest_end');
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
        Schema::dropIfExists('correction_request_rests');
    }
}
