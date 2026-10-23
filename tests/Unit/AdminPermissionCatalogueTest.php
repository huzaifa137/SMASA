<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Guards the integrity of config/admin_permissions.php, the single source of
 * truth for Admin Portal permissions. Pure PHP: needs no database or app boot.
 */
class AdminPermissionCatalogueTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        $this->config = require dirname(__DIR__, 2) . '/config/admin_permissions.php';
    }

    public function test_every_module_is_well_formed(): void
    {
        $this->assertNotEmpty($this->config['modules']);

        foreach ($this->config['modules'] as $key => $module) {
            $this->assertMatchesRegularExpression('/^[a-z][a-z_]*$/', $key, "module key {$key}");
            $this->assertNotEmpty($module['name'] ?? '', "{$key} needs a name");
            $this->assertNotEmpty($module['permissions'] ?? [], "{$key} needs permissions");

            foreach ($module['permissions'] as $action => $label) {
                $this->assertMatchesRegularExpression('/^[a-z][a-z_]*$/', $action, "{$key}.{$action}");
                $this->assertNotSame('', trim($label), "{$key}.{$action} needs a label");
            }
        }
    }

    public function test_permission_keys_are_unique_and_fit_the_database_column(): void
    {
        $keys = [];

        foreach ($this->config['modules'] as $module => $def) {
            foreach (array_keys($def['permissions']) as $action) {
                $key = "{$module}.{$action}";
                $this->assertArrayNotHasKey($key, $keys, "duplicate {$key}");
                $this->assertLessThanOrEqual(100, strlen($key));
                $keys[$key] = true;
            }
        }
    }

    public function test_legacy_bridge_only_points_at_existing_permissions(): void
    {
        $valid = [];
        foreach ($this->config['modules'] as $module => $def) {
            foreach (array_keys($def['permissions']) as $action) {
                $valid["{$module}.{$action}"] = true;
            }
        }

        foreach ($this->config['legacy_feature_map'] as $legacy => $target) {
            $this->assertArrayHasKey($target, $valid, "legacy feature {$legacy} -> {$target}");
        }

        foreach ($this->config['legacy_module_map'] as $legacy => $module) {
            $this->assertArrayHasKey($module, $this->config['modules'], "legacy module {$legacy} -> {$module}");
        }
    }
}
