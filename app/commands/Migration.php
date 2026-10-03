<?php
class Migration
{
    public static $command = 'migration';
    public static $description = 'Manage database migrations';
    public static $arguments = [
        '[action]' => 'Action: run, create-migration, rollback, rollback-all, refresh, status',
        '[name]' => 'Migration class name for create-migration',
    ];

    private static $route_map = [
        'run' => 'migrate',
        'create-migration' => 'create-migration',
        'rollback' => 'rollback',
        'rollback-all' => 'rollback-all',
        'refresh' => 'refresh',
        'status' => 'status',
    ];

    public function handle($action = null, array $flags = [], $name = null)
    {
        $action = $action ?? 'run';
        if (!isset(self::$route_map[$action])) {
            echo danger("Unknown migration action: \"{$action}\"") . PHP_EOL;
            echo 'Available actions: ' . implode(', ', array_keys(self::$route_map)) . PHP_EOL;
            exit(1);
        }
        if ($action === 'create-migration') {
            if (!$name || !preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
                echo danger('A valid migration name is required. Example: php lava migration create-migration create_products_table') . PHP_EOL;
                exit(1);
            }
            $route = 'create-migration/' . $name;
        } else {
            $route = self::$route_map[$action];
        }

        $index = PUBLIC_DIR . 'index.php';
        if (!file_exists($index)) {
            echo danger("index.php not found at: {$index}") . PHP_EOL;
            exit(1);
        }
        $command = sprintf('php %s %s', escapeshellarg($index), escapeshellarg($route));
        passthru($command);
    }
}
