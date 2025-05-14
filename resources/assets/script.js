function pluginActionHandler() {
	return function () {
		const $btn = $(this);
		const token = $('meta[name="csrf-token"]').attr('content');
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': token
			}
		});
		
		$.ajax({
			url: $btn.data('action'),
			type: 'POST',
			data: {_token: token},
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
	$('.tab-link').on('click',function () {
		var tabID = $(this).attr('data-tab');
		$(this).addClass('active').siblings().removeClass('active');
		$('#tab-'+tabID).addClass('active').siblings().removeClass('active');
	});
	
	$('.enable-btn, .disable-btn, .restart-btn, .delete-btn').on('click', pluginActionHandler());
	
	// 处理插件上传安装逻辑
	$('#uploadPlugin').on('click', function (e) {
		e.preventDefault(); // 阻止默认表单提交
		
		const form = $('#upload-form')[0];
		const formData = new FormData(form);
		
		$.ajax({
			url: form.action,
			type: 'POST',
			data: formData,
			processData: false,  // 不处理数据
			contentType: false,  // 不设置内容类型
			beforeSend: function () {
				Loading();
			},
			success: function (response) {
				Loading();
				Sweet('success', response.message || '插件上传并安装成功');
				setTimeout(function () {
					location.reload();
				}, 1500);
			},
			error: function (xhr) {
				Loading();
				if (xhr.status === 422) {
					const errors = xhr.responseJSON.errors;
					$.each(errors, function (key, value) {
						Sweet('error', value[0]);
					});
				} else {
					Sweet('error', '插件安装失败，请检查 ZIP 格式是否正确。');
				}
			}
		});
	});
	
	$('.plugin-detail').on('click', function () {
		const pluginData = $(this).data('row');
		$('#pluginDetailDesc').html(pluginData.readme||pluginData.description);
		$('#pluginDetailModalTitle').text(pluginData.name);
		$('#pluginDetailModal').modal('show');
	});
	
});
