<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePersonalityTraitsFromScoresTestsTable extends Migration
{
    public function up(): void
    {
        Schema::table('scores_tests', function (Blueprint $table) {
            $table->dropColumn([
                'empathy',
                'communication',
                'self_control',
                'sociability',
                'conscientiousness',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('scores_tests', function (Blueprint $table) {
            $table->float('empathy')->nullable();
            $table->float('communication')->nullable();
            $table->float('self_control')->nullable();
            $table->float('sociability')->nullable();
            $table->float('conscientiousness')->nullable();
        });
    }
}
