@extends('layouts.master')

@section('title', 'لوحة التحكم - نظام إدارة متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-tachometer-alt ml-2"></i>لوحة التحكم والمؤشرات
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ مرحباً بك، {{ Auth::check() ? Auth::user()->name : '' }} ({{ Auth::check() ? Auth::user()->role_arabic : '' }})</span>
		</div>
	</div>

	<!-- Period Selector Filter -->
	<div class="d-flex my-xl-auto right-content align-items-center" style="gap: 8px;">
		<form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center" id="dashboardFilterForm">
			<div class="input-group">
				<div class="input-group-prepend">
					<span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-primary"></i></span>
				</div>
				<select name="period" class="form-control font-weight-bold" onchange="this.form.submit()">
					<option value="today" {{ $currentPeriod == 'today' ? 'selected' : '' }}>اليوم ({{ now()->format('Y-m-d') }})</option>
					<option value="yesterday" {{ $currentPeriod == 'yesterday' ? 'selected' : '' }}>أمس</option>
					<option value="this_week" {{ $currentPeriod == 'this_week' ? 'selected' : '' }}>الأسبوع الحالي</option>
					<option value="this_month" {{ $currentPeriod == 'this_month' ? 'selected' : '' }}>الشهر الحالي</option>
					<option value="last_month" {{ $currentPeriod == 'last_month' ? 'selected' : '' }}>الشهر الماضي</option>
					<option value="this_year" {{ $currentPeriod == 'this_year' ? 'selected' : '' }}>العام الحالي</option>
				</select>
			</div>
		</form>
		<a href="{{ route('pos.index') }}" class="btn btn-success font-weight-bold shadow-sm">
			<i class="fas fa-cash-register ml-1"></i> نقطة البيع
		</a>
		<a href="{{ route('reports.index') }}" class="btn btn-outline-primary font-weight-bold">
			<i class="fas fa-chart-line ml-1"></i> التقارير التفصيلية
		</a>
	</div>
</div>
@endsection

@section('content')

<!-- Current Shift Status Alert / Bar -->
@if($data['active_shift'])
	<div class="alert alert-info border-info shadow-sm mb-3">
		<div class="d-flex justify-content-between align-items-center flex-wrap">
			<div class="d-flex align-items-center">
				<span class="badge badge-success px-3 py-2 font-weight-bold tx-13 ml-3">
					<i class="fas fa-unlock ml-1"></i> وردية عمل مفتوحة حالياً (#{{ $data['active_shift']->id }})
				</span>
				<div>
					<strong>المسؤول:</strong> {{ $data['active_shift']->user->name ?? '-' }} |
					<strong>وقت الفتح:</strong> {{ $data['active_shift']->opened_at->format('Y-m-d g:i A') }} |
					<strong>نقدية الافتتاح:</strong> {{ number_format($data['active_shift']->opening_cash, 2) }} جنيه مصري |
					<strong>النقدية المتوقعة بالدرج:</strong> <span class="badge badge-primary px-2 py-1 tx-13 font-weight-bold">{{ number_format($data['active_shift']->calculated_expected_cash, 2) }} جنيه</span>
				</div>
			</div>
			<div class="mt-2 mt-md-0">
				<a href="{{ route('shifts.current') }}" class="btn btn-sm btn-primary font-weight-bold">
					<i class="fas fa-eye ml-1"></i> متابعة / إغلاق الوردية
				</a>
			</div>
		</div>
	</div>
@else
	<div class="alert alert-warning border-warning shadow-sm mb-3">
		<div class="d-flex justify-content-between align-items-center flex-wrap">
			<div class="d-flex align-items-center">
				<i class="fas fa-lock tx-24 text-warning ml-3"></i>
				<div>
					<strong class="tx-14">تنبيه: لا توجد وردية عمل مفتوحة حالياً للكاشير.</strong>
					<p class="mb-0 tx-12 text-muted">لبدء عمليات البيع واستلام النقدية في الدرج، يرجى فتح وردية جديدة.</p>
				</div>
			</div>
			<div class="mt-2 mt-md-0">
				<a href="{{ route('shifts.current') }}" class="btn btn-sm btn-warning font-weight-bold">
					<i class="fas fa-key ml-1"></i> فتح وردية جديدة الآن
				</a>
			</div>
		</div>
	</div>
@endif

<!-- Today's Live Flash Bar (If viewing another period, still see today's pulse) -->
@if($currentPeriod !== 'today')
	<div class="card mb-3 bg-light border">
		<div class="card-body py-2">
			<div class="d-flex justify-content-between align-items-center flex-wrap tx-13">
				<div>
					<span class="badge badge-pill badge-danger ml-2 font-weight-bold">مباشر اليوم</span>
					<span><strong>مبيعات اليوم:</strong> {{ number_format($data['today']['net_sales'], 2) }} جنيه ({{ $data['today']['sales_count'] }} فاتورة)</span>
					<span class="mr-3"><strong>مرتجعات اليوم:</strong> {{ number_format($data['today']['returns_total'], 2) }} جنيه</span>
					<span class="mr-3"><strong>مصروفات اليوم:</strong> {{ number_format($data['today']['expenses_total'], 2) }} جنيه</span>
				</div>
				<div>
					<span class="text-muted tx-12">بيانات البطاقات أدناه تعرض فترة: <strong>{{ $dateRange['label'] }}</strong></span>
				</div>
			</div>
		</div>
	</div>
@endif

<!-- Row 1: Primary KPI Cards -->
<div class="row row-sm mb-2">
	<!-- Net Sales (Main Hero Card) -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="d-flex justify-content-between">
					<h6 class="mb-3 tx-13 text-white font-weight-bold">صافي المبيعات</h6>
					<span class="badge badge-light tx-11 font-weight-bold px-2 py-0">بعد المرتجعات</span>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($data['period']['net_sales'], 2) }} جنيه</h3>
							<p class="mb-0 tx-12 text-white op-8">
								{{ $data['period']['invoices_count'] }} فاتورة بيع مكتملة
							</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-chart-line text-white tx-26"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Gross Sales & Discounts -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="d-flex justify-content-between">
					<h6 class="mb-3 tx-13 text-white font-weight-bold">إجمالي المبيعات والخصومات</h6>
					<i class="fas fa-file-invoice text-white tx-18"></i>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($data['period']['gross_sales'], 2) }} جنيه</h3>
							<p class="mb-0 tx-12 text-white op-8">
								خصومات ممنوحة: {{ number_format($data['period']['discounts_total'], 2) }} جنيه
							</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-tags text-white tx-26"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Completed Returns -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="d-flex justify-content-between">
					<h6 class="mb-3 tx-13 text-white font-weight-bold">المرتجعات المعتمدة</h6>
					<span class="badge badge-light tx-11 font-weight-bold px-2 py-0">{{ $data['period']['returns_count'] }} إشعار</span>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($data['period']['returns_total'], 2) }} جنيه</h3>
							<p class="mb-0 tx-12 text-white op-8">
								استرداد نقدي: {{ number_format($data['period']['cash_returns'], 2) }} جنيه
							</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-undo-alt text-white tx-26"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Operating Expenses -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="d-flex justify-content-between">
					<h6 class="mb-3 tx-13 text-white font-weight-bold">المصروفات التشغيلية</h6>
					<span class="badge badge-light tx-11 font-weight-bold px-2 py-0">{{ $data['period']['expenses_count'] }} مصروف</span>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($data['period']['expenses_total'], 2) }} جنيه</h3>
							<p class="mb-0 tx-12 text-white op-8">
								مصروفات نقدية: {{ number_format($data['period']['cash_expenses'], 2) }} جنيه
							</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-receipt text-white tx-26"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Row 2: Financial Integrity & Profit Indicators -->
