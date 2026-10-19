@extends('layouts.master')

@section('title', 'تقرير مبيعات المنتجات - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-boxes ml-2"></i>تقرير مبيعات المنتجات والأصناف
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تحليل الكميات المباعة، الإيرادات، التكلفة، وربحية كل صنف</span>
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

<!-- Product Filter Bar -->
<div class="card mb-3 shadow-sm border">
	<div class="card-body py-2">
		<form method="GET" action="{{ route('reports.products') }}">
			<input type="hidden" name="period" value="{{ $period }}">
			<input type="hidden" name="date_from" value="{{ $dateRange['date_from'] }}">
			<input type="hidden" name="date_to" value="{{ $dateRange['date_to'] }}">

			<div class="row row-sm align-items-end">
				<div class="col-md-4 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">بحث باسم أو كود المنتج:</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-search"></i></span>
						</div>
						<input type="text" name="search" class="form-control" placeholder="اسم المنتج أو الكود..." value="{{ $filters['search'] ?? '' }}">
					</div>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">القسم الرئيسي:</label>
					<select name="category_id" class="form-control select2">
						<option value="">-- كافة الأقسام --</option>
						@foreach($categories as $category)
							<option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
								{{ $category->name }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">القسم الفرعي:</label>
					<select name="subcategory_id" class="form-control select2">
						<option value="">-- كافة الأقسام الفرعية --</option>
						@foreach($subcategories as $subcategory)
							<option value="{{ $subcategory->id }}" {{ ($filters['subcategory_id'] ?? '') == $subcategory->id ? 'selected' : '' }}>
								{{ $subcategory->name }}
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

<!-- KPI Cards -->
<div class="row row-sm mb-3">
	<!-- Net Quantity Sold -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">صافي القطع المباعة (Net Qty)</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ rtrim(rtrim(number_format($summary['total_net_qty'], 3), '0'), '.') }} قطعة</h3>
				<p class="mb-0 tx-12 text-white op-8">مباع: {{ rtrim(rtrim(number_format($summary['total_qty_sold'], 3), '0'), '.') }} | مرتجع: {{ rtrim(rtrim(number_format($summary['total_qty_returned'], 3), '0'), '.') }}</p>
			</div>
		</div>
	</div>

	<!-- Net Revenue -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">صافي إيرادات المنتجات</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['total_net_revenue'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">{{ $summary['total_products'] }} منتج تم بيعه</p>
			</div>
		</div>
	</div>

	<!-- Net COGS Cost -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">تكلفة البضاعة المباعة (COGS)</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['total_net_cost'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">وفق لقطة سعر التكلفة التاريخي</p>
			</div>
		</div>
	</div>

	<!-- Gross Profit & Margin -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white">مجمل أرباح المنتجات</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['total_gross_profit'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">هامش مجمل الربح: {{ $summary['profit_margin'] }}%</p>
			</div>
		</div>
	</div>
</div>

<!-- Detailed Table -->
<div class="card border shadow-sm" id="live-search-container">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-list ml-1 text-primary"></i> بيان مبيعات وأرباح الأصناف للفترة
		</h5>
		<span class="text-muted tx-13">عدد المنتجات: {{ $products->total() }}</span>
	</div>
	<div class="card-body">
		@if($products->isEmpty())
			<div class="text-center py-5 text-muted">
				<i class="fas fa-boxes fa-3x mb-3 text-muted"></i>
				<h5>لا توجد مبيعات لأي صنف في الفترة أو المعايير المحددة.</h5>
			</div>
		@else
			<div class="table-responsive">
				<table class="table table-hover table-striped text-center align-middle mb-0">
					<thead class="thead-light">
						<tr>
							<th>#</th>
							<th class="text-right">المنتج</th>
							<th>القسم</th>
							<th>الرصيد بالمخزن</th>
							<th>المباع</th>
							<th>المرتجع</th>
							<th>صافي القطع</th>
							<th>صافي الإيراد</th>
							<th>صافي التكلفة (COGS)</th>
							<th>مجمل الربح</th>
							<th>هامش الربح %</th>
						</tr>
					</thead>
					<tbody>
						@foreach($products as $product)
							@php
								$margin = $product->net_revenue > 0 ? round(($product->gross_profit / $product->net_revenue) * 100, 1) : 0;
							@endphp
							<tr>
								<td>{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
								<td class="text-right font-weight-bold">
									<a href="{{ route('products.show', $product->id) }}" class="text-dark">
										{{ $product->name }}
									</a>
									<span class="d-block tx-11 text-muted">#{{ $product->id }}</span>
								</td>
								<td class="tx-12">
									{{ $product->category_name ?? '-' }} / {{ $product->subcategory_name ?? '-' }}
								</td>
								<td class="font-weight-semibold {{ $product->stock_quantity <= 0 ? 'text-danger font-weight-bold' : '' }}">
									{{ rtrim(rtrim(number_format($product->stock_quantity, 3), '0'), '.') }}
								</td>
								<td>{{ rtrim(rtrim(number_format($product->qty_sold, 3), '0'), '.') }}</td>
								<td class="text-danger">{{ rtrim(rtrim(number_format($product->qty_returned, 3), '0'), '.') }}</td>
								<td>
									<span class="badge badge-success px-2 py-1 font-weight-bold">
										{{ rtrim(rtrim(number_format($product->net_qty, 3), '0'), '.') }}
									</span>
								</td>
								<td class="font-weight-bold text-dark">{{ number_format($product->net_revenue, 2) }} ج.م</td>
								<td class="text-muted">{{ number_format($product->net_cost, 2) }} ج.م</td>
								<td class="font-weight-bold {{ $product->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
									{{ number_format($product->gross_profit, 2) }} ج.م
								</td>
								<td>
									<span class="badge {{ $margin >= 20 ? 'badge-success' : ($margin > 0 ? 'badge-warning' : 'badge-danger') }} font-weight-bold">
										{{ $margin }}%
									</span>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="mt-3 d-flex justify-content-between align-items-center">
				<span class="text-muted tx-12">
					عرض المنتجات من {{ $products->firstItem() }} إلى {{ $products->lastItem() }} من إجمالي {{ $products->total() }}
				</span>
				<div>
					{{ $products->links() }}
				</div>
			</div>
		@endif
	</div>
</div>

@endsection

