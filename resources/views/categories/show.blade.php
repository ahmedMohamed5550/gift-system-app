@extends('layouts.master')

@section('title', 'تفاصيل القسم: ' . $category->name)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">الأقسام الرئيسية</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل {{ $category->name }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('subcategories.create', ['category_id' => $category->id]) }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-plus ml-1"></i> إضافة قسم فرعي لهذا القسم
			</a>
		</div>
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('categories.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-arrow-right ml-1"></i> العودة للأقسام
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Category Info Overview Card -->
<div class="card mg-b-20 border-top-primary">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div class="d-flex align-items-center">
				<div class="avatar avatar-md bg-primary-transparent text-primary ml-3 brround font-weight-bold">
					<i class="fas fa-folder tx-22"></i>
				</div>
				<div>
					<h3 class="font-weight-bold mb-0 text-dark">{{ $category->name }}</h3>
					<span class="text-muted tx-13">تم الإنشاء في: {{ $category->created_at ? $category->created_at->format('Y-m-d H:i') : '-' }}</span>
				</div>
			</div>
			<div>
				<span class="badge badge-pill {{ $category->is_active ? 'badge-success' : 'badge-danger' }} px-3 py-2 tx-13">
					{{ $category->is_active ? 'قسم نشط' : 'قسم معطل' }}
				</span>
				<a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-info btn-sm mr-2 font-weight-bold">
					<i class="fas fa-edit ml-1"></i> تعديل
				</a>
			</div>
		</div>

		@if($category->description)
			<div class="p-3 bg-light rounded text-dark mb-2">
				<strong>الوصف:</strong> {{ $category->description }}
			</div>
		@endif
	</div>
</div>

<!-- Subcategories Table Card -->
<div class="card">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<div>
			<h4 class="card-title mg-b-0"><i class="fas fa-sitemap ml-1 text-primary"></i> الأقسام الفرعية التابعة لهذا القسم</h4>
			<p class="tx-12 tx-gray-500 mb-0">جميع الأقسام الفرعية والمنتجات المندرجة تحت قسم "{{ $category->name }}".</p>
		</div>
		<span class="badge badge-light-primary tx-13 px-3 py-1 font-weight-bold">
			{{ $category->subcategories->count() }} أقسام فرعية
		</span>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover table-striped mg-b-0 text-md-nowrap border">
				<thead class="bg-light">
					<tr>
						<th class="wd-5p">#</th>
						<th>اسم القسم الفرعي</th>
						<th>الوصف</th>
						<th class="text-center">عدد المنتجات</th>
						<th>الحالة</th>
						<th>تاريخ الإضافة</th>
						<th class="text-center wd-15p">العمليات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($category->subcategories as $subcategory)
						<tr>
							<td class="font-weight-bold">{{ $loop->iteration }}</td>
							<td>
								<a href="{{ route('products.index', ['subcategory_id' => $subcategory->id]) }}" class="font-weight-bold text-dark">
									<i class="fas fa-angle-left ml-1 text-primary"></i> {{ $subcategory->name }}
								</a>
							</td>
							<td>{{ $subcategory->description ? Str::limit($subcategory->description, 50) : '-' }}</td>
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
								<div class="btn-group btn-group-sm" role="group">
									<a href="{{ route('subcategories.edit', $subcategory) }}" class="btn btn-info" title="تعديل">
										<i class="fas fa-edit"></i>
									</a>

									<form action="{{ route('subcategories.toggle-status', $subcategory) }}" method="POST" class="d-inline">
										@csrf
										@method('PATCH')
										<button type="submit" class="btn {{ $subcategory->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $subcategory->is_active ? 'تعطيل' : 'تفعيل' }}">
											<i class="fas {{ $subcategory->is_active ? 'fa-ban' : 'fa-check' }}"></i>
										</button>
									</form>

									<form action="{{ route('subcategories.destroy', $subcategory) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا القسم الفرعي؟')">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn btn-danger" title="حذف">
											<i class="fas fa-trash-alt"></i>
										</button>
									</form>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="text-center py-4 text-muted">
								<i class="fas fa-sitemap tx-40 mb-2 d-block"></i>
								لا توجد أقسام فرعية مسجلة تحت هذا القسم حتى الآن.
								<div class="mt-2">
									<a href="{{ route('subcategories.create', ['category_id' => $category->id]) }}" class="btn btn-sm btn-primary">
										<i class="fas fa-plus ml-1"></i> أضف أول قسم فرعي الآن
									</a>
								</div>
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection

