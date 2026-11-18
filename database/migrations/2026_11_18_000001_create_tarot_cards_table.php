<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarot_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('card_number')->unique(); // 0 to 77
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('arcana'); // Major, Minor
            $table->string('suit'); // Trump, Wands, Cups, Swords, Pentacles
            $table->string('element')->nullable(); // Fire, Water, Air, Earth, Spirit
            $table->string('keywords_upright');
            $table->string('keywords_reversed');
            $table->text('upright_general');
            $table->text('reversed_general');
            $table->text('upright_love');
            $table->text('reversed_love');
            $table->text('upright_career');
            $table->text('reversed_career');
            $table->text('upright_finance');
            $table->text('reversed_finance');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarot_cards');
    }
};
