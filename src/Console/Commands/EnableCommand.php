<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use Exception;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelPlugin\Support\Plugin;

class EnableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'plugin:enable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Enable the specified plugin.';

	protected static $source = 'plugin';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		/**
		 * check if user entred an argument.
		 */
		if ($this->argument('plugin') === null) {
			$this->enableAll();

			return 0;
		}

		/** @var Plugin $plugin */
		$plugin = $this->laravel['plugins.repository']->findOrFail($this->argument('plugin'));
		self::$source = $plugin->config()['permission']['source_by'];
		if ($plugin->isDisabled()) {

			// 执行迁移
			Artisan::call('plugin:migrate', ['plugin' => $plugin->getName()]);

			// 启用插件
			$plugin->enable();
			$this->info("Plugin [{$plugin}] enabled successful.");

			// 重新加载权限
			if ($plugin->config()['permission']['status']) {
				$this->reloadPermission($plugin);
			}
		} else {
			$this->comment("Plugin [{$plugin}] has already enabled.");
		}

		return 0;
	}

	/**
	 * enableAll.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function enableAll(): array
	{
		/** @var Plugin $plugin */
		$plugins = $this->laravel['plugins.repository']->all();

		foreach ($plugins as $plugin) {
			if ($plugin->isDisabled()) {
				$plugin->enable();
				$this->info("Plugin [{$plugin}]  enabled successful.");

				if ($plugin->config()['menu']['status']) {
					$this->reloadPermission($plugin);
				}
			} else {
				$this->comment("Plugin [{$plugin}] has already enabled.");
			}
		}
	}

	/**
	 * Reload menu
	 *
	 * @return void
	 * @throws Exception
	 */
	public function reloadPermission($plugin)
	{
		Permission::where('source',self::$source)->delete();
		// Repair tree structure
		Permission::fixTree();

		$permission_file = $plugin->getPath().'/'.$plugin->config()['permission']['file'];
		$permission_data = json_decode(file_get_contents($permission_file),true);
		$PermissionTo = self::generatePermissionData($permission_data);
		self::givePermissionTo($PermissionTo);
		// Repair tree structure
		Permission::fixTree();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments(): array
	{
		return [
			['plugin', InputArgument::OPTIONAL, 'Plugin name.'],
		];
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
				$permission->href         = self::GenerateUrl($item['route']??null);
				$permission->name         = $item['name'];
				$permission->guard_name   = $item['guard_name'] ?? 'admin';
				$permission->display_name = $item['display_name'] ?? 'admin';
				$permission->type         = $item['type']??'button';
				$permission->is_menu      = $item['is_menu']??'no';
				$permission->icon         = $item['icon'] ?? 'ph-duotone  ph-squares-four';
				$permission->route        = $item['route'] ?? null;
				$permission->sort         = $k+1;
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
			}
			$PermissionTo[] = $permission->name;
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
	private static function GenerateUrl($route=null): ?string
	{
		if (!$route) return null;
		return str_replace(url('/'),'',route($route));
	}

}
