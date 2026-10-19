@extends('layouts.master')

@section('title', 'تسجيل ملاحظة جديدة - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">الملاحظات والتنبيهات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تسجيل ملاحظة جديدة</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('notes.index') }}" class="btn btn-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لقائمة الملاحظات
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
				<h4 class="card-title mg-b-0"><i class="fas fa-sticky-note ml-1 text-primary"></i> بيانات الملاحظة الجديدة</h4>
				<p class="tx-12 tx-gray-500 mb-2">تسجيل الملاحظات والتنبيهات لمتابعة الأعمال والمعاملات المعلقة في المتجر.</p>
			</div>
			<div class="card-body">
				<form action="{{ route('notes.store') }}" method="POST">
					@csrf

					<!-- Title Type Selector -->
					<div class="form-group text-right">
						<label for="title_type" class="font-weight-semibold">نوع / عنوان الملاحظة <span class="text-danger">*</span></label>
						<select name="title_type" id="title_type" class="form-control select2 @error('title_type') is-invalid @enderror" required>
							<option value="">-- اختر نوع الملاحظة من القائمة --</option>
							@foreach($predefinedTitles as $preset)
								<option value="{{ $preset }}" {{ old('title_type') === $preset ? 'selected' : '' }}>
									{{ $preset }}
								</option>
							@endforeach
						</select>
						@error('title_type')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Custom Title Input (appears when أخرى is selected) -->
					<div class="form-group text-right" id="customTitleGroup" style="display: {{ old('title_type') === 'أخرى' ? 'block' : 'none' }};">
						<label for="custom_title" class="font-weight-semibold">العنوان المخصص <span class="text-danger">*</span></label>
						<input type="text" name="custom_title" id="custom_title" class="form-control @error('custom_title') is-invalid @enderror"
							   value="{{ old('custom_title') }}" placeholder="أدخل عنواناً مخصصاً للملاحظة...">
						@error('custom_title')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Detailed Content -->
					<div class="form-group text-right">
						<label for="content" class="font-weight-semibold">تفاصيل ونص الملاحظة <span class="text-danger">*</span></label>
						<textarea name="content" id="content" rows="4" class="form-control @error('content') is-invalid @enderror"
								  placeholder="اكتب كامل التفاصيل والبيانات المطلوبة للمتابعة هنا..." required>{{ old('content') }}</textarea>
						@error('content')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<div class="row row-sm">
						<!-- Optional Amount -->
						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="amount" class="font-weight-semibold">المبلغ المرتبط (اختياري)</label>
								<div class="input-group">
									<input type="number" step="0.01" min="0" name="amount" id="amount"
										   class="form-control @error('amount') is-invalid @enderror"
										   value="{{ old('amount') }}" placeholder="0.00">
									<div class="input-group-append">
										<span class="input-group-text font-weight-bold">جنيه مصري</span>
									</div>
								</div>
								<small class="text-muted">مفيد لتسجيل مبالغ مؤجلة أو عربون أو تذكير مالي.</small>
								@error('amount')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						<!-- Optional Follow-up Date/Time -->
						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="due_at" class="font-weight-semibold">موعد وتوقيت المتابعة (اختياري)</label>
								<input type="datetime-local" name="due_at" id="due_at"
									   class="form-control @error('due_at') is-invalid @enderror"
									   value="{{ old('due_at') }}">
								<small class="text-muted">لتحديد تاريخ وساعة المتابعة وإرسال التنبيه عند حلوله.</small>
								@error('due_at')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>
					</div>

					<!-- Status -->
					<div class="form-group text-right">
						<label for="status" class="font-weight-semibold">حالة الملاحظة البدئية</label>
						<select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
							<option value="open" {{ old('status', 'open') === 'open' ? 'selected' : '' }}>مفتوحة / قيد المتابعة</option>
							<option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>مكتملة</option>
							<option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
						</select>
						@error('status')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<div class="form-group mb-0 mt-4 d-flex justify-content-end" style="gap: 8px;">
						<button type="submit" class="btn btn-primary font-weight-bold px-4">
							<i class="fas fa-save ml-1"></i> حفظ الملاحظة
						</button>
						<a href="{{ route('notes.index') }}" class="btn btn-secondary px-4">
							إلغاء
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Info & Guidelines Sidebar -->
	<div class="col-lg-4 col-xl-4">
		<div class="card">
			<div class="card-header pb-0">
				<h5 class="card-title mb-1"><i class="fas fa-info-circle ml-1 text-info"></i> إرشادات تسجيل الملاحظات</h5>
			</div>
			<div class="card-body">
				<ul class="list-unstyled mb-0" style="line-height: 1.8;">
					<li class="mb-2">
						<i class="fas fa-check-circle text-success ml-1"></i>
						<strong>فلوس متأجلة:</strong> لتسجيل ديون أو مبالغ متبقية على عملاء أو مستحقات قيد التحصيل.
					</li>
					<li class="mb-2">
						<i class="fas fa-check-circle text-success ml-1"></i>
						<strong>طلب أو أوردر معلق:</strong> لتسجيل طلبات حجز هدايا خاصة أو تغليف بانتظار استلام العميل.
					</li>
					<li class="mb-2">
						<i class="fas fa-check-circle text-success ml-1"></i>
						<strong>نواقص وبضاعة:</strong> لتنبيه الإدارة عند ملاحظة نفاد أصناف معينة من الرفوف.
					</li>
					<li class="mb-2">
						<i class="fas fa-check-circle text-success ml-1"></i>
						<strong>ملاحظات الوردية:</strong> لتبادل التوجيهات والمعلومات الهامة مع الوردية التالية.
					</li>
					<li>
						<i class="fas fa-lock text-muted ml-1"></i>
						<strong>الأمان والخصوصية:</strong> يتم حفظ وقت التسجيل تلقائياً مع ربط الملاحظة بحسابك.
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js')
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const titleTypeSelect = document.getElementById('title_type');
		const customTitleGroup = document.getElementById('customTitleGroup');
		const customTitleInput = document.getElementById('custom_title');

		function toggleCustomTitle() {
			if (titleTypeSelect.value === 'أخرى') {
				customTitleGroup.style.display = 'block';
				customTitleInput.setAttribute('required', 'required');
			} else {
				customTitleGroup.style.display = 'none';
				customTitleInput.removeAttribute('required');
			}
		}

		titleTypeSelect.addEventListener('change', toggleCustomTitle);
		toggleCustomTitle();
	});
</script>
@endsection

