CREATE TABLE Users (
    id INT PRIMARY KEY IDENTITY(1,1),
    username NVARCHAR(50) UNIQUE NOT NULL,
    password_hash VARBINARY(256) NOT NULL,  -- 🔹 Store password as a secure binary hash
    email NVARCHAR(100) UNIQUE NOT NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);

CREATE TABLE UserSecurityStatus (
    id INT PRIMARY KEY IDENTITY(1,1),
    user_id INT FOREIGN KEY REFERENCES Users(id) ON DELETE CASCADE,
    last_login DATETIME DEFAULT GETDATE(),
    next_permission_refresh DATETIME,
    force_permission_refresh BIT DEFAULT 0
);

CREATE TABLE Roles (
    id INT IDENTITY(1,1) PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE Pages (
    id INT IDENTITY(1,1) PRIMARY KEY,
    page_name VARCHAR(100) NOT NULL, -- Friendly page name (e.g., "Dashboard")
    page_url VARCHAR(255) UNIQUE NOT NULL, -- Page URL (e.g., "/dashboard")
    route_name VARCHAR(100) UNIQUE NOT NULL -- Slim Route Name (e.g., "dashboard")
);

CREATE TABLE Permissions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    permission_key VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255) NOT NULL
);

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

CREATE TABLE UserRoles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (role_id) REFERENCES Roles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);