<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('description');
            $table->integer('status')->default(0);
            $table->string('meta_title');
            $table->string('meta_description');
            $table->float('weight');
            $table->float('length');
            $table->float('breadth');
            $table->float('height');
            $table->string('model_number');
            $table->float('price')->nullable();
            $table->integer('discount')->nullable();
            $table->enum('discount_type', ['amount', 'percentage'])->default('amount');
            $table->string('video_link')->nullable();
            $table->enum('type', ['single', 'variant'])->default('single');
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('brand_id')
                ->constrained('brands')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
        Schema::dropIfExists('products');
    }
};
