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
