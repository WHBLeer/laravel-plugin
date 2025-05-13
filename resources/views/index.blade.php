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
						<li class="tab-link active" data-tab="all">All Plugin</li>
						<li class="tab-link" data-tab="enabled">Enabled Plugin</li>
						<li class="tab-link" data-tab="disabled">Disabled Plugin</li>
						<li class="ms-auto">
							<div class="text-end">
								<button class="btn btn-primary w-45 h-45 icon-btn b-r-10 m-2" data-bs-toggle="modal" data-bs-target="#uploadPluginModal"><i class="ti ti-upload f-s-18"></i></button>
							</div>
						</li>
					</ul>
				</div>
				<div class="content-wrapper" id="card-container">
					<div id="tab-all" class="tabs-content active">
						<div class="row ">
							@foreach($data as $plugin)
								@include('plugins::partials.plugin-card', ['plugin' => $plugin, 'status' => 'all'])
							@endforeach
						</div>
					</div>
					<div id="tab-enabled" class="tabs-content">
						<div class="row ">
							@foreach($data as $plugin)
								@if ($plugin['status'] == 'Enabled')
									@include('plugins::partials.plugin-card', ['plugin' => $plugin, 'status' => 'Enabled'])
								@endif
							@endforeach
						</div>
					</div>
					<div id="tab-disabled" class="tabs-content">
						<div class="row ">
							@foreach($data as $plugin)
								@if ($plugin['status'] == 'Disabled')
									@include('plugins::partials.plugin-card', ['plugin' => $plugin, 'status' => 'Disabled'])
								@endif
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('modal')
	<div class="modal fade" id="pluginDetailModal" tabindex="-1"
		 aria-hidden="true">
		<div class="modal-dialog app_modal_sm">
			<div class="modal-content">
				<div class="modal-header bg-primary-800">
					<h1 class="modal-title fs-5 text-white" id="pluginDetailModalTitle">Small Modal</h1>
					<button type="button" class="fs-5 border-0 bg-none  text-white" data-bs-dismiss="modal"
							aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
				</div>
				<div class="modal-body text-center">
					<div class="d-flex gap-2">
						<img src="" alt="" id="pluginDetailImage" class="rounded-pill object-fit-cover h-90 w-90 b-r-10">
						<div class="text-start d-flex flex-column gap-2">
							<h5 id="pluginDetailName"></h5>
							<p id="pluginDetailDesc" class="m-0"></p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="uploadPluginModal" tabindex="-1" aria-labelledby="uploadPluginModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="uploadPluginModalLabel">Plugin ZIP File</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<small class="form-text text-muted">Upload a valid plugin ZIP package</small>
					<form class="app-form" id="upload-form" action="{{ route('admin.plugin.local') }}" method="POST" enctype="multipart/form-data">
						@csrf
						<div class="mb-3">
							<input type="file" class="form-control file_upload" id="pluginFile" accept=".zip" name="plugin_zip" required>
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
