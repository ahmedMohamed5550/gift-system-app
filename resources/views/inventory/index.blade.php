@extends('layouts.master')

@section('title', 'مراقبة المخزون والنواقص - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة المخزون والجرد</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ مراقبة المخزون والنواقص</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('inventory.adjustments.create') }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-sliders-h ml-1"></i> تسوية جردية جديدة
			</a>
		</div>
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('inventory.movements') }}" class="btn btn-info font-weight-bold">
				<i class="fas fa-history ml-1"></i> سجل الحركات العام
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Alerts -->
@if(session('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-check-circle ml-1"></i> تم بنجاح!</strong> {{ session('success') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(session('error'))
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> تنبيه!</strong> {{ session('error') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<!-- Inventory Analytics / KPIs -->
<div class="row row-sm">
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">إجمالي قيمة المخزون (سعر التكلفة)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($metrics['total_stock_value'], 2) }} ج.م</h3>
							<p class="mb-0 tx-12 text-white op-7">إجمالي رأس المال بالبضاعة</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-boxes text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">إجمالي المنتجات المتابعة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ $metrics['total_products'] }}</h3>
							<p class="mb-0 tx-12 text-white op-7">{{ $metrics['active_products'] }} منتج مفعل للبيع</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-cubes text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">تنبيهات انخفاض المخزون</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ $metrics['low_stock_count'] }}</h3>
							<p class="mb-0 tx-12 text-white op-7">وصلت أو أقل من حد الطلب الأدنى</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-exclamation-triangle text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">منتجات نفذت تماماً</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ $metrics['out_of_stock_count'] }}</h3>
							<p class="mb-0 tx-12 text-white op-7">الرصيد الحالي = 0</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-ban text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Filters & Actions Card -->
<div class="card mb-4">
	<div class="card-body">
		<!-- Quick Filter Tabs -->
		<div class="d-flex flex-wrap align-items-center mb-3 pb-3 border-bottom">
			<span class="font-weight-bold ml-3 text-dark"><i class="fas fa-filter ml-1 text-primary"></i> تصفية سريعة:</span>
			<a href="{{ route('inventory.index', array_merge(request()->except(['page', 'stock_status']), ['stock_status' => 'all'])) }}"
			   class="btn btn-sm {{ ($stockStatus ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary' }} ml-2 mb-1">
				الكل ({{ $metrics['total_products'] }})
			</a>
			<a href="{{ route('inventory.index', array_merge(request()->except(['page', 'stock_status']), ['stock_status' => 'low'])) }}"
			   class="btn btn-sm {{ ($stockStatus ?? '') === 'low' ? 'btn-warning' : 'btn-outline-warning' }} ml-2 mb-1">
				<i class="fas fa-exclamation-circle ml-1"></i> النواقص ({{ $metrics['low_stock_count'] }})
			</a>
			<a href="{{ route('inventory.index', array_merge(request()->except(['page', 'stock_status']), ['stock_status' => 'out'])) }}"
			   class="btn btn-sm {{ ($stockStatus ?? '') === 'out' ? 'btn-danger' : 'btn-outline-danger' }} ml-2 mb-1">
				<i class="fas fa-ban ml-1"></i> نفذت بالكامل ({{ $metrics['out_of_stock_count'] }})
			</a>
			<a href="{{ route('inventory.index', array_merge(request()->except(['page', 'stock_status']), ['stock_status' => 'in_stock'])) }}"
			   class="btn btn-sm {{ ($stockStatus ?? '') === 'in_stock' ? 'btn-success' : 'btn-outline-success' }} ml-2 mb-1">
				<i class="fas fa-check ml-1"></i> متوفرة
			</a>
		</div>

		<!-- Search & Category Form -->
		<form method="GET" action="{{ route('inventory.index') }}">
			<input type="hidden" name="stock_status" value="{{ $stockStatus ?? 'all' }}">
			<div class="row row-sm">
				<div class="col-lg-5 col-md-5 mb-2">
					<div class="input-group">
						<input type="text" name="search" class="form-control" placeholder="ابحث باسم المنتج أو تفاصيله..." value="{{ request('search') }}">
						<div class="input-group-append">
							<button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> بحث</button>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 mb-2">
					<select name="category_id" class="form-control" onchange="this.form.submit()">
						<option value="">-- كل الأقسام الرئيسية --</option>
						@foreach($categories as $cat)
							<option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
								{{ $cat->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-lg-3 col-md-3 mb-2 text-left">
					<a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-block">
						<i class="fas fa-redo ml-1"></i> إعادة ضبط الفلاتر
					</a>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Stock Inventory Table Card -->
<div class="card" id="live-search-container">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h4 class="card-title mg-b-0"><i class="fas fa-warehouse ml-1 text-primary"></i> رصيد المخزون الحالي للمنتجات</h4>
		<span class="tx-12 text-muted">إجمالي المنتجات المعروضة: {{ $products->total() }}</span>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
				<thead>
					<tr>
						<th>#</th>
						<th class="text-right">المنتج</th>
						<th>القسم</th>
						<th>سعر التكلفة</th>
						<th>سعر البيع</th>
						<th>الرصيد الحالي</th>
						<th>حد الطلب</th>
						<th>حالة المخزون</th>
						<th>قيمة الرصيد</th>
						<th>الإجراءات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($products as $product)
						<tr class="{{ $product->is_out_of_stock ? 'table-danger' : ($product->is_low_stock ? 'table-warning' : '') }}">
							<td>{{ $product->id }}</td>
							<td class="text-right font-weight-bold">
								<a href="{{ route('products.show', $product) }}" class="text-dark">
									{{ $product->name }}
								</a>
								@if(!$product->is_active)
									<span class="badge badge-secondary mr-1">معطل</span>
								@endif
							</td>
							<td>
								<span class="badge badge-light border">
									{{ $product->subcategory->category->name ?? '-' }} / {{ $product->subcategory->name ?? '-' }}
								</span>
							</td>
							<td>{{ number_format($product->purchase_price, 2) }} ج.م</td>
							<td class="font-weight-semibold text-primary">{{ number_format($product->selling_price, 2) }} ج.م</td>
							<td>
								<span class="badge {{ $product->stock_status_badge }} tx-14 px-3 py-1">
									{{ number_format($product->stock_quantity, 3) }}
								</span>
							</td>
							<td>
								<span class="tx-12 text-muted font-weight-bold">{{ number_format($product->minimum_stock, 3) }}</span>
							</td>
							<td>
								@if($product->is_out_of_stock)
									<span class="badge badge-danger"><i class="fas fa-ban ml-1"></i> نفذ من المخزون</span>
								@elseif($product->is_low_stock)
									<span class="badge badge-warning"><i class="fas fa-exclamation-triangle ml-1"></i> مخزون منخفض</span>
								@else
									<span class="badge badge-success"><i class="fas fa-check-circle ml-1"></i> متوفر</span>
								@endif
							</td>
							<td class="font-weight-bold text-dark">
								{{ number_format($product->total_stock_value, 2) }} ج.م
							</td>
							<td>
								<div class="btn-group" role="group">
									<a href="{{ route('inventory.adjustments.create', ['product_id' => $product->id]) }}"
									   class="btn btn-sm btn-primary" title="تسوية جردية سريعة">
										<i class="fas fa-sliders-h ml-1"></i> تسوية
									</a>
									<a href="{{ route('inventory.product-movements', $product) }}"
									   class="btn btn-sm btn-info" title="سجل حركات هذا المنتج">
										<i class="fas fa-history"></i>
									</a>
									<a href="{{ route('products.show', $product) }}"
									   class="btn btn-sm btn-secondary" title="بيانات المنتج الكاملة">
										<i class="fas fa-eye"></i>
									</a>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="10" class="text-center py-4">
								<i class="fas fa-box-open text-muted tx-40 mb-2"></i>
								<p class="text-muted mb-0">لا توجد منتجات مطابقة لخيارات البحث والتصفية.</p>
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<!-- Pagination -->
		<div class="mt-4 d-flex justify-content-center">
			{{ $products->links() }}
		</div>
	</div>
</div>
@endsection

