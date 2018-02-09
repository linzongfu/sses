<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('qustype_id')->comment('所属的类型');
            $table->string('title')->comment('问题的题目');
            $table->integer('type')->comment('问题的类型 0:单选题目 1:多选题 2:判断题 3:填空题 4:问答题');
            $table->text('info')->comment(" array 例如 选择题 四个选项 A:B:C:D");
            $table->text('answer')->comment('array 答案数组')->nullable();
            $table->timestamps();
            $table->foreign('qustype_id')->references('id')->on('qustype');
            $table->index(['id','qustype_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
