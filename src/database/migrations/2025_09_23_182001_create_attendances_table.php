<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('status')->default(0);
            $table->integer('approval')->default(0);
            $table->time('start')->nullable();
            $table->time('finish')->nullable();
            $table->time('full')->nullable();
            $table->date('date');
            $table->string('description', 255)->nullable();
            $table->timestamps();

            // 複合ユニーク制約をここで追加
            $table->unique(['user_id', 'date'], 'user_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
