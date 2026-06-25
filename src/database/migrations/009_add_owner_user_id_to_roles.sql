USE typhon_cath_crm;
ALTER TABLE roles ADD COLUMN owner_user_id INT NULL AFTER description;
