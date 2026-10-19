@extends('layouts.master')

@section('title', 'سجل الورديات وتسوية الخزينة - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة الورديات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ سجل الورديات وتسوية النقدية</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		@if($activeShift)
			<div class="mb-3 mb-xl-0 ml-2">
				<a href="{{ route('shifts.current') }}" class="btn btn-success font-weight-bold">
					<i class="fas fa-play-circle ml-1"></i> الوردية الحالية المفتوحة (#{{ $activeShift->id }})
				</a>
			</div>
		@else
			<div class="mb-3 mb-xl-0 ml-2">
				<button type="button" class="btn btn-primary font-weight-bold" data-toggle="modal" data-target="#openShiftModal">
					<i class="fas fa-plus ml-1"></i> فتح وردية جديدة
				</button>
			</div>
		@endif
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('expenses.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-money-bill-wave ml-1"></i> سجل المصروفات
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Alerts -->
@if(session('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-check-circle ml-1"></i> تم بنجاح!</strong> {{ session('success') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(session('error') || $errors->any())
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> تنبيه:</strong>
		@if(session('error'))
			{{ session('error') }}
		@endif
		@if($errors->any())
			<ul class="mb-0 mt-1 pr-3">
				@foreach($errors->all() as $err)
					<li>{{ $err }}</li>
				@endforeach
			</ul>
		@endif
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<!-- Shifts Analytics Cards -->
<div class="row row-sm">
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">إجمالي الورديات</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['total_shifts'] }}</h3>
							<p class="mb-0 tx-12 text-white op-7">{{ $stats['closed_shifts'] }} وردية مغلقة ومسواة</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-calendar-check text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">الورديات المفتوحة حالياً</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['open_shifts'] }}</h3>
							<p class="mb-0 tx-12 text-white op-7">قيد العمل بنقاط البيع</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-door-open text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">إجمالي النقدية المستلمة عند الإغلاق</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['total_cash_collected'], 2) }} ج.م</h3>
							<p class="mb-0 tx-12 text-white op-7">مجموع مبالغ الإغلاق الفعلية</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-cash-register text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card {{ $stats['total_difference'] < 0 ? 'bg-danger-gradient' : ($stats['total_difference'] > 0 ? 'bg-warning-gradient' : 'bg-secondary') }}">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">صافي فارق تسوية النقدية</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">
								{{ $stats['total_difference'] >= 0 ? '+' : '' }}{{ number_format($stats['total_difference'], 2) }} ج.م
							</h3>
							<p class="mb-0 tx-12 text-white op-7">
								{{ $stats['total_difference'] < 0 ? 'إجمالي عجز الخزينة' : ($stats['total_difference'] > 0 ? 'إجمالي فائض الخزينة' : 'مطابقة تامة') }}
							</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-balance-scale text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Filters Card -->
<div class="card mb-3">
	<div class="card-body py-3">
		<form method="GET" action="{{ route('shifts.index') }}">
			<div class="row row-sm">
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">حالة الوردية:</label>
					<select name="status" class="form-control">
						<option value="">-- كل الحالات --</option>
						<option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>مفتوحة</option>
						<option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>مغلقة ومسواة</option>
					</select>
				</div>
				<div class="col-md-4 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">الكاشير / المسؤول:</label>
					<select name="user_id" class="form-control">
						<option value="">-- كل المستخدمين --</option>
						@foreach($users as $u)
							<option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
								{{ $u->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-5 col-sm-12 mb-2">
					<label class="form-label tx-12 font-weight-semibold">الفترة الزمنية (تاريخ فتح الوردية):</label>
					<div class="input-group">
						<input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="من تاريخ">
						<div class="input-group-prepend input-group-append">
							<span class="input-group-text"><i class="fas fa-arrow-left"></i></span>
						</div>
						<input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="إلى تاريخ">
					</div>
				</div>

				<div class="col-12 text-left mt-2">
					<button type="submit" class="btn btn-primary font-weight-bold">
						<i class="fas fa-search ml-1"></i> تصفية النتائج
					</button>
					<a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary font-weight-bold">
						<i class="fas fa-times ml-1"></i> إلغاء التصفية
					</a>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Shifts Table Card -->
<div class="card">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h4 class="card-title mg-b-0"><i class="fas fa-list ml-1 text-primary"></i> سجل الورديات</h4>
		<span class="tx-12 text-muted">إجمالي الورديات المعروضة: {{ $shifts->total() }}</span>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
				<thead>
					<tr>
						<th>رقم الوردية</th>
						<th>الكاشير</th>
						<th>تاريخ ووقت الفتح</th>
						<th>تاريخ ووقت الإغلاق</th>
						<th>العهدة الافتتاحية</th>
						<th>النقدية المتوقعة</th>
						<th>النقدية الفعلية</th>
						<th>فارق التسوية</th>
						<th>الحالة</th>
						<th>الإجراءات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($shifts as $shift)
						<tr>
							<td class="font-weight-bold">
								<a href="{{ route('shifts.show', $shift) }}" class="text-primary">
									#{{ $shift->id }}
								</a>
							</td>
							<td>
								<i class="fas fa-user-circle ml-1 text-secondary"></i>
								{{ $shift->user->name ?? '-' }}
							</td>
							<td class="tx-12">
								<i class="far fa-clock ml-1 text-muted"></i>
								{{ $shift->opened_at ? $shift->opened_at->translatedFormat('Y-m-d h:i A') : '-' }}
							</td>
							<td class="tx-12">
								@if($shift->closed_at)
									<i class="far fa-clock ml-1 text-muted"></i>
									{{ $shift->closed_at->translatedFormat('Y-m-d h:i A') }}
								@else
									<span class="badge badge-warning">قيد التشغيل</span>
								@endif
							</td>
							<td class="font-weight-semibold">{{ number_format($shift->opening_cash, 2) }} ج.م</td>
							<td>
								<span class="font-weight-bold text-dark">
									{{ number_format($shift->expected_cash !== null ? $shift->expected_cash : $shift->calculated_expected_cash, 2) }} ج.م
								</span>
							</td>
							<td>
								@if($shift->closing_cash !== null)
									<span class="font-weight-bold text-primary">
										{{ number_format($shift->closing_cash, 2) }} ج.م
									</span>
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td>
								@if($shift->is_closed)
									@php
										$diff = $shift->difference ?? $shift->calculated_difference;
									@endphp
									@if($diff > 0.001)
										<span class="badge badge-success font-weight-bold tx-12">+{{ number_format($diff, 2) }} ج.م (زيادة)</span>
									@elseif($diff < -0.001)
										<span class="badge badge-danger font-weight-bold tx-12">{{ number_format($diff, 2) }} ج.م (عجز)</span>
									@else
										<span class="badge badge-primary font-weight-bold tx-12">مطابق (0.00)</span>
									@endif
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td>
								<span class="badge {{ $shift->status_badge_class }} px-2 py-1">
									{{ $shift->status_label_arabic }}
								</span>
							</td>
							<td>
								<div class="btn-group" role="group">
									<a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-info" title="تفاصيل الوردية والتسوية">
										<i class="fas fa-eye ml-1"></i> التفاصيل
									</a>
									<a href="{{ route('shifts.print', $shift) }}" target="_blank" class="btn btn-sm btn-secondary" title="طباعة تقرير الوردية">
										<i class="fas fa-print"></i>
									</a>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="10" class="text-center py-4">
								<i class="fas fa-folder-open text-muted tx-40 mb-2"></i>
								<p class="text-muted mb-0">لا توجد سجلات ورديات مطابقة للبحث.</p>
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<!-- Pagination -->
		<div class="mt-4 d-flex justify-content-center">
			{{ $shifts->links() }}
		</div>
	</div>
</div>

<!-- Modal: Open Shift -->
<div class="modal fade" id="openShiftModal" tabindex="-1" role="dialog" aria-labelledby="openShiftModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form action="{{ route('shifts.open') }}" method="POST">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title" id="openShiftModalLabel"><i class="fas fa-cash-register ml-1 text-primary"></i> فتح وردية عمل جديدة</h5>
					<button type="button" class="close ml-0 mr-auto" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-right">
					<div class="alert alert-info">
						<i class="fas fa-info-circle ml-1"></i> سيتم تسجيل الوردية باسمك الحالي <strong>({{ Auth::user()->name }})</strong> بتاريخ ووقت الآن.
					</div>

					<div class="form-group">
						<label for="opening_cash" class="font-weight-semibold">مبلغ العهدة النقدية الافتتاحية في الدرج <span class="text-danger">*</span></label>
						<div class="input-group">
							<input type="number" step="0.01" min="0" name="opening_cash" id="opening_cash" class="form-control" value="0.00" required>
							<div class="input-group-append">
								<span class="input-group-text">جنيه مصري</span>
							</div>
						</div>
						<small class="text-muted">أدخل رصيد الفكة أو العهدة المستلمة في بداية الوردية (0 إذا كان الدرج فارغاً).</small>
					</div>

					<div class="form-group">
						<label for="notes" class="font-weight-semibold">ملاحظات افتتاحية (اختياري)</label>
						<textarea name="notes" id="notes" rows="2" class="form-control" placeholder="أي ملاحظات حول استلام الوردية أو حالة الدرج..."></textarea>
					</div>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
					<button type="submit" class="btn btn-primary font-weight-bold">
						<i class="fas fa-check ml-1"></i> بدء فتح الوردية
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection

