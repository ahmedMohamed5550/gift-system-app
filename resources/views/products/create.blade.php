@extends('layouts.master')

@section('title', 'إضافة منتج جديد - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">المخزون والمنتجات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ إضافة منتج جديد</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('products.index') }}" class="btn btn-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة للمنتجات
		</a>
	</div>
</div>
@endsection

@section('content')
<div class="row row-sm justify-content-center">
	<div class="col-lg-9 col-xl-8">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-box-open ml-1 text-primary"></i> بيانات المنتج الجديد</h4>
				<p class="tx-12 tx-gray-500 mb-2">قم بإدخال بيانات المنتج والأسعار وكميات المخزون الأولية.</p>
			</div>
			<div class="card-body">
				<form action="{{ route('products.store') }}" method="POST">
					@csrf

					<div class="row">
						{{-- Name --}}
						<div class="col-md-7">
							<div class="form-group text-right">
								<label for="name" class="font-weight-semibold">اسم المنتج <span class="text-danger">*</span></label>
								<input id="name"
									   type="text"
									   name="name"
									   value="{{ old('name') }}"
									   class="form-control @error('name') is-invalid @enderror"
									   placeholder="مثال: ساعة يد أنيقة رجالية، باقة ورد مجفف..."
									   required
									   autofocus>
								@error('name')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						{{-- Subcategory --}}
						<div class="col-md-5">
							<div class="form-group text-right">
								<label for="subcategory_id" class="font-weight-semibold">القسم الفرعي <span class="text-danger">*</span></label>
								<select id="subcategory_id" name="subcategory_id" class="form-control @error('subcategory_id') is-invalid @enderror" required>
									<option value="">-- اختر القسم الفرعي --</option>
									@foreach($categories as $category)
										<optgroup label="📂 {{ $category->name }}">
											@foreach($category->subcategories as $sub)
												<option value="{{ $sub->id }}" {{ old('subcategory_id') == $sub->id ? 'selected' : '' }}>
													{{ $sub->name }}
												</option>
											@endforeach
										</optgroup>
									@endforeach
								</select>
								@error('subcategory_id')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						{{-- Purchase Price --}}
						<div class="col-md-3">
							<div class="form-group text-right">
								<label for="purchase_price" class="font-weight-semibold">سعر الشراء (التكلفة) <span class="text-danger">*</span></label>
								<div class="input-group">
									<input id="purchase_price"
										   type="number"
										   step="0.01"
										   min="0"
										   name="purchase_price"
										   value="{{ old('purchase_price', '0.00') }}"
										   class="form-control text-left @error('purchase_price') is-invalid @enderror"
										   required>
									<div class="input-group-append">
										<span class="input-group-text">جنيه مصري</span>
									</div>
								</div>
								@error('purchase_price')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						{{-- Selling Price --}}
						<div class="col-md-3">
							<div class="form-group text-right">
								<label for="selling_price" class="font-weight-semibold">سعر البيع للعميل <span class="text-danger">*</span></label>
								<div class="input-group">
									<input id="selling_price"
										   type="number"
										   step="0.01"
										   min="0"
										   name="selling_price"
										   value="{{ old('selling_price', '0.00') }}"
										   class="form-control text-left @error('selling_price') is-invalid @enderror"
										   required>
									<div class="input-group-append">
										<span class="input-group-text">جنيه مصري</span>
									</div>
								</div>
								@error('selling_price')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						{{-- Stock Quantity --}}
						<div class="col-md-3">
							<div class="form-group text-right">
								<label for="stock_quantity" class="font-weight-semibold">الكمية الحالية <span class="text-danger">*</span></label>
								<input id="stock_quantity"
									   type="number"
									   step="0.001"
									   min="0"
									   name="stock_quantity"
									   value="{{ old('stock_quantity', '0') }}"
									   class="form-control text-left @error('stock_quantity') is-invalid @enderror"
									   required>
								@error('stock_quantity')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						{{-- Minimum Stock --}}
						<div class="col-md-3">
							<div class="form-group text-right">
								<label for="minimum_stock" class="font-weight-semibold">الحد الأدنى للتنبيه <span class="text-danger">*</span></label>
								<input id="minimum_stock"
									   type="number"
									   step="0.001"
									   min="0"
									   name="minimum_stock"
									   value="{{ old('minimum_stock', '5') }}"
									   class="form-control text-left @error('minimum_stock') is-invalid @enderror"
									   required>
								@error('minimum_stock')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						{{-- Description --}}
						<div class="col-12">
							<div class="form-group text-right">
								<label for="description" class="font-weight-semibold">الوصف وملاحظات المنتج</label>
								<textarea id="description"
										  name="description"
										  rows="3"
										  class="form-control @error('description') is-invalid @enderror"
										  placeholder="وصف تفصيلي للمنتج، الحجم، اللون، أو أية مواصفات خاصة...">{{ old('description') }}</textarea>
								@error('description')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						{{-- Active Switch --}}
						<div class="col-12">
							<div class="form-group text-right">
								<div class="custom-control custom-switch mt-2">
									<input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
									<label class="custom-control-label font-weight-semibold" for="is_active">تفعيل المنتج في شاشة المبيعات ونقطة البيع</label>
								</div>
								@error('is_active')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>
					</div>

					<div class="pt-3 border-top text-left">
						<button type="submit" class="btn btn-primary px-4 font-weight-bold">
							<i class="fas fa-check ml-1"></i> حفظ المنتج
						</button>
						<a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-3 mr-2">
							إلغاء
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

