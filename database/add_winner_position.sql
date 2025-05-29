-- Add winner_position column to registrations table if it doesn't exist
ALTER TABLE registrations
ADD COLUMN IF NOT EXISTS winner_position VARCHAR(10) DEFAULT NULL
AFTER feedback_submitted;
