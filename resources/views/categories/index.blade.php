@extends('layouts.master')

@section('title', 'الأقسام الرئيسية - متجر الهدايا')

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
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الأقسام الرئيسية للمنتجات</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('categories.create') }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-plus ml-1"></i> إضافة قسم رئيسي جديد
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
					<h6 class="mb-3 tx-12 text-white">إجمالي الأقسام الرئيسية</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['total'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-layer-group text-white tx-24"></i>
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
					<h6 class="mb-3 tx-12 text-white">الأقسام النشطة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['active'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-check-circle text-white tx-24"></i>
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
					<h6 class="mb-3 tx-12 text-white">الأقسام المعطلة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['inactive'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-pause-circle text-white tx-24"></i>
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
		<form method="GET" action="{{ route('categories.index') }}" class="row row-sm">
			<div class="col-lg-7 col-md-6 mg-b-10">
				<div class="input-group">
					<input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="البحث باسم القسم أو الوصف...">
					<span class="input-group-append">
						<button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> بحث</button>
					</span>
				</div>
			</div>

			<div class="col-lg-3 col-md-3 mg-b-10">
				<select name="status" class="form-control" onchange="this.form.submit()">
					<option value="">-- جميع الحالات --</option>
					<option value="1" {{ request('status') === '1' ? 'selected' : '' }}>نشط</option>
					<option value="0" {{ request('status') === '0' ? 'selected' : '' }}>معطل</option>
				</select>
			</div>

			<div class="col-lg-2 col-md-3 mg-b-10 text-left">
				@if(request()->hasAny(['search', 'status']))
					<a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-block">
						<i class="fas fa-redo ml-1"></i> إعادة تعيين
					</a>
				@endif
			</div>
		</form>
	</div>
</div>

<!-- Categories Table Card -->
<div class="card" id="live-search-container">
	<div class="card-header pb-0">
		<div class="d-flex justify-content-between align-items-center">
			<h4 class="card-title mg-b-0">سجل الأقسام الرئيسية</h4>
			<span class="tx-13 text-muted">عرض {{ $categories->firstItem() ?? 0 }} إلى {{ $categories->lastItem() ?? 0 }} من أصل {{ $categories->total() }}</span>
		</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover table-striped mg-b-0 text-md-nowrap border">
				<thead class="bg-light">
					<tr>
						<th class="wd-5p">#</th>
						<th>اسم القسم</th>
						<th>الوصف</th>
						<th class="text-center">الأقسام الفرعية</th>
						<th class="text-center">إجمالي المنتجات</th>
						<th>الحالة</th>
						<th>تاريخ الإنشاء</th>
						<th class="text-center wd-15p">العمليات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($categories as $category)
						<tr>
							<td class="font-weight-bold">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
							<td>
								<a href="{{ route('categories.show', $category) }}" class="font-weight-bold text-primary">
									<i class="fas fa-folder ml-1 text-warning"></i> {{ $category->name }}
								</a>
							</td>
							<td>{{ $category->description ? Str::limit($category->description, 50) : '-' }}</td>
							<td class="text-center">
								<a href="{{ route('subcategories.index', ['category_id' => $category->id]) }}" class="badge badge-info-light tx-12 px-2 py-1" title="عرض الأقسام الفرعية">
									{{ $category->subcategories_count }} قسم فرعي
								</a>
							</td>
							<td class="text-center">
								<span class="badge badge-light tx-12 font-weight-bold">
									{{ $category->products_count }} منتج
								</span>
							</td>
							<td>
								<span class="badge badge-pill {{ $category->is_active ? 'badge-success' : 'badge-danger' }} px-3 py-1">
									{{ $category->is_active ? 'نشط' : 'معطل' }}
								</span>
							</td>
							<td>{{ $category->created_at ? $category->created_at->format('Y-m-d') : '-' }}</td>
							<td class="text-center">
								<div class="action-btns-wrapper">
									<a href="{{ route('categories.show', $category) }}" class="btn btn-sm btn-primary btn-action-icon" title="عرض التفاصيل والأقسام الفرعية">
										<i class="fas fa-eye"></i>
									</a>

									<a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-info btn-action-icon" title="تعديل">
										<i class="fas fa-edit"></i>
									</a>

									<!-- Toggle Status -->
									<form action="{{ route('categories.toggle-status', $category) }}" method="POST" class="d-inline m-0 p-0">
										@csrf
										@method('PATCH')
										<button type="submit" class="btn btn-sm {{ $category->is_active ? 'btn-warning' : 'btn-success' }} btn-action-icon" title="{{ $category->is_active ? 'تعطيل القسم' : 'تفعيل القسم' }}">
											<i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
										</button>
									</form>

									<!-- Delete -->
									<form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('هل أنت متأكد من رغبتك بحذف هذا القسم؟')">
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
								<i class="fas fa-folder-open tx-40 mb-2 d-block"></i>
								لا توجد أقسام رئيسية مطابقة.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="d-flex justify-content-center mt-4">
			{{ $categories->links() }}
		</div>
	</div>
</div>
@endsection

