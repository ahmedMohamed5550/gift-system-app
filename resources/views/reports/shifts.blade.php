@extends('layouts.master')

@section('title', 'تقرير الورديات والتسويات - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-cash-register ml-2"></i>تقرير الورديات ومطابقة الخزينة
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ مطابقة النقدية الفعلية، رصد العجز والزيادة، ومتابعة تسويات الورديات</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لمركز التقارير
		</a>
	</div>
</div>
@endsection

@section('content')

@include('reports.partials.date-filter', ['dateRange' => $dateRange, 'period' => $period])

<!-- Filter Options -->
<div class="card mb-3 shadow-sm border">
	<div class="card-body py-2">
		<form method="GET" action="{{ route('reports.shifts') }}">
			<input type="hidden" name="period" value="{{ $period }}">
			<input type="hidden" name="date_from" value="{{ $dateRange['date_from'] }}">
			<input type="hidden" name="date_to" value="{{ $dateRange['date_to'] }}">

			<div class="row row-sm align-items-end">
				<div class="col-md-4 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">المسؤول (الكاشير):</label>
					<select name="user_id" class="form-control select2">
						<option value="">-- كافة الموظفين --</option>
						@foreach($users as $user)
							<option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
								{{ $user->name }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="col-md-4 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">حالة الوردية:</label>
					<select name="status" class="form-control select2">
						<option value="">-- كافة الورديات --</option>
						<option value="open" {{ ($filters['status'] ?? '') == 'open' ? 'selected' : '' }}>مفتوحة حالياً</option>
						<option value="closed" {{ ($filters['status'] ?? '') == 'closed' ? 'selected' : '' }}>مغلقة ومسواة</option>
					</select>
				</div>

				<div class="col-md-4 col-sm-12 mb-2">
					<button type="submit" class="btn btn-secondary font-weight-bold btn-block">
						<i class="fas fa-filter ml-1"></i> تصفية الورديات
					</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- KPI Cards -->
<div class="row row-sm mb-3">
	<!-- Total Expected Cash -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">إجمالي النقدية المتوقعة</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['total_expected_cash'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">{{ $summary['total_shifts'] }} وردية ({{ $summary['open_shifts'] }} مفتوحة, {{ $summary['closed_shifts'] }} مغلقة)</p>
			</div>
		</div>
	</div>

	<!-- Total Actual Closing Cash -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">النقدية الفعلية المستلمة بالدرج</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['total_closing_cash'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">إجمالي ما تم جرده فعلياً عند الإغلاق</p>
			</div>
		</div>
	</div>

	<!-- Net Difference -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card {{ $summary['total_difference'] >= 0 ? 'bg-info-gradient' : 'bg-danger-gradient' }}">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">صافي الفروقات الكلي</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">
					{{ $summary['total_difference'] > 0 ? '+' : '' }}{{ number_format($summary['total_difference'], 2) }} جنيه
				</h3>
				<p class="mb-0 tx-12 text-white op-8">
					{{ $summary['total_difference'] > 0 ? 'زيادة إجمالية بالخزينة' : ($summary['total_difference'] < 0 ? 'عجز إجمالي بالخزينة' : 'مطابق تماماً') }}
				</p>
			</div>
		</div>
	</div>

	<!-- Reconciliation Status Statistics -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card border shadow-sm">
			<div class="card-body p-3">
				<span class="text-muted tx-12 d-block font-weight-bold mb-2">إحصائيات تسوية الورديات:</span>
				<div class="d-flex justify-content-between mb-1 tx-12">
					<span class="text-success font-weight-bold"><i class="fas fa-check-circle ml-1"></i> مطابق تماماً:</span>
					<strong>{{ $summary['balanced_count'] }} وردية</strong>
				</div>
				<div class="d-flex justify-content-between mb-1 tx-12">
					<span class="text-danger font-weight-bold"><i class="fas fa-times-circle ml-1"></i> ورديات بها عجز:</span>
					<strong>{{ $summary['shortage_count'] }} وردية</strong>
				</div>
				<div class="d-flex justify-content-between tx-12">
					<span class="text-primary font-weight-bold"><i class="fas fa-plus-circle ml-1"></i> ورديات بها زيادة:</span>
					<strong>{{ $summary['surplus_count'] }} وردية</strong>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Detailed Table -->
<div class="card border shadow-sm">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-list ml-1 text-primary"></i> سجل الورديات والتسويات التفصيلي
		</h5>
		<span class="text-muted tx-13">إجمالي الورديات: {{ $shifts->total() }}</span>
	</div>
	<div class="card-body">
		@if($shifts->isEmpty())
			<div class="text-center py-5 text-muted">
				<i class="fas fa-cash-register fa-3x mb-3 text-muted"></i>
				<h5>لا توجد ورديات عمل مسجلة في الفترة أو الفلاتر المحددة.</h5>
			</div>
		@else
			<div class="table-responsive">
				<table class="table table-hover table-striped text-center align-middle mb-0 tx-13">
					<thead class="thead-light">
						<tr>
							<th>رقم الوردية</th>
							<th>الكاشير المسؤول</th>
							<th>الحالة</th>
							<th>وقت الفتح</th>
							<th>وقت الإغلاق</th>
							<th>نقدية الافتتاح</th>
							<th>مبيعات كاش</th>
							<th>استرداد كاش</th>
							<th>مصروفات كاش</th>
							<th>المتوقع بالدرج</th>
							<th>الفعلي المسلم</th>
							<th>الفرق</th>
							<th>المطابقة</th>
							<th>الإجراءات</th>
						</tr>
					</thead>
					<tbody>
						@foreach($shifts as $shift)
							@php
								$diff = $shift->difference ?? $shift->calculated_difference;
							@endphp
							<tr>
								<td class="font-weight-bold text-primary">
									<a href="{{ route('shifts.show', $shift) }}">#{{ $shift->id }}</a>
								</td>
								<td class="font-weight-semibold">{{ $shift->user?->name ?? '-' }}</td>
								<td>
									<span class="badge {{ $shift->status_badge_class }}">
										{{ $shift->status_label_arabic }}
									</span>
								</td>
								<td class="tx-11">{{ $shift->opened_at->format('Y-m-d g:i A') }}</td>
								<td class="tx-11 text-muted">
									{{ $shift->closed_at ? $shift->closed_at->format('Y-m-d g:i A') : 'ما زالت مفتوحة' }}
								</td>
								<td>{{ number_format($shift->opening_cash, 2) }} ج.م</td>
								<td class="text-success font-weight-semibold">+{{ number_format($shift->cash_sales_total, 2) }}</td>
								<td class="text-danger">-{{ number_format($shift->cash_returns_total, 2) }}</td>
								<td class="text-warning">-{{ number_format($shift->cash_expenses_total, 2) }}</td>
								<td class="font-weight-bold text-dark tx-14">
									{{ number_format($shift->expected_cash ?? $shift->calculated_expected_cash, 2) }} ج.م
								</td>
								<td class="font-weight-bold">
									{{ $shift->closing_cash !== null ? number_format($shift->closing_cash, 2) . ' ج.م' : '-' }}
								</td>
								<td>
									@if($diff !== null)
										<strong class="{{ $diff > 0.001 ? 'text-success' : ($diff < -0.001 ? 'text-danger' : 'text-primary') }}">
											{{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }} ج.م
										</strong>
									@else
										-
									@endif
								</td>
								<td>
									@if($shift->isClosed)
										<span class="badge {{ $shift->difference_badge_class }}">
											{{ $shift->difference_label_arabic }}
										</span>
									@else
										<span class="badge badge-warning">جارية</span>
									@endif
								</td>
								<td>
									<div class="btn-group" role="group">
										<a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-info" title="عرض الوردية"><i class="fas fa-eye"></i></a>
										<a href="{{ route('shifts.print', $shift) }}" target="_blank" class="btn btn-sm btn-primary" title="طباعة التقرير"><i class="fas fa-print"></i></a>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="mt-3 d-flex justify-content-between align-items-center">
				<span class="text-muted tx-12">
					عرض الورديات من {{ $shifts->firstItem() }} إلى {{ $shifts->lastItem() }} من إجمالي {{ $shifts->total() }}
				</span>
				<div>
					{{ $shifts->links() }}
				</div>
			</div>
		@endif
	</div>
</div>

@endsection

