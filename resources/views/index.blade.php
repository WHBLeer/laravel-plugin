@extends('layout.master')
@section('title', 'Plugins Management')
@section('css')
	<!-- glight css -->
	<link rel="stylesheet" href="{{asset('assets/vendor/glightbox/glightbox.min.css')}}">
	<link rel="stylesheet" href="{{asset('assets/vendor/plugins/style.css')}}">
@endsection
@section('main-content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="tab-wrapper mb-3">
					<ul class="tabs">
						<li class="tab-link active" data-tab="1">All Plugin</li>
						<li class="tab-link" data-tab="2">Enabled Plugin</li>
						<li class="tab-link" data-tab="3">Disabled Plugin</li>
						<li class="ms-auto">
							<div class="text-end">
								<button class="btn btn-primary w-45 h-45 icon-btn b-r-10 m-2" data-bs-toggle="modal" data-bs-target="#uploadPluginModal"><i class="ti ti-upload f-s-18"></i></button>
							</div>
						</li>
					</ul>
				</div>
				<div class="content-wrapper" id="card-container">
					<div id="tab-1" class="tabs-content active">
						<div class="row ">
							@foreach($plugins as $plugin)
								@include('plugins::partials.plugin-card', ['plugin' => $plugin])
							@endforeach
						</div>
					</div>
					<div id="tab-2" class="tabs-content">
						<div class="row ">
							@foreach($plugins as $plugin)
								@include('plugins::partials.plugin-card', ['plugin' => $plugin])
							@endforeach
						</div>
					</div>
					<div id="tab-3" class="tabs-content">
						<div class="row ">
							@foreach($plugins as $plugin)
								@include('plugins::partials.plugin-card', ['plugin' => $plugin])
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('modal')
	<div class="modal fade" id="uploadPluginModal" tabindex="-1" aria-labelledby="uploadPluginModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="uploadPluginModalLabel">Modal title</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form class="app-form" id="upload-form" action="{{ route('admin.plugin.local') }}" method="POST" enctype="multipart/form-data">
						@csrf
						<div class="form-group">
							<label for="pluginFile">Plugin ZIP File</label>
							<input type="file" class="form-control-file" id="pluginFile" accept=".zip" name="plugin_zip" required>
							<small class="form-text text-muted">Upload a valid plugin ZIP package</small>
						</div>
						<div class="form-check mb-3 d-flex gap-1">
							<input class="form-check-input mg-2" type="checkbox" value="" name="enable" id="checkDefault">
							<label class="form-check-label" for="checkDefault">
								Enable
							</label>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="uploadPlugin">Upload & Install</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('script')
	<!--customizer-->
	<div id="customizer"></div>
	
	<!-- Glight js -->
	<script src="{{asset('assets/vendor/glightbox/glightbox.min.js')}}"></script>
	
	<!--js-->
	<script src="{{asset('assets/vendor/plugins/script.js')}}"></script>
	
@endsection
