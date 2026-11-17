<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function indexExists(string $table, string $index): bool
    {
        return collect(DB::select("SHOW INDEX FROM `$table`"))
            ->contains(fn ($item) => $item->Key_name === $index);
    }

    public function up(): void
    {
        if (Schema::hasTable('chat_messages')) {
            if (!$this->indexExists('chat_messages', 'chat_messages_chat_id_created_at_index')) {
                Schema::table('chat_messages', function (Blueprint $table) {
                    $table->index(
                        ['chat_id', 'created_at'],
                        'chat_messages_chat_id_created_at_index'
                    );
                });
            }
        }

        if (Schema::hasTable('call_histories')) {
            if (!$this->indexExists('call_histories', 'call_histories_user_astrologer_status_index')) {
                Schema::table('call_histories', function (Blueprint $table) {
                    $table->index(
                        ['user_id', 'astrologer_id', 'status'],
                        'call_histories_user_astrologer_status_index'
                    );
                });
            }
        }

        if (Schema::hasTable('transactions')) {
            if (!$this->indexExists('transactions', 'transactions_astrologer_id_transacted_at_index')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->index(
                        ['astrologer_id', 'transacted_at'],
                        'transactions_astrologer_id_transacted_at_index'
                    );
                });
            }
        }

        if (Schema::hasTable('user_wallet_transactions')) {
            if (!$this->indexExists('user_wallet_transactions', 'uwt_wallet_id_type_index')) {
                Schema::table('user_wallet_transactions', function (Blueprint $table) {
                    $table->index(
                        ['user_wallet_id', 'type'],
                        'uwt_wallet_id_type_index'
                    );
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('chat_messages')) {
            if ($this->indexExists('chat_messages', 'chat_messages_chat_id_created_at_index')) {
                Schema::table('chat_messages', function (Blueprint $table) {
                    $table->dropIndex('chat_messages_chat_id_created_at_index');
                });
            }
        }

        if (Schema::hasTable('call_histories')) {
            if ($this->indexExists('call_histories', 'call_histories_user_astrologer_status_index')) {
                Schema::table('call_histories', function (Blueprint $table) {
                    $table->dropIndex('call_histories_user_astrologer_status_index');
                });
            }
        }

        if (Schema::hasTable('transactions')) {
            if ($this->indexExists('transactions', 'transactions_astrologer_id_transacted_at_index')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->dropIndex('transactions_astrologer_id_transacted_at_index');
                });
            }
        }

        if (Schema::hasTable('user_wallet_transactions')) {
            if ($this->indexExists('user_wallet_transactions', 'uwt_wallet_id_type_index')) {
                Schema::table('user_wallet_transactions', function (Blueprint $table) {
                    $table->dropIndex('uwt_wallet_id_type_index');
                });
            }
        }
    }
};