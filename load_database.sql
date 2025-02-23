-- Insert roles
INSERT INTO Roles (role_name) VALUES ('Admin'), ('Editor'), ('Viewer');

-- Insert pages
INSERT INTO Pages (page_name, page_url) VALUES ('Dashboard', '/dashboard'), ('Settings', '/settings');

-- Insert permissions
INSERT INTO Permissions (permission_key, description) VALUES 
('view', 'Can view the page'),
('read', 'Can read the page'), 
('write', 'Can modify the page'), 
('delete', 'Can delete data on the page');

-- Assign permissions to roles
-- Admin can view, read, write, and delete on Dashboard and Settings
INSERT INTO RolePermissions (role_id, permission_id, page_id) VALUES 
(1, 1, 1), -- Admin - View - Dashboard
(1, 2, 1), -- Admin - Read - Dashboard
(1, 3, 1), -- Admin - Write - Dashboard
(1, 4, 1), -- Admin - Delete - Dashboard
(1, 1, 2), -- Admin - View - Settings
(1, 2, 2), -- Admin - Read - Settings
(1, 3, 2), -- Admin - Write - Settings
(1, 4, 2); -- Admin - Delete - Settings

-- Editor can view, read, and write on Dashboard only
INSERT INTO RolePermissions (role_id, permission_id, page_id) VALUES
(2, 1, 1), -- Editor - View - Dashboard
(2, 2, 1), -- Editor - Read - Dashboard
(2, 3, 1); -- Editor - Write - Dashboard

-- Viewer can only view and read on Dashboard
INSERT INTO RolePermissions (role_id, permission_id, page_id) VALUES
(3, 1, 1), -- Viewer - View - Dashboard
(3, 2, 1); -- Viewer - Read - Dashboard

-- Assign users to roles
INSERT INTO UserRoles (user_id, role_id) VALUES 
(1, 1), -- User 1 is an Admin
(2, 2), -- User 2 is an Editor
(3, 3); -- User 3 is a Viewer
