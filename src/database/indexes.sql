USE typhon_cath_crm;

CREATE INDEX idx_accounts_name ON accounts(account_name);
CREATE INDEX idx_contacts_account_id ON contacts(account_id);
CREATE INDEX idx_interactions_account_id ON interactions(account_id);
CREATE INDEX idx_rfqs_account_id ON rfqs(account_id);
CREATE INDEX idx_rfqs_stage ON rfqs(stage);
CREATE INDEX idx_quotes_rfq_id ON quotes(rfq_id);
CREATE INDEX idx_inventory_product_id ON inventory(product_id);
CREATE INDEX idx_reservations_rfq_id ON rfq_inventory_reservations(rfq_id);
CREATE INDEX idx_campaigns_status ON campaigns(status);
CREATE INDEX idx_campaign_audience_campaign_id ON campaign_audience(campaign_id);

-- Campaign FK and sort columns
CREATE INDEX idx_campaigns_created_by_user_id    ON campaigns(created_by_user_id);
CREATE INDEX idx_campaigns_created_at            ON campaigns(created_at);
CREATE INDEX idx_campaign_audience_account_id    ON campaign_audience(account_id);
CREATE INDEX idx_campaign_audience_contact_id    ON campaign_audience(contact_id);

-- RFQ sort columns (allForBoard and search both order by these)
CREATE INDEX idx_rfqs_created_at ON rfqs(created_at);
CREATE INDEX idx_rfqs_updated_at ON rfqs(updated_at);

-- quotesExpiringSoon orders by validity_end_date
CREATE INDEX idx_quotes_validity_end_date ON quotes(validity_end_date);

-- getReservationsByRfqId joins on product_id
CREATE INDEX idx_reservations_product_id ON rfq_inventory_reservations(product_id);

-- Campaign dashboard queries
-- upcomingScheduledSends: WHERE status='Scheduled' AND scheduled_at >= NOW() ORDER BY scheduled_at ASC
CREATE INDEX idx_campaigns_status_scheduled_at ON campaigns(status, scheduled_at);
-- topPerformers: WHERE status IN (...) AND open_rate IS NOT NULL ORDER BY open_rate DESC, sent_count DESC
CREATE INDEX idx_campaigns_status_open_rate ON campaigns(status, open_rate, sent_count);
-- campaignsWithMetrics: ORDER BY open_rate DESC
CREATE INDEX idx_campaigns_open_rate ON campaigns(open_rate);

-- campaignMomentum: WHERE created_at BETWEEN — covering index with status for the SUM()
CREATE INDEX idx_campaigns_created_at_status ON campaigns(created_at, status);

-- campaignMomentum segment subqueries: IN (SELECT DISTINCT campaign_id FROM campaign_audience WHERE account_id/contact_id IS NOT NULL)
CREATE INDEX idx_campaign_audience_campaign_account ON campaign_audience(campaign_id, account_id);
CREATE INDEX idx_campaign_audience_campaign_contact ON campaign_audience(campaign_id, contact_id);

-- Inventory product list sortable columns (InventoryRepository::all()/count())
CREATE INDEX idx_products_product_name ON products(product_name);
CREATE INDEX idx_inventory_available_quantity ON inventory(available_quantity);

-- Inventory Ledger (see migrations/015_create_inventory_movements.sql). Repeated
-- here, matching the indexes.sql/migration duplication convention established by
-- 014_dashboard_indexes.sql, in case indexes.sql is applied ahead of migrations.
CREATE INDEX idx_inventory_movements_product_id    ON inventory_movements(product_id);
CREATE INDEX idx_inventory_movements_created_at    ON inventory_movements(created_at);
CREATE INDEX idx_inventory_movements_user_id       ON inventory_movements(user_id);
CREATE INDEX idx_inventory_movements_movement_type ON inventory_movements(movement_type);

-- Reporting/dashboard heavy paths (see migrations/018_reporting_indexes.sql).
-- Recent Interactions ordering (interactions.interaction_date) and the Win Rate
-- by Account aggregation (rfqs by account_id+stage).
CREATE INDEX idx_interactions_interaction_date ON interactions(interaction_date);
CREATE INDEX idx_rfqs_account_stage            ON rfqs(account_id, stage);
