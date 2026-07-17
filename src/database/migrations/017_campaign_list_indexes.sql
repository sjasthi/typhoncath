-- 017_campaign_list_indexes.sql
-- Indexes for the DataTables server-side Campaigns list endpoint. Mirrors the
-- other list views: FULLTEXT on the name column for global search, plus indexes
-- for the new per-column filters / sortable columns.
--
-- Already covered in indexes.sql and intentionally NOT repeated:
--   campaigns.status      -> idx_campaigns_status
--   campaigns.created_at  -> idx_campaigns_created_at
--   campaigns.open_rate   -> idx_campaigns_open_rate

-- Global search on campaign name (MATCH ... AGAINST), with a LIKE fallback for
-- tokens shorter than the InnoDB minimum.
ALTER TABLE campaigns ADD FULLTEXT INDEX ft_campaigns_name (campaign_name);

-- Per-column type select filter + sortable numeric columns
CREATE INDEX idx_campaigns_type       ON campaigns(campaign_type);
CREATE INDEX idx_campaigns_sent_count ON campaigns(sent_count);
CREATE INDEX idx_campaigns_click_rate ON campaigns(click_rate);