<div class="row row-sm mb-3">
	<!-- Gross Profit (مجمل الربح) -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-12">
		<div class="card border shadow-sm">
			<div class="card-body p-3">
				<div class="d-flex justify-content-between align-items-center">
					<div>
						<span class="text-muted tx-12 d-block font-weight-bold">مجمل الربح :</span>
						<h4 class="font-weight-bold mb-0 {{ $data['period']['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
							{{ number_format($data['period']['gross_profit'], 2) }} <small class="tx-12">جنيه</small>
						</h4>
						<span class="tx-11 text-muted">
							هامش الربح: <strong class="text-dark">{{ $data['period']['gross_profit_margin'] }}%</strong> (التكلفة: {{ number_format($data['period']['net_cogs'], 2) }})
						</span>
					</div>
					<div class="p-2 bg-success-transparent rounded-circle">
						<i class="fas fa-coins text-success tx-22"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Net Profit (صافي الربح بعد المصروفات) -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-12">
		<div class="card border shadow-sm">
			<div class="card-body p-3">
				<div class="d-flex justify-content-between align-items-center">
					<div>
						<span class="text-muted tx-12 d-block font-weight-bold">صافي الربح :</span>
						<h4 class="font-weight-bold mb-0 {{ $data['period']['net_profit'] >= 0 ? 'text-primary' : 'text-danger' }}">
							{{ number_format($data['period']['net_profit'], 2) }} <small class="tx-12">جنيه</small>
						</h4>
						<span class="tx-11 text-muted">
							صافي الهامش: <strong class="text-dark">{{ $data['period']['net_profit_margin'] }}%</strong> بعد خصم المصروفات
						</span>
					</div>
					<div class="p-2 bg-primary-transparent rounded-circle">
						<i class="fas fa-award text-primary tx-22"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Physical Cash Flow in Drawer -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-12">
		<div class="card border shadow-sm">
			<div class="card-body p-3">
				<div class="d-flex justify-content-between align-items-center">
					<div>
						<span class="text-muted tx-12 d-block font-weight-bold">صافي النقدية بالدرج :</span>
						<h4 class="font-weight-bold mb-0 {{ $data['period']['net_cash_drawer_flow'] >= 0 ? 'text-dark' : 'text-danger' }}">
							{{ number_format($data['period']['net_cash_drawer_flow'], 2) }} <small class="tx-12">جنيه</small>
						</h4>
						<span class="tx-11 text-muted">
							كاش: +{{ number_format($data['period']['cash_sales'], 0) }} | استرداد: -{{ number_format($data['period']['cash_returns'], 0) }} | مصروف: -{{ number_format($data['period']['cash_expenses'], 0) }}
						</span>
					</div>
					<div class="p-2 bg-warning-transparent rounded-circle">
						<i class="fas fa-money-bill-wave text-warning tx-22"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Electronic Wallets & InstaPay -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-12">
		<div class="card border shadow-sm">
			<div class="card-body p-3">
				<div class="d-flex justify-content-between align-items-center">
					<div>
						<span class="text-muted tx-12 d-block font-weight-bold">المدفوعات الإلكترونية (محافظ / بنك):</span>
						<h4 class="font-weight-bold mb-0 text-info">
							{{ number_format($data['period']['electronic_sales'], 2) }} <small class="tx-12">جنيه</small>
						</h4>
						<span class="tx-11 text-muted">
							فودافون كاش: {{ number_format($data['period']['vodafone_cash_sales'], 0) }} | انستا باي: {{ number_format($data['period']['instapay_sales'], 0) }}
						</span>
					</div>
					<div class="p-2 bg-info-transparent rounded-circle">
						<i class="fas fa-mobile-alt text-info tx-22"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Row 3: Interactive Charts -->
