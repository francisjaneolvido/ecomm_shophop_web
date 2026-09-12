<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('email_verification_codes', 'user_id')) {
            Schema::table('email_verification_codes', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->unique()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('email_verification_codes', 'code_hash')) {
            Schema::table('email_verification_codes', function (Blueprint $table) {
                $table->string('code_hash')
                    ->after('user_id');
            });
        }

        if (! Schema::hasColumn('email_verification_codes', 'attempts')) {
            Schema::table('email_verification_codes', function (Blueprint $table) {
                $table->unsignedTinyInteger('attempts')
                    ->default(0)
                    ->after('code_hash');
            });
        }

        if (! Schema::hasColumn('email_verification_codes', 'expires_at')) {
            Schema::table('email_verification_codes', function (Blueprint $table) {
                $table->timestamp('expires_at')
                    ->after('attempts');
            });
        }

        if (! Schema::hasColumn('email_verification_codes', 'last_sent_at')) {
            Schema::table('email_verification_codes', function (Blueprint $table) {
                $table->timestamp('last_sent_at')
                    ->nullable()
                    ->after('expires_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('email_verification_codes', function (Blueprint $table) {
            if (Schema::hasColumn('email_verification_codes', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropUnique(['user_id']);
            }
        });

        Schema::table('email_verification_codes', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'user_id',
                'code_hash',
                'attempts',
                'expires_at',
                'last_sent_at',
            ] as $column) {
                if (Schema::hasColumn('email_verification_codes', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};