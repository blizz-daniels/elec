-- Retain the constituency data provided by polliong unit.csv for ranked registration searches.
ALTER TABLE polling_units
    ADD COLUMN IF NOT EXISTS house_of_representatives VARCHAR(190) NULL AFTER polling_name;