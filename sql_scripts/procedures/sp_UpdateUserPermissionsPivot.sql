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