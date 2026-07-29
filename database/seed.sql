INSERT IGNORE INTO roles (id, name, slug) VALUES
(1, 'Super Admin', 'super-admin'),
(2, 'State Executive', 'state-executive'),
(3, 'Senatorial Executive', 'senatorial-executive'),
(4, 'LGA Executive', 'lga-executive'),
(5, 'Ward Executive', 'ward-executive'),
(6, 'Polling Marshal', 'polling-marshal'),
(7, 'Registered Member', 'registered-member');

INSERT IGNORE INTO permissions (name, slug) VALUES
('Manage Users', 'manage-users'),
('Manage Geography', 'manage-geography'),
('Manage Elections', 'manage-elections'),
('Submit Results', 'submit-results'),
('Approve Results', 'approve-results'),
('Generate Reports', 'generate-reports');

INSERT IGNORE INTO senatorial_districts (id, name, slug) VALUES
(1, 'Ogun Central', 'ogun-central'),
(2, 'Ogun East', 'ogun-east'),
(3, 'Ogun West', 'ogun-west');

INSERT IGNORE INTO lgas (id, senatorial_district_id, name, code) VALUES
(1, 1, 'Abeokuta South', 'OGN-LGA-001'),
(2, 2, 'Ijebu Ode', 'OGN-LGA-002'),
(3, 3, 'Ota', 'OGN-LGA-003');

INSERT IGNORE INTO wards (id, lga_id, name, code) VALUES
(1, 1, 'Ake Ward', 'OGN-WRD-001'),
(2, 2, 'Ijebu Ode Ward 1', 'OGN-WRD-002'),
(3, 3, 'Ota Ward 1', 'OGN-WRD-003');

INSERT IGNORE INTO polling_units (id, senatorial_district_id, lga_id, ward_id, polling_code, polling_name, latitude, longitude, gps_address) VALUES
(1, 1, 1, 1, 'OGN-PU-001', 'Ake Primary School PU', 7.1500000, 3.3500000, 'Ake, Abeokuta South'),
(2, 2, 2, 2, 'OGN-PU-002', 'Ijebu Ode Town Hall PU', 6.8200000, 3.9230000, 'Ijebu Ode, Ogun State'),
(3, 3, 3, 3, 'OGN-PU-003', 'Ota Civic Centre PU', 6.6900000, 3.2320000, 'Ota, Ogun State');

INSERT IGNORE INTO users (id, role_id, full_name, email, phone, password, status) VALUES
(1, 1, 'Super Admin Demo', 'superadmin@ogun.test', '08010000001', '$2y$10$YmKyLkMBQwlT8ZMsSG5qqeqQvUvbYJsoeSdTSySI0wdfMjqoVdXbS', 'active'),
(2, 2, 'State Executive Demo', 'stateexec@ogun.test', '08010000002', '$2y$10$YmKyLkMBQwlT8ZMsSG5qqeqQvUvbYJsoeSdTSySI0wdfMjqoVdXbS', 'active'),
(3, 3, 'Senatorial Executive Demo', 'senatorialexec@ogun.test', '08010000003', '$2y$10$YmKyLkMBQwlT8ZMsSG5qqeqQvUvbYJsoeSdTSySI0wdfMjqoVdXbS', 'active'),
(4, 4, 'LGA Executive Demo', 'lgaexec@ogun.test', '08010000004', '$2y$10$YmKyLkMBQwlT8ZMsSG5qqeqQvUvbYJsoeSdTSySI0wdfMjqoVdXbS', 'active'),
(5, 5, 'Ward Executive Demo', 'wardexec@ogun.test', '08010000005', '$2y$10$YmKyLkMBQwlT8ZMsSG5qqeqQvUvbYJsoeSdTSySI0wdfMjqoVdXbS', 'active'),
(6, 6, 'Polling Marshal Demo', 'marshal@ogun.test', '08010000006', '$2y$10$YmKyLkMBQwlT8ZMsSG5qqeqQvUvbYJsoeSdTSySI0wdfMjqoVdXbS', 'active'),
(7, 7, 'Registered Member Demo', 'member@ogun.test', '08010000007', '$2y$10$YmKyLkMBQwlT8ZMsSG5qqeqQvUvbYJsoeSdTSySI0wdfMjqoVdXbS', 'active');

INSERT IGNORE INTO members (id, membership_number, surname, first_name, other_name, phone, email, state_of_origin, senatorial_district_id, state_of_residence, date_of_birth, gender, vin, nin, occupation, residential_address, lga_id, ward_id, polling_unit_id, passport_path, password, status, qr_code_path) VALUES
(1, 'OGN000001', 'Demo', 'Registered', 'Member', '08010000007', 'member@ogun.test', 'Ogun State', 1, 'Ogun Resident', '1990-01-01', 'other', 'VIN-TEST-001', 'NIN-TEST-001', 'Administrator', 'Abeokuta, Ogun State', 1, 1, 1, NULL, '$2y$10$YmKyLkMBQwlT8ZMsSG5qqeqQvUvbYJsoeSdTSySI0wdfMjqoVdXbS', 'approved', NULL);

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'Yayi Youth Vanguard Membership & Polling Unit Marshal Registration'),
('maintenance_mode', '0');
