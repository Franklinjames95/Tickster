<?php

namespace App\Services;

use Psr\Container\ContainerInterface;
use App\Services\DatabaseService;

class PermissionService {

    protected DatabaseService $db;
    protected int $timeout;

    public function __construct(ContainerInterface $container){
        $this->db = $container->get('db');
        $this->timeout = $container->get('settings')['security']['permission_timeout'] ?? 900; // Default: 15 mins
    }

    public function loadPermissions(int $userId, ?int $overrideTimeout = null): void {
        $timeout = $overrideTimeout ?? $this->timeout;

        // Reset session permissions
        $_SESSION['security']['permissions'] = $_SESSION['registered_pages'] = [];

        // Fetch user permissions
        foreach ($this->db->query("SELECT * FROM UserPermissionsPivot WHERE user_id = ?", [$userId]) as $row){
            if ($routeName = $row['route_name'] ?? null) {
                $_SESSION['registered_pages'][] = $routeName;
                $_SESSION['security']['permissions'][$routeName] ??= [];

                // Store permissions dynamically
                foreach($row as $key => $value){
                    if(str_starts_with($key, 'can_')){
                        $_SESSION['security']['permissions'][$routeName][$key] = (bool) $value;
                    }
                }
            }
        }

        // Save last refresh time
        $_SESSION['security']['permissions_last_refresh'] = time();

        // Schedule next permission refresh
        $this->db->execute(
            "UPDATE UserSecurityStatus SET force_permission_refresh = 0, next_permission_refresh = ? WHERE user_id = ?",
            [date('Y-m-d H:i:s', time() + $timeout), $userId]
        );
    }

    public function needsPermissionRefresh(int $userId): bool {
        $securityStatus = $this->db->query(
            "SELECT force_permission_refresh, next_permission_refresh FROM UserSecurityStatus WHERE user_id = ?",
            [$userId]
        )[0] ?? null;

        return !$securityStatus || $securityStatus['force_permission_refresh'] || time() >= strtotime($securityStatus['next_permission_refresh']);
    }
}
