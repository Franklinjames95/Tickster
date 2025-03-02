-- ====================================================
-- Drop Existing Tables (if they exist)
-- ====================================================
IF OBJECT_ID('dbo.UserRoles', 'U') IS NOT NULL DROP TABLE dbo.UserRoles;
IF OBJECT_ID('dbo.RolePermissions', 'U') IS NOT NULL DROP TABLE dbo.RolePermissions;
IF OBJECT_ID('dbo.Permissions', 'U') IS NOT NULL DROP TABLE dbo.Permissions;
IF OBJECT_ID('dbo.Pages', 'U') IS NOT NULL DROP TABLE dbo.Pages;
IF OBJECT_ID('dbo.Roles', 'U') IS NOT NULL DROP TABLE dbo.Roles;
IF OBJECT_ID('dbo.UserSecurityStatus', 'U') IS NOT NULL DROP TABLE dbo.UserSecurityStatus;
IF OBJECT_ID('dbo.Users', 'U') IS NOT NULL DROP TABLE dbo.Users;
IF OBJECT_ID('dbo.UserPermissionsPivot', 'V') IS NOT NULL DROP VIEW dbo.UserPermissionsPivot;
GO

-- ====================================================
-- Recreate Tables
-- ====================================================
CREATE TABLE Users (
    id INT PRIMARY KEY IDENTITY(1,1),
    username NVARCHAR(50) UNIQUE NOT NULL,
    password_hash VARBINARY(256) NOT NULL,  -- 🔹 Store password as a secure binary hash
    email NVARCHAR(100) UNIQUE NOT NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
GO

CREATE TABLE UserSecurityStatus (
    id INT PRIMARY KEY IDENTITY(1,1),
    user_id INT FOREIGN KEY REFERENCES Users(id) ON DELETE CASCADE,
    last_login DATETIME DEFAULT GETDATE(),
    next_permission_refresh DATETIME,
    force_permission_refresh BIT DEFAULT 0
);
GO

CREATE TABLE Roles (
    id INT IDENTITY(1,1) PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL
);
GO

CREATE TABLE Pages (
    id INT IDENTITY(1,1) PRIMARY KEY,
    page_name VARCHAR(100) NOT NULL, -- Friendly page name (e.g., "Dashboard")
    page_url VARCHAR(255) UNIQUE NOT NULL, -- Page URL (e.g., "/dashboard")
    route_name VARCHAR(100) UNIQUE NOT NULL -- Slim Route Name (e.g., "dashboard")
);
GO

CREATE TABLE Permissions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    permission_key VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255) NOT NULL
);
GO

CREATE TABLE RolePermissions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    page_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES Roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES Permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (page_id) REFERENCES Pages(id) ON DELETE CASCADE,
    UNIQUE (role_id, permission_id, page_id)
);
GO

CREATE TABLE UserRoles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (role_id) REFERENCES Roles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);
GO

-- ====================================================
-- Insert Default Data
-- ====================================================

-- Add Roles
INSERT INTO Roles (role_name) VALUES ('Admin'), ('Editor'), ('Viewer');
GO

-- Add Pages
INSERT INTO Pages (page_name, page_url, route_name) VALUES 
('Dashboard', '/dashboard', 'dashboard'),
('Settings', '/settings', 'settings');
GO

-- Add Permissions
INSERT INTO Permissions (permission_key, description) VALUES 
('view', 'Can view the page'),
('edit', 'Can edit the page'),
('delete', 'Can delete data on the page');
GO

-- Assign Role Permissions (Role X Permission X Page)
INSERT INTO RolePermissions (role_id, permission_id, page_id) VALUES 
-- Admin can do everything
(1, 1, 1), (1, 2, 1), (1, 3, 1),  
(1, 1, 2), (1, 2, 2), (1, 3, 2),  

-- Editor can view & edit but not delete
(2, 1, 1), (2, 2, 1),  
(2, 1, 2), (2, 2, 2);  
GO

-- Viewer can only view
INSERT INTO RolePermissions (role_id, permission_id, page_id) VALUES 
(3, 1, 1), (3, 1, 2);
GO

-- ====================================================
-- Add an Admin User with a Hashed Password
-- ====================================================
DECLARE @AdminUserId INT;
DECLARE @HashedPassword VARBINARY(256);

-- Hash the password "password" using SHA-256
SET @HashedPassword = HASHBYTES('SHA2_256', 'password');

INSERT INTO Users (username, password_hash, email)
VALUES ('admin', @HashedPassword, 'admin@example.com');

SET @AdminUserId = SCOPE_IDENTITY();

-- ====================================================
-- Assign User to Role (Using Dynamic User ID)
-- ====================================================
INSERT INTO UserRoles (user_id, role_id) 
VALUES (@AdminUserId, 1); -- Admin user
GO

-- ====================================================
-- Create Pivot View Stored Procedure
-- ====================================================
CREATE OR ALTER PROCEDURE sp_UpdateUserPermissionsPivot
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @DynamicPivotQuery NVARCHAR(MAX);
    DECLARE @PivotColumns NVARCHAR(MAX);
    DECLARE @CoalesceColumns NVARCHAR(MAX);

    -- Step 1: Generate Column List for Pivot (with 'can_' prefix)
    SELECT @PivotColumns = STRING_AGG(QUOTENAME(permission_key), ', ')
    FROM Permissions;

    -- Step 2: Generate COALESCE expressions for each permission column (Convert NULL to 0 & prefix 'can_')
    SELECT @CoalesceColumns = STRING_AGG('COALESCE(' + QUOTENAME(permission_key) + ', 0) AS ' + QUOTENAME('can_' + permission_key), ', ')
    FROM Permissions;

    -- Step 3: Construct the Dynamic Pivot Query
    SET @DynamicPivotQuery = '
    CREATE OR ALTER VIEW UserPermissionsPivot AS
    SELECT 
        user_id,
        username,
        role_name,
        route_name,
        page_url,
        ' + @CoalesceColumns + '
    FROM (
        SELECT 
            u.id AS user_id,
            u.username,
            r.role_name,
            p.route_name,
            p.page_url,
            perm.permission_key,
            1 AS has_permission
        FROM Users u
        JOIN UserRoles ur ON u.id = ur.user_id
        JOIN Roles r ON ur.role_id = r.id
        JOIN RolePermissions rp ON r.id = rp.role_id
        JOIN Pages p ON rp.page_id = p.id
        JOIN Permissions perm ON rp.permission_id = perm.id
    ) AS SourceTable
    PIVOT (
        MAX(has_permission) 
        FOR permission_key IN (' + @PivotColumns + ')
    ) AS PivotTable;';

    -- Step 4: Execute the Dynamic SQL
    EXEC sp_executesql @DynamicPivotQuery;
END;
GO

-- ====================================================
-- Execute Stored Procedure to Create View
-- ====================================================
EXEC sp_UpdateUserPermissionsPivot;
GO

-- ====================================================
-- Create Trigger to Auto-Update Pivot View
-- ====================================================
CREATE OR ALTER TRIGGER trg_UpdateUserPermissionsPivot
ON Permissions
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;
    EXEC sp_UpdateUserPermissionsPivot;
END;
GO
