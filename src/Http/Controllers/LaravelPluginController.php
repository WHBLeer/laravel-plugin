<?php
namespace Sanlilin\LaravelPlugin\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command as Console;
use Sanlilin\LaravelPlugin\Support\Plugin;
use Sanlilin\LaravelPlugin\Support\Config;
use Sanlilin\LaravelPlugin\Support\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Foundation\Application;
use Sanlilin\LaravelPlugin\Support\CompressPlugin;
use Sanlilin\LaravelPlugin\Contracts\ActivatorInterface;
use Sanlilin\LaravelPlugin\Support\Composer\ComposerRemove;
use Sanlilin\LaravelPlugin\Support\Publishing\AssetPublisher;
use Sanlilin\LaravelPlugin\Exceptions\CompressPluginException;
use Sanlilin\LaravelPlugin\Support\Generators\LocalInstallGenerator;

class LaravelPluginController extends Controller
{
	/**
	 * artisan plugin:list
	 * Show list of all plugins.
	 * 显示所有插件的列表。
	 *
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function list(Request $request)
	{
		switch ($request->status) {
			case 'enabled':
				$plugins = app('plugins.repository')->getByStatus(1);
				break;
			case 'disabled':
				$plugins = app('plugins.repository')->getByStatus(0);
				break;
			default:
				$plugins = app('plugins.repository')->all();
				break;
		}
		$status = $request->status??'all';
		$collection = collect();
		/** @var Plugin $plugin */
		foreach ($plugins as $plugin) {
			$logo = $plugin->getPath().'/Resources/assets'.$plugin->get('logo');
			if(!file_exists($logo)){
				$logo_src = asset('assets/plugin/'.$plugin->getLowerName().'/'.$plugin->get('logo'));
			} else {
				$logo_src = default_img();
			}
			$readme = $plugin->getPath().'/readme.md';
			if(file_exists($readme)){
				$readme_html = (string) Markdown::parse(file_get_contents($readme));
			} else {
				$readme_html = "<p>{$plugin->getDescription()}</p>";
			}
			$item = [
				'name' => $plugin->getName(),
				'alias' => $plugin->getAlias(),
				'version' => $plugin->get('version'),
				'description' => $plugin->getDescription(),
				'status' => $plugin->isEnabled() ? 'Enabled' : 'Disabled',
				'priority' => $plugin->get('priority'),
				'path' => $plugin->getPath(),
				'logo' => $logo_src,
				'author' => $plugin->get('author'),
				'readme' => $readme_html
			];
			$collection->push($item);
		}
		// 排序和分页操作
		$perPage = $request->per_page ?? 20; // 每页显示的数量
		$page = Paginator::resolveCurrentPage('page'); // 获取当前页码，默认为 'page'
		// 排序
		$sorted = $collection->sortBy('name', SORT_REGULAR, 'desc')->values();
		// 分页
		$sliced = $sorted->slice(($page - 1) * $perPage, $perPage);
		$data = new LengthAwarePaginator(
			$sliced,
			$sorted->count(),
			$perPage,
			$page,
			['path' => Paginator::resolveCurrentPath()]
		);

