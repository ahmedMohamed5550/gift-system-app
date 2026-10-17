@extends('layouts.master')

@section('title', 'تفاصيل المنتج: ' . $product->name)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">المخزون والمنتجات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل {{ $product->name }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('products.edit', $product) }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-edit ml-1"></i> تعديل بيانات المنتج
			</a>
		</div>
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('products.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Overview Card -->
<div class="card mg-b-20 border-top-primary">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div class="d-flex align-items-center">
				<div class="avatar avatar-lg bg-primary-transparent text-primary ml-3 brround font-weight-bold">
					<i class="fas fa-gift tx-26"></i>
				</div>
				<div>
					<h3 class="font-weight-bold mb-1 text-dark">{{ $product->name }}</h3>
					<div class="text-muted tx-13">
						@if($product->subcategory)
							<span class="badge badge-light-primary tx-12 px-2 py-1 ml-2">
								<i class="fas fa-folder text-warning ml-1"></i> {{ $product->subcategory->category->name ?? '' }}
							</span>
							<i class="fas fa-angle-left text-muted"></i>
							<span class="badge badge-light-info tx-12 px-2 py-1 mr-1">
								<i class="fas fa-tag ml-1"></i> {{ $product->subcategory->name }}
							</span>
						@endif
					</div>
				</div>
			</div>
			<div>
				<span class="badge badge-pill {{ $product->is_active ? 'badge-success' : 'badge-danger' }} px-3 py-2 tx-13">
					{{ $product->is_active ? 'منتج متاح ونشط' : 'منتج معطل' }}
				</span>

				@if($product->is_low_stock)
					<span class="badge badge-pill badge-danger px-3 py-2 tx-13 mr-1">
						<i class="fas fa-exclamation-triangle"></i> تنبيه: المخزون منخفض
					</span>
				@endif
			</div>
		</div>

		@if($product->description)
			<div class="p-3 bg-light rounded text-dark mt-3">
				<h6 class="font-weight-bold mb-1">وصف المنتج:</h6>
				<p class="mb-0">{{ $product->description }}</p>
			</div>
		@endif
	</div>
</div>

<!-- Price & Margin Metrics -->
<div class="row row-sm">
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card bg-gray-100">
			<div class="card-body">
				<span class="tx-13 text-muted d-block mb-1">سعر الشراء (التكلفة)</span>
				<h4 class="font-weight-bold text-dark mb-0">{{ number_format($product->purchase_price, 2) }} <span class="tx-14 font-weight-normal text-muted">ر.س</span></h4>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card bg-success-transparent border-success">
			<div class="card-body">
				<span class="tx-13 text-success d-block mb-1 font-weight-semibold">سعر البيع للجمهور</span>
				<h4 class="font-weight-bold text-success mb-0">{{ number_format($product->selling_price, 2) }} <span class="tx-14 font-weight-normal">ر.س</span></h4>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card bg-primary-transparent border-primary">
			<div class="card-body">
				<span class="tx-13 text-primary d-block mb-1 font-weight-semibold">هامش الربح المتوقع للقطعة</span>
				<h4 class="font-weight-bold text-primary mb-0">+{{ number_format($product->profit, 2) }} <span class="tx-14 font-weight-normal">ر.س ({{ $product->profit_margin_percentage }}%)</span></h4>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card bg-info-transparent border-info">
			<div class="card-body">
				<span class="tx-13 text-info d-block mb-1 font-weight-semibold">القيمة الإجمالية للمخزون الحالي</span>
				<h4 class="font-weight-bold text-info mb-0">{{ number_format($product->stock_quantity * $product->purchase_price, 2) }} <span class="tx-14 font-weight-normal">ر.س</span></h4>
			</div>
		</div>
	</div>
</div>

<!-- Stock Details & History Overview -->
<div class="row row-sm">
	<div class="col-lg-6">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-warehouse ml-1 text-primary"></i> بيانات ومستويات المخزون</h4>
			</div>
			<div class="card-body">
				<table class="table table-bordered mb-0">
					<tbody>
						<tr>
							<th class="bg-light font-weight-semibold wd-40p">الكمية الحالية المتوفرة</th>
							<td>
								<span class="badge badge-pill {{ $product->is_low_stock ? 'badge-danger' : 'badge-primary' }} px-3 py-1 font-weight-bold tx-14">
									{{ rtrim(rtrim(number_format($product->stock_quantity, 3), '0'), '.') }} قطعة
								</span>
							</td>
						</tr>
						<tr>
							<th class="bg-light font-weight-semibold">حد التنبيه الأدنى</th>
							<td>
								<span class="font-weight-bold text-muted tx-14">
									{{ rtrim(rtrim(number_format($product->minimum_stock, 3), '0'), '.') }} قطعة
								</span>
							</td>
						</tr>
						<tr>
							<th class="bg-light font-weight-semibold">تاريخ الإضافة للنظام</th>
							<td>{{ $product->created_at ? $product->created_at->format('Y-m-d H:i') : '-' }}</td>
						</tr>
						<tr>
							<th class="bg-light font-weight-semibold">آخر تحديث للبيانات</th>
							<td>{{ $product->updated_at ? $product->updated_at->diffForHumans() : '-' }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-chart-line ml-1 text-primary"></i> ملخص حركة وسجلات المنتج</h4>
			</div>
			<div class="card-body">
				<div class="d-flex justify-content-between p-3 bg-light rounded mb-3">
					<div>
						<h6 class="text-muted mb-1">مرات البيع في الفواتير</h6>
						<h4 class="font-weight-bold text-dark mb-0">{{ $product->saleItems->count() }} عملية</h4>
					</div>
					<div class="my-auto">
						<i class="fas fa-shopping-cart tx-30 text-success"></i>
					</div>
				</div>

				<div class="d-flex justify-content-between p-3 bg-light rounded">
					<div>
						<h6 class="text-muted mb-1">حركات المخزون المسجلة</h6>
						<h4 class="font-weight-bold text-dark mb-0">{{ $product->stockMovements->count() }} حركة</h4>
					</div>
					<div class="my-auto">
						<i class="fas fa-exchange-alt tx-30 text-info"></i>
					</div>
				</div>

				<div class="mt-4 text-left">
					<form action="{{ route('products.toggle-status', $product) }}" method="POST" class="d-inline">
						@csrf
						@method('PATCH')
						<button type="submit" class="btn {{ $product->is_active ? 'btn-warning' : 'btn-success' }} px-3 font-weight-bold">
							<i class="fas {{ $product->is_active ? 'fa-ban' : 'fa-check' }} ml-1"></i>
							{{ $product->is_active ? 'تعطيل المنتج' : 'تفعيل المنتج' }}
						</button>
					</form>

					<form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
						@csrf
						@method('DELETE')
						<button type="submit" class="btn btn-danger px-3 mr-2 font-weight-bold">
							<i class="fas fa-trash-alt ml-1"></i> حذف المنتج
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

