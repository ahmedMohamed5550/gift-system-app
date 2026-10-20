@extends('layouts.master')

@section('title', 'مركز التقارير والإحصائيات - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-chart-pie ml-2"></i>مركز التقارير والإحصائيات
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ التحليلات المالية والمخزنية الشاملة</span>
		</div>
	</div>
</div>
@endsection

@section('content')

<!-- Global Date Filter for Hub -->
@include('reports.partials.date-filter', ['dateRange' => $dateRange, 'period' => $period])

<!-- High-Level Financial Snapshot for Selected Period -->
<div class="row row-sm mb-4">
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card bg-primary-gradient text-white p-3 shadow-sm">
			<span class="tx-12 font-weight-bold op-8">صافي المبيعات</span>
			<h3 class="font-weight-bold mb-1">{{ number_format($data['period']['net_sales'], 2) }} جنيه</h3>
			<small class="op-8">{{ $data['period']['invoices_count'] }} فاتورة بعد خصم المرتجعات</small>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card bg-success-gradient text-white p-3 shadow-sm">
			<span class="tx-12 font-weight-bold op-8">مجمل الربح</span>
			<h3 class="font-weight-bold mb-1">{{ number_format($data['period']['gross_profit'], 2) }} جنيه</h3>
			<small class="op-8">هامش الربح التجاري: {{ $data['period']['gross_profit_margin'] }}%</small>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card bg-info-gradient text-white p-3 shadow-sm">
			<span class="tx-12 font-weight-bold op-8">صافي الربح</span>
			<h3 class="font-weight-bold mb-1">{{ number_format($data['period']['net_profit'], 2) }} جنيه</h3>
			<small class="op-8">بعد خصم المصروفات ({{ number_format($data['period']['expenses_total'], 2) }} ج.م)</small>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card bg-danger-gradient text-white p-3 shadow-sm">
			<span class="tx-12 font-weight-bold op-8">تنبيهات النواقص بالمخزن</span>
			<h3 class="font-weight-bold mb-1">{{ $data['low_stock_count'] }} منتج</h3>
			<small class="op-8">وصلت للحد الأدنى أو نفدت من المخزون</small>
		</div>
	</div>
</div>

<!-- Report Navigation Cards Grid -->
<div class="row row-sm">
	<!-- 1. Sales Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-primary-transparent rounded ml-3">
						<i class="fas fa-file-invoice text-primary tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.sales') }}" class="text-dark">تقرير المبيعات والفواتير</a>
						</h5>
						<span class="text-muted tx-12">متابعة الفواتير والخصومات والمبيعات الإجمالية</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					استعراض المبيعات اليومية والفترية مع تفصيل طرق الدفع ومبالغ الخصومات ومعدل سلة الشراء.
				</p>
				<a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-primary font-weight-bold btn-block">
					فتح تقرير المبيعات <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>

	<!-- 2. Payment Methods Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-success-transparent rounded ml-3">
						<i class="fas fa-wallet text-success tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.payment-methods') }}" class="text-dark">المبيعات حسب طرق الدفع</a>
						</h5>
						<span class="text-muted tx-12">نقداً، فودافون كاش، انستا باي</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					فصل النقدية الفعلية بالدرج عن تحويلات المحافظ الإلكترونية والحسابات البنكية مع نسب التوزيع.
				</p>
				<a href="{{ route('reports.payment-methods') }}" class="btn btn-sm btn-outline-success font-weight-bold btn-block">
					فتح تقرير طرق الدفع <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>

	<!-- 3. Profit Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-warning-transparent rounded ml-3">
						<i class="fas fa-balance-scale text-warning tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.profit') }}" class="text-dark">تقرير الأرباح والخسائر</a>
						</h5>
						<span class="text-muted tx-12">قائمة الدخل: الإيرادات، التكلفة، صافي الربح</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					احتساب تكلفة البضاعة المباعة الدقيقة وفق لقطات التكلفة ومجمل الربح وصافي الدخل بعد المصروفات.
				</p>
				<a href="{{ route('reports.profit') }}" class="btn btn-sm btn-outline-warning font-weight-bold btn-block">
					فتح تقرير الأرباح <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>

	<!-- 4. Products Sales Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-info-transparent rounded ml-3">
						<i class="fas fa-boxes text-info tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.products') }}" class="text-dark">تقرير مبيعات المنتجات</a>
						</h5>
						<span class="text-muted tx-12">حركة بيع الأصناف، الكميات، وهامش ربح كل منتج</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					تحليل أداء المنتجات الفردية، الكميات المباعة والمرتجعة، إجمالي الإيراد وربحية كل صنف.
				</p>
				<a href="{{ route('reports.products') }}" class="btn btn-sm btn-outline-info font-weight-bold btn-block">
					فتح تقرير المنتجات <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>

	<!-- 5. Employees Sales Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-secondary-transparent rounded ml-3">
						<i class="fas fa-user-tie text-secondary tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.employees') }}" class="text-dark">تقرير مبيعات الموظفين</a>
						</h5>
						<span class="text-muted tx-12">إنتاجية كل كاشير وعدد فواتيره ومبيعاته</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					متابعة أداء موظفي الكاشير، المبيعات المحققة، الخصومات الممنوحة، والمرتجعات المسجلة لكل موظف.
				</p>
				<a href="{{ route('reports.employees') }}" class="btn btn-sm btn-outline-secondary font-weight-bold btn-block">
					فتح تقرير الموظفين <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>

	<!-- 6. Shifts Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-purple-transparent rounded ml-3">
						<i class="fas fa-cash-register text-purple tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.shifts') }}" class="text-dark">تقرير الورديات والتسويات</a>
						</h5>
						<span class="text-muted tx-12">مطابقة النقدية، العجز، والزيادة بالخزينة</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					مراجعة ورديات العمل، مطابقة النقدية الافتتاحية والختامية، وكشف الفروقات (عجز أو زيادة) بكل دقة.
				</p>
				<a href="{{ route('reports.shifts') }}" class="btn btn-sm btn-outline-purple font-weight-bold btn-block">
					فتح تقرير الورديات <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>

	<!-- 7. Expenses Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-danger-transparent rounded ml-3">
						<i class="fas fa-receipt text-danger tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.expenses') }}" class="text-dark">تقرير المصروفات التشغيلية</a>
						</h5>
						<span class="text-muted tx-12">المصروفات حسب التصنيف وطريقة الدفع</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					تحليل النفقات والمصروفات حسب الفئات والبنود مع تمييز المصروفات النقدية والمصروفات الإلكترونية.
				</p>
				<a href="{{ route('reports.expenses') }}" class="btn btn-sm btn-outline-danger font-weight-bold btn-block">
					فتح تقرير المصروفات <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>

	<!-- 8. Inventory Movement Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-primary-transparent rounded ml-3">
						<i class="fas fa-dolly-flatbed text-primary tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.inventory') }}" class="text-dark">تقرير حركة المخزون</a>
						</h5>
						<span class="text-muted tx-12">سجل حركات الإدخال، الإخراج، والتسويات الجردية</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					تتبع كافة حركات المخزون من مشتريات ومبيعات ومرتجعات وتسويات مع تقييم القيمة الرأسمالية والبيعية.
				</p>
				<a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-outline-primary font-weight-bold btn-block">
					فتح تقرير المخزون <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>

	<!-- 9. Low Stock Report -->
	<div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-3">
		<div class="card border shadow-sm h-100 hover-shadow transition">
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="p-3 bg-warning-transparent rounded ml-3">
						<i class="fas fa-exclamation-circle text-warning tx-26"></i>
					</div>
					<div>
						<h5 class="font-weight-bold mb-1">
							<a href="{{ route('reports.low-stock') }}" class="text-dark">تقرير النواقص والحد الأدنى</a>
						</h5>
						<span class="text-muted tx-12">الأصناف المطلوب إعادة طلبها وتقدير تكلفتها</span>
					</div>
				</div>
				<p class="text-muted tx-13 mb-3">
					كشف فوري للمنتجات التي وصلت أو انخفضت عن حد الأمان مع اقتراح كميات إعادة الطلب وتكلفتها التقديرية.
				</p>
				<a href="{{ route('reports.low-stock') }}" class="btn btn-sm btn-outline-warning font-weight-bold btn-block">
					فتح تقرير النواقص <i class="fas fa-arrow-left mr-1"></i>
				</a>
			</div>
		</div>
	</div>
</div>

@endsection

