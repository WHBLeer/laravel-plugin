<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use Exception;
use App\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelPlugin\Support\Plugin;

class ReloadCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'plugin:reload';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reload the specified plugin.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		/**
		 * check if user entred an argument.
		 */
		if ($this->argument('plugin') === null) {
			$this->reloadAll();
			return 0;
		}

		/** @var Plugin $plugin */
		$plugin = $this->laravel['plugins.repository']->findOrFail($this->argument('plugin'));
		if ($plugin->isEnabled()) $plugin->disable();

		$plugin->enable();
		if ($plugin->config()['menu']['status']) {
			$this->reloadMenu($plugin);
		}
		$this->info("Plugin [{$plugin}] reload successful.");

		return 0;
	}

	/**
	 * reloadAll.
	 *
	 * @return void
	 */
	public function reloadAll(): void
	{
		$plugins = $this->laravel['plugins.repository']->all();
		/** @var Plugin $plugin */
		foreach ($plugins as $plugin) {
			if ($plugin->isEnabled()) $plugin->disable();

			$plugin->enable();
			if ($plugin->config()['menu']['status']) {
				$this->reloadMenu($plugin);
			}
			$this->info("Plugin [{$plugin}] reload successful.");
		}
	}

	/**
	 * Reload menu
	 *
	 * @return void
	 * @throws Exception
	 */
	public function reloadMenu($plugin)
	{
		Menu::where('source_by',$plugin->config()['menu']['source_by'])->delete();
		// Repair tree structure
		Menu::fixTree();

		$menu_file = $plugin->getPath().'/'.$plugin->config()['menu']['file'];
		$menu_data = json_decode(file_get_contents($menu_file),true);
		self::generateMenuData($menu_data,$plugin->config());

		// Repair tree structure
		Menu::fixTree();
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
	 * @param $menuItems
	 * @param $config
	 * @param $parent
	 * @param $level
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 11:05
	 */
	private static function generateMenuData($menuItems, $config, $parent = null, $level = 0)
	{
		foreach ($menuItems as $item) {
			/**
			 * If the menu in the menu.json file of the plug-in is already in the system, it is not changed, updated, or overwritten
			 */
			$hash = self::MenuHash($item);
			if (!$node = Menu::where('hash',$hash)->first()) {
				$node = new Menu();
				$node->parent_id    = $parent ? $parent->id : null;
				$node->url          = self::GenerateUrl($item['route_name']);
				$node->route_name   = $item['route_name'];
				$node->icon         = $item['icon'] ?? 'icon-product';
				$node->params       = $item['params'] ?? null;
				$node->name         = $item['name'];
				$node->is_show      = $item['is_show'];
				$node->is_menu      = $item['is_menu'];
				$node->sort_id      = 1000 - $level;
				$node->source_by    = $config['menu']['source_by'];
				$node->hash         = $hash;
				$node->save();

				if (!$parent) {
					// All Level-1 nodes must be placed before 'SYSTEM'
					$targetNode = Menu::withDepth()->having('depth', '=', 0)->where('name', 'SYSTEM')->first();
					$node->insertBeforeNode($targetNode);
				} else {
					// Placed by dependency
					$targetNode = Menu::where('parent_id', $parent->id)->first();
					$node->appendToNode($targetNode);
				}
			}
			if (isset($item['children'])) {
				self::generateMenuData($item['children'], $config, $node, $level + 1);
			}
		}
	}

	/**
	 * @param $data
	 * @return string
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 10:58
	 */
	private static function MenuHash($data): string
	{
		if (isset($data['children'])) unset($data['children']);
		if (isset($data['icon'])) unset($data['icon']);

		$str = implode('_',$data);
		return hash('md5',$str);
	}

	/**
	 * @param $route
	 * @return string
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 10:58
	 */
	private static function GenerateUrl($route): string
	{
		if (!$route) return 'javascript:void(0);';
		return '/'.(str_replace('.','/',$route));
	}

}
