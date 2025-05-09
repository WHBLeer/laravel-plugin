@extends('layout.master')
@section('title', 'Plugin Settings: ' . $plugin->getTitle())
@section('css')
	<!-- glight css -->
	<link rel="stylesheet" href="{{asset('assets/vendor/glightbox/glightbox.min.css')}}">
	<link rel="stylesheet" href="{{asset('assets/vendor/plugins/style.css')}}">
@endsection
@section('main-content')
	<div class="container-fluid">
		<div class="row mb-4">
			<div class="col-md-6">
				<h2>Plugin Settings: {{ $plugin->getTitle() }}</h2>
			</div>
		</div>
		
		<div class="card">
			<div class="card-body">
				@if(session('success'))
					<div class="alert alert-success">{{ session('success') }}</div>
				@endif
				
				<form action="{{ route('admin.plugin.settings.update', $plugin->getName()) }}" method="POST">
					@csrf
					@method('PUT')
					
					<div class="form-group">
						<label for="enabled">Status</label>
						<select class="form-control" id="enabled" name="enabled" disabled>
							<option value="1" {{ $plugin->isEnabled() ? 'selected' : '' }}>Enabled</option>
							<option value="0" {{ !$plugin->isEnabled() ? 'selected' : '' }}>Disabled</option>
						</select>
						<small class="form-text text-muted">Change status from plugins list</small>
					</div>
					
					<!-- 这里可以根据插件的配置项动态生成表单 -->
					@foreach($plugin->getConfig() as $key => $value)
						@if(!in_array($key, ['name', 'title', 'version', 'description', 'enabled']))
							<div class="form-group">
								<label for="{{ $key }}">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
								
								@if(is_bool($value))
									<select class="form-control" id="{{ $key }}" name="{{ $key }}">
										<option value="1" {{ $value ? 'selected' : '' }}>Yes</option>
										<option value="0" {{ !$value ? 'selected' : '' }}>No</option>
									</select>
								@elseif(is_array($value))
									<textarea class="form-control" id="{{ $key }}" name="{{ $key }}"
											  rows="3">{{ json_encode($value, JSON_PRETTY_PRINT) }}</textarea>
								@else
									<input type="text" class="form-control" id="{{ $key }}" name="{{ $key }}"
										   value="{{ $value }}">
								@endif
							</div>
						@endif
					@endforeach
					
					<button type="submit" class="btn btn-primary">Save Settings</button>
					<a href="{{ route('admin.plugin.index') }}" class="btn btn-secondary">Back to List</a>
				</form>
			</div>
		</div>
	</div>
@endsection