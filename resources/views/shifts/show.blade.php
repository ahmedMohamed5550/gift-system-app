@extends('layouts.master')

@section('title', 'تفاصيل الوردية وتسوية الخزينة - #' . $shift->id)

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة الورديات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل الوردية رقم #{{ $shift->id }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('shifts.print', $shift) }}" target="_blank" class="btn btn-primary font-weight-bold">
				<i class="fas fa-print ml-1"></i> طباعة تقرير الوردية
			</a>
		</div>
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('shifts.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-arrow-right ml-1"></i> العودة لسجل الورديات
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

<!-- Shift Information Header Card -->
<div class="card mb-4 border-right {{ $shift->is_open ? 'border-success' : 'border-secondary' }}" style="border-right-width: 5px !important;">
	<div class="card-body">
		<div class="row align-items-center">
			<div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
				<div class="d-flex align-items-center">
					<div class="avatar avatar-xl {{ $shift->is_open ? 'bg-success-transparent text-success' : 'bg-primary-transparent text-primary' }} rounded-circle ml-3">
						<i class="fas fa-cash-register tx-30"></i>
					</div>
					<div>
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold mb-0 ml-2">وردية العمل رقم #{{ $shift->id }}</h3>
							<span class="badge {{ $shift->status_badge_class }} px-2 py-1 tx-12">
								{{ $shift->status_label_arabic }}
							</span>
						</div>
						<p class="text-muted tx-13 mb-0 mt-1">
							الكاشير المسؤول: <strong>{{ $shift->user->name }}</strong>
						</p>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-12 text-lg-left text-md-right">
				<div class="d-inline-block text-right ml-4">
					<span class="tx-11 text-muted d-block">وقت الفتح</span>
					<strong class="tx-13">{{ $shift->opened_at ? $shift->opened_at->translatedFormat('Y-m-d h:i A') : '-' }}</strong>
				</div>
				<div class="d-inline-block text-right">
					<span class="tx-11 text-muted d-block">وقت الإغلاق</span>
					<strong class="tx-13">
						{{ $shift->closed_at ? $shift->closed_at->translatedFormat('Y-m-d h:i A') : 'مفتوحة حتى الآن' }}
					</strong>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Cash Drawer Reconciliation Breakdown Card -->
<div class="row row-sm">
	<div class="col-lg-7">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-balance-scale ml-1 text-primary"></i> تسوية النقدية بالدرج (Cash Reconciliation)</h4>
				<p class="tx-12 tx-gray-500 mb-2">حساب النقدية الفعلية المتوقعة بالدرج مقارنة بالمبلغ الفعلي عند الإغلاق.</p>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered mb-0">
						<tbody>
							<tr>
								<td class="font-weight-semibold bg-light wd-50p">
									<i class="fas fa-wallet ml-1 text-secondary"></i> العهدة الافتتاحية في بداية الوردية
								</td>
								<td class="font-weight-bold text-left tx-15">
									+ {{ number_format($summary['reconciliation']['opening_cash'], 2) }} ج.م
								</td>
							</tr>
							<tr>
								<td class="font-weight-semibold bg-light">
									<i class="fas fa-arrow-down ml-1 text-success"></i> إجمالي المبيعات النقدية (الكاش فقط)
								</td>
								<td class="font-weight-bold text-success text-left tx-15">
									+ {{ number_format($summary['reconciliation']['cash_sales'], 2) }} ج.م
								</td>
							</tr>
							<tr>
								<td class="font-weight-semibold bg-light">
									<i class="fas fa-undo ml-1 text-warning"></i> إجمالي المرتجعات النقدية (المردود كاش)
								</td>
								<td class="font-weight-bold text-warning text-left tx-15">
									- {{ number_format($summary['reconciliation']['cash_returns'], 2) }} ج.م
								</td>
							</tr>
							<tr>
								<td class="font-weight-semibold bg-light">
									<i class="fas fa-arrow-up ml-1 text-danger"></i> إجمالي المصروفات النقدية (المدفوعة من الدرج)
								</td>
								<td class="font-weight-bold text-danger text-left tx-15">
									- {{ number_format($summary['reconciliation']['cash_expenses'], 2) }} ج.م
								</td>
							</tr>
							<tr class="table-primary">
								<td class="font-weight-bold tx-16">
									<i class="fas fa-equals ml-1"></i> النقدية المحسوبة المتوقعة بالدرج (Expected Cash)
								</td>
								<td class="font-weight-bold text-primary text-left tx-18">
									{{ number_format($summary['reconciliation']['expected_cash'], 2) }} ج.م
								</td>
							</tr>
							<tr>
								<td class="font-weight-bold bg-light">
									<i class="fas fa-cash-register ml-1 text-dark"></i> النقدية الفعلية المحصية عند الإغلاق (Actual Cash)
								</td>
								<td class="font-weight-bold text-dark text-left tx-18">
									@if($summary['reconciliation']['actual_closing_cash'] !== null)
										{{ number_format($summary['reconciliation']['actual_closing_cash'], 2) }} ج.م
									@else
										<span class="badge badge-warning">الوردية لا تزال مفتوحة</span>
									@endif
								</td>
							</tr>
							@if($shift->is_closed)
								@php
									$diff = $summary['reconciliation']['difference'];
								@endphp
								<tr class="{{ $diff > 0.001 ? 'table-success' : ($diff < -0.001 ? 'table-danger' : 'table-info') }}">
									<td class="font-weight-bold tx-16">
										<i class="fas fa-calculator ml-1"></i> فارق التسوية النهائية (الفائض / العجز)
									</td>
									<td class="font-weight-bold text-left tx-18">
										@if($diff > 0.001)
											<span class="text-success">+{{ number_format($diff, 2) }} ج.م (زيادة / فائض بالدرج)</span>
										@elseif($diff < -0.001)
											<span class="text-danger">{{ number_format($diff, 2) }} ج.م (عجز في الدرج)</span>
										@else
											<span class="text-primary">0.00 ج.م (الدرج مطابق تماماً)</span>
										@endif
									</td>
								</tr>
							@endif
						</tbody>
					</table>
				</div>

				@if($shift->notes)
					<div class="alert alert-light border mt-3 mb-0">
						<span class="font-weight-bold d-block mb-1"><i class="fas fa-comment-dots ml-1 text-secondary"></i> ملاحظات الوردية:</span>
						<p class="mb-0 tx-13">{{ $shift->notes }}</p>
					</div>
				@endif
			</div>
		</div>
	</div>

	<!-- Non-Cash & Summary Analytics Column -->
	<div class="col-lg-5">
		<!-- Electronic Payments Card -->
		<div class="card mb-4">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-credit-card ml-1 text-primary"></i> المدفوعات الإلكترونية (مستبعدة من الدرج)</h4>
				<p class="tx-12 tx-gray-500 mb-0">معاملات محافظ إلكترونية تم تحصيلها مباشرة في الحسابات وليس بدرج الكاشير.</p>
			</div>
			<div class="card-body">
				<div class="d-flex justify-content-between p-3 bg-light rounded mb-3">
					<div>
						<span class="badge badge-danger mb-1">فودافون كاش</span>
						<h4 class="font-weight-bold text-dark mb-0">{{ number_format($summary['sales']['vodafone_cash'], 2) }} ج.م</h4>
					</div>
					<div class="my-auto">
						<i class="fas fa-mobile-alt tx-30 text-danger"></i>
					</div>
				</div>

				<div class="d-flex justify-content-between p-3 bg-light rounded mb-3">
					<div>
						<span class="badge badge-primary mb-1">انستا باي (InstaPay)</span>
						<h4 class="font-weight-bold text-dark mb-0">{{ number_format($summary['sales']['instapay'], 2) }} ج.م</h4>
					</div>
					<div class="my-auto">
						<i class="fas fa-university tx-30 text-primary"></i>
					</div>
				</div>

				<div class="d-flex justify-content-between p-2 border-top">
					<span class="text-muted font-weight-bold">إجمالي مبيعات المحافظ الإلكترونية:</span>
					<strong class="tx-15 text-dark">{{ number_format($summary['sales']['vodafone_cash'] + $summary['sales']['instapay'], 2) }} ج.م</strong>
				</div>
			</div>
		</div>

		<!-- Comprehensive Shift Totals Card -->
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-chart-pie ml-1 text-primary"></i> إجمالي حركة الوردية</h4>
			</div>
			<div class="card-body">
				<ul class="list-group list-group-flush">
					<li class="list-group-item d-flex justify-content-between align-items-center pr-0 pl-0">
						<span>إجمالي المبيعات (كافة الطرق):</span>
						<strong class="text-dark tx-15">{{ number_format($summary['sales']['total'], 2) }} ج.م ({{ $summary['sales']['count'] }} فاتورة)</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center pr-0 pl-0">
						<span>إجمالي المرتجعات:</span>
						<strong class="text-warning tx-15">{{ number_format($summary['returns']['total'], 2) }} ج.م ({{ $summary['returns']['count'] }} إرجاع)</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center pr-0 pl-0">
						<span>إجمالي المصروفات النشطة:</span>
						<strong class="text-danger tx-15">{{ number_format($summary['expenses']['total'], 2) }} ج.م ({{ $summary['expenses']['count'] }} مصروف)</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center pr-0 pl-0 bg-light p-2 mt-2 rounded">
						<span class="font-weight-bold">صافي دخل الوردية (المبيعات - المرتجعات - المصروفات):</span>
						<strong class="text-primary tx-16 font-weight-bold">
							{{ number_format($summary['sales']['total'] - $summary['returns']['total'] - $summary['expenses']['total'], 2) }} ج.م
						</strong>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<!-- Detailed Associated Lists Tabs Card -->
<div class="card">
	<div class="card-header pb-0">
		<h4 class="card-title mg-b-0"><i class="fas fa-receipt ml-1 text-primary"></i> الفواتير والمصروفات والمرتجعات المرتبطة</h4>
	</div>
	<div class="card-body">
		<div class="panel panel-primary tabs-style-2">
			<div class="tab-menu-heading">
				<div class="tabs-menu1">
					<ul class="nav panel-tabs main-nav-line">
						<li><a href="#tabShowSales" class="nav-link active" data-toggle="tab"><i class="fas fa-shopping-bag ml-1"></i> المبيعات ({{ $shift->sales->count() }})</a></li>
						<li><a href="#tabShowExpenses" class="nav-link" data-toggle="tab"><i class="fas fa-receipt ml-1"></i> المصروفات ({{ $shift->expenses->count() }})</a></li>
						<li><a href="#tabShowReturns" class="nav-link" data-toggle="tab"><i class="fas fa-undo-alt ml-1"></i> المرتجعات ({{ $shift->returns->count() }})</a></li>
					</ul>
				</div>
			</div>
			<div class="panel-body tabs-menu-body main-content-body-right border">
				<div class="tab-content">
					<!-- Sales Tab -->
					<div class="tab-pane active" id="tabShowSales">
						<div class="table-responsive">
							<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
								<thead>
									<tr>
										<th>رقم الفاتورة</th>
										<th>الوقت</th>
										<th>طريقة الدفع</th>
										<th>المجموع الفرعي</th>
										<th>الخصم</th>
										<th>الإجمالي النهائي</th>
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
											<td>{{ number_format($sale->subtotal, 2) }} ج.م</td>
											<td>{{ number_format($sale->discount, 2) }} ج.م</td>
											<td class="font-weight-bold text-dark">{{ number_format($sale->total, 2) }} ج.م</td>
											<td><span class="badge badge-success">{{ $sale->status }}</span></td>
										</tr>
									@empty
										<tr><td colspan="7" class="py-3 text-muted">لا توجد مبيعات مسجلة في هذه الوردية.</td></tr>
									@endforelse
								</tbody>
							</table>
						</div>
					</div>

					<!-- Expenses Tab -->
					<div class="tab-pane" id="tabShowExpenses">
						<div class="table-responsive">
							<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
								<thead>
									<tr>
										<th>#</th>
										<th>البند</th>
										<th>المبلغ</th>
										<th>طريقة الدفع</th>
										<th>أثرها على الدرج</th>
										<th>الحالة</th>
										<th>المسؤول</th>
										<th>البيان</th>
									</tr>
								</thead>
								<tbody>
									@forelse($shift->expenses as $expense)
										<tr class="{{ $expense->is_cancelled ? 'table-danger' : '' }}">
											<td>{{ $expense->id }}</td>
											<td class="font-weight-bold">{{ $expense->category }}</td>
											<td class="font-weight-bold {{ $expense->is_cancelled ? 'text-muted' : 'text-danger' }}">
												{{ number_format($expense->amount, 2) }} ج.م
											</td>
											<td>
												<span class="badge {{ $expense->payment_method_badge_class }}">
													{{ $expense->payment_method_label }}
												</span>
											</td>
											<td>
												@if($expense->is_cancelled)
													<span class="text-muted">مستبعد (ملغي)</span>
												@elseif($expense->is_cash)
													<span class="badge badge-danger">خصم من الدرج</span>
												@else
													<span class="badge badge-light border">محفظة إلكترونية</span>
												@endif
											</td>
											<td>
												<span class="badge {{ $expense->status_badge_class }}">
													{{ $expense->status_label }}
												</span>
											</td>
											<td>{{ $expense->user->name ?? '-' }}</td>
											<td class="tx-12">{{ $expense->description ?: '-' }}</td>
										</tr>
									@empty
										<tr><td colspan="8" class="py-3 text-muted">لا توجد مصروفات مسجلة في هذه الوردية.</td></tr>
									@endforelse
								</tbody>
							</table>
						</div>
					</div>

					<!-- Returns Tab -->
					<div class="tab-pane" id="tabShowReturns">
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
										<tr><td colspan="5" class="py-3 text-muted">لا توجد مرتجعات مسجلة في هذه الوردية.</td></tr>
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
@endsection

