<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Rule;
use App\Models\RuleField;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rule_field_rule', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RuleField::class);
            $table->foreignIdFor(Rule::class);
            /*$table->unsignedBigInteger('rule_id')->nullable(false);
            $table->foreign('rule_id')->references('id')->on('rules')->onDelete('cascade');
            $table->unsignedBigInteger('rule_field_id')->nullable(false);
            $table->foreign('rule_field_id')->references('id')->on('rule_fields')->onDelete('cascade');*/
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rule_field_rule');
    }
};