<div class="row row-sm mb-3">
	<!-- Sales Trend Chart (8 Cols) -->
	<div class="col-xl-8 col-lg-7 col-md-12">
		<div class="card border shadow-sm">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-chart-area ml-1 text-primary"></i> منحنى حركة المبيعات ({{ $dateRange['label'] }})
				</h6>
				<span class="badge badge-light border tx-12 font-weight-semibold">
					متوسط قيمة الفاتورة: {{ number_format($data['period']['average_invoice_value'], 2) }} جنيه
				</span>
			</div>
			<div class="card-body">
				<canvas id="salesTrendChart" style="height: 280px; width: 100%;"></canvas>
			</div>
		</div>
	</div>

	<!-- Payment Methods Doughnut Chart (4 Cols) -->
	<div class="col-xl-4 col-lg-5 col-md-12">
		<div class="card border shadow-sm">
			<div class="card-header pb-0">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-chart-pie ml-1 text-primary"></i> توزيع المبيعات حسب طريقة الدفع
				</h6>
			</div>
			<div class="card-body text-center">
				<div style="height: 220px; position: relative;">
					<canvas id="paymentMethodsChart"></canvas>
				</div>
				<div class="d-flex justify-content-around mt-3 tx-12">
					<div>
						<span class="d-block text-success font-weight-bold"><i class="fas fa-circle tx-10"></i> نقداً (كاش)</span>
						<strong>{{ number_format($data['period']['cash_sales'], 2) }} جنيه</strong>
					</div>
					<div>
						<span class="d-block text-danger font-weight-bold"><i class="fas fa-circle tx-10"></i> فودافون كاش</span>
						<strong>{{ number_format($data['period']['vodafone_cash_sales'], 2) }} جنيه</strong>
					</div>
					<div>
						<span class="d-block text-primary font-weight-bold"><i class="fas fa-circle tx-10"></i> انستا باي</span>
						<strong>{{ number_format($data['period']['instapay_sales'], 2) }} جنيه</strong>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Row 4: Lists & Tables (Best Selling, Low Stock, Recent Invoices) -->
