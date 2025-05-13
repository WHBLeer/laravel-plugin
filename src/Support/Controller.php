<?php

namespace Sanlilin\LaravelPlugin\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;


class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct()
	{
	}

	public function respond($type = 'success', $message = null, $data = null)
	{
		Session::flash('json-'.$type, $message);
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
