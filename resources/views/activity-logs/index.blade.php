@extends('layouts.master')

@section('title', 'سجل العمليات والنشاطات - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-shield-alt ml-2"></i>سجل العمليات والنشاطات
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ رصد وتوثيق كافة العمليات الإدارية، المالية، والمخزنية الحساسة بالنظام</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('dashboard') }}" class="btn btn-outline-primary font-weight-bold ml-2">
			<i class="fas fa-home ml-1"></i> لوحة التحكم
		</a>
		<button type="button" onclick="window.print()" class="btn btn-outline-secondary font-weight-bold">
			<i class="fas fa-print ml-1"></i> طباعة السجل
		</button>
	</div>
</div>
@endsection

@section('content')

@if(session('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-check-circle ml-1"></i></strong> {{ session('success') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(session('error'))
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i></strong> {{ session('error') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<!-- Security & Immutability Notice -->
<div class="alert alert-primary mb-3 shadow-sm" role="alert">
	<div class="d-flex align-items-center">
		<div class="alert-icon mr-2"><i class="fas fa-lock fa-2x"></i></div>
		<div>
			<h6 class="alert-heading font-weight-bold mb-1">سجل عمليات ونشاطات غير قابل للتعديل أو الحذف</h6>
			<p class="mb-0 tx-12">يتم توثيق كافة العمليات تلقائياً فور حدوثها مع تسجيل اسم المستخدم المنفذ والطابع الزمني والقيم السابقة والجديدة. لا يمكن لأي مستخدم تعديل أو مسح هذه السجلات.</p>
		</div>
	</div>
</div>

<!-- Stats Cards -->
<div class="row row-sm mb-3">
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">إجمالي العمليات المسجلة</h6>
				<h3 class="tx-24 font-weight-bold mb-1 text-white">{{ number_format($stats['total']) }}</h3>
				<p class="mb-0 tx-12 text-white op-7">كافة الحركات منذ إطلاق النظام</p>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">عمليات اليوم</h6>
				<h3 class="tx-24 font-weight-bold mb-1 text-white">{{ number_format($stats['today']) }}</h3>
				<p class="mb-0 tx-12 text-white op-7">حركات مسجلة بتاريخ اليوم</p>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">المبيعات والمرتجعات</h6>
				<h3 class="tx-24 font-weight-bold mb-1 text-white">{{ number_format($stats['sales'] + $stats['returns']) }}</h3>
				<p class="mb-0 tx-12 text-white op-7">فواتير بيع ومرتجعات</p>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">المخزون والمنتجات</h6>
				<h3 class="tx-24 font-weight-bold mb-1 text-white">{{ number_format($stats['inventory']) }}</h3>
				<p class="mb-0 tx-12 text-white op-7">تسويات وتعديل أسعار المخزون</p>
			</div>
		</div>
	</div>
</div>

<!-- Filters Card -->
<div class="card mb-3 shadow-sm border">
	<div class="card-header pb-2 bg-light d-flex justify-content-between align-items-center">
		<h6 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-filter ml-1 text-primary"></i> تصفية وبحث في سجل العمليات
		</h6>
		@if(array_filter($filters))
			<a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-outline-danger">
				<i class="fas fa-times ml-1"></i> إعادة تعيين الفلاتر
			</a>
		@endif
	</div>
	<div class="card-body">
		<form method="GET" action="{{ route('activity-logs.index') }}">
			<div class="row row-sm align-items-end">
				<!-- User filter -->
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">المستخدم المسؤول:</label>
					<select name="user_id" class="form-control select2">
						<option value="">-- كافة المستخدمين --</option>
						@foreach($users as $u)
							<option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>
								{{ $u->name }} ({{ $u->username }})
							</option>
						@endforeach
					</select>
				</div>

				<!-- Action filter -->
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">نوع العملية (Action):</label>
					<select name="action" class="form-control select2">
						<option value="">-- كافة العمليات --</option>
						@foreach($actions as $key => $label)
							<option value="{{ $key }}" {{ ($filters['action'] ?? '') == $key ? 'selected' : '' }}>
								{{ $label }}
							</option>
						@endforeach
					</select>
				</div>

				<!-- Date From -->
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">من تاريخ:</label>
					<input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
				</div>

				<!-- Date To -->
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">إلى تاريخ:</label>
					<input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
				</div>

				<!-- Search input -->
				<div class="col-md-2 col-sm-12 mb-2">
					<button type="submit" class="btn btn-primary font-weight-bold btn-block">
						<i class="fas fa-search ml-1"></i> تطبيق الفلتر
					</button>
				</div>
			</div>

			<div class="row row-sm mt-1">
				<div class="col-md-6 col-12">
					<input type="text" name="search" class="form-control form-control-sm" placeholder="بحث بالوصف، المعرف (ID)، أو اسم العملية..." value="{{ $filters['search'] ?? '' }}">
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Logs Table Card -->
<div class="card shadow-sm border" id="live-search-container">
	<div class="card-header pb-2 d-flex justify-content-between align-items-center">
		<h6 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-list ml-1 text-primary"></i> سجل العمليات الموثقة ({{ $logs->total() }})
		</h6>
		<span class="tx-12 text-muted">عرض الصفحة {{ $logs->currentPage() }} من إجمالي {{ $logs->lastPage() }}</span>
	</div>
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover table-striped mb-0 text-center text-nowrap">
				<thead class="thead-light">
					<tr>
						<th>#</th>
						<th>التاريخ والوقت</th>
						<th>المسؤول</th>
						<th>نوع العملية</th>
						<th>الكائن المتأثر</th>
						<th class="text-right" style="min-width: 300px;">بيان وتفاصيل العملية</th>
						<th>القيم السابقة / الجديدة</th>
						<th>تفاصيل</th>
					</tr>
				</thead>
				<tbody>
					@forelse($logs as $log)
						@php
							$badgeClass = match($log->action) {
								'sale_created', 'shift_opened', 'product_created', 'user_created' => 'badge-success',
								'sale_cancelled', 'return_cancelled', 'expense_cancelled', 'user_deleted', 'product_deleted' => 'badge-danger',
								'return_created', 'expense_created', 'stock_adjusted' => 'badge-warning text-dark',
								'user_updated', 'product_updated', 'expense_updated', 'shift_closed' => 'badge-primary',
								default => 'badge-secondary',
							};
						@endphp
						<tr>
							<td>{{ $log->id }}</td>
							<td class="tx-12 font-weight-semibold">
								<div>{{ $log->created_at->timezone('Africa/Cairo')->format('Y-m-d') }}</div>
								<span class="text-muted tx-11">{{ $log->created_at->timezone('Africa/Cairo')->format('h:i:s A') }}</span>
							</td>
							<td>
								@if($log->user)
									<span class="font-weight-bold text-dark">{{ $log->user->name }}</span>
									<span class="d-block tx-11 text-muted">{{ $log->user->role_arabic }}</span>
								@else
									<span class="text-muted font-italic">النظام / محذوف</span>
								@endif
							</td>
							<td>
								<span class="badge {{ $badgeClass }} px-2 py-1 tx-12">
									{{ $log->action_arabic }}
								</span>
							</td>
							<td>
								@if($log->subject_type)
									<span class="badge badge-light border text-dark">
										{{ $log->subject_type_arabic }} #{{ $log->subject_id }}
									</span>
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td class="text-right font-weight-semibold" style="white-space: normal; max-width: 380px;">
								{{ $log->description ?? '-' }}
							</td>
							<td>
								@if(!empty($log->old_values) || !empty($log->new_values))
									<button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#logModal{{ $log->id }}">
										<i class="fas fa-code ml-1"></i> فحص القيم
									</button>
								@else
									<span class="text-muted tx-12">لا توجد بيانات</span>
								@endif
							</td>
							<td>
								<a href="{{ route('activity-logs.show', $log) }}" class="btn btn-sm btn-outline-primary" title="عرض تفصيلي">
									<i class="fas fa-eye"></i>
								</a>
							</td>
						</tr>

						<!-- Modal for values diff -->
						@if(!empty($log->old_values) || !empty($log->new_values))
							<div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
								<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
									<div class="modal-content">
										<div class="modal-header bg-light">
											<h6 class="modal-title font-weight-bold">
												<i class="fas fa-history ml-1 text-primary"></i> بيانات النشاط - عملية #{{ $log->id }} ({{ $log->action_arabic }})
											</h6>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body text-right">
											<div class="mb-3 p-2 bg-light rounded border">
												<strong>البيان:</strong> {{ $log->description }}
												<div class="tx-11 text-muted mt-1">
													المسؤول: {{ $log->user->name ?? 'النظام' }} | الوقت: {{ $log->created_at->timezone('Africa/Cairo')->format('Y-m-d h:i:s A') }}
												</div>
											</div>

											<div class="row">
												<div class="col-md-6 mb-3">
													<h6 class="font-weight-bold text-danger"><i class="fas fa-undo-alt ml-1"></i> القيم السابقة (Old Values):</h6>
													@if(!empty($log->old_values))
														<pre class="bg-light p-3 border rounded text-left" style="direction: ltr; max-height: 250px; overflow-y: auto;"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
													@else
														<div class="alert alert-secondary py-2 tx-12">لا توجد قيم سابقة (عملية إنشاء أولية)</div>
													@endif
												</div>

												<div class="col-md-6 mb-3">
													<h6 class="font-weight-bold text-success"><i class="fas fa-check-circle ml-1"></i> القيم الجديدة (New Values):</h6>
													@if(!empty($log->new_values))
														<pre class="bg-light p-3 border rounded text-left" style="direction: ltr; max-height: 250px; overflow-y: auto;"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
													@else
														<div class="alert alert-secondary py-2 tx-12">لا توجد قيم جديدة</div>
													@endif
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">إغلاق</button>
											<a href="{{ route('activity-logs.show', $log) }}" class="btn btn-primary font-weight-bold">
												<i class="fas fa-external-link-alt ml-1"></i> فتح في صفحة منفصلة
											</a>
										</div>
									</div>
								</div>
							</div>
						@endif
					@empty
						<tr>
							<td colspan="8" class="text-center py-4 text-muted">
								<i class="fas fa-clipboard-list fa-2x mb-2 d-block text-secondary"></i>
								لا توجد سجلات عمليات أو نشاطات مطابقة لمعايير البحث الحالية.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
	@if($logs->hasPages())
		<div class="card-footer d-flex justify-content-between align-items-center bg-light">
			<span class="tx-12 text-muted">عرض {{ $logs->firstItem() }} إلى {{ $logs->lastItem() }} من {{ $logs->total() }} سجل</span>
			<div>{{ $logs->links() }}</div>
		</div>
	@endif
</div>

@endsection

