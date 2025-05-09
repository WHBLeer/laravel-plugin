<?php

namespace Sanlilin\LaravelPlugin\Http\Controllers;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends \Illuminate\Routing\Controller
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct()
	{
	}

	protected function respond($type = 'success', $message = null, $data = null)
	{
		if (request()->ajax() || request()->wantsJson()) {
			$response = [];

			if ($type === 'success') {
				$response['status'] = 'success';
				$response['message'] = $message ?? 'Operation successful.';
				if ($data !== null) {
					$response['data'] = $data;
				}
			} elseif ($type === 'error') {
				$response['status'] = 'error';
				$response['message'] = $message ?? 'An error occurred.';
			}

			return response()->json($response);
		}

		if ($type === 'success') {
			return redirect()->back()->with('success', $message);
		}

		return redirect()->back()->with('error', $message);
	}

}
