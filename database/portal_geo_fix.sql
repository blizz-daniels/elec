SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS senatorial_districts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lgas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    senatorial_district_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(50) NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_lgas_senatorial FOREIGN KEY (senatorial_district_id) REFERENCES senatorial_districts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lga_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(50) NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_wards_lga FOREIGN KEY (lga_id) REFERENCES lgas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS polling_units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    senatorial_district_id BIGINT UNSIGNED NOT NULL,
    lga_id BIGINT UNSIGNED NOT NULL,
    ward_id BIGINT UNSIGNED NOT NULL,
    polling_code VARCHAR(50) NOT NULL UNIQUE,
    polling_name VARCHAR(150) NOT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    gps_address VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pu_senatorial FOREIGN KEY (senatorial_district_id) REFERENCES senatorial_districts(id),
    CONSTRAINT fk_pu_lga FOREIGN KEY (lga_id) REFERENCES lgas(id),
    CONSTRAINT fk_pu_ward FOREIGN KEY (ward_id) REFERENCES wards(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE members
    ADD COLUMN IF NOT EXISTS senatorial_district_id BIGINT UNSIGNED NULL AFTER state_of_origin,
    ADD COLUMN IF NOT EXISTS nin VARCHAR(50) NULL AFTER vin,
    MODIFY state_of_residence VARCHAR(120) NOT NULL DEFAULT 'Ogun Resident';

INSERT IGNORE INTO senatorial_districts (id, name, slug) VALUES
(1, 'Abeokuta Senatorial District', 'abeokuta-senatorial-district'),
(2, 'Ijebu Senatorial District', 'ijebu-senatorial-district'),
(3, 'Remo Senatorial District', 'remo-senatorial-district');

INSERT IGNORE INTO lgas (id, senatorial_district_id, name, code) VALUES
(1, 1, 'Abeokuta North', 'OGN-LGA-001'),
(2, 2, 'Ijebu Ode', 'OGN-LGA-002'),
(3, 3, 'Ado-Odo/Ota', 'OGN-LGA-003');

INSERT IGNORE INTO wards (id, lga_id, name, code) VALUES
(1, 1, 'Ake Ward', 'OGN-WRD-001'),
(2, 2, 'Ijebu Ode Ward 1', 'OGN-WRD-002'),
(3, 3, 'Ota Ward 1', 'OGN-WRD-003');

INSERT IGNORE INTO polling_units (id, senatorial_district_id, lga_id, ward_id, polling_code, polling_name, latitude, longitude, gps_address) VALUES
(1, 1, 1, 1, 'OGN-PU-001', 'Ake Primary School Polling Unit', 0.0000000, 0.0000000, 'Ake, Ogun State'),
(2, 2, 2, 2, 'OGN-PU-002', 'Ijebu Ode Town Hall Polling Unit', 0.0000000, 0.0000000, 'Ijebu Ode, Ogun State'),
(3, 3, 3, 3, 'OGN-PU-003', 'Ota Civic Centre Polling Unit', 0.0000000, 0.0000000, 'Ota, Ogun State');

UPDATE members m
LEFT JOIN lgas l ON l.id = m.lga_id
SET m.senatorial_district_id = l.senatorial_district_id
WHERE m.senatorial_district_id IS NULL AND m.lga_id IS NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
