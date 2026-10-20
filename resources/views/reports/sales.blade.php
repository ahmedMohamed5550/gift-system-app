@extends('layouts.master')

@section('title', 'تقرير المبيعات والفواتير - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-file-invoice-dollar ml-2"></i>تقرير المبيعات والفواتير
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ استعراض المبيعات والخصومات والمرتجعات للفترة</span>
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

<!-- Date Filter Component -->
@include('reports.partials.date-filter', ['dateRange' => $dateRange, 'period' => $period])

<!-- Additional Filters Card -->
<div class="card mb-3 shadow-sm border">
	<div class="card-body py-2">
		<form method="GET" action="{{ route('reports.sales') }}">
			<input type="hidden" name="period" value="{{ $period }}">
			<input type="hidden" name="date_from" value="{{ $dateRange['date_from'] }}">
			<input type="hidden" name="date_to" value="{{ $dateRange['date_to'] }}">

			<div class="row row-sm align-items-end">
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">طريقة الدفع:</label>
					<select name="payment_method" class="form-control select2">
						<option value="">-- كافة طرق الدفع --</option>
						<option value="cash" {{ ($filters['payment_method'] ?? '') == 'cash' ? 'selected' : '' }}>نقداً (كاش)</option>
						<option value="vodafone_cash" {{ ($filters['payment_method'] ?? '') == 'vodafone_cash' ? 'selected' : '' }}>فودافون كاش</option>
						<option value="instapay" {{ ($filters['payment_method'] ?? '') == 'instapay' ? 'selected' : '' }}>انستا باي</option>
					</select>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">الكاشير / المسؤول:</label>
					<select name="user_id" class="form-control select2">
						<option value="">-- كافة الموظفين --</option>
						@foreach($users as $user)
							<option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
								{{ $user->name }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">حالة الفاتورة:</label>
					<select name="status" class="form-control select2">
						<option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>المكتملة فقط (النشطة)</option>
						<option value="cancelled" {{ ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>الملغاة فقط</option>
						<option value="" {{ empty($filters['status']) ? 'selected' : '' }}>كافة الحالات</option>
					</select>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<button type="submit" class="btn btn-secondary font-weight-bold btn-block">
						<i class="fas fa-filter ml-1"></i> تطبيق الفلاتر
					</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- KPI Cards -->
<div class="row row-sm mb-3">
	<!-- Net Sales -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">صافي المبيعات</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['net_sales'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">بعد استبعاد المرتجعات المعتمدة</p>
			</div>
		</div>
	</div>

	<!-- Gross Sales -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">إجمالي المبيعات</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['gross_sales'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">الخصومات: {{ number_format($summary['total_discount'], 2) }} جنيه</p>
			</div>
		</div>
	</div>

	<!-- Returns Total -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">إجمالي المرتجعات</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['returns_total'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">{{ $summary['returns_count'] }} إشعار مرتجع معتمد</p>
			</div>
		</div>
	</div>

	<!-- Invoices Count & AOV -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">عدد الفواتير ومتوسط السلة</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ $summary['total_invoices'] }} فاتورة</h3>
				<p class="mb-0 tx-12 text-white op-8">متوسط الفاتورة: {{ number_format($summary['average_invoice'], 2) }} جنيه</p>
			</div>
		</div>
	</div>
</div>

<!-- Detailed Sales Table -->
<div class="card border shadow-sm">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-list ml-1 text-primary"></i> قائمة فواتير المبيعات للفترة
		</h5>
		<span class="text-muted tx-13">إجمالي السجلات: {{ $sales->total() }}</span>
	</div>
	<div class="card-body">
		@if($sales->isEmpty())
			<div class="text-center py-5 text-muted">
				<i class="fas fa-search fa-3x mb-3 text-muted"></i>
				<h5>لا توجد فواتير مبيعات مطابقة لمعايير البحث المحددة.</h5>
			</div>
		@else
			<div class="table-responsive">
				<table class="table table-hover table-striped text-center align-middle mb-0">
					<thead class="thead-light">
						<tr>
							<th>#</th>
							<th>رقم الفاتورة</th>
							<th>الكاشير</th>
							<th>الوردية</th>
							<th>طريقة الدفع</th>
							<th>المجموع الفرعي</th>
							<th>الخصم</th>
							<th>الإجمالي النهائي</th>
							<th>الحالة</th>
							<th>تاريخ ووقت البيع</th>
							<th>الإجراءات</th>
						</tr>
					</thead>
					<tbody>
						@foreach($sales as $sale)
							<tr class="{{ $sale->isCancelled() ? 'table-danger' : '' }}">
								<td>{{ $loop->iteration + ($sales->currentPage() - 1) * $sales->perPage() }}</td>
								<td class="font-weight-bold text-primary">
									<a href="{{ route('sales.show', $sale) }}">#{{ $sale->invoice_number }}</a>
								</td>
								<td class="font-weight-semibold">{{ $sale->user?->name ?? '-' }}</td>
								<td>
									@if($sale->shift_id)
										<a href="{{ route('shifts.show', $sale->shift_id) }}" class="badge badge-light border">
											#{{ $sale->shift_id }}
										</a>
									@else
										-
									@endif
								</td>
								<td>
									<span class="badge {{ $sale->payment_method_badge_class }}">
										{{ $sale->payment_method_label }}
									</span>
								</td>
								<td>{{ number_format($sale->subtotal, 2) }} جنيه</td>
								<td class="{{ $sale->discount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
									{{ $sale->discount > 0 ? '-' . number_format($sale->discount, 2) : '0.00' }}
								</td>
								<td class="font-weight-bold tx-14 text-dark">{{ number_format($sale->total, 2) }} جنيه</td>
								<td>
									<span class="badge {{ $sale->status_badge_class }}">
										{{ $sale->status_label }}
									</span>
								</td>
								<td class="tx-12 text-muted">{{ $sale->created_at->format('Y-m-d g:i A') }}</td>
								<td>
									<div class="btn-group" role="group">
										<a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-info" title="عرض الفاتورة"><i class="fas fa-eye"></i></a>
										<a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-sm btn-primary" title="طباعة"><i class="fas fa-print"></i></a>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="mt-3 d-flex justify-content-between align-items-center">
				<span class="text-muted tx-12">
					عرض السجلات من {{ $sales->firstItem() }} إلى {{ $sales->lastItem() }} من إجمالي {{ $sales->total() }}
				</span>
				<div>
					{{ $sales->links() }}
				</div>
			</div>
		@endif
	</div>
</div>

@endsection

