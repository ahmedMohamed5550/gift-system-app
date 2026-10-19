@extends('layouts.master')

@section('title', 'تقرير النواقص وحد الأمان للمخزون - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-exclamation-triangle ml-2 text-warning"></i>تقرير النواقص وحد الأمان للمخزون
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ رصد المنتجات المنتهية والمنخفضة عن حد الأمان واقتراح كميات التوريد</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<button type="button" onclick="window.print()" class="btn btn-outline-secondary font-weight-bold ml-2">
			<i class="fas fa-print ml-1"></i> طباعة التقرير
		</button>
		<a href="{{ route('reports.index') }}" class="btn btn-outline-primary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لمركز التقارير
		</a>
	</div>
</div>
@endsection

@section('content')

<!-- Filter Bar -->
<div class="card mb-3 shadow-sm border">
	<div class="card-body py-2">
		<form method="GET" action="{{ route('reports.low-stock') }}">
			<div class="row row-sm align-items-end">
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">بحث باسم أو كود المنتج:</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-search"></i></span>
						</div>
						<input type="text" name="search" class="form-control" placeholder="اسم المنتج أو الكود..." value="{{ $filters['search'] ?? '' }}">
					</div>
				</div>

				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">مستوى التنبيه:</label>
					<select name="status" class="form-control select2">
						<option value="">-- كافة التنبيهات --</option>
						<option value="out_of_stock" {{ ($filters['status'] ?? '') == 'out_of_stock' ? 'selected' : '' }}>نفد من المخزن (رصيد صفري أو سالب)</option>
						<option value="low_stock" {{ ($filters['status'] ?? '') == 'low_stock' ? 'selected' : '' }}>قارب على النفاد (أقل من الحد الأدنى)</option>
					</select>
				</div>

				<div class="col-md-2 col-sm-6 mb-2">
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

				<div class="col-md-2 col-sm-6 mb-2">
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

				<div class="col-md-2 col-sm-12 mb-2">
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
	<!-- Total Alerts -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">إجمالي تنبيهات النواقص</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ $summary['total_alerts'] }} صنف</h3>
				<p class="mb-0 tx-12 text-white op-8">أصناف تحتاج لإعادة توريد عاجل</p>
			</div>
		</div>
	</div>

	<!-- Out of stock -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">نفد من المخزن تماماً (رصيد 0)</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ $summary['out_of_stock_count'] }} صنف</h3>
				<p class="mb-0 tx-12 text-white op-8">توقف بيعها لعدم وجود رصيد</p>
			</div>
		</div>
	</div>

	<!-- Low stock -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">قارب على النفاد (مستوى حرج)</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ $summary['low_stock_count'] }} صنف</h3>
				<p class="mb-0 tx-12 text-white op-8">الرصيد أقل من أو يساوي الحد الأدنى</p>
			</div>
		</div>
	</div>

	<!-- Reorder Cost Estimate -->
	<div class="col-xl-3 col-lg-6 col-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="p-3">
				<h6 class="mb-2 tx-12 text-white font-weight-bold">التكلفة التقديرية لإعادة التوريد</h6>
				<h3 class="tx-22 font-weight-bold mb-1 text-white">{{ number_format($summary['reorder_cost_estimate'], 2) }} جنيه</h3>
				<p class="mb-0 tx-12 text-white op-8">لتأمين رصيد آمن (ضعف الحد الأدنى)</p>
			</div>
		</div>
	</div>
</div>

<!-- Detailed Table -->
<div class="card border shadow-sm">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-list ml-1 text-warning"></i> قائمة المنتجات التي تحتاج لإعادة التوريد
		</h5>
		<span class="text-muted tx-13">عدد المنتجات: {{ $products->total() }}</span>
	</div>
	<div class="card-body">
		@if($products->isEmpty())
			<div class="text-center py-5 text-success">
				<i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
				<h5>مستوى المخزون آمن ومثالي! لا توجد منتجات منخفضة عن الحد الأدنى.</h5>
			</div>
		@else
			<div class="table-responsive">
				<table class="table table-hover table-striped text-center align-middle mb-0 tx-13">
					<thead class="thead-light">
						<tr>
							<th>#</th>
							<th class="text-right">المنتج</th>
							<th>التصنيف</th>
							<th>سعر الشراء</th>
							<th>سعر البيع</th>
							<th>الرصيد الحالي</th>
							<th>الحد الأدنى</th>
							<th>كمية التوريد المقترحة</th>
							<th>التكلفة التقديرية</th>
							<th>مستوى التنبيه</th>
							<th>إجراءات</th>
						</tr>
					</thead>
					<tbody>
						@foreach($products as $product)
							@php
								$suggestedQty = max(0, ($product->minimum_stock * 2) - $product->stock_quantity);
								$estCost = $suggestedQty * (float) $product->purchase_price;
							@endphp
							<tr class="{{ $product->stock_quantity <= 0 ? 'table-danger' : 'table-warning' }}">
								<td>{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
								<td class="text-right font-weight-bold text-dark">
									<a href="{{ route('products.show', $product) }}" class="text-dark">
										{{ $product->name }}
									</a>
									<span class="d-block tx-11 text-muted">#{{ $product->id }}</span>
								</td>
								<td class="tx-12 text-muted">
									{{ $product->subcategory->category->name ?? '-' }} / {{ $product->subcategory->name ?? '-' }}
								</td>
								<td>{{ number_format($product->purchase_price, 2) }} ج.م</td>
								<td>{{ number_format($product->selling_price, 2) }} ج.م</td>
								<td class="font-weight-bold tx-14 {{ $product->stock_quantity <= 0 ? 'text-danger' : 'text-warning' }}">
									{{ rtrim(rtrim(number_format($product->stock_quantity, 3), '0'), '.') }}
								</td>
								<td class="text-muted font-weight-semibold">
									{{ rtrim(rtrim(number_format($product->minimum_stock, 3), '0'), '.') }}
								</td>
								<td>
									<span class="badge badge-primary px-2 py-1 font-weight-bold tx-13">
										+{{ rtrim(rtrim(number_format($suggestedQty, 3), '0'), '.') }} قطعة
									</span>
								</td>
								<td class="font-weight-bold text-dark">
									{{ number_format($estCost, 2) }} ج.م
								</td>
								<td>
									@if($product->stock_quantity <= 0)
										<span class="badge badge-danger font-weight-bold px-2 py-1">نفد تماماً</span>
									@else
										<span class="badge badge-warning font-weight-bold px-2 py-1">قارب على النفاد</span>
									@endif
								</td>
								<td>
									<div class="btn-group" role="group">
										<a href="{{ route('inventory.adjustments.create', ['product_id' => $product->id]) }}" class="btn btn-xs btn-primary font-weight-bold" title="تسوية جردية">
											<i class="fas fa-sliders-h ml-1"></i> تسوية
										</a>
										<a href="{{ route('products.edit', $product) }}" class="btn btn-xs btn-outline-secondary" title="تعديل المنتج">
											<i class="fas fa-edit"></i>
										</a>
									</div>
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

