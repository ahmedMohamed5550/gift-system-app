@extends('layouts.master')

@section('title', 'تعديل المستخدم - ' . $user->name)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">المستخدمين</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تعديل حساب {{ $user->name }}</span>
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
				<h4 class="card-title mg-b-0"><i class="fas fa-user-edit ml-1 text-primary"></i> تعديل بيانات الحساب</h4>
				<p class="tx-12 tx-gray-500 mb-2">تعديل الاسم، اسم المستخدم، الدور، وكلمة المرور.</p>
			</div>
			<div class="card-body">
				<form action="{{ route('users.update', $user) }}" method="POST">
					@csrf
					@method('PUT')

					<div class="row">
						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="name" class="font-weight-semibold">الاسم الكامل <span class="text-danger">*</span></label>
								<input id="name"
									   type="text"
									   name="name"
									   value="{{ old('name', $user->name) }}"
									   class="form-control @error('name') is-invalid @enderror"
									   required>
								@error('name')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="username" class="font-weight-semibold">اسم المستخدم <span class="text-danger">*</span></label>
								<input id="username"
									   type="text"
									   name="username"
									   value="{{ old('username', $user->username) }}"
									   class="form-control @error('username') is-invalid @enderror"
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
									<option value="employee" {{ old('role', $user->role) === 'employee' ? 'selected' : '' }}>موظف مبيعات (كاشير)</option>
									<option value="owner" {{ old('role', $user->role) === 'owner' ? 'selected' : '' }}>مالك المتجر (مدير كامل الصلاحيات)</option>
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
									<input type="checkbox"
										   class="custom-control-input"
										   id="is_active"
										   name="is_active"
										   value="1"
										   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
										   {{ auth()->id() === $user->id ? 'disabled' : '' }}>
									<label class="custom-control-label font-weight-semibold" for="is_active">
										{{ $user->is_active ? 'الحساب نشط حالياً' : 'الحساب معطل حالياً' }}
										@if(auth()->id() === $user->id)
											<span class="text-muted tx-12">(لا يمكن تعطيل حسابك الحالي)</span>
										@endif
									</label>
								</div>
								@if(auth()->id() === $user->id)
									<input type="hidden" name="is_active" value="1">
								@endif
							</div>
						</div>

						<div class="col-12 mt-2 mb-3">
							<div class="alert alert-light border">
								<i class="fas fa-info-circle ml-1 text-info"></i>
								إذا كنت لا ترغب بتغيير كلمة المرور، اترك حقلي كلمة المرور أدناه فارغين.
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="password" class="font-weight-semibold">كلمة المرور الجديدة</label>
								<input id="password"
									   type="password"
									   name="password"
									   class="form-control @error('password') is-invalid @enderror"
									   placeholder="اترك فارغاً للإبقاء على الحالية">
								@error('password')
									<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="password_confirmation" class="font-weight-semibold">تأكيد كلمة المرور الجديدة</label>
								<input id="password_confirmation"
									   type="password"
									   name="password_confirmation"
									   class="form-control"
									   placeholder="أعد إدخال كلمة المرور">
							</div>
						</div>
					</div>

					<div class="pt-3 border-top text-left">
						<button type="submit" class="btn btn-primary px-4 font-weight-bold">
							<i class="fas fa-save ml-1"></i> حفظ التعديلات
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

