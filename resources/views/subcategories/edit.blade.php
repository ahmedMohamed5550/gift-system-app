@extends('layouts.master')

@section('title', 'تعديل القسم الفرعي - ' . $subcategory->name)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">الأقسام الفرعية</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تعديل: {{ $subcategory->name }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('subcategories.index') }}" class="btn btn-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة للأقسام الفرعية
		</a>
	</div>
</div>
@endsection

@section('content')
<div class="row row-sm justify-content-center">
	<div class="col-lg-7 col-xl-6">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-edit ml-1 text-primary"></i> تعديل بيانات القسم الفرعي</h4>
				<p class="tx-12 tx-gray-500 mb-2">تعديل الاسم والوصف والقسم الرئيسي التابع له.</p>
			</div>
			<div class="card-body">
				<form action="{{ route('subcategories.update', $subcategory) }}" method="POST">
					@csrf
					@method('PUT')

					<div class="form-group text-right">
						<label for="category_id" class="font-weight-semibold">القسم الرئيسي التابع له <span class="text-danger">*</span></label>
						<select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
							<option value="">-- اختر القسم الرئيسي --</option>
							@foreach($categories as $category)
								<option value="{{ $category->id }}" {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
									{{ $category->name }}
								</option>
							@endforeach
						</select>
						@error('category_id')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<div class="form-group text-right">
						<label for="name" class="font-weight-semibold">اسم القسم الفرعي <span class="text-danger">*</span></label>
						<input id="name"
							   type="text"
							   name="name"
							   value="{{ old('name', $subcategory->name) }}"
							   class="form-control @error('name') is-invalid @enderror"
							   required
							   autofocus>
						@error('name')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<div class="form-group text-right">
						<label for="description" class="font-weight-semibold">الوصف / ملاحظات</label>
						<textarea id="description"
								  name="description"
								  rows="3"
								  class="form-control @error('description') is-invalid @enderror">{{ old('description', $subcategory->description) }}</textarea>
						@error('description')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<div class="form-group text-right">
						<div class="custom-control custom-switch mt-2">
							<input type="checkbox"
								   class="custom-control-input"
								   id="is_active"
								   name="is_active"
								   value="1"
								   {{ old('is_active', $subcategory->is_active) ? 'checked' : '' }}>
							<label class="custom-control-label font-weight-semibold" for="is_active">
								{{ $subcategory->is_active ? 'القسم الفرعي نشط حالياً' : 'القسم الفرعي معطل حالياً' }}
							</label>
						</div>
						@error('is_active')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<div class="pt-3 border-top text-left">
						<button type="submit" class="btn btn-primary px-4 font-weight-bold">
							<i class="fas fa-save ml-1"></i> حفظ التعديلات
						</button>
						<a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary px-3 mr-2">
							إلغاء
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

