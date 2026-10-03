<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class MigrationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->library('migration');
    }

    private function guard_browser_access()
    {
        if (php_sapi_name() !== 'cli' && config_item('environment') !== 'development') {
            http_response_code(403);
            exit('Migration routes are available only in the development environment.');
        }
    }

    public function create_migration($migration_class) { $this->guard_browser_access(); $this->migration->create_migration($migration_class); }
    public function migrate() { $this->guard_browser_access(); $this->migration->migrate(); }
    public function rollback() { $this->guard_browser_access(); $this->migration->rollback(); }
    public function rollback_all() { $this->guard_browser_access(); $this->migration->rollback_all(); }
    public function refresh() { $this->guard_browser_access(); $this->migration->refresh(); }
    public function status() { $this->guard_browser_access(); $this->migration->status(); }
}
