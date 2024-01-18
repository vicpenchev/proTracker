<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TransactionCreateTypeEnum;
use App\Enums\TransactionTypeEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable(false);
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->nullable(true);
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->float('value')->nullable(false);
            $table->timestamp('date')->nullable(false)->default(now());
            $table->string('from_acc', 255)->nullable();
            $table->string('to_acc', 255)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('import_id')->nullable(true);
            $table->foreign('import_id')->references('id')->on('imports')->nullOnDelete();
            $table->enum('create_type', TransactionCreateTypeEnum::toArray())->nullable(false)->default(TransactionCreateTypeEnum::MANUAL);
            $table->enum('type', TransactionTypeEnum::toArray())->nullable(false)->default(TransactionTypeEnum::EXPENSE);
            $table->boolean('published')->nullable(false)->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
