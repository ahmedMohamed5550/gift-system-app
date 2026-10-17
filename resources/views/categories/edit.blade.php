@extends('layouts.master')

@section('title', 'تعديل القسم الرئيسي - ' . $category->name)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">الأقسام الرئيسية</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تعديل: {{ $category->name }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('categories.index') }}" class="btn btn-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة للأقسام
		</a>
	</div>
</div>
@endsection

@section('content')
<div class="row row-sm justify-content-center">
	<div class="col-lg-7 col-xl-6">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-edit ml-1 text-primary"></i> تعديل بيانات القسم</h4>
				<p class="tx-12 tx-gray-500 mb-2">تعديل الاسم والوصف وحالة التفعيل للقسم الرئيسي.</p>
			</div>
			<div class="card-body">
				<form action="{{ route('categories.update', $category) }}" method="POST">
					@csrf
					@method('PUT')

					<div class="form-group text-right">
						<label for="name" class="font-weight-semibold">اسم القسم الرئيسي <span class="text-danger">*</span></label>
						<input id="name"
							   type="text"
							   name="name"
							   value="{{ old('name', $category->name) }}"
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
								  class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
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
								   {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
							<label class="custom-control-label font-weight-semibold" for="is_active">
								{{ $category->is_active ? 'القسم نشط حالياً' : 'القسم معطل حالياً' }}
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
						<a href="{{ route('categories.index') }}" class="btn btn-outline-secondary px-3 mr-2">
							إلغاء
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

