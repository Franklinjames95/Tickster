CREATE OR ALTER TRIGGER trg_UpdateUserPermissionsPivot
ON Permissions
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    -- Call the stored procedure to update the pivot view
    EXEC sp_UpdateUserPermissionsPivot;
END;