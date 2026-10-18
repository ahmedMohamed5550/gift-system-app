@extends('layouts.master')

@section('title', 'الوردية الحالية - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة الورديات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الوردية الحالية ومتابعة الخزينة</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		@if($shift)
			<div class="mb-3 mb-xl-0 ml-2">
				<a href="{{ route('expenses.create') }}" class="btn btn-outline-danger font-weight-bold">
					<i class="fas fa-minus-circle ml-1"></i> تسجيل مصروف في الوردية
				</a>
			</div>
			<div class="mb-3 mb-xl-0 ml-2">
				<button type="button" class="btn btn-danger font-weight-bold" data-toggle="modal" data-target="#closeShiftModal">
					<i class="fas fa-lock ml-1"></i> إغلاق الوردية وتسوية الخزينة
				</button>
			</div>
		@endif
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('shifts.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-history ml-1"></i> سجل الورديات
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

@if(!$shift)
	<!-- No Active Shift State -->
	<div class="row row-sm justify-content-center">
		<div class="col-lg-7 col-xl-6">
			<div class="card text-center p-4">
				<div class="card-body">
					<div class="avatar avatar-xxl bg-warning-transparent text-warning rounded-circle mb-3 mx-auto">
						<i class="fas fa-cash-register tx-40"></i>
					</div>
					<h3 class="font-weight-bold text-dark mb-2">لا توجد وردية عمل مفتوحة حالياً</h3>
					<p class="text-muted tx-14 mb-4">
						مرحباً بك يا <strong>{{ Auth::user()->name }}</strong>. لكي تتمكن من تسجيل الفواتير والمصروفات، يجب فتح وردية جديدة وتحديد العهدة النقدية في الدرج.
					</p>

					<div class="card bg-gray-100 border text-right p-3 mb-3">
						<form action="{{ route('shifts.open') }}" method="POST">
							@csrf
							<div class="form-group">
								<label for="opening_cash_main" class="font-weight-semibold">العهدة النقدية الافتتاحية في الدرج <span class="text-danger">*</span></label>
								<div class="input-group">
									<input type="number" step="0.01" min="0" name="opening_cash" id="opening_cash_main" class="form-control form-control-lg font-weight-bold text-left" value="0.00" required>
									<div class="input-group-append">
										<span class="input-group-text font-weight-bold">جنيه مصري</span>
									</div>
								</div>
								<small class="text-muted">أدخل رصيد الفكة أو النقدية الموجودة في الدرج عند استلام الوردية (0 إذا كان فارغاً).</small>
							</div>

							<div class="form-group">
								<label for="notes_main" class="font-weight-semibold">ملاحظات افتتاحية (اختياري)</label>
								<textarea name="notes" id="notes_main" rows="2" class="form-control" placeholder="أي ملاحظات حول استلام الوردية..."></textarea>
							</div>

							<button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold mt-3">
								<i class="fas fa-play ml-1"></i> فتح الوردية وبدء العمل
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@else
	<!-- Active Shift Dashboard -->
	<!-- Shift Header Card -->
	<div class="card mb-4 border-right border-success" style="border-right-width: 5px !important;">
		<div class="card-body">
			<div class="row align-items-center">
				<div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
					<div class="d-flex align-items-center">
						<div class="avatar avatar-xl bg-success-transparent text-success rounded-circle ml-3">
							<i class="fas fa-cash-register tx-30"></i>
						</div>
						<div>
							<div class="d-flex align-items-center">
								<h3 class="font-weight-bold mb-0 ml-2">وردية العمل رقم #{{ $shift->id }}</h3>
								<span class="badge badge-success px-2 py-1 tx-12"><i class="fas fa-dot-circle ml-1"></i> مفتوحة حالياً</span>
							</div>
							<p class="text-muted tx-13 mb-0 mt-1">
								المسؤول: <strong>{{ $shift->user->name }}</strong> | تم الفتح في: <strong>{{ $shift->opened_at->translatedFormat('Y-m-d h:i A') }}</strong> ({{ $shift->opened_at->diffForHumans() }})
							</p>
						</div>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 text-lg-left text-center">
					<button type="button" class="btn btn-danger btn-lg font-weight-bold px-4" data-toggle="modal" data-target="#closeShiftModal">
						<i class="fas fa-lock ml-1"></i> إغلاق الوردية وتسوية الدرج
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Expected Cash Formula Visualizer -->
	<div class="card bg-primary-transparent border-primary mb-4">
		<div class="card-body p-3">
			<div class="d-flex flex-wrap justify-content-between align-items-center">
				<div class="my-1">
					<span class="tx-12 text-primary font-weight-bold d-block">معادلة النقدية الفعلية المتوقعة بالدرج (Expected Cash):</span>
					<span class="tx-13 text-dark">
						[العهدة الافتتاحية: <strong>{{ number_format($summary['reconciliation']['opening_cash'], 2) }}</strong>]
						+ [المبيعات النقدية: <strong>{{ number_format($summary['reconciliation']['cash_sales'], 2) }}</strong>]
						- [المرتجعات النقدية: <strong>{{ number_format($summary['reconciliation']['cash_returns'], 2) }}</strong>]
						- [المصروفات النقدية: <strong>{{ number_format($summary['reconciliation']['cash_expenses'], 2) }}</strong>]
					</span>
				</div>
				<div class="text-left my-1">
					<span class="tx-11 text-muted d-block font-weight-bold">المطلوب تواجده بالدرج الآن</span>
					<h3 class="tx-24 font-weight-bold text-primary mb-0">
						{{ number_format($summary['reconciliation']['expected_cash'], 2) }} ج.م
					</h3>
				</div>
			</div>
		</div>
	</div>

	<!-- Cash Reconciliation 4-Card Grid -->
	<div class="row row-sm">
		<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
			<div class="card overflow-hidden sales-card bg-secondary-gradient">
				<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
					<div class="">
						<h6 class="mb-3 tx-12 text-white">العهدة الافتتاحية (Opening Cash)</h6>
					</div>
					<div class="pb-0 mt-0">
						<div class="d-flex">
							<div class="">
								<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($summary['reconciliation']['opening_cash'], 2) }} ج.م</h3>
								<p class="mb-0 tx-12 text-white op-7">رصيد بداية الوردية</p>
							</div>
							<span class="float-right my-auto mr-auto">
								<i class="fas fa-wallet text-white tx-24"></i>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
			<div class="card overflow-hidden sales-card bg-success-gradient">
				<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
					<div class="">
						<h6 class="mb-3 tx-12 text-white">مبيعات نقدية (Cash Sales)</h6>
					</div>
					<div class="pb-0 mt-0">
						<div class="d-flex">
							<div class="">
								<h3 class="tx-20 font-weight-bold mb-1 text-white">+{{ number_format($summary['reconciliation']['cash_sales'], 2) }} ج.م</h3>
								<p class="mb-0 tx-12 text-white op-7">{{ $summary['sales']['count'] }} فاتورة مكتملة</p>
							</div>
							<span class="float-right my-auto mr-auto">
								<i class="fas fa-arrow-down text-white tx-24"></i>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
			<div class="card overflow-hidden sales-card bg-warning-gradient">
				<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
					<div class="">
						<h6 class="mb-3 tx-12 text-white">مرتجعات نقدية (Cash Refunds)</h6>
					</div>
					<div class="pb-0 mt-0">
						<div class="d-flex">
							<div class="">
								<h3 class="tx-20 font-weight-bold mb-1 text-white">-{{ number_format($summary['reconciliation']['cash_returns'], 2) }} ج.م</h3>
								<p class="mb-0 tx-12 text-white op-7">{{ $summary['returns']['count'] }} عملية إرجاع نقدي</p>
							</div>
							<span class="float-right my-auto mr-auto">
								<i class="fas fa-undo text-white tx-24"></i>
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
						<h6 class="mb-3 tx-12 text-white">مصروفات نقدية (Cash Expenses)</h6>
					</div>
					<div class="pb-0 mt-0">
						<div class="d-flex">
							<div class="">
								<h3 class="tx-20 font-weight-bold mb-1 text-white">-{{ number_format($summary['reconciliation']['cash_expenses'], 2) }} ج.م</h3>
								<p class="mb-0 tx-12 text-white op-7">{{ $summary['expenses']['count'] }} سند صرف من الدرج</p>
							</div>
							<span class="float-right my-auto mr-auto">
								<i class="fas fa-arrow-up text-white tx-24"></i>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Electronic Wallets Callout (Excluded from Drawer) -->
	<div class="alert alert-light border mb-4">
		<div class="row align-items-center text-center text-md-right">
			<div class="col-md-4 mb-2 mb-md-0">
				<span class="font-weight-bold text-dark"><i class="fas fa-mobile-alt ml-1 text-primary"></i> مدفوعات إلكترونية (مستبعدة من الدرج):</span>
			</div>
			<div class="col-md-4 mb-2 mb-md-0">
				<span class="badge badge-danger px-2 py-1 ml-1">فودافون كاش</span>
				<strong class="tx-15 text-dark">{{ number_format($summary['sales']['vodafone_cash'], 2) }} ج.م</strong>
			</div>
			<div class="col-md-4">
				<span class="badge badge-primary px-2 py-1 ml-1">انستا باي (InstaPay)</span>
				<strong class="tx-15 text-dark">{{ number_format($summary['sales']['instapay'], 2) }} ج.م</strong>
			</div>
		</div>
	</div>

	<!-- Associated Transactions Tabs Card -->
	<div class="card">
		<div class="card-header pb-0">
			<div class="d-flex justify-content-between align-items-center">
				<h4 class="card-title mg-b-0"><i class="fas fa-list-alt ml-1 text-primary"></i> العمليات المرتبطة بالوردية الحالية</h4>
				<a href="{{ route('expenses.create') }}" class="btn btn-sm btn-primary">
					<i class="fas fa-plus ml-1"></i> إضافة مصروف
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class="panel panel-primary tabs-style-2">
				<div class="tab-menu-heading">
					<div class="tabs-menu1">
						<!-- Tabs -->
						<ul class="nav panel-tabs main-nav-line">
							<li><a href="#tabSales" class="nav-link active" data-toggle="tab"><i class="fas fa-shopping-bag ml-1"></i> المبيعات ({{ $shift->sales->count() }})</a></li>
							<li><a href="#tabExpenses" class="nav-link" data-toggle="tab"><i class="fas fa-receipt ml-1"></i> المصروفات ({{ $shift->expenses->count() }})</a></li>
							<li><a href="#tabReturns" class="nav-link" data-toggle="tab"><i class="fas fa-undo-alt ml-1"></i> المرتجعات ({{ $shift->returns->count() }})</a></li>
						</ul>
					</div>
				</div>
				<div class="panel-body tabs-menu-body main-content-body-right border">
					<div class="tab-content">
						<!-- Tab Sales -->
						<div class="tab-pane active" id="tabSales">
							<div class="table-responsive">
								<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
									<thead>
										<tr>
											<th>رقم الفاتورة</th>
											<th>الوقت</th>
											<th>طريقة الدفع</th>
											<th>الإجمالي</th>
											<th>الحالة</th>
										</tr>
									</thead>
									<tbody>
										@forelse($shift->sales as $sale)
											<tr>
												<td class="font-weight-bold">{{ $sale->invoice_number }}</td>
												<td class="tx-12">{{ $sale->created_at ? $sale->created_at->translatedFormat('h:i A') : '-' }}</td>
												<td>
													<span class="badge {{ $sale->payment_method === 'cash' ? 'badge-success' : ($sale->payment_method === 'vodafone_cash' ? 'badge-danger' : 'badge-primary') }}">
														{{ $sale->payment_method === 'cash' ? 'كاش' : ($sale->payment_method === 'vodafone_cash' ? 'فودافون كاش' : 'انستا باي') }}
													</span>
												</td>
												<td class="font-weight-bold">{{ number_format($sale->total, 2) }} ج.م</td>
												<td><span class="badge badge-success">{{ $sale->status }}</span></td>
											</tr>
										@empty
											<tr><td colspan="5" class="py-3 text-muted">لم يتم تسجيل مبيعات في هذه الوردية بعد.</td></tr>
										@endforelse
									</tbody>
								</table>
							</div>
						</div>

						<!-- Tab Expenses -->
						<div class="tab-pane" id="tabExpenses">
							<div class="table-responsive">
								<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
									<thead>
										<tr>
											<th>#</th>
											<th>البند</th>
											<th>المبلغ</th>
											<th>طريقة الدفع</th>
											<th>الحالة</th>
											<th>البيان</th>
										</tr>
									</thead>
									<tbody>
										@forelse($shift->expenses as $expense)
											<tr class="{{ $expense->is_cancelled ? 'table-danger' : '' }}">
												<td>{{ $expense->id }}</td>
												<td class="font-weight-bold">{{ $expense->category }}</td>
												<td class="font-weight-bold text-danger">{{ number_format($expense->amount, 2) }} ج.م</td>
												<td><span class="badge {{ $expense->payment_method_badge_class }}">{{ $expense->payment_method_label }}</span></td>
												<td><span class="badge {{ $expense->status_badge_class }}">{{ $expense->status_label }}</span></td>
												<td class="tx-12">{{ $expense->description ?: '-' }}</td>
											</tr>
										@empty
											<tr><td colspan="6" class="py-3 text-muted">لم يتم تسجيل أي مصروفات في هذه الوردية.</td></tr>
										@endforelse
									</tbody>
								</table>
							</div>
						</div>

						<!-- Tab Returns -->
						<div class="tab-pane" id="tabReturns">
							<div class="table-responsive">
								<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
									<thead>
										<tr>
											<th>رقم الإرجاع</th>
											<th>رقم الفاتورة الأصلية</th>
											<th>المبلغ المردود</th>
											<th>السبب</th>
											<th>الحالة</th>
										</tr>
									</thead>
									<tbody>
										@forelse($shift->returns as $ret)
											<tr>
												<td class="font-weight-bold">{{ $ret->return_number }}</td>
												<td>{{ $ret->sale->invoice_number ?? '-' }}</td>
												<td class="font-weight-bold text-warning">{{ number_format($ret->total_amount, 2) }} ج.م</td>
												<td class="tx-12">{{ $ret->reason }}</td>
												<td><span class="badge badge-warning">{{ $ret->status }}</span></td>
											</tr>
										@empty
											<tr><td colspan="5" class="py-3 text-muted">لم يتم تسجيل مرتجعات في هذه الوردية.</td></tr>
										@endforelse
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal: Close Shift & Reconcile Cash -->
	<div class="modal fade" id="closeShiftModal" tabindex="-1" role="dialog" aria-labelledby="closeShiftModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<form action="{{ route('shifts.close', $shift) }}" method="POST" id="closeShiftForm">
					@csrf
					<div class="modal-header bg-danger-transparent">
						<h5 class="modal-title text-danger font-weight-bold" id="closeShiftModalLabel">
							<i class="fas fa-lock ml-1"></i> إغلاق الوردية رقم #{{ $shift->id }} وتسوية الخزينة
						</h5>
						<button type="button" class="close ml-0 mr-auto" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body text-right">
						<!-- Expected Cash Banner in Modal -->
						<div class="card bg-light border p-3 mb-3">
							<div class="row align-items-center text-center">
								<div class="col-sm-6 border-left">
									<span class="tx-12 text-muted d-block">النقدية المحسوبة المتوقعة بالدرج</span>
									<h3 class="tx-22 font-weight-bold text-primary mb-0" id="modalExpectedCash" data-expected="{{ $summary['reconciliation']['expected_cash'] }}">
										{{ number_format($summary['reconciliation']['expected_cash'], 2) }} ج.م
									</h3>
								</div>
								<div class="col-sm-6 mt-2 mt-sm-0">
									<span class="tx-12 text-muted d-block">فارق التسوية (عجز / زيادة)</span>
									<h3 class="tx-22 font-weight-bold mb-0 text-muted" id="modalDifferencePreview">
										0.00 ج.م
									</h3>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="closing_cash" class="font-weight-semibold tx-15">
								المبلغ النقدي الفعلي الموجود بالدرج الآن <span class="text-danger">*</span>
							</label>
							<div class="input-group">
								<input type="number" step="0.01" min="0" name="closing_cash" id="closing_cash"
									   class="form-control form-control-lg font-weight-bold text-left tx-20"
									   placeholder="قم بعد النقدية الموجودة بالدرج وأدخل المبلغ..." required>
								<div class="input-group-append">
									<span class="input-group-text font-weight-bold">جنيه مصري</span>
								</div>
							</div>
							<small class="text-muted d-block mt-1">يجب جرد النقدية بالدرج بدقة وإدخال المبلغ الفعلي. سيقوم النظام بحساب الفارق وتوثيقه تلقائياً.</small>
						</div>

						<div class="form-group">
							<label for="close_notes" class="font-weight-semibold">ملاحظات الإغلاق والتسوية (اختياري)</label>
							<textarea name="notes" id="close_notes" rows="2" class="form-control" placeholder="أي ملاحظات حول الفارق، أو تسليم النقدية للإدارة..."></textarea>
						</div>
					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء والعودة</button>
						<button type="submit" class="btn btn-danger font-weight-bold px-4" onclick="return confirm('هل تأكدت من عد النقدية بالدرج؟ لا يمكن التراجع عن إغلاق الوردية بعد الاعتماد.')">
							<i class="fas fa-check-circle ml-1"></i> اعتماد الإغلاق وتسوية الخزينة
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endif
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
	const closingInput = document.getElementById('closing_cash');
	const expectedEl = document.getElementById('modalExpectedCash');
	const diffPreview = document.getElementById('modalDifferencePreview');

	if (closingInput && expectedEl && diffPreview) {
		const expectedVal = parseFloat(expectedEl.dataset.expected || 0);

		closingInput.addEventListener('input', function () {
			const enteredVal = parseFloat(closingInput.value || 0);
			const diff = enteredVal - expectedVal;

			if (isNaN(enteredVal) || closingInput.value === '') {
				diffPreview.className = 'tx-22 font-weight-bold mb-0 text-muted';
				diffPreview.textContent = '0.00 ج.م';
				return;
			}

			if (diff > 0.001) {
				diffPreview.className = 'tx-22 font-weight-bold mb-0 text-success';
				diffPreview.textContent = '+' + diff.toFixed(2) + ' ج.م (فائض في الدرج)';
			} else if (diff < -0.001) {
				diffPreview.className = 'tx-22 font-weight-bold mb-0 text-danger';
				diffPreview.textContent = diff.toFixed(2) + ' ج.م (عجز في الدرج)';
			} else {
				diffPreview.className = 'tx-22 font-weight-bold mb-0 text-primary';
				diffPreview.textContent = '0.00 ج.م (مطابق تماماً)';
			}
		});
	}
});
</script>
@endsection

