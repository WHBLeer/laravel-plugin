<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;

class InstallPluginsSystemCommand extends Command
{
	protected $signature = 'plugins-system:install';
	protected $description = 'Install the plugins system and setup permissions';

	public function handle()
	{
		$this->info('Setting up plugins system permissions...');

		// 创建权限
		$this->assignPermissions();

		// 发布资源
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'laravel-plugin-config'
		]);
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'laravel-plugin-views'
		]);
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'laravel-plugin-assets'
		]);

		$this->info('Plugins system installed successfully!');
	}

	public function assignPermissions()
	{
		$system = Permission::firstOrCreate([
			'name' => 'system',
			'guard_name' => 'admin',
			'display_name' => '系统管理',
			'type' => 'node',
			'is_menu' => 'yes',
			'icon' => 'fas fa-cog',
		]);
		$parent = Permission::firstOrCreate([
			'name' => 'plugins',
			'guard_name' => 'admin',
			'display_name' => '插件',
			'type' => 'link',
			'is_menu' => 'yes',
			'icon' => 'ph-duotone  ph-squares-four',
			'route' => 'admin.plugin',
			'parent_id' => $system->id,
		]);
		$permissions = [
			[
				'name' => 'plugins.disable',
				'guard_name' => 'admin',
				'display_name' => '插件禁用',
				'type' => 'button',
				'is_menu' => 'no',
				'icon' => 'ph-duotone  ph-squares-four',
				'route' => 'admin.plugin.disable',
				'parent_id' => $parent->id,
			],
			[
				'name' => 'plugins.enable',
				'guard_name' => 'admin',
				'display_name' => '插件启用',
				'type' => 'button',
				'is_menu' => 'no',
				'icon' => 'ph-duotone  ph-squares-four',
				'route' => 'admin.plugin.enable',
				'parent_id' => $parent->id,
			],
			[
				'name' => 'plugins.delete',
				'guard_name' => 'admin',
				'display_name' => '插件删除',
				'icon' => 'ph-duotone  ph-squares-four',
				'route' => 'admin.plugin.delete',
				'parent_id' => $parent->id,
			],
			[
				'name' => 'plugins.batch',
				'guard_name' => 'admin',
				'display_name' => '批量处理',
				'type' => 'button',
				'is_menu' => 'no',
				'icon' => 'ph-duotone  ph-squares-four',
				'route' => 'admin.plugin.batch',
				'parent_id' => $parent->id,
			],
			[
				'name' => 'plugins.install',
				'guard_name' => 'admin',
				'display_name' => '插件安装',
				'type' => 'button',
				'is_menu' => 'no',
				'icon' => 'ph-duotone  ph-squares-four',
				'route' => 'admin.plugin.install',
				'parent_id' => $parent->id,
			],
			[
				'name' => 'plugins.local',
				'guard_name' => 'admin',
				'display_name' => '本地安装',
				'type' => 'button',
				'is_menu' => 'no',
				'icon' => 'ph-duotone  ph-squares-four',
				'route' => 'admin.plugin.local',
				'parent_id' => $parent->id,
			],
			[
				'name' => 'plugins.setting',
				'guard_name' => 'admin',
				'display_name' => '插件配置',
				'type' => 'button',
				'is_menu' => 'no',
				'icon' => 'ph-duotone  ph-squares-four',
				'route' => 'admin.plugin.setting',
				'parent_id' => $parent->id,
			],
		];
		$PermissionTo = [];
		foreach ($permissions as $permission) {
			Permission::firstOrCreate($permission);
			$PermissionTo[] = $permission['name'];
			$this->line("Created permission: {$permission['name']}");
		}

		// 确保管理员角色存在
		$superAdmin = Role::firstOrCreate([
			'name' => 'Super Admin',
			'guard_name' => 'admin',
		]);
		$roleAdmin = Role::firstOrCreate([
			'name' => 'Admin',
			'guard_name' => 'admin',
		]);

		// 分配权限
		$superAdmin->givePermissionTo(Permission::all());
		$roleAdmin->givePermissionTo($PermissionTo);
		$this->info('All permissions assigned to admin role.');
	}
}