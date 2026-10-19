@extends('layouts.master')

@section('title', 'إدارة المستخدمين - متجر الهدايا')

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
			<h4 class="content-title mb-0 my-auto">المستخدمين</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ قائمة حسابات النظام</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('users.create') }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-user-plus ml-1"></i> إضافة مستخدم جديد
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Alert Messages -->
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
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">إجمالي المستخدمين</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['total'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-users text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">الحسابات النشطة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['active'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-user-check text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">موظفو المبيعات</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['employees'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-cash-register text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-warning-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">مديرو النظام (الملاك)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['owners'] }}</h4>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-user-shield text-white tx-24"></i>
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
		<form method="GET" action="{{ route('users.index') }}" class="row row-sm">
			<div class="col-lg-5 col-md-6 mg-b-10">
				<div class="input-group">
					<input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="البحث بالاسم أو اسم المستخدم...">
					<span class="input-group-append">
						<button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> بحث</button>
					</span>
				</div>
			</div>

			<div class="col-lg-3 col-md-3 mg-b-10">
				<select name="role" class="form-control" onchange="this.form.submit()">
					<option value="">-- جميع الأدوار --</option>
					<option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>مالك المتجر</option>
					<option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>موظف مبيعات</option>
				</select>
			</div>

			<div class="col-lg-2 col-md-3 mg-b-10">
				<select name="status" class="form-control" onchange="this.form.submit()">
					<option value="">-- جميع الحالات --</option>
					<option value="1" {{ request('status') === '1' ? 'selected' : '' }}>نشط</option>
					<option value="0" {{ request('status') === '0' ? 'selected' : '' }}>معطل</option>
				</select>
			</div>

			<div class="col-lg-2 col-md-12 mg-b-10 text-left">
				@if(request()->hasAny(['search', 'role', 'status']))
					<a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-block">
						<i class="fas fa-redo ml-1"></i> إعادة تعيين
					</a>
				@endif
			</div>
		</form>
	</div>
</div>

<!-- Users Table Card -->
<div class="card" id="live-search-container">
	<div class="card-header pb-0">
		<div class="d-flex justify-content-between align-items-center">
			<h4 class="card-title mg-b-0">سجل المستخدمين</h4>
			<span class="tx-13 text-muted">عرض {{ $users->firstItem() ?? 0 }} إلى {{ $users->lastItem() ?? 0 }} من أصل {{ $users->total() }}</span>
		</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover table-striped mg-b-0 text-md-nowrap border">
				<thead class="bg-light">
					<tr>
						<th class="wd-5p">#</th>
						<th>الاسم</th>
						<th>اسم المستخدم</th>
						<th>الدور</th>
						<th>الحالة</th>
						<th>آخر دخول</th>
						<th>تاريخ التسجيل</th>
						<th class="text-center wd-15p">العمليات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($users as $user)
						<tr>
							<td class="font-weight-bold">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
							<td>
								<div class="d-flex align-items-center">
									<div class="avatar avatar-sm brround bg-primary-transparent text-primary ml-2 font-weight-bold">
										{{ mb_substr($user->name, 0, 1) }}
									</div>
									<div>
										<span class="font-weight-semibold">{{ $user->name }}</span>
										@if(auth()->id() === $user->id)
											<span class="badge badge-light-primary tx-11 ml-1">(أنت)</span>
										@endif
									</div>
								</div>
							</td>
							<td><span class="badge badge-light font-weight-bold tx-13">{{ $user->username }}</span></td>
							<td>
								<span class="badge badge-pill {{ $user->isOwner() ? 'badge-primary' : 'badge-info' }} px-3 py-1">
									{{ $user->role_arabic }}
								</span>
							</td>
							<td>
								<span class="badge badge-pill {{ $user->is_active ? 'badge-success' : 'badge-danger' }} px-3 py-1">
									{{ $user->is_active ? 'نشط' : 'معطل' }}
								</span>
							</td>
							<td>
								{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'لم يسجل دخول بعد' }}
							</td>
							<td>{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</td>
							<td class="text-center">
								<div class="action-btns-wrapper">
									<a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-info btn-action-icon" title="تعديل">
										<i class="fas fa-edit"></i>
									</a>

									@if(auth()->id() !== $user->id)
										<!-- Toggle Status -->
										<form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('هل أنت متأكد من تغيير حالة هذا الحساب؟')">
											@csrf
											@method('PATCH')
											<button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }} btn-action-icon" title="{{ $user->is_active ? 'تعطيل الحساب' : 'تفعيل الحساب' }}">
												<i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
											</button>
										</form>

										<!-- Delete / Soft-Delete -->
										<form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ سيتم الاحتفاظ بالسجلات والمبيعات التاريخية المرتبطة به.')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-sm btn-danger btn-action-icon" title="حذف">
												<i class="fas fa-trash-alt"></i>
											</button>
										</form>
									@endif
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="8" class="text-center py-4 text-muted">
								<i class="fas fa-user-friends tx-40 mb-2 d-block"></i>
								لا يوجد مستخدمين مطابقين لخيارات البحث.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="d-flex justify-content-center mt-4">
			{{ $users->links() }}
		</div>
	</div>
</div>
@endsection

