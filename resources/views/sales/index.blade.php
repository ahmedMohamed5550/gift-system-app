@extends('layouts.master')

@section('title', 'سجل فواتير المبيعات - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary"><i class="fas fa-file-invoice ml-2"></i>فواتير المبيعات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ سجل ومتابعة عمليات البيع</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="pr-1 mb-3 mb-xl-0">
			<a href="{{ route('pos.index') }}" class="btn btn-success font-weight-bold shadow-sm">
				<i class="fas fa-cash-register ml-1"></i> فتح نقطة البيع
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')

@if(session('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-check-circle ml-1"></i> تم بنجاح!</strong> {{ session('success') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(session('error'))
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> تنبيه!</strong> {{ session('error') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<!-- Sales KPI Cards -->
<div class="row row-sm mb-2">
	<!-- Total Completed Sales -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">إجمالي المبيعات المكتملة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['total_sales_amount'], 2) }} جنيه مصري</h3>
							<p class="mb-0 tx-12 text-white op-7">{{ $stats['completed_count'] }} فاتورة بيع ناجحة</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-receipt text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Cash Sales -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-success-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">المبيعات النقدية (كاش)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['cash_sales_amount'], 2) }} جنيه مصري</h3>
							<p class="mb-0 tx-12 text-white op-7">نقدية مضافة إلى درج الخزينة</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-money-bill-wave text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Electronic Sales -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">المدفوعات الإلكترونية</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($stats['electronic_sales_amount'], 2) }} جنيه مصري</h3>
							<p class="mb-0 tx-12 text-white op-7">فودافون كاش + انستا باي</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-mobile-alt text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Cancelled Sales -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">الفواتير الملغاة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ $stats['cancelled_count'] }} فاتورة</h3>
							<p class="mb-0 tx-12 text-white op-7">بقيمة {{ number_format($stats['cancelled_amount'], 2) }} جنيه (مسترجعة)</p>
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
		<form method="GET" action="{{ route('sales.index') }}">
			<div class="row row-sm">
				<!-- Search -->
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">بحث برقم الفاتورة أو الكاشير:</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-search"></i></span>
						</div>
						<input type="text" name="search" class="form-control" placeholder="رقم الفاتورة أو اسم الكاشير..." value="{{ request('search') }}">
					</div>
				</div>

				<!-- Payment Method -->
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">طريقة الدفع:</label>
					<select name="payment_method" class="form-control">
						<option value="">-- كل الطرق --</option>
						<option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>نقداً (كاش)</option>
						<option value="vodafone_cash" {{ request('payment_method') === 'vodafone_cash' ? 'selected' : '' }}>فودافون كاش</option>
						<option value="instapay" {{ request('payment_method') === 'instapay' ? 'selected' : '' }}>انستا باي</option>
					</select>
				</div>

				<!-- Status -->
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">حالة الفاتورة:</label>
					<select name="status" class="form-control">
						<option value="">-- كل الحالات --</option>
						<option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>مكتملة</option>
						<option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
					</select>
				</div>

				<!-- Cashier -->
				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">الكاشير:</label>
					<select name="user_id" class="form-control">
						<option value="">-- كل المستخدمين --</option>
						@foreach($users as $u)
							<option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
								{{ $u->name }}
							</option>
						@endforeach
					</select>
				</div>

				<!-- Date Range -->
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
					<a href="{{ route('sales.index') }}" class="btn btn-outline-secondary font-weight-bold">
						<i class="fas fa-times ml-1"></i> إلغاء التصفية
					</a>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Sales Table Card -->
<div class="card" id="live-search-container">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h4 class="card-title mg-b-0"><i class="fas fa-list-alt ml-1 text-primary"></i> قائمة الفواتير</h4>
		<span class="tx-12 text-muted">إجمالي الفواتير المسجلة: {{ $sales->total() }}</span>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped table-hover text-md-nowrap mb-0 text-center">
				<thead>
					<tr>
						<th>#</th>
						<th>رقم الفاتورة</th>
						<th>التاريخ والوقت</th>
						<th>الكاشير</th>
						<th>الوردية</th>
						<th>طريقة الدفع</th>
						<th>عدد الأصناف</th>
						<th>الخصم</th>
						<th>الإجمالي النهائي</th>
						<th>الحالة</th>
						<th>الإجراءات</th>
					</tr>
				</thead>
				<tbody>
					@forelse($sales as $sale)
						<tr class="{{ $sale->isCancelled() ? 'table-danger' : '' }}">
							<td>{{ $sale->id }}</td>
							<td class="font-weight-bold">
								<a href="{{ route('sales.show', $sale) }}" class="text-primary font-weight-bold">
									{{ $sale->invoice_number }}
								</a>
							</td>
							<td class="tx-12 text-muted">
								<i class="far fa-clock ml-1"></i>
								{{ $sale->created_at ? $sale->created_at->translatedFormat('Y-m-d h:i A') : '-' }}
							</td>
							<td>
								<i class="fas fa-user-circle ml-1 text-secondary"></i>
								{{ $sale->user->name ?? '-' }}
							</td>
							<td>
								@if($sale->shift)
									<a href="{{ route('shifts.show', $sale->shift) }}" class="badge badge-light border" title="عرض تفاصيل الوردية">
										#{{ $sale->shift_id }}
									</a>
								@else
									<span class="text-muted">-</span>
								@endif
							</td>
							<td>
								<span class="badge {{ $sale->payment_method_badge_class }} px-2 py-1">
									{{ $sale->payment_method_label }}
								</span>
							</td>
							<td>
								<span class="badge badge-pill badge-light font-weight-bold tx-12">
									{{ $sale->items->count() }} ({{ rtrim(rtrim(number_format($sale->items->sum('quantity'), 3), '0'), '.') }})
								</span>
							</td>
							<td>
								@if($sale->discount > 0)
									<span class="text-danger tx-12 font-weight-bold">-{{ number_format($sale->discount, 2) }}</span>
								@else
									<span class="text-muted tx-12">0.00</span>
								@endif
							</td>
							<td class="font-weight-bold {{ $sale->isCancelled() ? 'text-muted' : 'text-success' }} tx-14">
								{{ number_format($sale->total, 2) }} جنيه مصري
							</td>
							<td>
								<span class="badge {{ $sale->status_badge_class }} px-2 py-1">
									{{ $sale->status_label }}
								</span>
							</td>
							<td>
								<div class="btn-group" role="group">
									<a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-info" title="عرض التفاصيل">
										<i class="fas fa-eye"></i>
									</a>
									<a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-sm btn-primary" title="طباعة الإيصال">
										<i class="fas fa-print"></i>
									</a>
									@if($sale->isCompleted())
										<button type="button" class="btn btn-sm btn-danger"
												data-toggle="modal" data-target="#cancelSaleModal{{ $sale->id }}"
												title="إلغاء الفاتورة واسترجاع المخزون">
											<i class="fas fa-ban"></i>
										</button>
									@endif
								</div>

								@if($sale->isCompleted())
									<!-- Modal: Cancel Sale -->
									<div class="modal fade" id="cancelSaleModal{{ $sale->id }}" tabindex="-1" role="dialog" aria-hidden="true">
										<div class="modal-dialog" role="document">
											<div class="modal-content text-right">
												<form action="{{ route('sales.cancel', $sale) }}" method="POST">
													@csrf
													@method('PATCH')
													<div class="modal-header bg-danger-transparent">
														<h5 class="modal-title text-danger font-weight-bold">
															<i class="fas fa-ban ml-1"></i> إلغاء الفاتورة رقم #{{ $sale->invoice_number }}
														</h5>
														<button type="button" class="close ml-0 mr-auto" data-dismiss="modal" aria-label="Close">
															<span aria-hidden="true">&times;</span>
														</button>
													</div>
													<div class="modal-body">
														<div class="alert alert-warning">
															<strong><i class="fas fa-exclamation-triangle ml-1"></i> تنبيه هام:</strong>
															سيؤدي إلغاء الفاتورة إلى استرجاع كافة الأصناف المباعة إلى رصيد المخزون فوراً وتوثيق حركة إلغاء مبيعات، وتحديث حالة الفاتورة إلى "ملغاة".
														</div>
														<p class="mb-2"><strong>إجمالي الفاتورة:</strong> {{ number_format($sale->total, 2) }} جنيه مصري ({{ $sale->payment_method_label }})</p>
														<div class="form-group">
															<label class="font-weight-semibold">سبب الإلغاء (اختياري / للتوثيق):</label>
															<textarea name="reason" rows="2" class="form-control" placeholder="اكتب سبب إلغاء الفاتورة..."></textarea>
														</div>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-secondary" data-dismiss="modal">تراجع</button>
														<button type="submit" class="btn btn-danger font-weight-bold">
															<i class="fas fa-check ml-1"></i> تأكيد إلغاء الفاتورة واسترجاع المخزون
														</button>
													</div>
												</form>
											</div>
										</div>
									</div>
								@endif
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="11" class="text-center py-5 text-muted">
								<i class="fas fa-file-invoice tx-40 mb-2 op-4"></i>
								<p class="tx-14 font-weight-semibold mb-0">لا توجد فواتير مبيعات مطابقة لمعايير البحث.</p>
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="mt-3 d-flex justify-content-center">
			{{ $sales->links() }}
		</div>
	</div>
</div>

@endsection

