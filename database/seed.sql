USE ogun_political;

INSERT INTO roles (name, slug) VALUES
('Super Admin', 'super-admin'),
('State Executive', 'state-executive'),
('Senatorial Executive', 'senatorial-executive'),
('LGA Executive', 'lga-executive'),
('Ward Executive', 'ward-executive'),
('Polling Marshal', 'polling-marshal'),
('Registered Member', 'registered-member');

INSERT INTO permissions (name, slug) VALUES
('Manage Users', 'manage-users'),
('Manage Geography', 'manage-geography'),
('Manage Elections', 'manage-elections'),
('Submit Results', 'submit-results'),
('Approve Results', 'approve-results'),
('Generate Reports', 'generate-reports');

INSERT INTO senatorial_districts (name, slug) VALUES
('Ogun Central', 'ogun-central'),
('Ogun East', 'ogun-east'),
('Ogun West', 'ogun-west');

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Ogun State Political Membership & Election Monitoring System'),
('maintenance_mode', '0');
