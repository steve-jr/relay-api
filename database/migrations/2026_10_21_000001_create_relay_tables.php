<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Authentication support
            $table->rememberToken();

            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('creator')->index();
            $table->char('country',2)->default('GH');
            $table->char('currency',3)->default('GHS');
        });

        Schema::create('creator_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('display_name');
            $table->text('bio')->nullable();
            $table->string('city')->nullable();
            $table->char('country',2)->index();
            $table->char('currency',3);
            $table->string('primary_niche')->nullable()->index();
            $table->json('secondary_niches')->nullable();
            $table->json('industries')->nullable();
            $table->json('socials')->nullable();
            $table->decimal('average_engagement_rate',7,2)->default(0);
            $table->decimal('starting_rate',14,2)->default(0);
            $table->string('profile_image_url',2048)->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('objective')->nullable();
            $table->decimal('budget',14,2);
            $table->char('currency',3);
            $table->char('country',2)->index();
            $table->string('status')->default('draft')->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('deliverables')->nullable();
            $table->timestamps();
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('open')->index();
            $table->string('turn')->nullable();
            $table->unsignedTinyInteger('max_rounds')->default(5);
            $table->decimal('proposed_rate',14,2);
            $table->char('currency',3);
            $table->json('deliverables');
            $table->date('deadline')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id','creator_id']);
        });

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('round');
            $table->string('by');
            $table->decimal('fee',14,2);
            $table->char('currency',3);
            $table->json('deliverables');
            $table->date('deadline')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['invitation_id','round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('creator_profiles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','country','currency']);
        });
        Schema::dropIfExists('users');
    }
};
