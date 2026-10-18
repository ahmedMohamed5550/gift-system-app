@extends('layouts.master')

@section('title', 'سجل حركات المنتج - ' . $product->name)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة المخزون</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ سجل حركات: {{ $product->name }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('inventory.adjustments.create', ['product_id' => $product->id]) }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-sliders-h ml-1"></i> تسوية رصيد هذا المنتج
			</a>
		</div>
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('inventory.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-warehouse ml-1"></i> مراقبة المخزون
			</a>
		</div>
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('products.show', $product) }}" class="btn btn-light border font-weight-bold">
				<i class="fas fa-eye ml-1"></i> بطاقة المنتج
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Product Summary Header Card -->
<div class="card mb-4 border-right border-primary" style="border-right-width: 4px !important;">
	<div class="card-body">
		<div class="row align-items-center">
			<div class="col-lg-5 col-md-12 mb-3 mb-lg-0">
				<div class="d-flex align-items-center">
					<div class="avatar avatar-lg bg-primary-transparent text-primary rounded-circle ml-3">
						<i class="fas fa-box tx-24"></i>
					</div>
					<div>
						<h4 class="font-weight-bold mb-1">{{ $product->name }}</h4>
						<span class="text-muted tx-13">
							<i class="fas fa-folder ml-1"></i>
							{{ $product->subcategory->category->name ?? '-' }} / {{ $product->subcategory->name ?? '-' }}
						</span>
					</div>
				</div>
			</div>
			<div class="col-lg-7 col-md-12">
				<div class="row text-center">
					<div class="col-3">
						<span class="tx-11 text-muted d-block font-weight-bold">الرصيد الحالي</span>
						<span class="badge {{ $product->stock_status_badge }} tx-15 px-3 py-1">
							{{ number_format($product->stock_quantity, 3) }}
						</span>
					</div>
					<div class="col-3 border-right border-left">
						<span class="tx-11 text-muted d-block font-weight-bold">حد الطلب</span>
						<strong class="tx-16 text-warning">{{ number_format($product->minimum_stock, 3) }}</strong>
					</div>
					<div class="col-3 border-left">
						<span class="tx-11 text-muted d-block font-weight-bold">سعر الشراء</span>
						<strong class="tx-16 text-dark">{{ number_format($product->purchase_price, 2) }} ج.م</strong>
					</div>
					<div class="col-3">
						<span class="tx-11 text-muted d-block font-weight-bold">سعر البيع</span>
						<strong class="tx-16 text-primary">{{ number_format($product->selling_price, 2) }} ج.م</strong>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Filter Toolbar -->
<div class="card mb-4">
	<div class="card-body py-3">
		<form method="GET" action="{{ route('inventory.product-movements', $product) }}">
			<div class="row row-sm align-items-end">
				<div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
					<label class="form-label tx-12">نوع العملية</label>
					<select name="reference_type" class="form-control form-control-sm">
						<option value="">-- كل العمليات --</option>
						<option value="adjustment" {{ request('reference_type') == 'adjustment' ? 'selected' : '' }}>تسوية جردية</option>
						<option value="purchase" {{ request('reference_type') == 'purchase' ? 'selected' : '' }}>شراء / توريد</option>
						<option value="sale" {{ request('reference_type') == 'sale' ? 'selected' : '' }}>مبيعات</option>
						<option value="return" {{ request('reference_type') == 'return' ? 'selected' : '' }}>مرتجع مبيعات</option>
					</select>
				</div>
				<div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
					<label class="form-label tx-12">الاتجاه</label>
					<select name="type" class="form-control form-control-sm">
						<option value="">-- وارد وصادر --</option>
						<option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>وارد (+)</option>
						<option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>صادر (-)</option>
					</select>
				</div>
				<div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
					<label class="form-label tx-12">من تاريخ</label>
					<input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
				</div>
				<div class="col-lg-3 col-md-12 d-flex">
					<button type="submit" class="btn btn-sm btn-primary ml-2 flex-grow-1">
						<i class="fas fa-filter ml-1"></i> تصفية
					</button>
					<a href="{{ route('inventory.product-movements', $product) }}" class="btn btn-sm btn-secondary">
						<i class="fas fa-redo"></i>
					</a>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Product Movements Table Card -->
<div class="card">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h4 class="card-title mg-b-0"><i class="fas fa-history ml-1 text-primary"></i> السجل التاريخي لحركات المنتج</h4>
		<span class="tx-12 text-muted">إجمالي الحركات: {{ $movements->total() }}</span>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
				<thead>
					<tr>
						<th>#</th>
						<th>التاريخ والوقت</th>
						<th>نوع العملية</th>
						<th>الاتجاه</th>
						<th>الكمية</th>
						<th>المستخدم المسؤول</th>
						<th>المرجع</th>
						<th class="text-right">البيان / سبب الحركة</th>
					</tr>
				</thead>
				<tbody>
					@forelse($movements as $movement)
						<tr>
							<td>{{ $movement->id }}</td>
							<td class="tx-12 text-muted font-weight-semibold">
								<i class="far fa-clock ml-1"></i>
								{{ $movement->created_at ? $movement->created_at->translatedFormat('Y-m-d h:i A') : '-' }}
							</td>
							<td>
								<span class="badge {{ $movement->reference_type_badge_class }} px-2 py-1">
									{{ $movement->reference_type_label }}
								</span>
							</td>
							<td>
								<span class="badge {{ $movement->type_badge_class }}">
									{{ $movement->type_direction_arabic }}
								</span>
							</td>
							<td>
								<span class="font-weight-bold tx-15 {{ $movement->is_increase ? 'text-success' : 'text-danger' }}">
									{{ $movement->formatted_quantity }}
								</span>
							</td>
							<td>
								<i class="fas fa-user-circle ml-1 text-secondary"></i>
								{{ $movement->user->name ?? 'النظام' }}
							</td>
							<td>
								@if($movement->reference_id)
									<span class="badge badge-light border">#{{ $movement->reference_id }}</span>
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td class="text-right tx-12">
								{{ $movement->notes ?: '-' }}
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="8" class="text-center py-4">
								<i class="fas fa-box-open text-muted tx-40 mb-2"></i>
								<p class="text-muted mb-0">لم تسجل أي حركة مخزنية لهذا المنتج بعد.</p>
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<!-- Pagination -->
		<div class="mt-4 d-flex justify-content-center">
			{{ $movements->links() }}
		</div>
	</div>
</div>
@endsection

