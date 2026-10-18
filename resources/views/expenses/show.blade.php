@extends('layouts.master')

@section('title', 'تفاصيل المصروف - #' . $expense->id)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة المصروفات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل سند المصروف رقم #{{ $expense->id }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		@if(!$expense->is_cancelled)
			<div class="mb-3 mb-xl-0 ml-2">
				<a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary font-weight-bold">
					<i class="fas fa-edit ml-1"></i> تعديل المصروف
				</a>
			</div>
			<div class="mb-3 mb-xl-0 ml-2">
				<button type="button" class="btn btn-danger font-weight-bold" data-toggle="modal" data-target="#cancelModal">
					<i class="fas fa-ban ml-1"></i> إلغاء المصروف
				</button>
			</div>
		@endif
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('expenses.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
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

@if($expense->is_cancelled)
	<div class="alert alert-danger mb-4">
		<h5 class="font-weight-bold"><i class="fas fa-ban ml-1"></i> هذا المصروف ملغي ومستبعد من حسابات الخزينة والتسويات</h5>
		<p class="mb-1 tx-13">تم الإلغاء بواسطة: <strong>{{ $expense->cancelledBy->name ?? 'غير محدد' }}</strong> في تاريخ: <strong>{{ $expense->cancelled_at ? $expense->cancelled_at->format('Y-m-d H:i') : '-' }}</strong></p>
		<p class="mb-0 tx-13"><strong>سبب الإلغاء الموثق:</strong> {{ $expense->cancellation_reason ?: 'لم يتم تسجيل سبب' }}</p>
	</div>
@endif

<div class="row row-sm justify-content-center">
	<div class="col-lg-8 col-xl-7">
		<div class="card">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h4 class="card-title mg-b-0"><i class="fas fa-receipt ml-1 text-primary"></i> بيانات سند الصرف</h4>
				<span class="badge {{ $expense->status_badge_class }} tx-13 px-3 py-1">
					{{ $expense->status_label }}
				</span>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered mb-0">
						<tbody>
							<tr>
								<th class="bg-light wd-35p">رقم سند المصروف</th>
								<td class="font-weight-bold">#{{ $expense->id }}</td>
							</tr>
							<tr>
								<th class="bg-light">بند المصروف</th>
								<td class="font-weight-bold tx-16 text-primary">{{ $expense->category }}</td>
							</tr>
							<tr>
								<th class="bg-light">المبلغ</th>
								<td class="font-weight-bold tx-18 text-danger">{{ number_format($expense->amount, 2) }} ج.م</td>
							</tr>
							<tr>
								<th class="bg-light">طريقة الدفع</th>
								<td>
									<span class="badge {{ $expense->payment_method_badge_class }} tx-13 px-2 py-1">
										{{ $expense->payment_method_label }}
									</span>
									<span class="tx-12 text-muted mr-2">
										({{ $expense->is_cash ? 'يخصم من نقدية الدرج' : 'دفع إلكتروني - لا يؤثر على الدرج' }})
									</span>
								</td>
							</tr>
							<tr>
								<th class="bg-light">الوردية المرتبطة</th>
								<td>
									@if($expense->shift)
										<a href="{{ route('shifts.show', $expense->shift) }}" class="btn btn-sm btn-outline-primary">
											<i class="fas fa-cash-register ml-1"></i> وردية #{{ $expense->shift_id }} (الكاشير: {{ $expense->shift->user->name }})
										</a>
									@else
										<span class="text-muted">مصروف عام (غير مرتبط بوردية كاشير)</span>
									@endif
								</td>
							</tr>
							<tr>
								<th class="bg-light">المستخدم المسجّل</th>
								<td>
									<i class="fas fa-user ml-1 text-secondary"></i> {{ $expense->user->name ?? '-' }}
								</td>
							</tr>
							<tr>
								<th class="bg-light">تاريخ ووقت التسجيل</th>
								<td>{{ $expense->created_at ? $expense->created_at->translatedFormat('Y-m-d h:i A') : '-' }}</td>
							</tr>
							<tr>
								<th class="bg-light">البيان والتفاصيل</th>
								<td>{{ $expense->description ?: 'لا يوجد بيان مسجل' }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Cancel Expense -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form action="{{ route('expenses.cancel', $expense) }}" method="POST">
				@csrf
				@method('PATCH')
				<div class="modal-header bg-danger-transparent">
					<h5 class="modal-title text-danger font-weight-bold" id="cancelModalLabel">
						<i class="fas fa-ban ml-1"></i> إلغاء المصروف رقم #{{ $expense->id }}
					</h5>
					<button type="button" class="close ml-0 mr-auto" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-right">
					<div class="alert alert-warning">
						<i class="fas fa-exclamation-triangle ml-1"></i> سيتم الاحتفاظ بالسجل التاريخي للمصروف دون حذفه نهائياً، مع استبعاده من حسابات تسوية الخزينة.
					</div>

					<div class="form-group">
						<label for="reason" class="font-weight-semibold">سبب الإلغاء لتوثيق السجل المالي <span class="text-danger">*</span></label>
						<textarea name="reason" id="reason" rows="3" class="form-control" placeholder="يرجى كتابة سبب الإلغاء بالتفصيل..." required></textarea>
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

