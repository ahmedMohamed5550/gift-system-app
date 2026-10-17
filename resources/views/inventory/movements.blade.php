@extends('layouts.master')

@section('title', 'سجل حركات المخزون - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة المخزون والجرد</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ سجل حركات المخزون العام</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('inventory.adjustments.create') }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-sliders-h ml-1"></i> تسوية جردية جديدة
			</a>
		</div>
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('inventory.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-warehouse ml-1"></i> مراقبة المخزون
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Movement Analytics Cards -->
<div class="row row-sm">
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">إجمالي الحركات المسجلة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['total_count']) }}</h3>
							<p class="mb-0 tx-12 text-white op-7">حركة مخزنية موثقة</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-history text-white tx-24"></i>
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
					<h6 class="mb-3 tx-12 text-white">حركات الوارد الإضافية (IN)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['in_count']) }}</h3>
							<p class="mb-0 tx-12 text-white op-7">شراء، تسوية إضافة، مرتجع</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-arrow-down text-white tx-24"></i>
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
					<h6 class="mb-3 tx-12 text-white">حركات الصرف والخصم (OUT)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['out_count']) }}</h3>
							<p class="mb-0 tx-12 text-white op-7">مبيعات، تسوية عجز أو تلف</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-arrow-up text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">صافي فارق الكميات (Net Change)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">
								{{ $stats['net_quantity'] >= 0 ? '+' : '' }}{{ number_format($stats['net_quantity'], 3) }}
							</h3>
							<p class="mb-0 tx-12 text-white op-7">إجمالي الزيادة / النقص</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-exchange-alt text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
	<div class="card-body">
		<form method="GET" action="{{ route('inventory.movements') }}">
			<div class="row row-sm">
				<div class="col-lg-3 col-md-6 mb-2">
					<label class="form-label tx-12">المنتج</label>
					<select name="product_id" class="form-control">
						<option value="">-- كل المنتجات --</option>
						@foreach($products as $p)
							<option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
								{{ $p->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-lg-2 col-md-6 mb-2">
					<label class="form-label tx-12">نوع الحركة</label>
					<select name="reference_type" class="form-control">
						<option value="">-- كل العمليات --</option>
						<option value="adjustment" {{ request('reference_type') == 'adjustment' ? 'selected' : '' }}>تسوية جردية</option>
						<option value="purchase" {{ request('reference_type') == 'purchase' ? 'selected' : '' }}>شراء / توريد</option>
						<option value="sale" {{ request('reference_type') == 'sale' ? 'selected' : '' }}>مبيعات</option>
						<option value="return" {{ request('reference_type') == 'return' ? 'selected' : '' }}>مرتجع مبيعات</option>
					</select>
				</div>
				<div class="col-lg-2 col-md-6 mb-2">
					<label class="form-label tx-12">الاتجاه</label>
					<select name="type" class="form-control">
						<option value="">-- وارد وصادر --</option>
						<option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>وارد (+)</option>
						<option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>صادر (-)</option>
					</select>
				</div>
				<div class="col-lg-2 col-md-6 mb-2">
					<label class="form-label tx-12">المسؤول</label>
					<select name="user_id" class="form-control">
						<option value="">-- كل المستخدمين --</option>
						@foreach($users as $u)
							<option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
								{{ $u->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-lg-3 col-md-12 mb-2 d-flex align-items-end">
					<button type="submit" class="btn btn-primary ml-2 flex-grow-1">
						<i class="fas fa-filter ml-1"></i> تصفية
					</button>
					<a href="{{ route('inventory.movements') }}" class="btn btn-secondary">
						<i class="fas fa-redo"></i>
					</a>
				</div>
			</div>
			<div class="row row-sm mt-1">
				<div class="col-lg-3 col-md-6 mb-2">
					<label class="form-label tx-12">من تاريخ</label>
					<input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
				</div>
				<div class="col-lg-3 col-md-6 mb-2">
					<label class="form-label tx-12">إلى تاريخ</label>
					<input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Stock Movements Table Card -->
<div class="card">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h4 class="card-title mg-b-0"><i class="fas fa-list-alt ml-1 text-primary"></i> سجل الحركات المخزنية التفصيلي</h4>
		<span class="tx-12 text-muted">إجمالي الحركات المعروضة: {{ $movements->total() }}</span>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
				<thead>
					<tr>
						<th>#</th>
						<th>التاريخ والوقت</th>
						<th class="text-right">المنتج</th>
						<th>العملية</th>
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
								{{ $movement->created_at ? $movement->created_at->format('Y-m-d H:i') : '-' }}
							</td>
							<td class="text-right font-weight-bold">
								@if($movement->product)
									<a href="{{ route('inventory.product-movements', $movement->product) }}" class="text-dark">
										{{ $movement->product->name }}
									</a>
									<span class="d-block tx-11 text-muted">
										{{ $movement->product->subcategory->name ?? '' }}
									</span>
								@else
									<span class="text-muted">منتج محذوف (#{{ $movement->product_id }})</span>
								@endif
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
								<span class="font-weight-bold tx-14 {{ $movement->is_increase ? 'text-success' : 'text-danger' }}">
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
							<td colspan="9" class="text-center py-4">
								<i class="fas fa-clipboard-list text-muted tx-40 mb-2"></i>
								<p class="text-muted mb-0">لا توجد حركات مخزنية مطابقة للبحث أو لم يتم تسجيل حركات بعد.</p>
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