<div class="row row-sm">
	<!-- Best Selling Products (6 Cols) -->
	<div class="col-xl-6 col-lg-12 mb-3">
		<div class="card border shadow-sm h-100">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-fire ml-1 text-danger"></i> المنتجات الأكثر مبيعاً في الفترة
				</h6>
				<a href="{{ route('reports.products') }}" class="tx-12 font-weight-bold text-primary">
					عرض تقرير المنتجات بالكامل <i class="fas fa-angle-left mr-1"></i>
				</a>
			</div>
			<div class="card-body p-0">
				@if($data['best_selling_products']->isEmpty())
					<div class="text-center py-4 text-muted tx-13">
						لا توجد مبيعات مسجلة في الفترة المحددة.
					</div>
				@else
					<div class="table-responsive">
						<table class="table table-hover table-striped text-center align-middle mb-0 tx-13">
							<thead class="thead-light">
								<tr>
									<th>#</th>
									<th class="text-right">المنتج</th>
									<th>الكمية المباعة</th>
									<th>صافي القطع</th>
									<th>صافي الإيراد</th>
								</tr>
							</thead>
							<tbody>
								@foreach($data['best_selling_products'] as $prod)
									<tr>
										<td>{{ $loop->iteration }}</td>
										<td class="text-right font-weight-bold">
											<a href="{{ route('products.show', $prod->id) }}" class="text-dark">
												{{ $prod->name }}
											</a>
											<span class="d-block tx-11 text-muted">#{{ $prod->id }}</span>
										</td>
										<td>{{ rtrim(rtrim(number_format($prod->qty_sold, 3), '0'), '.') }}</td>
										<td>
											<span class="badge badge-success px-2 py-1 font-weight-bold">
												{{ rtrim(rtrim(number_format($prod->net_qty, 3), '0'), '.') }}
											</span>
										</td>
										<td class="font-weight-bold text-primary">
											{{ number_format($prod->net_revenue, 2) }} جنيه
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				@endif
			</div>
		</div>
	</div>

	<!-- Low Stock Alerts (6 Cols) -->
	<div class="col-xl-6 col-lg-12 mb-3">
		<div class="card border shadow-sm h-100">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-exclamation-triangle ml-1 text-warning"></i> تنبيهات النواقص والحد الأدنى ({{ $data['low_stock_count'] }})
				</h6>
				<a href="{{ route('reports.low-stock') }}" class="tx-12 font-weight-bold text-danger">
					عرض تقرير النواقص <i class="fas fa-angle-left mr-1"></i>
				</a>
			</div>
			<div class="card-body p-0">
				@if($data['low_stock_list']->isEmpty())
					<div class="text-center py-4 text-success tx-13 font-weight-bold">
						<i class="fas fa-check-circle tx-24 ml-1"></i> رائع! كافة المنتجات لديها رصيد آمن أعلى من الحد الأدنى.
					</div>
				@else
					<div class="table-responsive">
						<table class="table table-hover table-striped text-center align-middle mb-0 tx-13">
							<thead class="thead-light">
								<tr>
									<th class="text-right">المنتج</th>
									<th>التصنيف</th>
									<th>الرصيد الحالي</th>
									<th>الحد الأدنى</th>
									<th>الحالة</th>
								</tr>
							</thead>
							<tbody>
								@foreach($data['low_stock_list'] as $product)
									<tr>
										<td class="text-right font-weight-bold">
											<a href="{{ route('products.show', $product) }}" class="text-dark">
												{{ $product->name }}
											</a>
										</td>
										<td class="tx-11 text-muted">
											{{ $product->subcategory->name ?? '-' }}
										</td>
										<td class="font-weight-bold {{ $product->stock_quantity <= 0 ? 'text-danger' : 'text-warning' }}">
											{{ rtrim(rtrim(number_format($product->stock_quantity, 3), '0'), '.') }}
										</td>
										<td class="text-muted">
											{{ rtrim(rtrim(number_format($product->minimum_stock, 3), '0'), '.') }}
										</td>
										<td>
											@if($product->stock_quantity <= 0)
												<span class="badge badge-danger font-weight-bold">نفد من المخزون</span>
											@else
												<span class="badge badge-warning font-weight-bold">أوشك على النفاد</span>
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				@endif
			</div>
		</div>
	</div>
