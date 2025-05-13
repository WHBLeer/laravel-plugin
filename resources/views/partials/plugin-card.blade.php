<div class="col-md-4 col-lg-3 col-xxl-2">
	<div class="card blog-card overflow-hidden">
		<a href="javascript:;" data-glightbox="type: image; zoomable: false;" class="plugin-detail" data-row="{{json_encode($plugin)}}">
			<img src="{{$plugin['logo']}}" class="card-img-top" alt="...">
		</a>
		<div class="tag-container">
			<span class="badge text-light-{{ $plugin['status']=='Enabled'?'success':'secondary' }}">{{ $plugin['status'] }}</span>
		</div>
		<div class="card-body">
			<p class="text-body-secondary"><i class="ti ti-calendar-due"></i> Version: {{ $plugin['version'] }}</p>
			<a href="javascript:;" class="bloglink plugin-detail" data-row="{{json_encode($plugin)}}">
				<h5 class="title-text mb-2">{{ $plugin['name'] }}</h5>
			</a>
			<p class="card-text text-secondary">
				{{ $plugin['description'] }}
			</p>
			<div class="app-divider-v dashed py-3"></div>
			<div class="d-flex justify-content-between align-items-center gap-2 position-relative">
				<div class="h-40 w-40 d-flex-center b-r-10 overflow-hidden bg-primary position-absolute">
					<img src="{{plugin_logo($plugin['author']['name'],false)}}" alt="avatar" class="img-fluid">
				</div>
				<div class="ps-5">
					<h6 class="text-dark f-w-500 mb-0"> {{ $plugin['author']['name'] }}</h6>
					<p class="text-secondary f-s-12 mb-0">{{ $plugin['author']['email'] }}</p>
				</div>
				<div>
					<div class="btn-group dropdown-icon-none">
						<button class="btn border-0 icon-btn b-r-4 dropdown-toggle" type="button"
								data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
							<i class="ti ti-dots-vertical f-s-18 text-dark"></i>
						</button>
						<ul class="dropdown-menu">
							<li class="restart-btn" data-action="{{ route('admin.plugin.restart', $plugin['name']) }}">
								<a class="dropdown-item text-primary" href="javascript:;">
									<i class="ti ti-refresh"></i> Restart
								</a>
							</li>
							@if($plugin['status'])
								<li class="disable-btn" data-action="{{ route('admin.plugin.disable', $plugin['name']) }}">
									<a class="dropdown-item text-warning" href="javascript:;">
										<i class="ti ti-analyze-off"></i> Disable
									</a>
								</li>
							@else
								<li class="enable-btn" data-action="{{ route('admin.plugin.enable', $plugin['name']) }}">
									<a class="dropdown-item text-success" href="javascript:;">
										<i class="ti ti-analyze"></i> Enable
									</a>
								</li>
							@endif
							<li class="pack_up-btn">
								<a class="dropdown-item text-primary" target="_blank"
								   href="{{ route('admin.plugin.pack_up', $plugin['name']) }}">
									<i class="ti ti-download"></i> Download
								</a>
							</li>
							<li class="delete-btn" data-action="{{ route('admin.plugin.delete', $plugin['name']) }}">
								<a class="dropdown-item text-danger" href="javascript:;">
									<i class="ti ti-trash-x"></i> Delete
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>