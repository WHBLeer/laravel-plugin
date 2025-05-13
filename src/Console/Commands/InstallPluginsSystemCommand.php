<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Console\Command;

class InstallPluginsSystemCommand extends Command
{
	protected $signature = 'plugins-system:install';
	protected $description = 'Install the plugins system';

	protected static $source = 'plugin';
	public function handle()
	{
		// 发布资源
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'plugins-config'
		]);
		$this->info('All permissions assigned to admin role.');
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'plugins-views'
		]);
		$this->info('All permissions assigned to admin role.');
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'plugins-assets'
		]);
		$this->info('All permissions assigned to admin role.');
		$this->call('vendor:publish', [
			'--provider' => 'Sanlilin\LaravelPlugin\Providers\PluginServiceProvider',
			'--tag' => 'plugins-migrations'
		]);
		$this->info('Plugins system installed successfully!');

		$show_in_menu = $this->laravel['config']->get('plugins.show_in_menu');
		if ($show_in_menu && Permission::where('source', self::$source)->count() == 0) {
			$permission_file = __DIR__ . '/../../../config/permission.json';
			$permission_data = json_decode(file_get_contents($permission_file), true);

			$PermissionTo = self::generatePermissionData($permission_data);
			self::givePermissionTo($PermissionTo);
		}
	}

	/**
	 * Process the menu recursively
	 * @param $items
	 * @param $parent
	 * @param array $PermissionTo
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 11:05
	 */
	private static function generatePermissionData($items, $parent = null, array $PermissionTo = [])
	{
		foreach ($items as $k => $item) {
			/**
			 * If the menu in the permission.json file of the plug-in is already in the system, it is not changed, updated, or overwritten
			 */
			if (!$permission = Permission::where('name',$item['name'])->first()) {
				/**
				 * "name": "plugins.local",
				 * "guard_name": "admin",
				 * "display_name": "本地安装",
				 * "type": "button",
				 * "is_menu": "no",
				 * "icon": "ph-duotone  ph-squares-four",
				 * "route": "admin.plugin.local"
				 */
				$permission = new Permission();
				$permission->parent_id    = $parent ? $parent->id : null;
				$permission->href         = self::GenerateUrl($item['route']);
				$permission->name         = $item['name'];
				$permission->guard_name   = $item['guard_name'] ?? 'admin';
				$permission->display_name = $item['display_name'] ?? 'admin';
				$permission->type         = $item['type']??'button';
				$permission->is_menu      = $item['is_menu']??'no';
				$permission->icon         = $item['icon'] ?? 'ph-duotone  ph-squares-four';
				$permission->route        = $item['route'] ?? null;
				$permission->sort_id      = $k+1;
				$permission->source    = self::$source;
				$permission->save();

				if (!$parent) {
					// All Level-1 nodes must be placed before 'system'
					$targetNode = Permission::withDepth()->having('depth', '=', 0)->where('name', 'system')->first();
					$permission->insertBeforeNode($targetNode);
				} else {
					// Placed by dependency
					$targetNode = Permission::where('parent_id', $parent->id)->first();
					if ($targetNode->name!='SYSTEM') {
						$permission->appendToNode($targetNode);
					} else {
						$permission->prependToNode($targetNode);
					}
				}
				$PermissionTo[] = $permission->name;
			}
			if (isset($item['children'])) {
				self::generatePermissionData($item['children'], $permission, $PermissionTo);
			}
		}

		return  $PermissionTo;
	}

	/**
	 * @param $PermissionTo
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 10:58
	 */
	private static function givePermissionTo($PermissionTo)
	{
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
	}

	/**
	 * @param $route
	 * @return string|null
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 10:58
	 */
	private static function GenerateUrl($route): ?string
	{
		if (!$route) return null;
		return str_replace(url('/'),'/',route($route));
	}
}