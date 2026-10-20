@extends('layouts.master')

@section('title', 'تفاصيل سجل العمليات والنشاطات #' . $activityLog->id . ' - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-search-plus ml-2"></i>تفاصيل سجل العمليات والنشاطات رقم #{{ $activityLog->id }}
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ رصد تفصيلي للتغييرات والقيم المسجلة</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('activity-logs.index') }}" class="btn btn-outline-primary font-weight-bold ml-2">
			<i class="fas fa-arrow-right ml-1"></i> العودة لسجل العمليات والنشاطات
		</a>
		<button type="button" onclick="window.print()" class="btn btn-outline-secondary font-weight-bold">
			<i class="fas fa-print ml-1"></i> طباعة التقرير
		</button>
	</div>
</div>
@endsection

@section('content')

@php
	$badgeClass = match($activityLog->action) {
		'sale_created', 'shift_opened', 'product_created', 'user_created' => 'badge-success',
		'sale_cancelled', 'return_cancelled', 'expense_cancelled', 'user_deleted', 'product_deleted' => 'badge-danger',
		'return_created', 'expense_created', 'stock_adjusted' => 'badge-warning text-dark',
		'user_updated', 'product_updated', 'expense_updated', 'shift_closed' => 'badge-primary',
		default => 'badge-secondary',
	};
@endphp

<!-- Overview Header Card -->
<div class="card mb-3 shadow-sm border">
	<div class="card-body">
		<div class="row align-items-center">
			<div class="col-md-8">
				<div class="d-flex align-items-center mb-2">
					<span class="badge {{ $badgeClass }} px-3 py-2 tx-14 ml-2">
						{{ $activityLog->action_arabic }}
					</span>
					<span class="badge badge-light border text-dark tx-13">
						الرمز التقني: <code>{{ $activityLog->action }}</code>
					</span>
				</div>
				<h5 class="font-weight-bold text-dark mb-1">{{ $activityLog->description ?? 'لا يوجد وصف إضافي' }}</h5>
				<p class="text-muted tx-12 mb-0">
					الكائن المتأثر: <strong class="text-dark">{{ $activityLog->subject_type_arabic }} (#{{ $activityLog->subject_id ?? '-' }})</strong>
					| الفئة البرمجية: <code>{{ $activityLog->subject_type ?? 'N/A' }}</code>
				</p>
			</div>
			<div class="col-md-4 text-md-left text-right mt-3 mt-md-0">
				<div class="tx-13 text-muted">المستخدم المنفذ:</div>
				<div class="font-weight-bold tx-16 text-dark">{{ $activityLog->user->name ?? 'النظام / محذوف' }}</div>
				<div class="tx-12 text-primary font-weight-semibold">{{ $activityLog->user->role_arabic ?? '' }}</div>
				<div class="tx-12 text-muted mt-1">
					<i class="far fa-clock ml-1"></i> {{ $activityLog->created_at->timezone('Africa/Cairo')->format('Y-m-d h:i:s A') }} (توقيت القاهرة)
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Values Comparison Side by Side -->
<div class="row">
	<!-- Old Values -->
	<div class="col-lg-6 mb-3">
		<div class="card h-100 shadow-sm border border-danger-light">
			<div class="card-header bg-danger-transparent py-2">
				<h6 class="card-title mb-0 font-weight-bold text-danger">
					<i class="fas fa-undo-alt ml-1"></i> القيم السابقة قبل العملية
				</h6>
			</div>
			<div class="card-body">
				@if(!empty($activityLog->old_values))
					<div class="table-responsive">
						<table class="table table-sm table-bordered mb-3">
							<thead class="thead-light">
								<tr>
									<th>الحقل</th>
									<th>القيمة السابقة</th>
								</tr>
							</thead>
							<tbody>
								@foreach($activityLog->old_values as $key => $val)
									<tr>
										<td class="font-weight-bold text-dark" style="width: 35%;">{{ $key }}</td>
										<td class="text-danger font-weight-semibold">
											@if(is_bool($val))
												<span class="badge {{ $val ? 'badge-success' : 'badge-danger' }}">{{ $val ? 'نعم (مفعل)' : 'لا (معطل)' }}</span>
											@elseif(is_array($val))
												<pre class="mb-0 text-left" style="direction: ltr;"><code>{{ json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
											@else
												{{ $val ?? '<فارغ>' }}
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					<details>
						<summary class="tx-12 text-muted cursor-pointer mb-2 font-weight-bold">عرض تمثيل البيانات الخام</summary>
						<pre class="bg-light p-3 border rounded text-left" style="direction: ltr;"><code>{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
					</details>
				@else
					<div class="alert alert-secondary py-3 text-center mb-0">
						<i class="fas fa-info-circle ml-1"></i> لا توجد قيم سابقة (عملية إنشاء جديدة أو عملية لا تتطلب قيماً مسبقة).
					</div>
				@endif
			</div>
		</div>
	</div>

	<!-- New Values -->
	<div class="col-lg-6 mb-3">
		<div class="card h-100 shadow-sm border border-success-light">
			<div class="card-header bg-success-transparent py-2">
				<h6 class="card-title mb-0 font-weight-bold text-success">
					<i class="fas fa-check-circle ml-1"></i> القيم الجديدة المسجلة
				</h6>
			</div>
			<div class="card-body">
				@if(!empty($activityLog->new_values))
					<div class="table-responsive">
						<table class="table table-sm table-bordered mb-3">
							<thead class="thead-light">
								<tr>
									<th>الحقل</th>
									<th>القيمة الجديدة</th>
								</tr>
							</thead>
							<tbody>
								@foreach($activityLog->new_values as $key => $val)
									<tr>
										<td class="font-weight-bold text-dark" style="width: 35%;">{{ $key }}</td>
										<td class="text-success font-weight-semibold">
											@if(is_bool($val))
												<span class="badge {{ $val ? 'badge-success' : 'badge-danger' }}">{{ $val ? 'نعم (مفعل)' : 'لا (معطل)' }}</span>
											@elseif(is_array($val))
												<pre class="mb-0 text-left" style="direction: ltr;"><code>{{ json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
											@else
												{{ $val ?? '<فارغ>' }}
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					<details>
						<summary class="tx-12 text-muted cursor-pointer mb-2 font-weight-bold">عرض تمثيل البيانات الخام</summary>
						<pre class="bg-light p-3 border rounded text-left" style="direction: ltr;"><code>{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
					</details>
				@else
					<div class="alert alert-secondary py-3 text-center mb-0">
						<i class="fas fa-info-circle ml-1"></i> لا توجد قيم جديدة مسجلة في هذا القيد.
					</div>
				@endif
			</div>
		</div>
	</div>
</div>

@endsection

