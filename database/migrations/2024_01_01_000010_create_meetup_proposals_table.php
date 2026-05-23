<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetup_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proposed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('campus_zone_id')->constrained('campus_zones')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('meetup_proposals')->nullOnDelete();
            $table->timestamp('proposed_at');
            $table->enum('status', ['pending', 'accepted', 'declined', 'counter_proposed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetup_proposals');
    }
};
