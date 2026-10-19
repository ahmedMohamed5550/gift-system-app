@extends('layouts.master')

@section('title', 'إدارة المنتجات والمخزون - متجر الهدايا')

@section('css')
<style>
	.action-btns-wrapper {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 5px;
	}
	.btn-action-icon {
		width: 32px !important;
		height: 32px !important;
		min-width: 32px !important;
		min-height: 32px !important;
		padding: 0 !important;
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		border-radius: 4px !important;
		font-size: 13px !important;
		line-height: 1 !important;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		border: none !important;
	}
	.btn-action-icon i {
		font-size: 13px !important;
		line-height: 1 !important;
		margin: 0 !important;
	}
	.btn-action-icon:hover {
		opacity: 0.9;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
	}
</style>
@endsection

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">المخزون والمنتجات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ قائمة المنتجات</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('products.create') }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-box-open ml-1"></i> إضافة منتج جديد
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Alerts -->
@if(session('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-check-circle ml-1"></i> {{ session('success') }}</strong>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(session('error'))
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> {{ session('error') }}</strong>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<!-- Stats Cards -->
<div class="row row-sm">
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">إجمالي المنتجات</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['total'] }}</h4>
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
				<div>
					<h6 class="mb-3 tx-12 text-white">المنتجات النشطة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['active'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-check text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card {{ $stats['low_stock'] > 0 ? 'bg-danger-gradient' : 'bg-warning-gradient' }}">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">تنبيهات انخفاض المخزون</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['low_stock'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-exclamation-circle text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">القيمة التقديرية للمخزون (تكلفة)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-18 font-weight-bold mb-1 text-white">{{ number_format($stats['inventory_value'], 2) }} جنيه مصري</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-coins text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Search & Filter Card -->
<div class="card mg-b-20">
	<div class="card-body">
		<form method="GET" action="{{ route('products.index') }}" class="row row-sm">
			<div class="col-lg-3 col-md-6 mg-b-10">
				<div class="input-group">
					<input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="اسم المنتج أو الوصف...">
					<span class="input-group-append">
						<button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
					</span>
				</div>
			</div>

			<div class="col-lg-2 col-md-3 mg-b-10">
				<select name="category_id" class="form-control" onchange="this.form.submit()">
					<option value="">-- كل الأقسام الرئيسية --</option>
					@foreach($categories as $category)
						<option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
							{{ $category->name }}
						</option>
					@endforeach
				</select>
			</div>

			<div class="col-lg-2 col-md-3 mg-b-10">
				<select name="subcategory_id" class="form-control" onchange="this.form.submit()">
					<option value="">-- كل الأقسام الفرعية --</option>
					@foreach($subcategories as $subcategory)
						<option value="{{ $subcategory->id }}" {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
							{{ $subcategory->name }} ({{ $subcategory->category->name ?? '' }})
						</option>
					@endforeach
				</select>
			</div>

			<div class="col-lg-2 col-md-3 mg-b-10">
				<select name="status" class="form-control" onchange="this.form.submit()">
					<option value="">-- جميع الحالات --</option>
					<option value="1" {{ request('status') === '1' ? 'selected' : '' }}>نشط</option>
					<option value="0" {{ request('status') === '0' ? 'selected' : '' }}>معطل</option>
				</select>
			</div>

			<div class="col-lg-2 col-md-3 mg-b-10">
				<select name="stock_filter" class="form-control" onchange="this.form.submit()">
					<option value="">-- جميع الكميات --</option>
					<option value="low" {{ request('stock_filter') === 'low' ? 'selected' : '' }}>مخزون منخفض فقط</option>
				</select>
			</div>

			<div class="col-lg-1 col-md-12 mg-b-10 text-left">
				@if(request()->hasAny(['search', 'category_id', 'subcategory_id', 'status', 'stock_filter']))
					<a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-block px-1" title="إعادة تعيين">
						<i class="fas fa-redo"></i>
					</a>
				@endif
			</div>
		</form>
	</div>
</div>

<!-- Products Table Card -->
<div class="card" id="live-search-container">
	<div class="card-header pb-0">
		<div class="d-flex justify-content-between align-items-center">
			<h4 class="card-title mg-b-0">سجل منتجات المتجر</h4>
			<span class="tx-13 text-muted">عرض {{ $products->firstItem() ?? 0 }} إلى {{ $products->lastItem() ?? 0 }} من أصل {{ $products->total() }}</span>
		</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover table-striped mg-b-0 text-md-nowrap border">
				<thead class="bg-light">
					<tr>
						<th class="wd-5p">#</th>
						<th>المنتج</th>
						<th>التصنيف</th>
						<th class="text-right">سعر الشراء</th>
						<th class="text-right">سعر البيع</th>
						<th class="text-center">هامش الربح</th>
						<th class="text-center">الكمية بالمخزون</th>
						<th class="text-center">الحد الأدنى</th>
						<th>الحالة</th>
						<th class="text-center wd-15p">العمليات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($products as $product)
						<tr class="{{ $product->is_low_stock ? 'bg-danger-transparent' : '' }}">
							<td class="font-weight-bold">{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
							<td>
								<a href="{{ route('products.show', $product) }}" class="font-weight-bold text-dark">
									<i class="fas fa-gift ml-1 text-primary"></i> {{ $product->name }}
								</a>
								@if($product->is_low_stock)
									<span class="badge badge-danger tx-10 mr-1"><i class="fas fa-exclamation-triangle"></i> وشك النفاد</span>
								@endif
							</td>
							<td>
								@if($product->subcategory)
									<span class="badge badge-light-info tx-12 px-2 py-1">
										{{ $product->subcategory->category->name ?? '' }} / {{ $product->subcategory->name }}
									</span>
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td class="text-right font-weight-bold text-muted">{{ number_format($product->purchase_price, 2) }} جنيه مصري</td>
							<td class="text-right font-weight-bold text-success">{{ number_format($product->selling_price, 2) }} جنيه مصري</td>
							<td class="text-center">
								<span class="badge badge-pill {{ $product->profit >= 0 ? 'badge-success-light' : 'badge-danger-light' }} font-weight-bold">
									+{{ number_format($product->profit, 2) }} جنيه مصري
									({{ $product->profit_margin_percentage }}%)
								</span>
							</td>
							<td class="text-center">
								<span class="badge badge-pill {{ $product->is_low_stock ? 'badge-danger' : 'badge-primary' }} px-3 py-1 font-weight-bold tx-13">
									{{ rtrim(rtrim(number_format($product->stock_quantity, 3), '0'), '.') }}
								</span>
							</td>
							<td class="text-center text-muted">
								{{ rtrim(rtrim(number_format($product->minimum_stock, 3), '0'), '.') }}
							</td>
							<td>
								<span class="badge badge-pill {{ $product->is_active ? 'badge-success' : 'badge-danger' }} px-2 py-1">
									{{ $product->is_active ? 'نشط' : 'معطل' }}
								</span>
							</td>
							<td class="text-center">
								<div class="action-btns-wrapper">
									<a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-primary btn-action-icon" title="عرض تفاصيل المنتج">
										<i class="fas fa-eye"></i>
									</a>

									<a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-info btn-action-icon" title="تعديل">
										<i class="fas fa-edit"></i>
									</a>

									<!-- Toggle Status -->
									<form action="{{ route('products.toggle-status', $product) }}" method="POST" class="d-inline m-0 p-0">
										@csrf
										@method('PATCH')
										<button type="submit" class="btn btn-sm {{ $product->is_active ? 'btn-warning' : 'btn-success' }} btn-action-icon" title="{{ $product->is_active ? 'تعطيل المنتج' : 'تفعيل المنتج' }}">
											<i class="fas {{ $product->is_active ? 'fa-ban' : 'fa-check' }}"></i>
										</button>
									</form>

									<!-- Delete / Soft-Delete -->
									<form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟ سيتم الاحتفاظ بكافة فواتير المبيعات وحركات المخزون التاريخية.')">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn btn-sm btn-danger btn-action-icon" title="حذف">
											<i class="fas fa-trash-alt"></i>
										</button>
									</form>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="10" class="text-center py-4 text-muted">
								<i class="fas fa-box-open tx-40 mb-2 d-block"></i>
								لا توجد منتجات مسجلة تطابق خيارات الفلترة المحددة.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="d-flex justify-content-center mt-4">
			{{ $products->links() }}
		</div>
	</div>
</div>
@endsection

