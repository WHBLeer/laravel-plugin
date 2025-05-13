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
use Illuminate\Support\Facades\File;
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
	public function index(Request $request)
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
				$logo_src = asset('assets/vender/plugins/'.$plugin->getLowerName().'/'.$plugin->get('logo'));
			} else {
				$logo_src = plugin_logo($plugin->getStudlyName(),false);
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
		return view('plugins::index',compact('data','status','enabled','disabled','all'));
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
		return view('plugins::market',compact('rows'));
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

			return $this->respond('success', "Plugin [{$plugin}] disabled successful.");
		} else {
			return $this->respond('success', "Plugin [{$plugin}] has already disabled.");
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

			return $this->respond('success', "Plugin [{$plugin}] enabled successful.");
		} else {
			return $this->respond('success', "Plugin [{$plugin}] has already enabled.");
		}
	}

	/**
	 * @param Request $request
	 * @return JsonResponse|\Illuminate\Http\RedirectResponse
	 *
	 * @author: hongbinwang
	 * @time  : 2025/5/13 下午8:39
	 */
	public function restart(Request $request)
	{
		/** @var Plugin $plugin */
		$plugin = app('plugins.repository')->findOrFail($request->plugin);
		if ($plugin->isEnabled()) {
			$plugin->disable();
		}
		$plugin->enable();
		return $this->respond('success', "Plugin [{$plugin}] restarted successful.");
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

			return $this->respond('success', "Plugin {$request->plugin} has been deleted.");
		} catch (\Exception $exception) {
			return $this->respond('error', $exception->getMessage());
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
			return $this->respond('error', $exception->getMessage());
		}
	}

	/**
	 * 本地上传插件安装包.zip进行安装
	 * @param Request $request
	 * @return JsonResponse|\Illuminate\Http\RedirectResponse
	 *
	 * @author: hongbinwang
	 * @time  : 2025/5/13 下午8:34
	 */
	public function local(Request $request)
	{
		try {
			$request->validate([
				'plugin_zip' => 'required|file|mimes:zip|max:102400' // 限制100MB
			]);

			if (!File::exists(storage_path('app/plugins/temp'))) {
				File::makeDirectory(storage_path('app/plugins/temp'));
			}
			$tempZipPath = $request->file('plugin_zip')->storeAs(
				'plugins/temp',
				Str::uuid().'.zip',
				'local'
			);

			$extractPath = storage_path('app/plugins/temp/' . Str::uuid());
			$zip = new \ZipArchive;

			if ($zip->open(storage_path('app/' . $tempZipPath))) {
				$zip->extractTo($extractPath);
				$zip->close();
			} else {
				throw new \Exception("Failed to open ZIP file");
			}

			$pluginJsonPath = $extractPath . '/plugin.json';
			if (!file_exists($pluginJsonPath)) {
				throw new \Exception("plugin.json not found in root directory");
			}

			$pluginConfig = json_decode(file_get_contents($pluginJsonPath), true);
			$requiredKeys = ['name', 'alias', 'version', 'providers', 'author'];

			foreach ($requiredKeys as $key) {
				if (!array_key_exists($key, $pluginConfig)) {
					throw new \Exception("plugin.json missing required key: $key");
				}
			}

			$code = LocalInstallGenerator::make()
				->setLocalPath($extractPath)
				->setFilesystem(app('files'))
				->setPluginRepository(app('plugins.repository'))
				->setActivator(app(ActivatorInterface::class))
				->setActive(false)
				->setConsole(new Console())
				->generate();

			// 7. 清理临时文件
			app('files')->delete(storage_path('app/' . $tempZipPath));
			app('files')->deleteDirectory($extractPath);

			return $this->respond('success', "Plugin installed successfully");

		} catch (\Exception $e) {
			// 错误时清理残留文件
			isset($tempZipPath) && app('files')->delete(storage_path('app/' . $tempZipPath));
			isset($extractPath) && app('files')->deleteDirectory($extractPath);

			return $this->respond('error', "Installation failed: " . $e->getMessage());
		}
	}

	/**
	 * @param Request $request
	 * @return JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
	 *
	 * @author: hongbinwang
	 * @time  : 2025/5/13 下午10:05
	 */
	public function pack_up(Request $request)
	{
		try {
			$pluginName = $request->plugin;

			/** @var Plugin $plugin */
			$plugin = app('plugins.repository')->findOrFail($pluginName);

			Log::channel('plugin')->info("开始打包插件: {$plugin->getStudlyName()}");

			// 使用压缩处理器
			$compressHandler = new CompressPlugin($plugin);
			if (!$compressHandler->handle()) {
				throw new \Exception("插件压缩失败");
			}

			$zipPath = $plugin->getCompressFilePath();

			// 设置下载文件名
			$downloadName = sprintf('%s-v%s.zip',
				$plugin->getLowerName(),
				$plugin->get('version')
			);

			// 返回文件下载响应
			return response()
				->download($zipPath, $downloadName)
				->deleteFileAfterSend(true);

		} catch (\Exception $e) {
			Log::channel('plugin')->error("插件打包失败: " . $e->getMessage());
			return $this->respond('error', $e->getMessage(), [], 500);
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

		return $this->respond('success', "Plugin {$plugin->getStudlyName()} published successfully");
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
				return $this->respond('error', 'The password must be at least 8 characters.');
			}

			$result = app('plugins.client')->register(
				$account,
				$name,
				$password,
				$password
			);

			$token = data_get($result, 'token');
			Config::set('token', $token);

			return $this->respond('success', 'Authenticated successfully.'.PHP_EOL);
		} catch (\Exception $exception) {
			return $this->respond('error', $exception->getMessage());
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

			return $this->respond('success', 'Authenticated successfully.'.PHP_EOL);
		} catch (\Exception $exception) {
			return $this->respond('error', $exception->getMessage());
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
				return $this->respond('error', "Please authenticate using the 'login' command before proceeding.");
			}

			/** @var Plugin $plugin */
			$plugin = app('plugins.repository')->findOrFail($request->plugin);

			Log::channel('plugins')->info("Plugin {$plugin->getStudlyName()} starts to compress");

			if (! (new CompressPlugin($plugin))->handle()) {
				return $this->respond('error', "Plugin {$plugin->getStudlyName()} compression Failed");
			}
			Log::channel('plugins')->info("Plugin {$plugin->getStudlyName()} compression completed");

			$compressPath = $plugin->getCompressFilePath();

			$stream = fopen($compressPath, 'r+');

			try {
				app('plugins.client')->upload([
					'body' => $stream,
					'headers' => ['plugin-info' => json_encode($plugin->json()->getAttributes(), true)],
					'progress' => 0,
				]);
			} catch (\Exception $exception) {
				return $this->respond('error', 'Plugin upload failed : '.$exception->getMessage());
			}

			app('files')->delete($compressPath);

			if (is_resource($stream)) {
				fclose($stream);
			}
			return $this->respond('success', 'Plugin upload completed');
		} catch (\Mockery\Exception $exception) {
			return $this->respond('error', $exception->getMessage());
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
				return $this->respond('error', "Please authenticate using the 'login' command before proceeding.");
			}
			$plugins = data_get(app('plugins.client')->plugins(1), 'data');
			$sn = $request->input_sn;

			if (! $plugin = data_get($plugins, $sn)) {
				return $this->respond('error', __("The plugin number: {$sn} does not exist"));
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
				return $this->respond('error', __("The plugin version: {$versionId} does not exist"));
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

				return $this->respond('success', __('Plugin downloaded successfully'));
			} catch (\Exception $exception) {
				return $this->respond('error', $exception->getMessage());
			}
		} catch (\Exception $exception) {
			return $this->respond('error', $exception->getMessage());
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
				return $this->respond('error', "Please authenticate using the 'login' command before proceeding.");
			}
			$plugins = data_get(app('plugins.client')->plugins(1), 'data');
			$sn = $request->input_sn;

			if (! $plugin = data_get($plugins, $sn)) {
				return $this->respond('error', __("The plugin number: {$sn} does not exist"));
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
				return $this->respond('error', __("The plugin version: {$versionId} does not exist"));
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

				return $this->respond('success', __('Plugin downloaded successfully'));
			} catch (\Exception $exception) {
				return $this->respond('error', $exception->getMessage());
			}
		} catch (\Exception $exception) {
			return $this->respond('error', $exception->getMessage());
		} finally {
			Storage::delete($path);
		}
	}


	/**
	 * 批量处理插件（启用、禁用、删除、发布）
	 * @param Request $request
	 * @return JsonResponse|\Illuminate\Http\RedirectResponse
	 *
	 * @author: hongbinwang
	 * @time  : 2025/5/13 下午8:28
	 */
	public function batch(Request $request)
	{
		$plugins = explode(',', $request->input('plugins'));
		$operation = $request->input('operation');

		// 支持的操作类型映射到对应方法
		$allowedOperations = [
			'enable' => 'enable',
			'disable' => 'disable',
			'restart' => 'restart',
			'delete' => 'delete',
			'publish' => 'publish',
		];

		if (!array_key_exists($operation, $allowedOperations)) {
			return $this->respond('error', 'Unsupported operation');
		}

		$results = [];
		foreach ($plugins as $pluginName) {
			// 调用对应的控制器方法
			$method = $allowedOperations[$operation];
			$response = $this->{$method}(new Request(['plugin' => $pluginName]));

			// 解析响应结果
			$responseData = json_decode($response->getContent(), true);
			$results[] = [
				'plugin' => $pluginName,
				'status' => $responseData['status'],
				'message' => $responseData['message'] ?? null,
			];
		}

		return $this->respond('success', "Batch operation '{$operation}' completed successfully.", ['results' => $results]);
	}

}