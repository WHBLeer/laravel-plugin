// 封装统一的 AJAX 请求方法
function pluginActionHandler() {
	return function () {
		const $btn = $(this);
		const row = $btn.data('row');
		
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		
		$.ajax({
			url: $btn.data('action'),
			type: 'POST',
			data: { plugin: row.name },
			beforeSend: function () {
				Loading();
			},
			success: function (response) {
				Loading();
				Sweet('success', response.message || '操作成功');
				setTimeout(function () {
					location.reload();
				}, 1500);
			},
			error: function (xhr) {
				Loading();
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					$.each(xhr.responseJSON.errors, function (key, item) {
						Sweet('error', item);
					});
				} else {
					Sweet('error', '操作失败，请重试。');
				}
			}
		});
	};
}

// 绑定事件
$(document).ready(function () {
	$('.enable-btn, .disable-btn, .restart-btn, .delete-btn').on('click', pluginActionHandler());
});
