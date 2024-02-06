<?php

use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\RuleTypeEnum;

return new class extends Migration
{
    /**
     * Create the "rules" table.
     *
     * This method is responsible for creating the "rules" table in the database. It defines the table structure
     * and sets up the necessary foreign key constraint. The table has the following columns:
     *   - id: primary key
     *   - title: a string field with maximum length of 255 characters. It cannot be null.
     *   - type: an enum field with possible values defined in RuleTypeEnum. It cannot be null.
     *   - user_id: an unsigned big integer field representing the foreign key to the "users" table. It cannot be null.
     *   - rules: a long text field.
     *   - created_at: a timestamp column for tracking the creation time.
     *   - updated_at: a timestamp column for tracking the last update time.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable(false);
            $table->enum('type', RuleTypeEnum::toArray())->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('transaction_type', TransactionTypeEnum::toArray())->nullable(false);
            $table->unsignedBigInteger('category_id')->nullable(true);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->longText('merge_fields');
            $table->longText('rules');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
