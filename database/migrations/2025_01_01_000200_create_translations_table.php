<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            $table->unique(['locale_id', 'key']);
            $table->index('key');
            $table->index('locale_id');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};

