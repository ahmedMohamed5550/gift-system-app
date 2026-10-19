@extends('layouts.master')

@section('title', 'تقرير مبيعات الموظفين - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-user-tie ml-2"></i>تقرير مبيعات الموظفين والكاشير
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ متابعة إنتاجية موظفي الكاشير والمبيعات المحققة للفترة</span>
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

<!-- KPI Summary Cards -->
<div class="row row-sm mb-3">
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">صافي المبيعات الإجمالية</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($totals['net_sales'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">{{ $totals['sales_count'] }} فاتورة بيع</p>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">المبيعات النقدية (كاش)</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($totals['cash_sales'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">موردة لخزينة الدرج</p>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">المدفوعات الإلكترونية</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($totals['electronic_sales'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">فودافون كاش + انستا باي</p>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">المرتجعات المسجلة</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($totals['returns_total'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">{{ $totals['returns_count'] }} إشعار مرتجع</p>
			</div>
		</div>
	</div>
</div>

<!-- Employees Performance Table -->
<div class="card border shadow-sm">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-users ml-1 text-primary"></i> أداء موظفي الكاشير والمبيعات
		</h5>
		<span class="text-muted tx-13">عدد الموظفين: {{ $employees->count() }}</span>
	</div>
	<div class="card-body">
		@if($employees->isEmpty())
			<div class="text-center py-5 text-muted">
				<i class="fas fa-user-slash fa-3x mb-3 text-muted"></i>
				<h5>لا توجد بيانات مبيعات لأي موظف في الفترة المحددة.</h5>
			</div>
		@else
			<div class="table-responsive">
				<table class="table table-hover table-striped text-center align-middle mb-0">
					<thead class="thead-light">
						<tr>
							<th>#</th>
							<th class="text-right">الموظف</th>
							<th>الدور الوظيفي</th>
							<th>الورديات</th>
							<th>عدد الفواتير</th>
							<th>إجمالي المبيعات</th>
							<th>الخصومات</th>
							<th>المرتجعات</th>
							<th>صافي المبيعات</th>
							<th>مبيعات كاش</th>
							<th>مبيعات إلكترونية</th>
							<th>صافي نقدية الدرج</th>
						</tr>
					</thead>
					<tbody>
						@foreach($employees as $employee)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td class="text-right font-weight-bold">
									<a href="{{ route('users.show', $employee->id) }}" class="text-dark">
										{{ $employee->name }}
									</a>
									<span class="d-block tx-11 text-muted">{{ $employee->username }}</span>
								</td>
								<td>
									<span class="badge {{ $employee->role == 'owner' ? 'badge-primary' : 'badge-secondary' }} font-weight-bold">
										{{ $employee->role == 'owner' ? 'مالك / مدير' : 'كاشير / موظف' }}
									</span>
								</td>
								<td>{{ $employee->shifts_count }}</td>
								<td class="font-weight-semibold">{{ $employee->sales_count }}</td>
								<td>{{ number_format($employee->gross_sales, 2) }} ج.م</td>
								<td class="{{ $employee->total_discounts > 0 ? 'text-danger' : 'text-muted' }}">
									{{ $employee->total_discounts > 0 ? '-' . number_format($employee->total_discounts, 2) : '0.00' }}
								</td>
								<td class="{{ $employee->returns_total > 0 ? 'text-danger font-weight-semibold' : 'text-muted' }}">
									{{ $employee->returns_total > 0 ? '-' . number_format($employee->returns_total, 2) : '0.00' }}
								</td>
								<td class="font-weight-bold tx-14 text-primary">
									{{ number_format($employee->net_sales, 2) }} ج.م
								</td>
								<td class="text-success font-weight-semibold">
									{{ number_format($employee->cash_sales, 2) }} ج.م
								</td>
								<td class="text-info font-weight-semibold">
									{{ number_format($employee->electronic_sales, 2) }} ج.م
								</td>
								<td class="font-weight-bold text-dark">
									{{ number_format($employee->net_cash, 2) }} ج.م
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	</div>
</div>

@endsection

