<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('action');                             // login, create_ticket, reply_ticket... (nhãn: config/helpdesk.php)
            $table->text('message')->nullable();                  // null nếu hoạt động không có thông báo
            $table->foreignId('notify_user_id')->nullable()->constrained('users')->nullOnDelete(); // người nhận thông báo
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
