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

	/**
	 * 自定义错误信息返回
	 * @param $message
	 * @return JsonResponse
	 *
	 * @author: hongbinwang
	 * @time: 2023/8/21 17:41
	 */
	public function jsonError($message): JsonResponse
	{
		Session::flash('json-error', $message);
		$error['errors']['error'] = $message;
		return response()->json($error, 421);
	}

	/**
	 * 自定义成功信息返回
	 * @param $message
	 * @return JsonResponse
	 *
	 * @author: hongbinwang
	 * @time: 2023/8/21 17:41
	 */
	public function jsonSuccess($message): JsonResponse
	{
		Session::flash('json-success', $message);
		return response()->json($message);
	}


}
