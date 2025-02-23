<?php

namespace App\Config;

use eftec\bladeone\BladeOne;

/**
 * CustomBlade extends BladeOne pretty much just a wrapper to add functionality.
 */
class CustomBlade extends BladeOne {

    /**
     * Constructor to initialize Blade settings and register custom directives.
     *
     * @param string $views  Path to the views directory
     * @param string $cache  Path to the cache directory
     * @param int $mode      Blade mode (default: auto)
     */
    public function __construct(string $views, string $cache, int $mode = self::MODE_AUTO){
        parent::__construct($views, $cache, $mode);
        $this->registerDirectives();
    }

    /**
     * Override BladeOne's `run` method if needed.
     * This allows us to modify the rendering behavior globally.
     */
    public function run($view = null, $variables = []): string {
        // Add global variables before rendering
        $vars['app_name'] = 'Frank/Tickser';

        // Call parent method to render the view
        return parent::run($view, $vars);
    }

    /**
     * Registers custom Blade directives for use in templates.
     * This method allows us to define shorthand syntax and other features.
     */
    private function registerDirectives(){
        /** -------------------------
         * General Utility Directives
         * ------------------------- */
        
        // Empty directives for IDE formatting
        $this->directive('dummy', fn() => '');
        $this->directive('enddummy', fn() => '');
        
        // Dump Data: @dump($data) → Pretty prints data
        $this->directive('dump', fn($var) => "<?= '<pre>' . print_r({$var}, true) . '</pre>'; ?>");
    
        // Environment Variable: @env('APP_NAME') → Retrieves ENV value
        $this->directive('env', fn($env) => "<?= getenv({$env}) ?: ''; ?>");
    
        // Session Flash Messages: @flash('success') → Show success message
        $this->directive('flash', fn($key) => "<?= \$_SESSION['flash'][$key] ?? ''; ?>");
    
        /** -------------------------
         * Security & Authentication Directives
         * ------------------------- */
    
        // CSRF Token: @csrf → Prints a hidden input with CSRF token
        $this->directive('csrf', fn() => '<?= \'<input type="hidden" name="_token" value="\' . ($_SESSION[\'_token\'] ?? \'\') . \'">\'; ?>');

        // Authenticated User: @auth → Checks if the user is logged in
        $this->directive('auth', fn() => "<?php if(isset(\$_SESSION['user'])): ?>");
        $this->directive('endauth', fn() => "<?php endif; ?>");

        // Authenticated User with Role(s): @authRole('admin') or @authRole(['admin', 'editor'])
        $this->directive('authRole', fn($roles) => 
            "<?php if(isset(\$_SESSION['user']) && (" . 
            "(is_array({$roles}) ? in_array(\$_SESSION['role'], {$roles}) : \$_SESSION['role'] === trim({$roles}, \"'\\\"\"))" . 
            ")): ?>"
        );
        $this->directive('endAuthRole', fn() => "<?php endif; ?>");

        // Guest User: @guest → Shows content only if not logged in
        $this->directive('guest', fn() => "<?php if(!isset(\$_SESSION['user'])): ?>");
        $this->directive('endguest', fn() => "<?php endif; ?>");

        // If Admin User: @admin → Content only for admins
        $this->directive('admin', fn() => "<?php if(isset(\$_SESSION['user']) && \$_SESSION['role'] === 'admin'): ?>");
        $this->directive('endadmin', fn() => "<?php endif; ?>");

        // check if the user has a specific permission
        $this->directive('authPermission', fn($permission) =>
            "<?php if(isset(\$_SESSION['user']) && in_array({$permission}, \$_SESSION['permissions'] ?? [])): ?>"
        );
        $this->directive('endAuthPermission', fn() => "<?php endif; ?>");
    
        /** -------------------------
         * Content Display Directives
         * ------------------------- */
        
        $this->directive('datetime', fn($date) => "<?= date('Y-m-d H:i', strtotime({$date})); ?>");
        $this->directive('break', fn() => "<?= '<br>' ?>");
        $this->directive('divider', fn() => "<?= '<hr>' ?>");
    
        /** -------------------------
         * Asset & Resource Directives
         * ------------------------- */
        
        // Asset Path Helper: @asset('image.jpg') → /assets/image.jpg
        $this->directive('asset', fn($path) => "'/assets/' . trim($path, \"'\\\"\")");
    
        // Include JavaScript File Dynamically: @js('script.js')
        $this->directive('js', fn($file) => "<?= '<script src=\"/js/' . {$file} . '\"></script>'; ?>");
    
        // Include CSS File Dynamically: @css('style.css')
        $this->directive('css', fn($file) => "<?= '<link rel=\"stylesheet\" href=\"/css/' . {$file} . '\">' ?>");
    }    
    
}