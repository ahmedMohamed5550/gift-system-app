@extends('layouts.master')

@section('title', 'تعديل المصروف - #' . $expense->id)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة المصروفات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تعديل المصروف رقم #{{ $expense->id }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('expenses.index') }}" class="btn btn-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
		</a>
	</div>
</div>
@endsection

@section('content')
<!-- Error Alerts -->
@if($errors->any())
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> يرجى تصحيح الأخطاء التالية:</strong>
		<ul class="mb-0 mt-2 pr-3">
			@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<div class="row row-sm justify-content-center">
	<div class="col-lg-8 col-xl-7">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-edit ml-1 text-primary"></i> تعديل بيانات المصروف</h4>
				<p class="tx-12 tx-gray-500 mb-2">تعديل بيانات المصروف رقم #{{ $expense->id }}.</p>
			</div>
			<div class="card-body">
				<form action="{{ route('expenses.update', $expense) }}" method="POST">
					@csrf
					@method('PUT')

					<!-- Category with Presets -->
					<div class="form-group text-right">
						<label for="category" class="font-weight-semibold">بند المصروف <span class="text-danger">*</span></label>
						<div class="mb-2">
							<span class="tx-11 text-muted d-block mb-1">بنود شائعة جاهزة:</span>
							@foreach($categories as $cat)
								<button type="button" class="btn btn-xs btn-outline-secondary preset-cat mb-1">{{ $cat }}</button>
							@endforeach
						</div>
						<input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror"
							   value="{{ old('category', $expense->category) }}" required>
						@error('category')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Amount -->
					<div class="form-group text-right">
						<label for="amount" class="font-weight-semibold">مبلغ المصروف <span class="text-danger">*</span></label>
						<div class="input-group">
							<input type="number" step="0.01" min="0.01" name="amount" id="amount"
								   class="form-control form-control-lg font-weight-bold text-left @error('amount') is-invalid @enderror"
								   value="{{ old('amount', $expense->amount) }}" required>
							<div class="input-group-append">
								<span class="input-group-text font-weight-bold">جنيه مصري</span>
							</div>
						</div>
						@error('amount')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Payment Method -->
					<div class="form-group text-right">
						<label class="font-weight-semibold d-block">طريقة دفع المصروف <span class="text-danger">*</span></label>
						<div class="row row-sm">
							<div class="col-md-4 mb-2">
								<label class="rdiobox">
									<input type="radio" name="payment_method" value="cash" {{ old('payment_method', $expense->payment_method) === 'cash' ? 'checked' : '' }}>
									<span><strong class="text-success">نقداً (من درج الكاشير)</strong></span>
								</label>
								<small class="text-muted d-block mr-4">يخصم من النقدية المتوقعة بالدرج</small>
							</div>
							<div class="col-md-4 mb-2">
								<label class="rdiobox">
									<input type="radio" name="payment_method" value="vodafone_cash" {{ old('payment_method', $expense->payment_method) === 'vodafone_cash' ? 'checked' : '' }}>
									<span><strong class="text-danger">فودافون كاش</strong></span>
								</label>
								<small class="text-muted d-block mr-4">لا يؤثر على درج الكاشير النقدي</small>
							</div>
							<div class="col-md-4 mb-2">
								<label class="rdiobox">
									<input type="radio" name="payment_method" value="instapay" {{ old('payment_method', $expense->payment_method) === 'instapay' ? 'checked' : '' }}>
									<span><strong class="text-primary">انستا باي (InstaPay)</strong></span>
								</label>
								<small class="text-muted d-block mr-4">لا يؤثر على درج الكاشير النقدي</small>
							</div>
						</div>
						@error('payment_method')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Associated Shift -->
					<div class="form-group text-right">
						<label for="shift_id" class="font-weight-semibold">الوردية المرتبطة بالمصروف</label>
						<select name="shift_id" id="shift_id" class="form-control @error('shift_id') is-invalid @enderror">
							<option value="">-- مصروف عام للمتجر (غير مرتبط بوردية كاشير محددة) --</option>
							@if($expense->shift)
								<option value="{{ $expense->shift_id }}" selected>
									وردية #{{ $expense->shift_id }} ({{ $expense->shift->user->name }} - {{ $expense->shift->opened_at->format('Y-m-d') }})
								</option>
							@endif
							@foreach($openShifts as $sh)
								@if(!$expense->shift || $sh->id !== $expense->shift_id)
									<option value="{{ $sh->id }}" {{ old('shift_id', $expense->shift_id) == $sh->id ? 'selected' : '' }}>
										وردية مفتوحة (#{{ $sh->id }} - {{ $sh->user->name }})
									</option>
								@endif
							@endforeach
						</select>
						@error('shift_id')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Description -->
					<div class="form-group text-right">
						<label for="description" class="font-weight-semibold">البيان وتفاصيل المصروف</label>
						<textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $expense->description) }}</textarea>
						@error('description')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Buttons -->
					<div class="form-group text-left mt-4 mb-0">
						<button type="submit" class="btn btn-primary font-weight-bold px-4">
							<i class="fas fa-save ml-1"></i> حفظ التعديلات
						</button>
						<a href="{{ route('expenses.index') }}" class="btn btn-secondary mr-2">
							إلغاء
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
	const catInput = document.getElementById('category');
	document.querySelectorAll('.preset-cat').forEach(function(btn) {
		btn.addEventListener('click', function() {
			catInput.value = this.textContent.trim();
		});
	});
});
</script>
@endsection

