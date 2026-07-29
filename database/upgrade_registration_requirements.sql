ALTER TABLE members
    ADD COLUMN senatorial_district_id BIGINT UNSIGNED NULL AFTER state_of_origin,
    ADD COLUMN nin VARCHAR(50) NULL AFTER vin,
    MODIFY state_of_residence VARCHAR(120) NOT NULL DEFAULT 'Ogun Resident',
    ADD CONSTRAINT fk_members_senatorial FOREIGN KEY (senatorial_district_id) REFERENCES senatorial_districts(id) ON DELETE SET NULL;

UPDATE members m
LEFT JOIN lgas l ON l.id = m.lga_id
SET m.senatorial_district_id = l.senatorial_district_id
WHERE m.senatorial_district_id IS NULL AND m.lga_id IS NOT NULL;
