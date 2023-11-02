<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
		if ($plugin->isDisabled()) {
			$plugin->enable();

			$this->info("Plugin [{$plugin}] enabled successful.");
			if ($plugin->config()['menu']['status']) {
				$this->insertMenu($plugin);
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
					$this->insertMenu($plugin);
				}
			} else {
				$this->comment("Plugin [{$plugin}] has already enabled.");
			}
		}
	}
	/**
	 * 添加菜单到系统中
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function insertMenu($plugin)
	{
		$menu_file = $plugin->getPath().'/'.$plugin->config()['menu']['file'];
		$menu_data = json_decode(file_get_contents($menu_file),true);
		$newNodes = self::createMenus($menu_data,$plugin->config());
		$after_menu_hash = $plugin->config()['menu']['after_hash'];
		if (!!$after_menu_hash) {
			// 在指定根节点或一级节点内添加
			$targetNode = Menu::withDepth()->where('hash',$after_menu_hash)->first();
			if (!$targetNode->isRoot()) {
				// 不是根节点
				$targetNode = $targetNode->getRoot();
			}
		} else {
			// 在所有节点后添加 查询最后一个根节点
			$targetNode = Menu::withDepth()->having('depth', '=', 0)->orderBy('id','desc')->first();
		}
		foreach ($newNodes as $newNode) {
			if (!$newNode->parent_id) {
				$nodeId = $newNode->id;
				// $newNode移动到$targetNode后面
				$newNode->insertAfterNode($targetNode);
				$targetNode = Menu::find($nodeId);
			}
		}
	}

	/**
	 * @throws \Exception
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
		//关闭外键约束
		DB::statement('SET FOREIGN_KEY_CHECKS = 0');
		DB::table('menus')->insert($menus);
		DB::statement('SET FOREIGN_KEY_CHECKS = 1');
		DB::commit();
		//修复树结构
		Menu::fixTree();
		return Menu::where('source_by',$config['menu']['source_by'])->get();
	}

	private static function MenuHash($data,$source_by)
	{
		$data['source_by'] = $source_by;
		if (isset($data['children'])) unset($data['children']);
		if (isset($data['icon'])) unset($data['icon']);

		$str = implode('_',$data);
		return hash('md5',$str);
	}

	private static function GenerateUrl($route)
	{
		if (!$route) return 'javascript:void(0);';
		return '/'.(str_replace('.','/',$route));
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
}
