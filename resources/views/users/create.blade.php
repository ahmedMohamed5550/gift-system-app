@extends('layouts.master')

@section('title', 'إضافة مستخدم جديد - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">المستخدمين</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ إضافة مستخدم جديد</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('users.index') }}" class="btn btn-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
		</a>
	</div>
</div>
@endsection

@section('content')
<div class="row row-sm justify-content-center">
	<div class="col-lg-8 col-xl-7">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-user-plus ml-1 text-primary"></i> بيانات المستخدم الجديد</h4>
				<p class="tx-12 tx-gray-500 mb-2">قم بملء البيانات التالية لإنشاء حساب لموظف أو مدير للنظام.</p>
			</div>
			<div class="card-body">
				<form action="{{ route('users.store') }}" method="POST">
					@csrf

					<div class="row">
						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="name" class="font-weight-semibold">الاسم الكامل <span class="text-danger">*</span></label>
								<input id="name"
									   type="text"
									   name="name"
									   value="{{ old('name') }}"
									   class="form-control @error('name') is-invalid @enderror"
									   placeholder="مثال: أحمد محمد"
									   required
									   autofocus>
								@error('name')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="username" class="font-weight-semibold">اسم المستخدم (لتسجيل الدخول) <span class="text-danger">*</span></label>
								<input id="username"
									   type="text"
									   name="username"
									   value="{{ old('username') }}"
									   class="form-control @error('username') is-invalid @enderror"
									   placeholder="مثال: ahmed_pos"
									   required>
								@error('username')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="role" class="font-weight-semibold">الدور والصلاحية <span class="text-danger">*</span></label>
								<select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
									<option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>موظف مبيعات (كاشير)</option>
									<option value="owner" {{ old('role') === 'owner' ? 'selected' : '' }}>مالك المتجر (مدير كامل الصلاحيات)</option>
								</select>
								@error('role')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label class="font-weight-semibold d-block">حالة الحساب</label>
								<div class="custom-control custom-switch mt-2">
									<input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
									<label class="custom-control-label font-weight-semibold" for="is_active">تفعيل الحساب فوراً</label>
								</div>
								@error('is_active')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="password" class="font-weight-semibold">كلمة المرور <span class="text-danger">*</span></label>
								<input id="password"
									   type="password"
									   name="password"
									   class="form-control @error('password') is-invalid @enderror"
									   placeholder="6 أحرف على الأقل"
									   required>
								@error('password')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="password_confirmation" class="font-weight-semibold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
								<input id="password_confirmation"
									   type="password"
									   name="password_confirmation"
									   class="form-control"
									   placeholder="أعد إدخال كلمة المرور"
									   required>
							</div>
						</div>
					</div>

					<div class="pt-3 border-top text-left">
						<button type="submit" class="btn btn-primary px-4 font-weight-bold">
							<i class="fas fa-check ml-1"></i> حفظ المستخدم
						</button>
						<a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-3 mr-2">
							إلغاء
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

