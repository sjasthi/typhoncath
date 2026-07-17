-- 016_datatables_list_indexes.sql
-- Indexes supporting the DataTables server-side list endpoints (RFQ, Customer,
-- Inventory, Admin). They keep ORDER BY an index walk (not a filesort) and make
-- the per-column exact-match filters index-friendly, so the list pages stay
-- under the 3s budget at scale (perf_seed: ~20k RFQs, ~1k accounts).
--
-- Already covered elsewhere and intentionally NOT repeated here:
--   accounts.account_name  -> idx_accounts_name        (indexes.sql)
--   accounts.account_name  -> ft_accounts_name FULLTEXT (013_...)
--   rfqs.title             -> ft_rfqs_title FULLTEXT     (013_...)
--   rfqs.stage/created_at/updated_at/account_id         (indexes.sql / 014_...)

-- Customer accounts: per-column select filters (industry / source)
CREATE INDEX idx_accounts_industry ON accounts(industry);
CREATE INDEX idx_accounts_source   ON accounts(source);

-- Inventory products: sortable columns (sku is already UNIQUE)
CREATE INDEX idx_products_name  ON products(product_name);
CREATE INDEX idx_products_price ON products(price);

-- Admin users: sortable / filterable columns
CREATE INDEX idx_users_name       ON users(name);
CREATE INDEX idx_users_email      ON users(email);
CREATE INDEX idx_users_created_at ON users(created_at);
