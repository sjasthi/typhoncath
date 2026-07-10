-- 013_add_fulltext_search_indexes.sql
-- Adds FULLTEXT indexes so the RFQ list search can use MATCH ... AGAINST
-- (BOOLEAN MODE) instead of a leading-wildcard LIKE, which cannot use an index
-- and scans the whole table on every search.
--   - rfqs.title            -> ft_rfqs_title
--   - accounts.account_name -> ft_accounts_name
--
-- Required by RFQRepository::buildWhere(): until this migration runs, a text
-- search on the RFQ list will error with
--   "Can't find FULLTEXT index matching the column list".
-- (Short queries below the InnoDB minimum token size fall back to LIKE, so they
-- keep working either way.)
ALTER TABLE rfqs     ADD FULLTEXT INDEX ft_rfqs_title (title);
ALTER TABLE accounts ADD FULLTEXT INDEX ft_accounts_name (account_name);