</div>

<!-- Row 5: Recent Sales Table -->
<div class="row row-sm mb-3">
	<div class="col-12">
		<div class="card border shadow-sm">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-receipt ml-1 text-primary"></i> آخر فواتير المبيعات المسجلة بالمتجر
				</h6>
				<a href="{{ route('sales.index') }}" class="tx-12 font-weight-bold text-primary">
					سجل كافة الفواتير <i class="fas fa-angle-left mr-1"></i>
				</a>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover table-striped text-center align-middle mb-0 tx-13">
						<thead class="thead-light">
							<tr>
								<th>رقم الفاتورة</th>
								<th>الكاشير</th>
								<th>طريقة الدفع</th>
								<th>الإجمالي</th>
								<th>الحالة</th>
								<th>التوقيت</th>
								<th>الإجراءات</th>
							</tr>
						</thead>
						<tbody>
							@foreach($data['recent_sales'] as $sale)
								<tr>
									<td class="font-weight-bold text-primary">
										<a href="{{ route('sales.show', $sale) }}">#{{ $sale->invoice_number }}</a>
									</td>
									<td>{{ $sale->user->name ?? '-' }}</td>
									<td>
										<span class="badge {{ $sale->payment_method_badge_class }}">
											{{ $sale->payment_method_label }}
										</span>
									</td>
									<td class="font-weight-bold text-dark">{{ number_format($sale->total, 2) }} جنيه</td>
									<td>
										<span class="badge {{ $sale->status_badge_class }}">
											{{ $sale->status_label }}
										</span>
									</td>
									<td class="tx-12 text-muted">{{ $sale->created_at->format('Y-m-d g:i A') }}</td>
									<td>
										<a href="{{ route('sales.show', $sale) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
										<a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-xs btn-primary"><i class="fas fa-print"></i></a>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')
<!-- Internal Chart.bundle js -->
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Sales Trend Chart
    const trendCtx = document.getElementById('salesTrendChart');
    if (trendCtx) {
        new Chart(trendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($data['chart_data']['labels']) !!},
                datasets: [
                    {
                        label: 'إجمالي المبيعات (جنيه)',
                        data: {!! json_encode($data['chart_data']['sales']) !!},
                        borderColor: '#0162e8',
                        backgroundColor: 'rgba(1, 98, 232, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'صافي المبيعات بعد المرتجع (جنيه)',
                        data: {!! json_encode($data['chart_data']['net_sales']) !!},
                        borderColor: '#22c03c',
                        backgroundColor: 'rgba(34, 192, 60, 0.05)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) { return value.toLocaleString() + ' ج.م'; }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(item, data) {
                            return data.datasets[item.datasetIndex].label + ': ' + Number(item.yLabel).toLocaleString() + ' جنيه';
                        }
                    }
                }
            }
        });
    }

    // 2. Payment Methods Doughnut Chart
    const pmCtx = document.getElementById('paymentMethodsChart');
    if (pmCtx) {
        new Chart(pmCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['نقداً (كاش)', 'فودافون كاش', 'انستا باي'],
                datasets: [{
                    data: [
                        {{ (float) $data['period']['cash_sales'] }},
                        {{ (float) $data['period']['vodafone_cash_sales'] }},
                        {{ (float) $data['period']['instapay_sales'] }}
                    ],
                    backgroundColor: ['#22c03c', '#ee335e', '#0162e8'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false }
            }
        });
    }
});
</script>
@endsection
