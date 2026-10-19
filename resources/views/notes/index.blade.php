@extends('layouts.master')

@section('title', 'إدارة الملاحظات والتنبيهات - متجر الهدايا')

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
			<h4 class="content-title mb-0 my-auto">الملاحظات والتنبيهات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ سجل الملاحظات ومتابعة المهام</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('notes.create') }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-plus ml-1"></i> تسجيل ملاحظة جديدة
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Alerts -->
@if(session('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-check-circle ml-1"></i> تم بنجاح!</strong> {{ session('success') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(session('info'))
	<div class="alert alert-info alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-info-circle ml-1"></i> إشعار:</strong> {{ session('info') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(session('error') || $errors->any())
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> تنبيه:</strong>
		@if(session('error'))
			{{ session('error') }}
		@endif
		@if($errors->any())
			<ul class="mb-0 mt-1 pr-3">
				@foreach($errors->all() as $err)
					<li>{{ $err }}</li>
				@endforeach
			</ul>
		@endif
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<!-- Summary Cards -->
<div class="row row-sm">
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white font-weight-bold">إجمالي الملاحظات المسجلة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-22 font-weight-bold mb-1 text-white">{{ $stats['total'] }}</h4>
							<p class="mb-0 tx-12 text-white op-7">
								@if(auth()->user()->isOwner())
									كافة ملاحظات فريق العمل
								@else
									ملاحظاتي الخاصة
								@endif
							</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-clipboard-list text-white tx-24"></i>
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
					<h6 class="mb-3 tx-12 text-white font-weight-bold">ملاحظات قيد المتابعة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-22 font-weight-bold mb-1 text-white">{{ $stats['open'] }}</h4>
							<p class="mb-0 tx-12 text-white op-7">
								@if($stats['overdue'] > 0)
									<span class="badge badge-danger tx-10">{{ $stats['overdue'] }} متأخرة عن موعدها</span>
								@else
									بحاجة لمتابعة وإنجاز
								@endif
							</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-clock text-white tx-24"></i>
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
					<h6 class="mb-3 tx-12 text-white font-weight-bold">الملاحظات المكتملة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-22 font-weight-bold mb-1 text-white">{{ $stats['completed'] }}</h4>
							<p class="mb-0 tx-12 text-white op-7">تم إنجازها بنجاح</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-check-circle text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white font-weight-bold">المبالغ المعلقة بالملاحظات</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h4 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['open_amount'], 2) }}</h4>
							<p class="mb-0 tx-12 text-white op-7">جنيه مصري (مفتوحة)</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-money-bill-wave text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Filters Card -->
<div class="card custom-card">
	<div class="card-body">
		<form method="GET" action="{{ route('notes.index') }}">
			<div class="row row-sm align-items-end">
				<!-- Search -->
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-bold">بحث في الملاحظات:</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
						</div>
						<input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="العنوان، النص، أو المبلغ...">
					</div>
				</div>

				<!-- Title Predefined Filter -->
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-bold">نوع / تصنيف الملاحظة:</label>
					<select name="title" class="form-control">
						<option value="">-- كافة الأنواع --</option>
						@foreach($predefinedTitles as $titleOption)
							<option value="{{ $titleOption }}" {{ request('title') === $titleOption ? 'selected' : '' }}>
								{{ $titleOption }}
							</option>
						@endforeach
					</select>
				</div>

				<!-- Status Filter -->
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-bold">الحالة:</label>
					<select name="status" class="form-control">
						<option value="">-- كل الحالات --</option>
						<option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>قيد المتابعة / مفتوحة</option>
						<option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>مكتملة</option>
						<option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
					</select>
				</div>

				<!-- Due Date Status Filter -->
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-bold">موعد المتابعة:</label>
					<select name="due_status" class="form-control">
						<option value="">-- كل المواعيد --</option>
						<option value="overdue" {{ request('due_status') === 'overdue' ? 'selected' : '' }}>متأخرة عن الموعد ⚠️</option>
						<option value="today" {{ request('due_status') === 'today' ? 'selected' : '' }}>موعدها اليوم</option>
						<option value="upcoming" {{ request('due_status') === 'upcoming' ? 'selected' : '' }}>قادمة مستقبلاً</option>
						<option value="has_due" {{ request('due_status') === 'has_due' ? 'selected' : '' }}>لها موعد متابعة</option>
						<option value="no_due" {{ request('due_status') === 'no_due' ? 'selected' : '' }}>بدون موعد</option>
					</select>
				</div>

				@if(auth()->user()->isOwner())
				<!-- Employee filter (only for owner) -->
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-bold">الموظف المسؤول:</label>
					<select name="user_id" class="form-control">
						<option value="">-- كل الموظفين --</option>
						@foreach($users as $emp)
							<option value="{{ $emp->id }}" {{ (string) request('user_id') === (string) $emp->id ? 'selected' : '' }}>
								{{ $emp->name }}
							</option>
						@endforeach
					</select>
				</div>
				@endif

				<!-- Creation Date From -->
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-bold">تاريخ الإنشاء من:</label>
					<input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
				</div>

				<!-- Creation Date To -->
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-bold">إلى تاريخ:</label>
					<input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
				</div>

				<!-- Action Buttons -->
				<div class="col-md-3 col-sm-6 mb-2">
					<div class="d-flex" style="gap: 5px;">
						<button type="submit" class="btn btn-primary flex-fill font-weight-bold">
							<i class="fas fa-filter ml-1"></i> فلترة
						</button>
						<a href="{{ route('notes.index') }}" class="btn btn-light" title="إعادة ضبط">
							<i class="fas fa-undo"></i>
						</a>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Notes Table -->
<div class="card custom-card" id="live-search-container">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<div>
			<h6 class="card-title mb-1 font-weight-bold">سجل وقائمة الملاحظات</h6>
			<p class="text-muted card-sub-title mb-0">عرض الملاحظات مع تفاصيل المتابعة والمبالغ والحالة</p>
		</div>
		<div>
			<span class="badge badge-light-primary tx-12 px-2 py-1 font-weight-bold">
				العدد الإجمالي المعروض: {{ $notes->total() }} ملاحظة
			</span>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover table-striped text-nowrap text-md-nowrap mb-0">
				<thead class="bg-light">
					<tr>
						<th class="wd-5p">#</th>
						<th>عنوان الملاحظة</th>
						<th>محتوى الملاحظة</th>
						<th class="text-center">المبلغ المعلق</th>
						<th class="text-center">موعد المتابعة</th>
						<th>المنشئ</th>
						<th>تاريخ ووقت التسجيل</th>
						<th class="text-center">الحالة</th>
						<th class="text-center wd-15p">العمليات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($notes as $note)
						<tr class="{{ $note->isOverdue() ? 'bg-danger-transparent' : '' }}">
							<td class="font-weight-bold">{{ $loop->iteration + ($notes->currentPage() - 1) * $notes->perPage() }}</td>
							<td>
								<a href="{{ route('notes.show', $note) }}" class="font-weight-bold text-dark">
									<i class="fas fa-sticky-note ml-1 text-primary"></i> {{ $note->title }}
								</a>
								@if($note->isOverdue())
									<span class="badge badge-danger tx-10 mr-1"><i class="fas fa-exclamation-triangle"></i> متأخرة</span>
								@elseif($note->isDueToday())
									<span class="badge badge-warning tx-10 mr-1"><i class="fas fa-bell"></i> اليوم</span>
								@endif
							</td>
							<td>
								<span class="text-muted" title="{{ $note->content }}">
									{{ Str::limit($note->content, 50) }}
								</span>
							</td>
							<td class="text-center">
								@if($note->amount !== null)
									<span class="font-weight-bold text-dark">{{ number_format($note->amount, 2) }} جنيه مصري</span>
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td class="text-center">
								@if($note->due_at)
									<div>
										<span class="font-weight-bold {{ $note->isOverdue() ? 'text-danger' : 'text-dark' }}">
											{{ $note->due_at->format('Y-m-d') }}
										</span>
										<small class="d-block text-muted">{{ $note->due_at->format('h:i A') }}</small>
									</div>
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td>
								<div class="d-flex align-items-center">
									<div class="avatar avatar-xs brround bg-primary-transparent text-primary ml-1 font-weight-bold">
										{{ mb_substr($note->user->name ?? 'م', 0, 1) }}
									</div>
									<span class="font-weight-semibold">{{ $note->user->name ?? '-' }}</span>
								</div>
							</td>
							<td>
								<div>
									<span class="font-weight-bold text-dark">{{ $note->created_at->format('Y-m-d') }}</span>
									<small class="d-block text-muted">{{ $note->created_at->format('h:i A') }} ({{ $note->created_at->diffForHumans() }})</small>
								</div>
							</td>
							<td class="text-center">
								<span class="badge badge-pill {{ $note->status_badge_class }} px-2 py-1 font-weight-bold">
									{{ $note->status_arabic }}
								</span>
							</td>
							<td class="text-center">
								<div class="action-btns-wrapper">
									<!-- View details -->
									<a href="{{ route('notes.show', $note) }}" class="btn btn-sm btn-primary btn-action-icon" title="عرض التفاصيل">
										<i class="fas fa-eye"></i>
									</a>

									<!-- Edit -->
									<a href="{{ route('notes.edit', $note) }}" class="btn btn-sm btn-info btn-action-icon" title="تعديل">
										<i class="fas fa-edit"></i>
									</a>

									@if($note->isOpen())
										<!-- Mark as Completed -->
										<form action="{{ route('notes.complete', $note) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('هل تريد تحديد هذه الملاحظة كمكتملة؟')">
											@csrf
											@method('PATCH')
											<button type="submit" class="btn btn-sm btn-success btn-action-icon" title="تحديد كمكتملة">
												<i class="fas fa-check"></i>
											</button>
										</form>

										<!-- Cancel Note -->
										<form action="{{ route('notes.cancel', $note) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('هل أنت متأكد من إلغاء هذه الملاحظة؟')">
											@csrf
											@method('PATCH')
											<button type="submit" class="btn btn-sm btn-warning btn-action-icon" title="إلغاء الملاحظة">
												<i class="fas fa-ban"></i>
											</button>
										</form>
									@endif

									<!-- Safe Archival / Delete -->
									<form action="{{ route('notes.destroy', $note) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('هل أنت متأكد من أرشفة هذه الملاحظة؟')">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn btn-sm btn-danger btn-action-icon" title="أرشفة / حذف آمن">
											<i class="fas fa-trash-alt"></i>
										</button>
									</form>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="9" class="text-center py-5 text-muted">
								<i class="fas fa-clipboard tx-40 mb-2 d-block text-muted"></i>
								لا توجد ملاحظات مسجلة تطابق خيارات البحث والفلترة.
								<div class="mt-3">
									<a href="{{ route('notes.create') }}" class="btn btn-sm btn-primary">
										<i class="fas fa-plus ml-1"></i> تسجيل ملاحظة جديدة الآن
									</a>
								</div>
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="d-flex justify-content-center mt-4">
			{{ $notes->links() }}
		</div>
	</div>
</div>
@endsection

