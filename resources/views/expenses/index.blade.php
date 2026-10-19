@extends('layouts.master')

@section('title', 'سجل المصروفات - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة المصروفات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ قائمة وسجل المصروفات النثرية والتشغيلية</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('expenses.create') }}" class="btn btn-primary font-weight-bold">
				<i class="fas fa-plus ml-1"></i> تسجيل مصروف جديد
			</a>
		</div>
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('shifts.current') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-cash-register ml-1"></i> الوردية الحالية
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

<!-- Expense Analytics Cards -->
<div class="row row-sm">
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">إجمالي المصروفات النشطة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['total_active'], 2) }} ج.م</h3>
							<p class="mb-0 tx-12 text-white op-7">{{ $stats['active_count'] }} سند صرف معتمد</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-receipt text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">مصروفات نقدية (تخصم من الدرج)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['cash_active'], 2) }} ج.م</h3>
							<p class="mb-0 tx-12 text-white op-7">مدفوعة كاش من الخزينة</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-money-bill-wave text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">مصروفات إلكترونية (محافظ / بنك)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['non_cash_active'], 2) }} ج.م</h3>
							<p class="mb-0 tx-12 text-white op-7">لا تؤثر على نقدية الدرج</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-mobile-alt text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-secondary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div class="">
					<h6 class="mb-3 tx-12 text-white">مصروفات ملغاة (محفوظة تاريخياً)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div class="">
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['cancelled_total'], 2) }} ج.م</h3>
							<p class="mb-0 tx-12 text-white op-7">{{ $stats['cancelled_count'] }} سند ملغي ومستبعد</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-ban text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Filters Card -->
