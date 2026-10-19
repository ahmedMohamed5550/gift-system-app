@extends('layouts.master')

@section('title', 'تقرير المصروفات التشغيلية - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-receipt ml-2"></i>تقرير المصروفات والنفقات
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تحليل النفقات التشغيلية حسب التصنيفات وطرق السداد</span>
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

<!-- Filters Bar -->
<div class="card mb-3 shadow-sm border">
	<div class="card-body py-2">
		<form method="GET" action="{{ route('reports.expenses') }}">
			<input type="hidden" name="period" value="{{ $period }}">
			<input type="hidden" name="date_from" value="{{ $dateRange['date_from'] }}">
			<input type="hidden" name="date_to" value="{{ $dateRange['date_to'] }}">

			<div class="row row-sm align-items-end">
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">تصنيف المصروف:</label>
					<select name="category" class="form-control select2">
						<option value="">-- كافة التصنيفات --</option>
						@foreach($categories_list as $cat)
							<option value="{{ $cat }}" {{ ($filters['category'] ?? '') == $cat ? 'selected' : '' }}>
								{{ $cat }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">طريقة السداد:</label>
					<select name="payment_method" class="form-control select2">
						<option value="">-- كافة طرق السداد --</option>
						<option value="cash" {{ ($filters['payment_method'] ?? '') == 'cash' ? 'selected' : '' }}>نقداً (كاش - من الدرج)</option>
						<option value="vodafone_cash" {{ ($filters['payment_method'] ?? '') == 'vodafone_cash' ? 'selected' : '' }}>فودافون كاش</option>
						<option value="instapay" {{ ($filters['payment_method'] ?? '') == 'instapay' ? 'selected' : '' }}>انستا باي</option>
					</select>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">حالة المصروف:</label>
					<select name="status" class="form-control select2">
						<option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>النشطة والمعتمدة فقط</option>
						<option value="cancelled" {{ ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>الملغاة فقط</option>
						<option value="" {{ empty($filters['status']) ? 'selected' : '' }}>كافة الحالات</option>
					</select>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<button type="submit" class="btn btn-secondary font-weight-bold btn-block">
						<i class="fas fa-filter ml-1"></i> تصفية المصروفات
					</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- KPI Cards -->
<div class="row row-sm mb-3">
	<!-- Total Expenses -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">إجمالي المصروفات النشطة</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['total_amount'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">{{ $summary['total_count'] }} بند مصروف</p>
			</div>
		</div>
	</div>

	<!-- Cash Expenses -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">مصروفات نقدية (من الدرج)</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['cash_expenses'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">يخصم من نقدية الخزينة الفعلية</p>
			</div>
		</div>
	</div>

	<!-- Electronic Expenses -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">مصروفات إلكترونية (محافظ / بنك)</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['electronic_expenses'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">لا يؤثر على درج الخزينة الفعلي</p>
			</div>
		</div>
	</div>

	<!-- Categories Count -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-secondary">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">تنوع بنود الصرف</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ $categories_breakdown->count() }} تصنيف</h3>
				<p class="mb-0 tx-12 text-white op-8">تم الصرف عليها خلال الفترة</p>
			</div>
		</div>
	</div>
</div>

<!-- Categories Breakdown Table -->
@if($categories_breakdown->isNotEmpty())
	<div class="card border shadow-sm mb-3">
		<div class="card-header pb-0">
			<h5 class="card-title mb-0 font-weight-bold text-dark">
				<i class="fas fa-chart-pie ml-1 text-primary"></i> توزيع المصروفات حسب التصنيف
			</h5>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-striped table-bordered text-center align-middle mb-0">
					<thead class="thead-light">
						<tr>
							<th class="text-right">التصنيف</th>
							<th>عدد البنود</th>
							<th>المبلغ النقدي (كاش)</th>
							<th>المبلغ الإلكتروني</th>
							<th>الإجمالي</th>
							<th>النسبة من إجمالي المصروفات</th>
						</tr>
					</thead>
					<tbody>
						@foreach($categories_breakdown as $cat)
							@php
								$percentage = $summary['total_amount'] > 0 ? round(($cat->total_amount / $summary['total_amount']) * 100, 1) : 0;
							@endphp
							<tr>
								<td class="text-right font-weight-bold text-dark">{{ $cat->category }}</td>
								<td>{{ $cat->count }}</td>
								<td class="text-danger">{{ number_format($cat->cash_amount, 2) }} ج.م</td>
								<td class="text-info">{{ number_format($cat->electronic_amount, 2) }} ج.م</td>
								<td class="font-weight-bold tx-14 text-dark">{{ number_format($cat->total_amount, 2) }} ج.م</td>
								<td>
									<span class="font-weight-bold">{{ $percentage }}%</span>
									<div class="progress progress-xs mt-1">
										<div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endif

<!-- Detailed Expenses Table -->
<div class="card border shadow-sm">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-list ml-1 text-primary"></i> سجل قيود المصروفات التفصيلي
		</h5>
		<span class="text-muted tx-13">عدد السجلات: {{ $expenses->total() }}</span>
	</div>
	<div class="card-body">
		@if($expenses->isEmpty())
			<div class="text-center py-5 text-muted">
				<i class="fas fa-receipt fa-3x mb-3 text-muted"></i>
				<h5>لا توجد مصروفات مسجلة مطابقة لمعايير البحث.</h5>
			</div>
		@else
			<div class="table-responsive">
				<table class="table table-hover table-striped text-center align-middle mb-0 tx-13">
					<thead class="thead-light">
						<tr>
							<th>#</th>
							<th class="text-right">التصنيف</th>
							<th class="text-right">البيان / الوصف</th>
							<th>المبلغ</th>
							<th>طريقة السداد</th>
							<th>المسؤول</th>
							<th>الوردية</th>
							<th>التاريخ والوقت</th>
							<th>الحالة</th>
						</tr>
					</thead>
					<tbody>
						@foreach($expenses as $expense)
							<tr class="{{ $expense->is_cancelled ? 'table-danger' : '' }}">
								<td>{{ $loop->iteration + ($expenses->currentPage() - 1) * $expenses->perPage() }}</td>
								<td class="text-right font-weight-bold text-dark">
									<a href="{{ route('expenses.show', $expense) }}" class="text-dark">
										{{ $expense->category }}
									</a>
								</td>
								<td class="text-right">{{ $expense->description ?? '-' }}</td>
								<td class="font-weight-bold tx-14 text-danger">
									{{ number_format($expense->amount, 2) }} جنيه
								</td>
								<td>
									<span class="badge {{ $expense->payment_method_badge_class }}">
										{{ $expense->payment_method_label }}
									</span>
								</td>
								<td>{{ $expense->user?->name ?? '-' }}</td>
								<td>
									@if($expense->shift_id)
										<a href="{{ route('shifts.show', $expense->shift_id) }}" class="badge badge-light border">
											#{{ $expense->shift_id }}
										</a>
									@else
										-
									@endif
								</td>
								<td class="tx-12 text-muted">{{ $expense->created_at->format('Y-m-d g:i A') }}</td>
								<td>
									<span class="badge {{ $expense->status_badge_class }}">
										{{ $expense->status_label }}
									</span>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="mt-3 d-flex justify-content-between align-items-center">
				<span class="text-muted tx-12">
					عرض القيود من {{ $expenses->firstItem() }} إلى {{ $expenses->lastItem() }} من إجمالي {{ $expenses->total() }}
				</span>
				<div>
					{{ $expenses->links() }}
				</div>
			</div>
		@endif
	</div>
</div>

@endsection

