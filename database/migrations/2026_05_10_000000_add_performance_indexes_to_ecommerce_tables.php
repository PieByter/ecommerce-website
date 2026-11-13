<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes for the filters and lookups used by the storefront and admin panel.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'category_id'], 'products_active_category_index');
            $table->index('stock', 'products_stock_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
            $table->index(['user_id', 'created_at'], 'orders_user_created_at_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'product_id'], 'order_items_order_product_index');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index(['product_id', 'rating'], 'product_reviews_product_rating_index');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'purchase_orders_status_created_at_index');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->index(['purchase_order_id', 'product_id'], 'purchase_order_items_po_product_index');
        });

        Schema::table('product_supplier', function (Blueprint $table) {
            $table->index(['supplier_id', 'product_id'], 'product_supplier_supplier_product_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_supplier', function (Blueprint $table) {
            $table->dropIndex('product_supplier_supplier_product_index');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropIndex('purchase_order_items_po_product_index');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('purchase_orders_status_created_at_index');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('product_reviews_product_rating_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_product_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_at_index');
            $table->dropIndex('orders_user_created_at_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_category_index');
            $table->dropIndex('products_stock_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
        });
    }
};
