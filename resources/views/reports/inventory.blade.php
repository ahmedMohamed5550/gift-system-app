@extends('layouts.master')

@section('title', 'تقرير حركة المخزون وتقييم المستودع - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-dolly-flatbed ml-2"></i>تقرير حركة المخزون وتقييم المستودع
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تتبع الوارد والصادر، التسويات الجردية، والقيمة الرأسمالية والبيعية للمخزون</span>
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

<!-- Valuation Assumption Alert -->
<div class="alert alert-info border shadow-sm mb-3">
	<div class="d-flex align-items-center">
		<i class="fas fa-info-circle tx-24 text-info ml-3"></i>
		<div class="tx-13">
			<strong>معايير التقييم المخزني المعتمدة في النظام:</strong>
			يتم احتساب تكلفة البضاعة المباعة التاريخية بدقة تامة بالاعتماد على <strong>لقطة سعر التكلفة</strong> المحفوظة في بند الفاتورة لحظة البيع، مما يضمن دقة الأرباح حتى مع تغير أسعار الشراء لاحقاً.
			أما <strong>التقييم الحالي للمخزون بالمستودع</strong> فيحتسب بضرب الرصيد المتوفر حالياً في أحدث سعر شراء مسجل للمنتج.
		</div>
	</div>
</div>

<!-- Warehouse Valuation Snapshot -->
<div class="row row-sm mb-3">
	<!-- Total Stock Cost Value -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">القيمة التكليفية للمخزون</h6>
				<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($warehouse['total_cost_value'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">إجمالي رأس المال المجمد في البضاعة</p>
			</div>
		</div>
	</div>

	<!-- Total Retail Value -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">القيمة البيعية للمخزون</h6>
				<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($warehouse['total_retail_value'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">القيمة المتوقعة عند بيع الرصيد بالكامل</p>
			</div>
		</div>
	</div>

	<!-- Expected Gross Profit in Stock -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">هامش الربح الكامن بالمخزن</h6>
				<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($warehouse['potential_profit'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">فارق البيع عن التكلفة لرصيد المستودع</p>
			</div>
		</div>
	</div>

	<!-- Units in Stock -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">إجمالي كمية القطع بالمستودع</h6>
				<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ rtrim(rtrim(number_format($warehouse['total_units'], 3), '0'), '.') }} قطعة</h3>
				<p class="mb-0 tx-12 text-white op-8">موزعة على {{ $warehouse['total_products'] }} صنف نشط</p>
			</div>
		</div>
	</div>
</div>

<!-- Movement Filters Bar -->
<div class="card mb-3 shadow-sm border">
	<div class="card-body py-2">
		<form method="GET" action="{{ route('reports.inventory') }}">
			<input type="hidden" name="period" value="{{ $period }}">
			<input type="hidden" name="date_from" value="{{ $dateRange['date_from'] }}">
			<input type="hidden" name="date_to" value="{{ $dateRange['date_to'] }}">

			<div class="row row-sm align-items-end">
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">نوع العملية (المرجع):</label>
					<select name="reference_type" class="form-control select2">
						<option value="">-- كافة العمليات --</option>
						<option value="sale" {{ ($filters['reference_type'] ?? '') == 'sale' ? 'selected' : '' }}>مبيعات (خروج بضاعة)</option>
						<option value="return" {{ ($filters['reference_type'] ?? '') == 'return' ? 'selected' : '' }}>مرتجع مبيعات (إرجاع للمخزن)</option>
						<option value="purchase" {{ ($filters['reference_type'] ?? '') == 'purchase' ? 'selected' : '' }}>مشتريات جديدة (توريد)</option>
						<option value="adjustment" {{ ($filters['reference_type'] ?? '') == 'adjustment' ? 'selected' : '' }}>تسوية جردية يدوية</option>
					</select>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">طبيعة الحركة:</label>
					<select name="type" class="form-control select2">
						<option value="">-- وارد ومنصرف --</option>
						<option value="in" {{ ($filters['type'] ?? '') == 'in' ? 'selected' : '' }}>وارد فقط (زيادة +)</option>
						<option value="out" {{ ($filters['type'] ?? '') == 'out' ? 'selected' : '' }}>منصرف فقط (خصم -)</option>
					</select>
				</div>

				<div class="col-md-4 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">المنتج:</label>
					<select name="product_id" class="form-control select2">
						<option value="">-- كافة المنتجات --</option>
						@foreach($products as $prod)
							<option value="{{ $prod->id }}" {{ ($filters['product_id'] ?? '') == $prod->id ? 'selected' : '' }}>
								{{ $prod->name }} (#{{ $prod->id }})
							</option>
						@endforeach
					</select>
				</div>

				<div class="col-md-2 col-sm-6 mb-2">
					<button type="submit" class="btn btn-secondary font-weight-bold btn-block">
						<i class="fas fa-filter ml-1"></i> تصفية
					</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Movement Period Summary Stats -->
<div class="row row-sm mb-3">
	<div class="col-md-3 col-6 mb-2">
		<div class="p-3 bg-white border rounded text-center">
			<span class="text-muted tx-12 d-block">إجمالي الحركات بالفترة:</span>
			<strong class="tx-18 text-primary">{{ $summary['total_movements'] }} حركة</strong>
		</div>
	</div>
	<div class="col-md-3 col-6 mb-2">
		<div class="p-3 bg-white border rounded text-center">
			<span class="text-muted tx-12 d-block">إجمالي القطع الواردة (+):</span>
			<strong class="tx-18 text-success">+{{ rtrim(rtrim(number_format($summary['total_in'], 3), '0'), '.') }} قطعة</strong>
		</div>
	</div>
	<div class="col-md-3 col-6 mb-2">
		<div class="p-3 bg-white border rounded text-center">
			<span class="text-muted tx-12 d-block">إجمالي القطع المنصرفة (-):</span>
			<strong class="tx-18 text-danger">-{{ rtrim(rtrim(number_format($summary['total_out'], 3), '0'), '.') }} قطعة</strong>
		</div>
	</div>
	<div class="col-md-3 col-6 mb-2">
		<div class="p-3 bg-white border rounded text-center">
			<span class="text-muted tx-12 d-block">صافي تغير المخزون:</span>
			<strong class="tx-18 {{ $summary['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
				{{ $summary['net_change'] > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($summary['net_change'], 3), '0'), '.') }} قطعة
			</strong>
		</div>
	</div>
</div>

<!-- Movements Table -->
<div class="card border shadow-sm">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-list ml-1 text-primary"></i> سجل حركات المخزون التفصيلي
		</h5>
		<span class="text-muted tx-13">عدد السجلات: {{ $movements->total() }}</span>
	</div>
	<div class="card-body">
		@if($movements->isEmpty())
			<div class="text-center py-5 text-muted">
				<i class="fas fa-dolly fa-3x mb-3 text-muted"></i>
				<h5>لا توجد حركات مخزنية مسجلة مطابقة لمعايير البحث.</h5>
			</div>
		@else
			<div class="table-responsive">
				<table class="table table-hover table-striped text-center align-middle mb-0 tx-13">
					<thead class="thead-light">
						<tr>
							<th>#</th>
							<th class="text-right">المنتج</th>
							<th>نوع الحركة</th>
							<th>العملية المرجعية</th>
							<th>الكمية</th>
							<th>المسؤول</th>
							<th>التاريخ والوقت</th>
							<th class="text-right">البيان / الملاحظات</th>
						</tr>
					</thead>
					<tbody>
						@foreach($movements as $movement)
							<tr>
								<td>{{ $loop->iteration + ($movements->currentPage() - 1) * $movements->perPage() }}</td>
								<td class="text-right font-weight-bold">
									@if($movement->product)
										<a href="{{ route('products.show', $movement->product) }}" class="text-dark">
											{{ $movement->product->name }}
										</a>
										<span class="d-block tx-11 text-muted">#{{ $movement->product->id }}</span>
									@else
										<span class="text-muted">منتج محذوف (#{{ $movement->product_id }})</span>
									@endif
								</td>
								<td>
									@if($movement->type === 'in')
										<span class="badge badge-success px-2 py-1"><i class="fas fa-arrow-down ml-1"></i> وارد (+)</span>
									@else
										<span class="badge badge-danger px-2 py-1"><i class="fas fa-arrow-up ml-1"></i> منصرف (-)</span>
									@endif
								</td>
								<td>
									<span class="badge badge-light border font-weight-bold">
										{{ match($movement->reference_type) {
											'sale' => 'فاتورة مبيعات',
											'return' => 'مرتجع مبيعات',
											'purchase' => 'توريد مشتريات',
											'adjustment' => 'تسوية جردية',
											default => $movement->reference_type
										} }}
									</span>
									@if($movement->reference_id)
										<span class="tx-11 text-muted d-block">#{{ $movement->reference_id }}</span>
									@endif
								</td>
								<td class="font-weight-bold tx-14 {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
									{{ $movement->quantity > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($movement->quantity, 3), '0'), '.') }}
								</td>
								<td>{{ $movement->user?->name ?? '-' }}</td>
								<td class="tx-12 text-muted">{{ $movement->created_at->format('Y-m-d g:i A') }}</td>
								<td class="text-right tx-12 text-muted">{{ $movement->notes ?? '-' }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="mt-3 d-flex justify-content-between align-items-center">
				<span class="text-muted tx-12">
					عرض الحركات من {{ $movements->firstItem() }} إلى {{ $movements->lastItem() }} من إجمالي {{ $movements->total() }}
				</span>
				<div>
					{{ $movements->links() }}
				</div>
			</div>
		@endif
	</div>
</div>

@endsection

