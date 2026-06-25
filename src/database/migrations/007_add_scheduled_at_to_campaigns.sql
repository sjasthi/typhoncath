USE typhon_cath_crm;

ALTER TABLE campaigns
    ADD COLUMN scheduled_at DATETIME NULL AFTER status;
