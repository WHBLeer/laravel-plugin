@extends('layouts.app')
@section('head')
    @include('layouts.partials.header_section',['title'=>__('Native Plugin'),'new_btn'=>'checkKey(this)'])
@endsection
@section('content')
    @include('layouts.partials.min_static',[
				'lists' => [
					[
						'active' => $status=='all'?'active':'',
						'href' => route('seller.plugin.list'),
						'title' => __('All'),
						'badge' => $all,
					],
					[
						'active' => $status=='enabled'?'active':'',
						'href' => route('seller.plugin.list','status=enabled'),
						'title' => __('Enabled'),
						'badge' => $enabled,
					],
					[
						'active' => $status=='disabled'?'active':'',
						'href' => route('seller.plugin.list','status=disabled'),
						'title' => __('Disabled'),
						'badge' => $disabled,
					],
			]])
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('layouts.partials.search_form',[
						'batch_form_link'=>route('seller.plugin.batch'),
						'batch_options' => [
							'enable' => __('Batch Enable'),
							'disable' => __('Batch Disable'),
							'delete' => __('Delete Permanently'),
						],
					])
                    <div class="table-responsive custom-table">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="am-select" width="10%">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input checkAll" id="customCheck12">
                                        <label class="custom-control-label checkAll" for="customCheck12"></label>
                                    </div>
                                </th>
                                <th class="text-left">{{ __('Logo') }}</th>
                                <th class="text-left">{{ __('Name') }}</th>
                                <th class="text-left">{{ __('Description') }}</th>
                                <th>{{ __('Version') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-right">{{ __('Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $row)
                                <tr>
                                    <th>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="ids[]" class="custom-control-input" id="customCheck{{ $row['name'] }}" value="{{ $row['name'] }}">
                                            <label class="custom-control-label" for="customCheck{{ $row['name'] }}"></label>
                                        </div>
                                    </th>
                                    <td><img src="{{ asset($row['logo']) }}" height="50" alt="{{ $row['name'] }}" ></td>
                                    <td>
                                        <a href="javascript:void(0);" onclick='pluginDetail(this)' data-row='{{json_encode($row)}}'>{{ $row['name'] }}</a><br/>
										<a href="{{ $row['author']['home'] }}" target="_blank" class="text-secondary mb-2">{{ $row['author']['name'] }}&lt;{{ $row['author']['email'] }}&gt;</a>
                                    </td>
                                    <td>{{ $row['description'] }}</td>
                                    <td><span class="badge badge-info">V{{ $row['version'] }}</span></td>
                                    <td>
                                        @if($row['status'] == 'Enabled') <span class="badge badge-success">{{ __('Enable') }}</span>
                                        @else <span class="badge badge-warning">{{ __('Disable') }}</span>@endif
                                    </td>
                                    <td class="text-right">
                                        @if($row['status'] == 'Enabled')
                                            <a href="javascript:void(0);" class="btn btn-sm btn-plugin btn-warning disable-plugin" data-name="{{$row['name']}}">{{ __('Disable') }}</a>
                                        @else
                                            <a href="javascript:void(0);" class="btn btn-sm btn-plugin btn-success enable-plugin" data-name="{{$row['name']}}">{{ __('Enable') }}</a>
                                        @endif
                                        <a href="javascript:void(0);" class="btn btn-sm plugin-status btn-danger delete-plugin" data-name="{{$row['name']}}">{{ __('Delete') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('modal')
    <div class="modal fade" id="pluginDetail" tabindex="-1" aria-labelledby="pluginDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pluginDetailLabel">{{ __('Plugin Detail') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row">
                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                        <img class="plugin-image" id="plugin-image" src="" alt="" width="100%"/>
                    </div>
                    <div class="col-12 col-sm-9 col-md-9 col-lg-9">
                        <h4><a href="" target="_blank" id="plugin-title"></a></h4>
                        <div class="plugin-description" style="min-height: 100px;">
                            <p id="plugin-description"></p>
                        </div>
                        <div class="plugin-action text-right">
                            <a href="javascript:void(0);" class="btn btn-sm plugin-status btn-success enable-plugin" data-name="" id="plugin-status"></a>
                            <a href="javascript:void(0);" class="btn btn-sm plugin-status btn-danger delete-plugin" data-name="">{{ __('Delete') }}</a>
                        </div>
                    </div>
                    <div id="plugin-readme" class="plugin-readme"></div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endpush
@push('js')
<script>
	function pluginDetail(obj) {
        let item = $(obj).data('row');
        console.log(item);
        $('#plugin-image').attr('src',item.logo);
        $('#plugin-title').text(item.name);
        $('#plugin-title').attr('href',item.home);
        $('#plugin-description').text(item.description);
        if (item.status == 'Enabled') {
            $('#plugin-status').text('{{ __('Disable') }}').removeClass('btn-success').removeClass('enable-plugin').addClass('btn-warning').addClass('disable-plugin');
        } else {
            $('#plugin-status').text('{{ __('Enable') }}').removeClass('btn-warning').removeClass('disable-plugin').addClass('btn-success').addClass('enable-plugin');
        }
        $('.plugin-status').attr('name',item.home);
        $('#plugin-readme').html(item.readme);
		$('#pluginDetail').modal('show');
	}
	$('.enable-plugin').click(function() {
		const name = $(this).data('name');
		const btn = $('.btn-plugin');
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: "{{ route('seller.plugin.enable') }}",
			type: 'POST',
			data: {
				plugin: name,
				_token: "{{ csrf_token() }}"
			},
			beforeSend: function() {
				btn.attr('disabled','').addClass('btn-progress')
				Loading();
			},
			success: function(response) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				Sweet('success',response);
				setTimeout(function(){
					location.reload();
				}, 1500);
			},
			error: function(xhr, status, error) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				$.each(xhr.responseJSON.errors, function (key, item) {
					Sweet('error',item)
				});
			}
		})
	})
	$('.disable-plugin').click(function() {
		const name = $(this).data('name');
		const btn = $('.btn-plugin');
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: "{{ route('seller.plugin.disable') }}",
			type: 'POST',
			data: {
				plugin: name,
				_token: "{{ csrf_token() }}"
			},
			beforeSend: function() {
				btn.attr('disabled','').addClass('btn-progress')
				Loading();
			},
			success: function(response) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				Sweet('success',response);
				setTimeout(function(){
					location.reload();
				}, 1500);
			},
			error: function(xhr, status, error) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				$.each(xhr.responseJSON.errors, function (key, item) {
					Sweet('error',item)
				});
			}
		})
	})
	$('.delete-plugin').click(function() {
		const name = $(this).data('name');
		const btn = $('.btn-plugin');
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: "{{ route('seller.plugin.delete') }}",
			type: 'POST',
			data: {
				plugin: name,
				_token: "{{ csrf_token() }}"
			},
			beforeSend: function() {
				btn.attr('disabled','').addClass('btn-progress')
				Loading();
			},
			success: function(response) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				Sweet('success',response);
				setTimeout(function(){
					location.reload();
				}, 1500);
			},
			error: function(xhr, status, error) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				$.each(xhr.responseJSON.errors, function (key, item) {
					Sweet('error',item)
				});
			}
		})
	})
</script>
@endpush
