<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('reply_to_id')->nullable()->after('community_id')
                ->constrained('messages')->nullOnDelete();
            $table->timestamp('edited_at')->nullable()->after('file_size');
            $table->softDeletes();
            $table->boolean('deleted_for_everyone')->default(false)->after('deleted_at');
        });

        Schema::create('chat_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_type'); // user | group
            $table->unsignedBigInteger('target_id');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_favorited')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'target_type', 'target_id']);
            $table->index(['user_id', 'is_archived']);
        });

        Schema::create('starred_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'message_id']);
        });

        Schema::create('message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 16);
            $table->timestamps();

            $table->unique(['message_id', 'user_id', 'emoji']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reactions');
        Schema::dropIfExists('starred_messages');
        Schema::dropIfExists('chat_preferences');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['reply_to_id']);
            $table->dropColumn(['reply_to_id', 'edited_at', 'deleted_for_everyone']);
            $table->dropSoftDeletes();
        });
    }
};
