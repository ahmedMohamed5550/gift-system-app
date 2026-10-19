@extends('layouts.master')

@section('title', 'الأقسام الفرعية - متجر الهدايا')

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
			<h4 class="content-title mb-0 my-auto">التصنيفات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الأقسام الفرعية</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('subcategories.create', ['category_id' => request('category_id')]) }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-plus ml-1"></i> إضافة قسم فرعي جديد
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
	<div class="col-xl-4 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">إجمالي الأقسام الفرعية</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['total'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-sitemap text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-4 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">الأقسام الفرعية النشطة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['active'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-check-double text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-4 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">الأقسام الفرعية المعطلة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['inactive'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-pause text-white tx-24"></i>
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
		<form method="GET" action="{{ route('subcategories.index') }}" class="row row-sm">
			<div class="col-lg-4 col-md-6 mg-b-10">
				<div class="input-group">
					<input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="البحث بالاسم أو الوصف...">
					<span class="input-group-append">
						<button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> بحث</button>
					</span>
				</div>
			</div>

			<div class="col-lg-3 col-md-3 mg-b-10">
				<select name="category_id" class="form-control" onchange="this.form.submit()">
					<option value="">-- فلترة حسب القسم الرئيسي --</option>
					@foreach($categories as $category)
						<option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
							{{ $category->name }}
						</option>
					@endforeach
				</select>
			</div>

			<div class="col-lg-3 col-md-3 mg-b-10">
				<select name="status" class="form-control" onchange="this.form.submit()">
					<option value="">-- جميع الحالات --</option>
					<option value="1" {{ request('status') === '1' ? 'selected' : '' }}>نشط</option>
					<option value="0" {{ request('status') === '0' ? 'selected' : '' }}>معطل</option>
				</select>
			</div>

			<div class="col-lg-2 col-md-12 mg-b-10 text-left">
				@if(request()->hasAny(['search', 'category_id', 'status']))
					<a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary btn-block">
						<i class="fas fa-redo ml-1"></i> إعادة تعيين
					</a>
				@endif
			</div>
		</form>
	</div>
</div>

<!-- Subcategories Table Card -->
<div class="card">
	<div class="card-header pb-0">
		<div class="d-flex justify-content-between align-items-center">
			<h4 class="card-title mg-b-0">سجل الأقسام الفرعية</h4>
			<span class="tx-13 text-muted">عرض {{ $subcategories->firstItem() ?? 0 }} إلى {{ $subcategories->lastItem() ?? 0 }} من أصل {{ $subcategories->total() }}</span>
		</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover table-striped mg-b-0 text-md-nowrap border">
				<thead class="bg-light">
					<tr>
						<th class="wd-5p">#</th>
						<th>اسم القسم الفرعي</th>
						<th>القسم الرئيسي</th>
						<th>الوصف</th>
						<th class="text-center">المنتجات التابعة</th>
						<th>الحالة</th>
						<th>تاريخ الإضافة</th>
						<th class="text-center wd-15p">العمليات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($subcategories as $subcategory)
						<tr>
							<td class="font-weight-bold">{{ $loop->iteration + ($subcategories->currentPage() - 1) * $subcategories->perPage() }}</td>
							<td class="font-weight-bold text-dark">
								<i class="fas fa-tag ml-1 text-info"></i> {{ $subcategory->name }}
							</td>
							<td>
								@if($subcategory->category)
									<a href="{{ route('categories.show', $subcategory->category) }}" class="badge badge-light-primary tx-12 px-2 py-1 font-weight-bold">
										<i class="fas fa-folder ml-1"></i> {{ $subcategory->category->name }}
									</a>
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td>{{ $subcategory->description ? Str::limit($subcategory->description, 45) : '-' }}</td>
							<td class="text-center">
								<a href="{{ route('products.index', ['subcategory_id' => $subcategory->id]) }}" class="badge badge-info tx-12 px-2 py-1 font-weight-bold">
									{{ $subcategory->products_count }} منتج
								</a>
							</td>
							<td>
								<span class="badge badge-pill {{ $subcategory->is_active ? 'badge-success' : 'badge-danger' }} px-3 py-1">
									{{ $subcategory->is_active ? 'نشط' : 'معطل' }}
								</span>
							</td>
							<td>{{ $subcategory->created_at ? $subcategory->created_at->format('Y-m-d') : '-' }}</td>
							<td class="text-center">
								<div class="action-btns-wrapper">
									<a href="{{ route('subcategories.edit', $subcategory) }}" class="btn btn-sm btn-info btn-action-icon" title="تعديل">
										<i class="fas fa-edit"></i>
									</a>

									<!-- Toggle Status -->
									<form action="{{ route('subcategories.toggle-status', $subcategory) }}" method="POST" class="d-inline m-0 p-0">
										@csrf
										@method('PATCH')
										<button type="submit" class="btn btn-sm {{ $subcategory->is_active ? 'btn-warning' : 'btn-success' }} btn-action-icon" title="{{ $subcategory->is_active ? 'تعطيل' : 'تفعيل' }}">
											<i class="fas {{ $subcategory->is_active ? 'fa-ban' : 'fa-check' }}"></i>
										</button>
									</form>

									<!-- Delete -->
									<form action="{{ route('subcategories.destroy', $subcategory) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('هل أنت متأكد من حذف هذا القسم الفرعي؟')">
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
							<td colspan="8" class="text-center py-4 text-muted">
								<i class="fas fa-sitemap tx-40 mb-2 d-block"></i>
								لا توجد أقسام فرعية مطابقة لخيارات البحث أو الفلترة.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="d-flex justify-content-center mt-4">
			{{ $subcategories->links() }}
		</div>
	</div>
</div>
@endsection

