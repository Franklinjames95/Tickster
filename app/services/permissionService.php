<?php

namespace App\Services;

use Psr\Container\ContainerInterface;
use PDO;

class PermissionService {
    
    protected ContainerInterface $container;
    protected PDO $db;
    protected int $timeout;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
        $this->db = $container->get('db'); 
        $this->timeout = $container->get('settings')['security']['permission_timeout'] ?? 900; // Default to 15 mins
    }

    public function loadPermissions($userId, ?int $overrideTimeout = null){
        $timeout = $overrideTimeout ?? $this->timeout;

        // Check if permissions need to be refreshed
        $checkQuery = $this->db->prepare("SELECT force_permission_refresh, next_permission_refresh 
                                          FROM UserSecurityStatus 
                                          WHERE user_id = :user_id");
        $checkQuery->execute(['user_id' => $userId]);
        $securityStatus = $checkQuery->fetch(\PDO::FETCH_ASSOC);

        $now = new \DateTime();

        if ($securityStatus['force_permission_refresh'] || 
            (isset($securityStatus['next_permission_refresh']) 
                && $now >= new \DateTime($securityStatus['next_permission_refresh']))) 
        {
            // Get user roles
            $query = $this->db->prepare("SELECT r.id, r.role_name FROM Roles r 
                                         JOIN UserRoles ur ON ur.role_id = r.id 
                                         WHERE ur.user_id = :user_id");
            $query->execute(['user_id' => $userId]);
            $roles = $query->fetchAll(\PDO::FETCH_ASSOC);

            // Store roles in session under 'security'
            $_SESSION['security']['roles'] = array_column($roles, 'role_name');

            // Get role permissions
            $roleIds = array_column($roles, 'id');
            if($roleIds){
                $inPlaceholders = implode(',', array_fill(0, count($roleIds), '?'));
                $permissionsQuery = $this->db->prepare(
                    "SELECT DISTINCT p.permission_key FROM Permissions p 
                     JOIN RolePermissions rp ON rp.permission_id = p.id 
                     WHERE rp.role_id IN ($inPlaceholders)"
                );
                $permissionsQuery->execute($roleIds);
                $permissions = $permissionsQuery->fetchAll(\PDO::FETCH_COLUMN);

                // Store permissions in session under 'security'
                $_SESSION['security']['permissions'] = $permissions;
            }

            // Store the last refresh timestamp under 'security'
            $_SESSION['security']['permissions_last_refresh'] = time();

            // Update the next permission refresh timestamp based on timeout
            $nextRefresh = (new \DateTime())->add(new \DateInterval('PT' . $timeout . 'S'))->format('Y-m-d H:i:s');

            // Reset the force_permission_refresh flag and set next refresh timestamp
            $updateQuery = $this->db->prepare("UPDATE UserSecurityStatus 
                                               SET force_permission_refresh = 0, 
                                                   next_permission_refresh = :next_refresh 
                                               WHERE user_id = :user_id");
            $updateQuery->execute(['next_refresh' => $nextRefresh, 'user_id' => $userId]);
        }
    }

    public function needsPermissionRefresh($userId, ?int $overrideTimeout = null): bool
    {
        $timeout = $overrideTimeout ?? $this->timeout;

        // Check if a forced refresh is needed or if timeout has been exceeded
        $query = $this->db->prepare("SELECT force_permission_refresh 
                                    FROM UserSecurityStatus 
                                    WHERE user_id = :user_id");
        $query->execute(['user_id' => $userId]);
        $forceRefresh = $query->fetchColumn();

        $lastRefresh = $_SESSION['security']['permissions_last_refresh'] ?? 0;

        return $forceRefresh || time() - $lastRefresh > $timeout;
    }
}
