@extends('layouts.app')
@section('head')
@include('layouts.partials.header_section',['title'=>__('Plugin Market')])
@endsection
@section('content')
<div class="row">
    @foreach($rows as $row)
    <div class="col-12 col-sm-3 col-md-2">
        <article class="article article-style-b" style="padding: 20px 20px 0 20px">
            <div class="article-header">
                <div class="article-image" data-background="{{ asset($row['logo']) }}">
                </div>
				<div class="article-badge">
					<div class="article-badge-item bg-danger"><i class="fas fa-fire"></i> {{__('Trending')}}</div>
				</div>
            </div>
            <div class="article-details">
                <div class="article-title">
                    <h2><a href="{{$row['home']}}" target="_blank">{{ $row['name'] }}</a></h2>
                </div>
                <p>{{ $row['description'] }} </p>
                <div class="article-cta">
					@if(plugin_native($row['name']))
						@if($row['status'] == 'Enabled')
							<a href="javascript:void(0);" class="btn btn-plugin btn-danger disable-plugin">{{ __('Disable') }}</a>
						@else
							<a href="javascript:void(0);" class="btn btn-plugin btn-info enable-plugin" data-name="{{$row['name']}}">{{ __('Enable') }}</a>
						@endif
					@else
						<a href="javascript:void(0);" class="btn btn-plugin btn-success version-plugin" data-name="{{$row['name']}}">{{ __('Download') }}</a>
					@endif
                </div>
            </div>
        </article>
    </div>
    @endforeach
</div>
@endsection@push('modal')
	<!-- Modal -->
	<div class="modal fade" id="pluginVersionModal" tabindex="-1" aria-labelledby="pluginVersionModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content">
				<form method="post" action="{{ route('seller.plugin.download') }}" id="download_form">
					<div class="modal-header">
						<h5 class="modal-title">{{ __('Download Plugin') }}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						@csrf
						<input type="hidden" name="plugin_name" id="plugin_name">
						<div class="table-responsive custom-table">
							<table class="table">
								<thead>
								<tr>
									<th class="text-left">{{ __('id') }}</th>
									<th class="text-left">{{ __('version') }}</th>
									<th class="text-left">{{ __('description') }}</th>
									<th class="text-left">{{ __('download_times') }}</th>
									<th class="text-left">{{ __('price') }}</th>
								</tr>
								</thead>
								<tbody id="plugin-versions">
								</tbody>
							</table>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary btn-plugin" data-dismiss="modal">{{ __('Cancel') }}</button>
						<button type="submit" class="btn btn-primary btn-plugin">{{ __('Download') }}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endpush

@push('js')
<script>
	$('.enable-plugin').click(function() {
		const btn = $('.btn-plugin');
		const url = "{{ route('seller.plugin.enable') }}";
		const name = $(this).data('name');
		let data = {
			plugin: name,
		}
		plugin_submit(url,data,btn)
	})
	$('.disable-plugin').click(function() {
		const btn = $('.btn-plugin');
		const url = "{{ route('seller.plugin.disable') }}";
		let data = {
			plugin: $(this).data('name'),
		}
		plugin_submit(url,data,btn)
	})
	$('.version-plugin').click(function() {
		const btn = $('.btn-plugin');
		const url = "{{ route('seller.plugin.download') }}";
		let data = {
			plugin: $(this).data('name'),
			input_sn:$(this).data('input_sn')
		}
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: url,
			type: 'POST',
			data: data,
			beforeSend: function() {
				btn.attr('disabled','').addClass('btn-progress')
				Loading();
			},
			success: function(response) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				$('#download_form')[0].reset();
				$('#plugin_name').val(data.plugin);
				$('#plugin_input_sn').val(data.input_sn);
				if (response.data.length==1){
					$('#input_version_id').val(response.data[0].id);
					$('.download_form').submit();
				} else {
					$('#input_version_id').remove();
					let table = [];
					for (const datum of response.data) {
						let tr = [];
						tr.push('<tr>');
						tr.push('	<th>');
						tr.push('		<div class="custom-control custom-radio">');
						tr.push('			<input id="version_id'+datum.id+'" name="input_version_id" value="'+datum.id+'" type="radio" class="custom-control-input">');
						tr.push('			<label class="custom-control-label" for="version_id'+datum.id+'">'+datum.id+'</label>');
						tr.push('		</div>');
						tr.push('	</th>');
						tr.push('	<td><span class="badge badge-info">V'+datum.version+'</span></td>');
						tr.push('	<td>'+datum.description+'</td>');
						tr.push('	<td><span class="badge badge-success">'+datum.download_times+'</span></td>');
						tr.push('	<td>'+datum.price+'</td>');
						tr.push('</tr>');

						table.push(tr.join(''));
					}
					$('#plugin-versions').append(table.join(''));
					$('#pluginVersionModal').modal().show();
				}
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
	$('.download_form').submit(function(e) {
		const btn = $('.btn-plugin');
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: this.action,
			type: 'POST',
			data: new formData(this),
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

	function plugin_submit(url,data,btn)
	{
		data._token = "{{ csrf_token() }}";
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: url,
			type: 'POST',
			data: data,
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
	}
</script>
@endpush
