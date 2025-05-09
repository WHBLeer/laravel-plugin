@extends('layout.master')
@section('title', 'Plugins Management')
@section('css')
	<!-- glight css -->
	<link rel="stylesheet" href="{{asset('assets/vendor/glightbox/glightbox.min.css')}}">
	<link rel="stylesheet" href="{{asset('assets/vendor/laravel-plugin/style.css')}}">
@endsection
@section('main-content')
	<div class="container-fluid">
		<div class="card">
			<div class="card-header">
				<div class="d-flex gap-2 justify-content-between flex-sm-row flex-column">
					<h5>Plugins Management</h5>
					<button class="btn btn-primary" data-toggle="modal" data-target="#uploadPluginModal">
						<i class="fas fa-upload"></i> Upload Plugin
					</button>
				</div>
			</div>
			<div class="card-body">
				<div class="row ">
					@foreach($plugins as $plugin)
						@include('plugins::partials.plugin-card', ['plugin' => $plugin])
					@endforeach
				</div>
			</div>
		</div>
	</div>
@endsection
@section('modal')
	<div class="modal fade" id="uploadPluginModal" tabindex="-1" aria-labelledby="uploadPluginModalLabel" style="display: none;" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" id="staticBackdrop">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="uploadPluginModalLabel1">Create Task</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form action="{{ route('admin.plugin.local') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="modal-body">
						<div class="form-group">
							<label for="pluginFile">Plugin ZIP File</label>
							<input type="file" class="form-control-file" id="pluginFile" name="plugin" required>
							<small class="form-text text-muted">Upload a valid plugin ZIP package</small>
						</div>
						<div class="form-check mb-3 d-flex gap-1">
							<input class="form-check-input mg-2" type="checkbox" value="" name="enable" id="checkDefault">
							<label class="form-check-label" for="checkDefault">
								Enable
							</label>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Upload & Install</button>
					</div>
				</form>
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
	<script src="{{asset('assets/vendor/laravel-plugin/script.js')}}"></script>
@endsection

