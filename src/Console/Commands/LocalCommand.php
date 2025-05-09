<?php

namespace Sanlilin\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelPlugin\Traits\PluginCommandTrait;
use Sanlilin\LaravelPlugin\Contracts\ActivatorInterface;
use Sanlilin\LaravelPlugin\Support\Generators\LocalInstallGenerator;
use ZipArchive;

class LocalCommand extends Command
{
	use PluginCommandTrait;

	protected $name = 'plugin:local';

	protected $description = 'Install the locally uploaded plugin.';

	public function handle(): int
	{
		$zipPath = $this->argument('path');

		try {
			// 创建临时目录用于解压
			$tempDir = dirname($zipPath).'/'.pathinfo($zipPath, PATHINFO_FILENAME);

			// 解压ZIP文件
			$zip = new ZipArchive;
			if ($zip->open($zipPath) === true) {
				$zip->extractTo($tempDir);
				$zip->close();
			} else {
				throw new \Exception("Failed to open the ZIP file: {$zipPath}");
			}

			// 使用解压后的目录进行安装
			$code = LocalInstallGenerator::make()
				->setLocalPath($tempDir)
				->setFilesystem($this->laravel['files'])
				->setPluginRepository($this->laravel['plugins.repository'])
				->setActivator($this->laravel[ActivatorInterface::class])
				->setActive(! $this->option('disabled'))
				->setConsole($this)
				->generate();

			return $code;
		} catch (\Exception $exception) {
			$this->error($exception->getMessage());
			return E_ERROR;
		} finally {
			// 清理：删除ZIP文件和解压的临时目录
			if (isset($zipPath) && file_exists($zipPath)) {
				unlink($zipPath);
			}
			if (isset($tempDir) && is_dir($tempDir)) {
				$this->laravel['files']->deleteDirectory($tempDir);
			}
		}
	}

	protected function getArguments(): array
	{
		return [
			['path', InputArgument::REQUIRED, 'Local path of the ZIP file.'],
		];
	}

	protected function getOptions(): array
	{
		return [
			['disabled', 'd', InputOption::VALUE_NONE, 'Do not enable the plugin at creation.'],
			['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the plugin already exists.'],
		];
	}
}