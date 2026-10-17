@extends('layouts.master')

@section('title', 'الملف الشخصي - نظام إدارة متجر الهدايا')

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">المستخدم</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الملف الشخصي</span>
		</div>
	</div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')
<!-- row -->
<div class="row row-sm">
	<div class="col-lg-4 col-xl-3">
		<div class="card mg-b-20">
			<div class="card-body text-center">
				<div class="mb-3">
					<img alt="avatar" class="rounded-circle avatar-xxl" src="{{ URL::asset('assets/img/faces/6.jpg') }}">
				</div>
				<h5 class="font-weight-bold mb-1">{{ $user->name }}</h5>
				<p class="text-muted mb-2">{{ '@' . $user->username }}</p>

				<div class="mt-2">
					<span class="badge badge-pill {{ $user->isOwner() ? 'badge-primary' : 'badge-info' }} px-3 py-1 tx-13">
						{{ $user->role_arabic }}
					</span>
					<span class="badge badge-pill {{ $user->is_active ? 'badge-success' : 'badge-danger' }} px-3 py-1 tx-13 mr-1">
						{{ $user->is_active ? 'حساب نشط' : 'حساب معطل' }}
					</span>
				</div>

				<div class="mt-4 pt-3 border-top text-right tx-13 text-muted">
					<div class="d-flex justify-content-between mb-2">
						<span>تاريخ الانضمام:</span>
						<span class="font-weight-semibold text-dark">{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</span>
					</div>
					<div class="d-flex justify-content-between">
						<span>آخر تسجيل دخول:</span>
						<span class="font-weight-semibold text-dark">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'أول جلسة' }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-8 col-xl-9">
		@if (session('status') === 'profile-updated')
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="fas fa-check-circle ml-1"></i> تم تحديث بيانات الملف الشخصي بنجاح.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		@elseif (session('status') === 'password-updated')
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="fas fa-check-circle ml-1"></i> تم تحديث كلمة المرور بنجاح.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		@endif

		<!-- Update Profile Info Card -->
		<div class="card mg-b-20">
			<div class="card-header pb-0">
				<div class="d-flex justify-content-between">
					<h4 class="card-title mg-b-0"><i class="fas fa-user-edit ml-1 text-primary"></i> بيانات الحساب الأساسية</h4>
				</div>
				<p class="tx-12 tx-gray-500 mb-2">تحديث الاسم واسم المستخدم الخاص بالحساب.</p>
			</div>
			<div class="card-body">
				<form method="POST" action="{{ route('profile.update') }}">
					@csrf
					@method('patch')

					<div class="row">
						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="name" class="font-weight-semibold">الاسم الكامل <span class="text-danger">*</span></label>
								<input id="name"
									   type="text"
									   name="name"
									   value="{{ old('name', $user->name) }}"
									   class="form-control @error('name') is-invalid @enderror"
									   required
									   autofocus>
								@error('name')
									<span class="invalid-feedback d-block text-right mt-1" role="alert">
										<strong>{{ $message }}</strong>
									</span>
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
									<span class="invalid-feedback d-block text-right mt-1" role="alert">
										<strong>{{ $message }}</strong>
									</span>
								@enderror
							</div>
						</div>
					</div>

					<div class="text-left mt-3">
						<button type="submit" class="btn btn-primary px-4 font-weight-bold">
							<i class="fas fa-save ml-1"></i> حفظ التعديلات
						</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Update Password Card -->
		<div class="card">
			<div class="card-header pb-0">
				<div class="d-flex justify-content-between">
					<h4 class="card-title mg-b-0"><i class="fas fa-key ml-1 text-primary"></i> تغيير كلمة المرور</h4>
				</div>
				<p class="tx-12 tx-gray-500 mb-2">تأكد من اختيار كلمة مرور قوية للحفاظ على أمان حسابك.</p>
			</div>
			<div class="card-body">
				<form method="POST" action="{{ route('password.update') }}">
					@csrf
					@method('put')

					<div class="row">
						<div class="col-md-12">
							<div class="form-group text-right">
								<label for="current_password" class="font-weight-semibold">كلمة المرور الحالية <span class="text-danger">*</span></label>
								<input id="current_password"
									   type="password"
									   name="current_password"
									   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
									   required
									   autocomplete="current-password">
								@error('current_password', 'updatePassword')
									<span class="invalid-feedback d-block text-right mt-1" role="alert">
										<strong>{{ $message }}</strong>
									</span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="password" class="font-weight-semibold">كلمة المرور الجديدة <span class="text-danger">*</span></label>
								<input id="password"
									   type="password"
									   name="password"
									   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
									   required
									   autocomplete="new-password">
								@error('password', 'updatePassword')
									<span class="invalid-feedback d-block text-right mt-1" role="alert">
										<strong>{{ $message }}</strong>
									</span>
								@enderror
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group text-right">
								<label for="password_confirmation" class="font-weight-semibold">تأكيد كلمة المرور الجديدة <span class="text-danger">*</span></label>
								<input id="password_confirmation"
									   type="password"
									   name="password_confirmation"
									   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
									   required
									   autocomplete="new-password">
								@error('password_confirmation', 'updatePassword')
									<span class="invalid-feedback d-block text-right mt-1" role="alert">
										<strong>{{ $message }}</strong>
									</span>
								@enderror
							</div>
						</div>
					</div>

					<div class="text-left mt-3">
						<button type="submit" class="btn btn-success px-4 font-weight-bold">
							<i class="fas fa-lock ml-1"></i> تحديث كلمة المرور
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- row closed -->
@endsection
