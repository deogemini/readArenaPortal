<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_gateway_settings') && Schema::hasColumn('sms_gateway_settings', 'client_secret')) {
            Schema::table('sms_gateway_settings', function (Blueprint $table): void {
                $table->text('client_secret')->nullable()->change();
            });
        }

        Schema::create('notification_channel_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('email_enabled')->default(false);
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_encryption', 12)->default('tls');
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->boolean('push_enabled')->default(false);
            $table->string('firebase_project_id')->nullable();
            $table->longText('firebase_service_account_json')->nullable();
            $table->timestamps();
        });

        Schema::create('push_device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 512)->unique();
            $table->string('platform', 20)->default('android');
            $table->string('device_name')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'platform']);
        });

        if (Schema::hasTable('sms_gateway_settings') && Schema::hasColumn('sms_gateway_settings', 'client_secret')) {
            DB::table('sms_gateway_settings')->where('client_secret', '')->update(['client_secret' => null]);
            DB::table('sms_gateway_settings')
                ->whereNotNull('client_secret')
                ->orderBy('id')
                ->chunkById(100, function ($settings): void {
                    foreach ($settings as $setting) {
                        $secret = (string) $setting->client_secret;
                        if ($secret === '') {
                            continue;
                        }

                        try {
                            Crypt::decryptString($secret);
                            continue;
                        } catch (\Throwable) {
                            // Existing settings were stored as plain text before encrypted casts were added.
                        }

                        DB::table('sms_gateway_settings')->where('id', $setting->id)->update([
                            'client_secret' => Crypt::encryptString($secret),
                        ]);
                    }
                });

        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sms_gateway_settings') && Schema::hasColumn('sms_gateway_settings', 'client_secret')) {
            DB::table('sms_gateway_settings')
                ->whereNotNull('client_secret')
                ->orderBy('id')
                ->chunkById(100, function ($settings): void {
                    foreach ($settings as $setting) {
                        try {
                            $secret = Crypt::decryptString((string) $setting->client_secret);
                        } catch (\Throwable) {
                            continue;
                        }

                        DB::table('sms_gateway_settings')->where('id', $setting->id)->update([
                            'client_secret' => $secret,
                        ]);
                    }
                });

            Schema::table('sms_gateway_settings', function (Blueprint $table): void {
                $table->string('client_secret')->nullable()->change();
            });
        }

        Schema::dropIfExists('push_device_tokens');
        Schema::dropIfExists('notification_channel_settings');
    }
};