<div class="card mb-3">
	<div class="card-body py-3">
		<form method="GET" action="{{ route('expenses.index') }}">
			<div class="row row-sm">
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">البحث:</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-search"></i></span>
						</div>
						<input type="text" name="search" class="form-control" placeholder="ابحث في البند أو البيان..." value="{{ request('search') }}">
					</div>
				</div>
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">طريقة الدفع:</label>
					<select name="payment_method" class="form-control">
						<option value="">-- كل الطرق --</option>
						<option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>نقداً (كاش)</option>
						<option value="vodafone_cash" {{ request('payment_method') === 'vodafone_cash' ? 'selected' : '' }}>فودافون كاش</option>
						<option value="instapay" {{ request('payment_method') === 'instapay' ? 'selected' : '' }}>انستا باي</option>
					</select>
				</div>
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">الحالة:</label>
					<select name="status" class="form-control">
						<option value="">-- كل الحالات --</option>
						<option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط / معتمد</option>
						<option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
					</select>
				</div>
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">المسؤول:</label>
					<select name="user_id" class="form-control">
						<option value="">-- كل المستخدمين --</option>
						@foreach($users as $u)
							<option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
								{{ $u->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">الفترة الزمنية:</label>
					<div class="input-group">
						<input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="من تاريخ">
						<div class="input-group-prepend input-group-append">
							<span class="input-group-text"><i class="fas fa-arrow-left"></i></span>
						</div>
						<input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="إلى تاريخ">
					</div>
				</div>

				<div class="col-12 text-left mt-2">
					<button type="submit" class="btn btn-primary font-weight-bold">
						<i class="fas fa-search ml-1"></i> تصفية النتائج
					</button>
					<a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary font-weight-bold">
						<i class="fas fa-times ml-1"></i> إلغاء التصفية
					</a>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Expenses Table Card -->
<div class="card" id="live-search-container">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h4 class="card-title mg-b-0"><i class="fas fa-file-invoice-dollar ml-1 text-primary"></i> قائمة المصروفات</h4>
		<span class="tx-12 text-muted">إجمالي السجلات: {{ $expenses->total() }}</span>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
				<thead>
					<tr>
						<th>#</th>
						<th>التاريخ والوقت</th>
						<th class="text-right">بند المصروف</th>
						<th>المبلغ</th>
						<th>طريقة الدفع</th>
						<th>الوردية</th>
						<th>المسؤول</th>
						<th>الحالة</th>
						<th>الإجراءات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($expenses as $expense)
						<tr class="{{ $expense->is_cancelled ? 'table-danger' : '' }}">
							<td>{{ $expense->id }}</td>
							<td class="tx-12 font-weight-semibold">
								<i class="far fa-clock ml-1 text-muted"></i>
								{{ $expense->created_at ? $expense->created_at->translatedFormat('Y-m-d h:i A') : '-' }}
							</td>
							<td class="text-right font-weight-bold">
								<a href="{{ route('expenses.show', $expense) }}" class="text-dark">
									{{ $expense->category }}
								</a>
								@if($expense->description)
									<span class="d-block tx-11 text-muted">{{ Str::limit($expense->description, 35) }}</span>
								@endif
							</td>
							<td class="font-weight-bold {{ $expense->is_cancelled ? 'text-muted' : 'text-danger' }}">
								{{ number_format($expense->amount, 2) }} ج.م
							</td>
							<td>
								<span class="badge {{ $expense->payment_method_badge_class }}">
									{{ $expense->payment_method_label }}
								</span>
							</td>
							<td>
								@if($expense->shift)
									<a href="{{ route('shifts.show', $expense->shift) }}" class="badge badge-light border">
										وردية #{{ $expense->shift_id }}
									</a>
								@else
									<span class="text-muted">عام (بدون وردية)</span>
								@endif
							</td>
							<td>{{ $expense->user->name ?? '-' }}</td>
							<td>
								<span class="badge {{ $expense->status_badge_class }}">
									{{ $expense->status_label }}
								</span>
							</td>
							<td>
								<div class="btn-group" role="group">
									<a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-info" title="عرض التفاصيل">
										<i class="fas fa-eye"></i>
									</a>
									@if(!$expense->is_cancelled)
										<a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-primary" title="تعديل">
											<i class="fas fa-edit"></i>
										</a>
										<button type="button" class="btn btn-sm btn-danger btn-cancel-expense"
												data-id="{{ $expense->id }}"
												data-category="{{ $expense->category }}"
												data-amount="{{ number_format($expense->amount, 2) }}"
												title="إلغاء المصروف">
											<i class="fas fa-ban"></i>
										</button>
									@endif
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="9" class="text-center py-4">
								<i class="fas fa-wallet text-muted tx-40 mb-2"></i>
								<p class="text-muted mb-0">لا توجد مصروفات مسجلة مطابقة للبحث.</p>
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<!-- Pagination -->
		<div class="mt-4 d-flex justify-content-center">
			{{ $expenses->links() }}
		</div>
	</div>
</div>

<!-- Modal: Cancel Expense -->
<div class="modal fade" id="cancelExpenseModal" tabindex="-1" role="dialog" aria-labelledby="cancelExpenseModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="cancelExpenseForm" method="POST">
				@csrf
				@method('PATCH')
				<div class="modal-header bg-danger-transparent">
					<h5 class="modal-title text-danger font-weight-bold" id="cancelExpenseModalLabel">
						<i class="fas fa-ban ml-1"></i> إلغاء المصروف وتوثيق السجل المالي
					</h5>
					<button type="button" class="close ml-0 mr-auto" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-right">
					<div class="alert alert-warning">
						<i class="fas fa-exclamation-triangle ml-1"></i>
						سيتم الاحتفاظ ببيانات هذا المصروف (<strong id="modalExpenseCategory"></strong> بمبلغ <strong id="modalExpenseAmount"></strong> ج.م) في السجل التاريخي دون حذفه نهائياً، مع استبعاده من حسابات تسوية الخزينة.
					</div>

					<div class="form-group">
						<label for="cancel_reason" class="font-weight-semibold">سبب إلغاء المصروف <span class="text-danger">*</span></label>
						<textarea name="reason" id="cancel_reason" rows="3" class="form-control" placeholder="يرجى كتابة سبب الإلغاء بدقة (مثال: تسجيل خاطئ، استرداد المبلغ، إلغاء الطلب)..." required></textarea>
					</div>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">تراجع</button>
					<button type="submit" class="btn btn-danger font-weight-bold">
						<i class="fas fa-check ml-1"></i> تأكيد إلغاء المصروف
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
	document.querySelectorAll('.btn-cancel-expense').forEach(function(btn) {
		btn.addEventListener('click', function() {
			const id = this.dataset.id;
			const category = this.dataset.category;
			const amount = this.dataset.amount;

			document.getElementById('modalExpenseCategory').textContent = category;
			document.getElementById('modalExpenseAmount').textContent = amount;
			document.getElementById('cancelExpenseForm').action = '/expenses/' + id + '/cancel';

			$('#cancelExpenseModal').modal('show');
		});
	});
});
</script>
@endsection

