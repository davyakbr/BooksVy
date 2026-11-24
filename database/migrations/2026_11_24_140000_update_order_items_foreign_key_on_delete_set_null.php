<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah foreign key order_items.book_id agar menjadi NULLABLE dengan ON DELETE SET NULL
        // Hal ini memungkinkan buku yang sudah selesai transaksinya dihapus tanpa merusak riwayat faktur pesanan.
        DB::statement('ALTER TABLE `order_items` MODIFY `book_id` INT NULL');

        try {
            DB::statement('ALTER TABLE `order_items` DROP FOREIGN KEY `fk_order_items_book`');
        } catch (\Exception $e) {
            // Abaikan jika FK belum ada
        }

        DB::statement('ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE `order_items` DROP FOREIGN KEY `fk_order_items_book`');
        } catch (\Exception $e) {
            // Abaikan jika FK belum ada
        }

        DB::statement('ALTER TABLE `order_items` MODIFY `book_id` INT NOT NULL');
        DB::statement('ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE');
    }
};
