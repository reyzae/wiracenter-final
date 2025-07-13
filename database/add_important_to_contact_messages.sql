-- Add 'important' column to contact_messages table for starred/important feature
ALTER TABLE contact_messages ADD COLUMN important TINYINT(1) NOT NULL DEFAULT 0; 