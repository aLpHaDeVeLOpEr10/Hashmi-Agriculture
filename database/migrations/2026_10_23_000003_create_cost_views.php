<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW v_expense_status AS
            SELECT
                e.id AS expense_id,
                e.farm_id,
                e.season_id,
                e.season_stage_id,
                e.category_id,
                e.vendor_id,
                e.expense_date,
                e.is_notional,
                e.amount,
                COALESCE(pa.paid, 0) AS paid,
                CASE WHEN e.is_notional = TRUE THEN 0 ELSE e.amount - COALESCE(pa.paid, 0) END AS balance,
                CASE
                    WHEN e.is_notional = TRUE THEN 'notional'
                    WHEN COALESCE(pa.paid, 0) <= 0 THEN 'unpaid'
                    WHEN COALESCE(pa.paid, 0) < e.amount THEN 'partial'
                    ELSE 'paid'
                END AS payment_status
            FROM expenses e
            LEFT JOIN (
                SELECT expense_id, SUM(amount) AS paid FROM payment_allocations GROUP BY expense_id
            ) pa ON pa.expense_id = e.id
            WHERE e.deleted_at IS NULL
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW v_stage_totals AS
            SELECT
                ss.id AS season_stage_id,
                ss.season_id,
                COALESCE(SUM(CASE WHEN v.is_notional = FALSE THEN v.amount ELSE 0 END), 0) AS total,
                COALESCE(SUM(CASE WHEN v.is_notional = TRUE THEN v.amount ELSE 0 END), 0) AS notional_total,
                COALESCE(SUM(v.paid), 0) AS paid,
                COALESCE(SUM(v.balance), 0) AS balance,
                COUNT(v.expense_id) AS line_count
            FROM season_stages ss
            LEFT JOIN v_expense_status v ON v.season_stage_id = ss.id
            GROUP BY ss.id, ss.season_id
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW v_category_breakdown AS
            SELECT
                v.season_id,
                v.category_id,
                c.parent_id AS parent_category_id,
                c.cost_type,
                SUM(CASE WHEN v.is_notional = FALSE THEN v.amount ELSE 0 END) AS total,
                SUM(CASE WHEN v.is_notional = TRUE THEN v.amount ELSE 0 END) AS notional_total,
                COUNT(*) AS line_count
            FROM v_expense_status v
            JOIN expense_categories c ON c.id = v.category_id
            GROUP BY v.season_id, v.category_id, c.parent_id, c.cost_type
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW v_season_summary AS
            SELECT
                s.id AS season_id,
                s.farm_id,
                s.total_area_acres,
                COALESCE(t.total, 0) AS total_cost,
                COALESCE(t.total, 0) + COALESCE(t.notional_total, 0) AS economic_cost,
                COALESCE(t.paid, 0) AS paid,
                COALESCE(t.balance, 0) AS outstanding,
                CASE WHEN s.total_area_acres > 0 THEN COALESCE(t.total, 0) * 1.0 / s.total_area_acres END AS cost_per_acre
            FROM crop_seasons s
            LEFT JOIN (
                SELECT season_id, SUM(total) AS total, SUM(notional_total) AS notional_total, SUM(paid) AS paid, SUM(balance) AS balance
                FROM v_stage_totals GROUP BY season_id
            ) t ON t.season_id = s.id
        SQL);
    }

    public function down(): void
    {
        foreach (['v_season_summary', 'v_category_breakdown', 'v_stage_totals', 'v_expense_status'] as $view) {
            DB::statement("DROP VIEW IF EXISTS {$view}");
        }
    }
};
