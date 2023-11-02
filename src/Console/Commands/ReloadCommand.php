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
		$newNodes = self::createMenus($menu_data,$plugin->config());
		$after_menu_hash = $plugin->config()['menu']['after_hash'];
		if (!!$after_menu_hash) {
			// Added to the specified root or level 1 node
			$targetNode = Menu::withDepth()->where('hash',$after_menu_hash)->first();
			if (!$targetNode->isRoot()) {
				// Not root node
				$targetNode = $targetNode->getRoot();
			}
		} else {
			// Added after the last root node
			$targetNode = Menu::withDepth()->having('depth', '=', 0)->orderBy('id','desc')->first();
		}
		foreach ($newNodes as $newNode) {
			if (!$newNode->parent_id) {
				$nodeId = $newNode->id;
				// The `$newNode` moves behind the `$targetNode`
				$newNode->insertAfterNode($targetNode);
				$targetNode = Menu::find($nodeId);
			}
		}
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
	 * @param $data
	 * @param $config
	 * @return mixed
	 * @throws Exception
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/2 14:29
	 */
	private static function createMenus($data,$config)
	{
		$id = Menu::max('id');
		$sort_id = Menu::whereNull('parent_id')->min('sort_id');
		$menus = [];
		foreach ($data as $k1 => $v1) {
			$id++;
			$id1 = $id;
			$menus[] = [
				'id'         => $id1,
				'parent_id'  => 0,
				'url'        => self::GenerateUrl($v1['route_name']),
				'route_name' => $v1['route_name'],
				'icon'       => $v1['icon']??'icon-product',
				'params'     => $v1['params']??null,
				'name'       => $v1['name'],
				'is_show'    => $v1['is_show'],
				'is_menu'    => $v1['is_menu'],
				'sort_id'    => $sort_id-($k1+1),
				'source_by'  => $config['menu']['source_by'],
				'hash'       => self::MenuHash($v1,$config['menu']['source_by']),
			];
			if (isset($v1['children'])) {
				foreach ($v1['children'] as $k2 => $v2) {
					$id++;
					$id2 = $id;
					$menus[] = [
						'id'         => $id2,
						'parent_id'  => $id1,
						'url'        => self::GenerateUrl($v2['route_name']),
						'route_name' => $v2['route_name'],
						'icon'       => $v2['icon']??'icon-product',
						'params'     => $v2['params']??null,
						'name'       => $v2['name'],
						'is_show'    => $v2['is_show'],
						'is_menu'    => $v2['is_menu'],
						'sort_id'    => 1000-$k2,
						'source_by'  => $config['menu']['source_by'],
						'hash'       => self::MenuHash($v2,$config['menu']['source_by']),
					];
					if (isset($v2['children'])) {
						foreach ($v2['children'] as $k3 => $v3) {
							$id++;
							$id3 = $id;
							$menus[] = [
								'id'         => $id3,
								'parent_id'  => $id2,
								'url'        => self::GenerateUrl($v3['route_name']),
								'route_name' => $v3['route_name'],
								'icon'       => $v3['icon']??'icon-product',
								'params'     => $v3['params']??null,
								'name'       => $v3['name'],
								'is_show'    => $v3['is_show'],
								'is_menu'    => $v3['is_menu'],
								'sort_id'    => 1000-$k3,
								'source_by'  => $config['menu']['source_by'],
								'hash'       => self::MenuHash($v3,$config['menu']['source_by']),
							];
						}
					}
				}
			}
		}
		DB::beginTransaction();
		try {
			DB::statement('SET FOREIGN_KEY_CHECKS = 0');
			DB::table('menus')->insert($menus);
			DB::statement('SET FOREIGN_KEY_CHECKS = 1');
			DB::commit();
			Menu::fixTree();
			return Menu::where('source_by',$config['menu']['source_by'])->get();
		} catch (Exception $exception) {
			DB::rollback();
			throw $exception;
		}
	}

	private static function MenuHash($data,$source_by): string
	{
		$data['source_by'] = $source_by;
		if (isset($data['children'])) unset($data['children']);
		if (isset($data['icon'])) unset($data['icon']);

		$str = implode('_',$data);
		return hash('md5',$str);
	}

	private static function GenerateUrl($route): string
	{
		if (!$route) return 'javascript:void(0);';
		return '/'.(str_replace('.','/',$route));
	}

}