		$enabled = count(app('plugins.repository')->getByStatus(1));
		$disabled = count(app('plugins.repository')->getByStatus(0));
		$all = count(app('plugins.repository')->all());
		return view('laravel-plugin::list',compact('data','status','enabled','disabled','all'));
	}

	/**
	 * 插件市场列表。
	 *
	 * @param Request $request
	 *
	 * @return Application|Factory|View
	 * @throws Exception
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function market(Request $request)
	{
		if (! Config::get('token')) {
			throw new Exception("Please authenticate using the 'login' command before proceeding.");
		}
		$plugins = data_get(app('plugins.client')->plugins(1), 'data');
		$rows = array_reduce($plugins, function ($rows, $item) {
			$rows[] = [
				count($rows),
				$item['name'],
				$item['author'],
				$item['download_times'],
			];

			return $rows;
		}, []);
		foreach ($rows as $sn => &$item) {
			$plugin = data_get($plugins, $sn);
			array_map(fn ($version) => [
				$version['id'],
				$version['version'],
				$version['description'],
				$version['download_times'],
				$version['status_str'],
				$version['price'],
			], data_get($plugin, 'versions'));
		}
		return view('laravel-plugin::market',compact('rows'));
	}

	/**
	 * artisan plugin:disable
	 * Disable the specified plugin.
	 * 禁用指定的插件。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function disable(Request $request)
	{
		/** @var Plugin $plugin */
		$plugin = app('plugins.repository')->findOrFail($request->plugin);

		if ($plugin->isEnabled()) {
			$plugin->disable();

			return $this->jsonSuccess("Plugin [{$plugin}] disabled successful.");
		} else {
			return $this->jsonSuccess("Plugin [{$plugin}] has already disabled.");
		}
	}

	/**
	 * artisan plugin:enable
	 * Enable the specified plugin.
	 * 启用指定的插件。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function enable(Request $request)
	{
		/** @var Plugin $plugin */
		$plugin = app('plugins.repository')->findOrFail($request->plugin);

		if ($plugin->isDisabled()) {
			$plugin->enable();

			return $this->jsonSuccess("Plugin [{$plugin}] enabled successful.");
		} else {
			return $this->jsonSuccess("Plugin [{$plugin}] has already enabled.");
		}
	}

	/**
	 * artisan plugin:delete
	 * Delete a plugin from the application
	 * 从应用程序中删除插件
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function delete(Request $request)
	{
		try {
			/** @var Plugin $plugin */
			$plugin = app('plugins.repository')->findOrFail($request->plugin);

			ComposerRemove::make()->appendRemovePluginRequires(
				$plugin->getStudlyName(),
				$plugin->getAllComposerRequires()
			)->run();

			$plugin->delete();

			return $this->jsonSuccess("Plugin {$request->plugin} has been deleted.");
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * 批量处理插件
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function batch(Request $request)
	{
		dd($request->all());
		try {
			/** @var Plugin $plugin */
			$plugin = app('plugins.repository')->findOrFail($request->plugin);

			ComposerRemove::make()->appendRemovePluginRequires(
				$plugin->getStudlyName(),
				$plugin->getAllComposerRequires()
			)->run();

			$plugin->delete();

			return $this->jsonSuccess("Plugin {$request->plugin} has been deleted.");
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * artisan plugin:install
	 * Install the plugin through the file directory.
	 * 通过文件目录安装插件。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse|int
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function install(Request $request)
	{
		$path = $request->path;
		try {
			$code = LocalInstallGenerator::make()
				->setLocalPath($path)
				->setFilesystem(app('files'))
				->setPluginRepository(app('plugins.repository'))
				->setActivator(app(ActivatorInterface::class))
				->setActive(false)
				->setConsole(new Console())
				->generate();

			return $code;
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * artisan plugin:publish
	 * Publish a plugin's assets to the application
	 * 将插件的资产发布到应用程序中
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function publish(Request $request)
	{
		$plugin = app('plugins.repository')->findOrFail($request->plugin);
		with(new AssetPublisher($plugin))
			->setRepository(app('plugins.repository'))
			->setConsole(new Console())
			->publish();

		return $this->jsonSuccess("Plugin {$plugin->getStudlyName()} published successfully");
	}

	/**
	 * artisan plugin:register
	 * register to the plugin server.
	 * 注册到插件市场。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function register(Request $request)
	{
		try {
			$name = $request->name;
			$account = $request->account;
			$password = $request->password;
			if (Str::length($password) < 8) {
				return $this->jsonError('The password must be at least 8 characters.');
			}

			$result = app('plugins.client')->register(
				$account,
				$name,
				$password,
				$password
			);

			$token = data_get($result, 'token');
			Config::set('token', $token);

			return $this->jsonSuccess('Authenticated successfully.'.PHP_EOL);
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * artisan plugin:login
	 * Login to the plugin server.
	 * 登录到插件市场。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function login(Request $request)
	{
		try {
			$result = app('plugins.client')->login(
				$email = $request->account,
				$password = $request->password
			);
			$token = data_get($result, 'token');
			Config::set('token', $token);

			return $this->jsonSuccess('Authenticated successfully.'.PHP_EOL);
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * artisan plugin:upload
	 * Upload the plugin to the server.
	 * 将插件上传到插件市场。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @throws CompressPluginException
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function upload(Request $request)
	{
		try {
			if (! Config::get('token')) {
				return $this->jsonError("Please authenticate using the 'login' command before proceeding.");
			}

			/** @var Plugin $plugin */
			$plugin = app('plugins.repository')->findOrFail($request->plugin);

			Log::info("Plugin {$plugin->getStudlyName()} starts to compress");

			if (! (new CompressPlugin($plugin))->handle()) {
				return $this->jsonError("Plugin {$plugin->getStudlyName()} compression Failed");
			}
			Log::info("Plugin {$plugin->getStudlyName()} compression completed");

			$compressPath = $plugin->getCompressFilePath();

			$stream = fopen($compressPath, 'r+');

			try {
				app('plugins.client')->upload([
					'body' => $stream,
					'headers' => ['plugin-info' => json_encode($plugin->json()->getAttributes(), true)],
					'progress' => 0,
				]);
			} catch (\Exception $exception) {
				return $this->jsonError('Plugin upload failed : '.$exception->getMessage());
			}

			app('files')->delete($compressPath);

			if (is_resource($stream)) {
				fclose($stream);
			}
			return $this->jsonSuccess('Plugin upload completed');
		} catch (\Mockery\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}


	/**
	 * Download plugin from server to local.
	 * 从插件市场获取插件版本。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function version(Request $request)
	{
		$path = Str::uuid().'.zip';
		try {
			if (! Config::get('token')) {
				return $this->jsonError("Please authenticate using the 'login' command before proceeding.");
			}
			$plugins = data_get(app('plugins.client')->plugins(1), 'data');
			$sn = $request->input_sn;

			if (! $plugin = data_get($plugins, $sn)) {
				return $this->jsonError(__("The plugin number: {$sn} does not exist"));
			}

			array_map(fn ($version) => [
				$version['id'],
				$version['version'],
				$version['description'],
				$version['download_times'],
				$version['status_str'],
				$version['price'],
			], data_get($plugin, 'versions'));

			$versionId = $request->input_version_id;

			if (! in_array($versionId, Arr::pluck($plugin['versions'], 'id'))) {
				return $this->jsonError(__("The plugin version: {$versionId} does not exist"));
			}

			Storage::put($path, app('plugins.client')->download($versionId));

			try {
				$code = LocalInstallGenerator::make()
					->setLocalPath(Storage::path($path))
					->setFilesystem(app('files'))
					->setPluginRepository(app('plugins.repository'))
					->setActivator(app(ActivatorInterface::class))
					->setActive(false)
					->setConsole(new Console())
					->generate();

				return $this->jsonError(__('Plugin downloaded successfully'));
			} catch (\Exception $exception) {
				return $this->jsonError($exception->getMessage());
			}
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		} finally {
			Storage::delete($path);
		}
	}

	/**
	 * artisan plugin:download
	 * Download plugin from server to local.
	 * 从插件市场下载插件到本地。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function download(Request $request)
	{
		$path = Str::uuid().'.zip';
		try {
			if (! Config::get('token')) {
				return $this->jsonError("Please authenticate using the 'login' command before proceeding.");
			}
			$plugins = data_get(app('plugins.client')->plugins(1), 'data');
			$sn = $request->input_sn;

			if (! $plugin = data_get($plugins, $sn)) {
				return $this->jsonError(__("The plugin number: {$sn} does not exist"));
			}

			array_map(fn ($version) => [
				$version['id'],
				$version['version'],
				$version['description'],
				$version['download_times'],
				$version['status_str'],
				$version['price'],
			], data_get($plugin, 'versions'));

			$versionId = $request->input_version_id;

			if (! in_array($versionId, Arr::pluck($plugin['versions'], 'id'))) {
				return $this->jsonError(__("The plugin version: {$versionId} does not exist"));
			}

			Storage::put($path, app('plugins.client')->download($versionId));

			try {
				$code = LocalInstallGenerator::make()
					->setLocalPath(Storage::path($path))
					->setFilesystem(app('files'))
					->setPluginRepository(app('plugins.repository'))
					->setActivator(app(ActivatorInterface::class))
					->setActive(false)
					->setConsole(new Console())
					->generate();

				return $this->jsonError(__('Plugin downloaded successfully'));
			} catch (\Exception $exception) {
				return $this->jsonError($exception->getMessage());
			}
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		} finally {
			Storage::delete($path);
		}
	}
}