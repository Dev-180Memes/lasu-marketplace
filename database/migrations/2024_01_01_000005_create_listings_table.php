<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('campus_zone_id')->nullable()->constrained('campus_zones')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->boolean('is_negotiable')->default(false);
            $table->boolean('is_preorder')->default(false);
            $table->enum('item_condition', ['new', 'fairly_used', 'used'])->default('new');
            $table->unsignedInteger('stock_quantity')->default(1);
            $table->enum('availability', ['available', 'out_of_stock', 'sold'])->default('available');
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
