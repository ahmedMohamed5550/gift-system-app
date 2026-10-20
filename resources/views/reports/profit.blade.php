@extends('layouts.master')

@section('title', 'تقرير الأرباح والخسائر وقائمة الدخل - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-balance-scale ml-2"></i>تقرير الأرباح والخسائر وقائمة الدخل
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الإيرادات، صافي المبيعات، تكلفة البضاعة المباعة، المصروفات، وصافي الدخل</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<button type="button" onclick="window.print()" class="btn btn-outline-secondary font-weight-bold ml-2">
			<i class="fas fa-print ml-1"></i> طباعة قائمة الدخل
		</button>
		<a href="{{ route('reports.index') }}" class="btn btn-outline-primary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لمركز التقارير
		</a>
	</div>
</div>
@endsection

@section('content')

@include('reports.partials.date-filter', ['dateRange' => $dateRange, 'period' => $period])

<!-- Core P&L Summary Cards -->
<div class="row row-sm mb-3">
	<!-- Net Sales -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">صافي المبيعات</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($income_statement['net_sales'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">بعد خصم الخصومات والمرتجعات</p>
			</div>
		</div>
	</div>

	<!-- Net COGS -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">تكلفة البضاعة المباعة</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($income_statement['net_cogs'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">محسوبة بلقطة التكلفة التاريخية</p>
			</div>
		</div>
	</div>

	<!-- Gross Profit -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">مجمل الربح التجاري</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($income_statement['gross_profit'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">هامش مجمل الربح: {{ $income_statement['gross_profit_margin'] }}%</p>
			</div>
		</div>
	</div>

	<!-- Net Profit / Income -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card {{ $income_statement['net_profit'] >= 0 ? 'bg-warning-gradient' : 'bg-secondary' }}">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">صافي الربح / الدخل</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($income_statement['net_profit'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">صافي الهامش: {{ $income_statement['net_profit_margin'] }}% بعد المصروفات</p>
			</div>
		</div>
	</div>
</div>

<div class="row row-sm mb-3">
	<!-- Left Side: Formal Income Statement (7 Cols) -->
	<div class="col-xl-7 col-lg-7 col-12">
		<div class="card border shadow-sm">
			<div class="card-header pb-2 bg-light d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-file-invoice ml-1 text-primary"></i> قائمة الدخل الرسمية
				</h5>
				<span class="badge badge-light border tx-12">{{ $dateRange['label'] }}</span>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-bordered mb-0 align-middle">
						<tbody>
							<!-- 1. Revenue Section -->
							<tr class="table-primary font-weight-bold">
								<td colspan="2" class="tx-14"><i class="fas fa-angle-left ml-1"></i> 1. الإيرادات التشغيلية والمبيعات:</td>
							</tr>
							<tr>
								<td class="pr-4">إجمالي مبيعات البضاعة</td>
								<td class="text-left font-weight-semibold">{{ number_format($income_statement['gross_subtotal'], 2) }} ج.م</td>
							</tr>
							<tr>
								<td class="pr-4 text-danger">(-) الخصومات والتخفيضات التجارية الممنوحة</td>
								<td class="text-left text-danger font-weight-semibold">-{{ number_format($income_statement['total_discount'], 2) }} ج.م</td>
							</tr>
							<tr class="bg-light">
								<td class="pr-4 font-weight-bold">(=) إجمالي المبيعات بعد الخصم</td>
								<td class="text-left font-weight-bold">{{ number_format($income_statement['gross_sales'], 2) }} ج.م</td>
							</tr>
							<tr>
								<td class="pr-4 text-danger">(-) إجمالي المرتجعات المعتمدة</td>
								<td class="text-left text-danger font-weight-semibold">-{{ number_format($income_statement['total_returns'], 2) }} ج.م</td>
							</tr>
							<tr class="bg-primary-transparent font-weight-bold tx-15">
								<td class="text-primary font-weight-bold">(=) صافي المبيعات والإيرادات</td>
								<td class="text-left text-primary font-weight-bold">{{ number_format($income_statement['net_sales'], 2) }} جنيه مصري</td>
							</tr>

							<!-- 2. COGS Section -->
							<tr class="table-danger font-weight-bold">
								<td colspan="2" class="tx-14"><i class="fas fa-angle-left ml-1"></i> 2. تكلفة البضاعة المباعة:</td>
							</tr>
							<tr>
								<td class="pr-4 text-muted">تكلفة الأصناف المباعة (وفق لقطات سعر التكلفة التاريخية)</td>
								<td class="text-left font-weight-semibold">{{ number_format($income_statement['gross_cogs'], 2) }} ج.م</td>
							</tr>
							<tr>
								<td class="pr-4 text-success">(-) تكلفة الأصناف المسترجعة للمخزون</td>
								<td class="text-left text-success font-weight-semibold">-{{ number_format($income_statement['returned_cogs'], 2) }} ج.م</td>
							</tr>
							<tr class="bg-light font-weight-bold">
								<td class="pr-4 font-weight-bold">(=) صافي تكلفة البضاعة المباعة</td>
								<td class="text-left text-danger font-weight-bold">{{ number_format($income_statement['net_cogs'], 2) }} جنيه مصري</td>
							</tr>

							<!-- 3. Gross Profit -->
							<tr class="bg-success-transparent font-weight-bold tx-15">
								<td class="text-success font-weight-bold">(=) مجمل الربح التجاري</td>
								<td class="text-left text-success font-weight-bold tx-16">{{ number_format($income_statement['gross_profit'], 2) }} جنيه مصري</td>
							</tr>
							<tr class="tx-12 text-muted">
								<td class="pr-4">نسبة هامش مجمل الربح</td>
								<td class="text-left font-weight-bold text-dark">{{ $income_statement['gross_profit_margin'] }}%</td>
							</tr>

							<!-- 4. Operating Expenses Section -->
							<tr class="table-warning font-weight-bold">
								<td colspan="2" class="tx-14"><i class="fas fa-angle-left ml-1"></i> 3. المصروفات التشغيلية والنفقات:</td>
							</tr>
							@foreach($income_statement['expenses_by_category'] as $expCat)
								<tr>
									<td class="pr-4 text-muted">{{ $expCat->category }} ({{ $expCat->count }} بند)</td>
									<td class="text-left text-danger font-weight-semibold">-{{ number_format($expCat->total, 2) }} ج.م</td>
								</tr>
							@endforeach
							<tr class="bg-light font-weight-bold">
								<td class="pr-4 font-weight-bold">(=) إجمالي المصروفات التشغيلية</td>
								<td class="text-left text-danger font-weight-bold">-{{ number_format($income_statement['total_expenses'], 2) }} جنيه مصري</td>
							</tr>

							<!-- 5. Net Profit / Final Result -->
							<tr class="{{ $income_statement['net_profit'] >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }} font-weight-bold tx-16">
								<td class="font-weight-bold text-white">(=) صافي الربح / الدخل النهائي</td>
								<td class="text-left font-weight-bold text-white tx-18">{{ number_format($income_statement['net_profit'], 2) }} جنيه مصري</td>
							</tr>
							<tr class="tx-12 font-weight-bold bg-light">
								<td>نسبة هامش صافي الربح</td>
								<td class="text-left {{ $income_statement['net_profit_margin'] >= 0 ? 'text-success' : 'text-danger' }}">
									{{ $income_statement['net_profit_margin'] }}%
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- Right Side: Cash Flow & Financial Reconciliation (5 Cols) -->
	<div class="col-xl-5 col-lg-5 col-12">
		<!-- Physical Cash Flow Box -->
		<div class="card border shadow-sm mb-3">
			<div class="card-header pb-2 bg-light">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-money-bill-wave ml-1 text-success"></i> حركة النقدية الفعلية بدرج الخزينة
				</h6>
			</div>
			<div class="card-body">
				<ul class="list-group list-group-flush pr-0 tx-13">
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted"><i class="fas fa-plus text-success ml-1"></i> مقبوضات المبيعات النقدية:</span>
						<strong class="text-success">+{{ number_format($cash_flow['cash_sales'], 2) }} ج.م</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted"><i class="fas fa-minus text-danger ml-1"></i> مدفوعات المرتجعات النقدية:</span>
						<strong class="text-danger">-{{ number_format($cash_flow['cash_returns'], 2) }} ج.م</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted"><i class="fas fa-minus text-warning ml-1"></i> مدفوعات المصروفات النقدية:</span>
						<strong class="text-warning">-{{ number_format($cash_flow['cash_expenses'], 2) }} ج.م</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-light p-2 rounded mt-2">
						<span class="font-weight-bold text-dark tx-14">صافي التدفق النقدي بالدرج:</span>
						<strong class="tx-16 font-weight-bold {{ $cash_flow['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }}">
							{{ $cash_flow['net_cash_flow'] > 0 ? '+' : '' }}{{ number_format($cash_flow['net_cash_flow'], 2) }} جنيه مصري
						</strong>
					</li>
				</ul>
				<small class="text-muted tx-11 d-block mt-2">
					* يمثل هذا المبلغ صافي السيولة النقدية التي يفترض زيادتها أو نقصانها في درج النقدية الفعلي خلال الفترة.
				</small>
			</div>
		</div>

		<!-- Electronic Wallets & InstaPay Box -->
		<div class="card border shadow-sm mb-3">
			<div class="card-header pb-2 bg-light">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-mobile-alt ml-1 text-info"></i> حركة المدفوعات الإلكترونية (محافظ وبنك)
				</h6>
			</div>
			<div class="card-body">
				<ul class="list-group list-group-flush pr-0 tx-13">
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">صافي فودافون كاش:</span>
						<strong class="text-dark">
							{{ number_format($cash_flow['vodafone_cash_sales'] - $cash_flow['vodafone_cash_returns'], 2) }} ج.م
						</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">صافي انستا باي:</span>
						<strong class="text-dark">
							{{ number_format($cash_flow['instapay_sales'] - $cash_flow['instapay_returns'], 2) }} ج.م
						</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">مصروفات محولة إلكترونياً:</span>
						<strong class="text-warning">-{{ number_format($cash_flow['electronic_expenses'], 2) }} ج.م</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-light p-2 rounded mt-2">
						<span class="font-weight-bold text-dark tx-14">صافي الرصيد الإلكتروني:</span>
						<strong class="tx-16 font-weight-bold text-info">
							{{ number_format($cash_flow['net_electronic_flow'], 2) }} جنيه مصري
						</strong>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<!-- Periodic Daily / Monthly Breakdown Table -->
@if(!empty($breakdown))
	<div class="card border shadow-sm mb-3">
		<div class="card-header pb-0 d-flex justify-content-between align-items-center">
			<h5 class="card-title mb-0 font-weight-bold text-dark">
				<i class="fas fa-calendar-day ml-1 text-primary"></i> التوزيع الزمني للأرباح والمبيعات للفترة
			</h5>
			<span class="text-muted tx-12">بيان تفصيلي يومي</span>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-striped table-bordered text-center align-middle mb-0 tx-13">
					<thead class="thead-light">
						<tr>
							<th>التاريخ</th>
							<th>اليوم</th>
							<th>إجمالي المبيعات</th>
							<th>المرتجعات</th>
							<th>صافي المبيعات</th>
							<th>صافي التكلفة</th>
							<th>مجمل الربح</th>
							<th>المصروفات</th>
							<th>صافي الربح</th>
						</tr>
					</thead>
					<tbody>
						@foreach($breakdown as $row)
							<tr>
								<td class="font-weight-bold text-dark">{{ $row['date'] }}</td>
								<td class="text-muted">{{ $row['day_name'] }}</td>
								<td>{{ number_format($row['gross_sales'], 2) }}</td>
								<td class="{{ $row['returns'] > 0 ? 'text-danger font-weight-semibold' : 'text-muted' }}">
									{{ $row['returns'] > 0 ? '-' . number_format($row['returns'], 2) : '0.00' }}
								</td>
								<td class="font-weight-bold text-primary">{{ number_format($row['net_sales'], 2) }}</td>
								<td class="text-muted">{{ number_format($row['net_cogs'], 2) }}</td>
								<td class="font-weight-bold {{ $row['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
									{{ number_format($row['gross_profit'], 2) }}
								</td>
								<td class="{{ $row['expenses'] > 0 ? 'text-warning font-weight-semibold' : 'text-muted' }}">
									{{ $row['expenses'] > 0 ? '-' . number_format($row['expenses'], 2) : '0.00' }}
								</td>
								<td class="font-weight-bold tx-14 {{ $row['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
									{{ number_format($row['net_profit'], 2) }} ج.م
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endif

@endsection

