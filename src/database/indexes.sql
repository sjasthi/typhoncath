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
