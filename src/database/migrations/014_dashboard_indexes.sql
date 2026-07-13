-- 014_dashboard_indexes.sql
-- Indexes the dashboard's RFQ + Campaign aggregate queries rely on. These mirror
-- the relevant subset of database/indexes.sql, promoted into a tracked migration
-- so a fresh install (schema.sql only) gets them without a separate manual step.
-- Index names match indexes.sql exactly; if that file was already applied, the
-- duplicate-key errors are treated as benign by the migration runner.
--
-- Query → index:
--   RFQRepository::stageSummary / totalValueByStage  (WHERE/GROUP BY stage) -> idx_rfqs_stage
--   RFQRepository::recentRfqs                        (ORDER BY updated_at)   -> idx_rfqs_updated_at
--   RFQ list default sort / created ordering                                -> idx_rfqs_created_at
--   RFQRepository::quotesExpiringSoon                (ORDER BY end date)     -> idx_quotes_validity_end_date
--   CampaignRepository::dashboardStats / activeCount (WHERE status)          -> idx_campaigns_status
--   CampaignRepository::upcomingScheduledSends       (status + scheduled_at) -> idx_campaigns_status_scheduled_at
--
-- (rfqs.account_id and quotes.rfq_id are already indexed by their foreign keys,
--  so winRateByAccount's GROUP BY join and the quote-value joins are covered.)

CREATE INDEX idx_rfqs_stage      ON rfqs(stage);
CREATE INDEX idx_rfqs_created_at ON rfqs(created_at);
CREATE INDEX idx_rfqs_updated_at ON rfqs(updated_at);

CREATE INDEX idx_quotes_validity_end_date ON quotes(validity_end_date);

CREATE INDEX idx_campaigns_status              ON campaigns(status);
CREATE INDEX idx_campaigns_status_scheduled_at ON campaigns(status, scheduled_at);
