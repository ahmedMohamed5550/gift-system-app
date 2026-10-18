@extends('layouts.master')

@section('title', 'تفاصيل الفاتورة #' . $sale->invoice_number . ' - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary"><i class="fas fa-file-invoice ml-2"></i>فاتورة مبيعات #{{ $sale->invoice_number }}</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل وبيانات الفاتورة</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content" style="gap: 8px;">
		<a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-primary font-weight-bold shadow-sm">
			<i class="fas fa-print ml-1"></i> طباعة الإيصال
		</a>
		@if($sale->isCompleted())
			<button type="button" class="btn btn-outline-danger font-weight-bold" data-toggle="modal" data-target="#cancelSaleModal">
				<i class="fas fa-ban ml-1"></i> إلغاء الفاتورة واسترجاع المخزون
			</button>
		@endif
		<a href="{{ route('sales.index') }}" class="btn btn-outline-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لقائمة الفواتير
		</a>
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

@if($sale->isCancelled())
	<div class="alert alert-danger shadow-sm mb-3">
		<div class="d-flex align-items-center">
			<i class="fas fa-ban tx-28 ml-3 text-danger"></i>
			<div>
				<h5 class="alert-heading font-weight-bold mb-1">هذه الفاتورة ملغاة</h5>
				<p class="mb-0 tx-13">تم إلغاء هذه الفاتورة وإعادة كافة الكميات المباعة إلى رصيد المخزون في النظام.</p>
			</div>
		</div>
	</div>
@endif

<div class="row row-sm">
	<!-- Left Side: Items Table & Totals (8 Cols) -->
	<div class="col-xl-8 col-lg-7 col-md-12">
		<div class="card mb-3">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-boxes ml-1 text-primary"></i> بنود وأصناف الفاتورة
				</h5>
				<span class="badge badge-light border tx-12">
					عدد الأصناف: {{ $sale->items->count() }} (إجمالي القطع: {{ rtrim(rtrim(number_format($sale->items->sum('quantity'), 3), '0'), '.') }})
				</span>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-bordered text-center align-middle mb-0">
						<thead class="thead-light">
							<tr>
								<th>#</th>
								<th class="text-right">المنتج</th>
								<th>سعر البيع للوحدة</th>
								<th>الكمية المباعة</th>
								<th>الإجمالي</th>
								<th>سعر التكلفة (وقت البيع)</th>
								<th>هامش الربح المحقق</th>
							</tr>
						</thead>
						<tbody>
							@foreach($sale->items as $item)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td class="text-right font-weight-bold">
										@if($item->product)
											<a href="{{ route('products.show', $item->product) }}" class="text-dark">
												{{ $item->product->name }}
											</a>
											<span class="d-block tx-11 text-muted">
												{{ $item->product->subcategory->category->name ?? '' }} / {{ $item->product->subcategory->name ?? '' }}
											</span>
										@else
											<span class="text-muted">منتج محذوف (#{{ $item->product_id }})</span>
										@endif
									</td>
									<td class="font-weight-semibold">
										{{ number_format($item->unit_price, 2) }} جنيه
									</td>
									<td>
										<span class="badge badge-pill badge-primary font-weight-bold tx-13 px-3 py-1">
											{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}
										</span>
									</td>
									<td class="font-weight-bold text-dark tx-14">
										{{ number_format($item->total, 2) }} جنيه مصري
									</td>
									<td class="tx-12 text-muted">
										{{ number_format($item->unit_cost, 2) }} جنيه
									</td>
									<td>
										<span class="badge {{ $item->profit >= 0 ? 'badge-success-light text-success' : 'badge-danger-light text-danger' }} font-weight-bold tx-12 px-2 py-1">
											{{ $item->profit >= 0 ? '+' : '' }}{{ number_format($item->profit, 2) }} جنيه
										</span>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				<!-- Financial Breakdown Box -->
				<div class="row row-sm justify-content-end mt-4">
					<div class="col-md-6 col-sm-12">
						<div class="table-responsive">
							<table class="table table-bordered mb-0">
								<tbody>
									<tr>
										<th class="bg-light tx-13 wd-50p">المجموع الفرعي</th>
										<td class="text-left font-weight-bold tx-14">{{ number_format($sale->subtotal, 2) }} جنيه مصري</td>
									</tr>
									<tr>
										<th class="bg-light tx-13">الخصم الممنوح</th>
										<td class="text-left font-weight-bold text-danger tx-14">
											-{{ number_format($sale->discount, 2) }} جنيه مصري
										</td>
									</tr>
									<tr class="bg-primary-transparent">
										<th class="tx-15 font-weight-bold text-primary">الإجمالي الصافي النهائي</th>
										<td class="text-left font-weight-bold text-primary tx-18">
											{{ number_format($sale->total, 2) }} جنيه مصري
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Right Side: Invoice Meta & Audit Trail (4 Cols) -->
	<div class="col-xl-4 col-lg-5 col-md-12">
		<!-- Invoice Information Card -->
		<div class="card mb-3">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-info-circle ml-1 text-primary"></i> بيانات الفاتورة
				</h5>
				<span class="badge {{ $sale->status_badge_class }} tx-13 px-3 py-1">
					{{ $sale->status_label }}
				</span>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered mb-0">
						<tbody>
							<tr>
								<th class="bg-light wd-40p">رقم الفاتورة</th>
								<td class="font-weight-bold text-primary tx-15">#{{ $sale->invoice_number }}</td>
							</tr>
							<tr>
								<th class="bg-light">الكاشير المسجّل</th>
								<td>
									<i class="fas fa-user-circle ml-1 text-secondary"></i>
									<strong>{{ $sale->user->name ?? '-' }}</strong>
								</td>
							</tr>
							<tr>
								<th class="bg-light">الوردية المرتبطة</th>
								<td>
									@if($sale->shift)
										<a href="{{ route('shifts.show', $sale->shift) }}" class="btn btn-xs btn-outline-primary font-weight-bold">
											<i class="fas fa-cash-register ml-1"></i> وردية #{{ $sale->shift_id }}
										</a>
									@else
										<span class="text-muted">-</span>
									@endif
								</td>
							</tr>
							<tr>
								<th class="bg-light">تاريخ ووقت البيع</th>
								<td class="tx-12 font-weight-semibold">
									<i class="far fa-clock ml-1 text-muted"></i>
									{{ $sale->created_at ? $sale->created_at->translatedFormat('Y-m-d h:i A') : '-' }}
								</td>
							</tr>
							<tr>
								<th class="bg-light">طريقة الدفع</th>
								<td>
									<span class="badge {{ $sale->payment_method_badge_class }} tx-13 px-2 py-1">
										{{ $sale->payment_method_label }}
									</span>
									<span class="d-block tx-11 text-muted mt-1">
										@if($sale->payment_method === 'cash')
											(نقدية مضافة لدرج الكاشير بالوردية)
										@else
											(دفع إلكتروني - لا يؤثر على نقدية الدرج)
										@endif
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Activity & Audit Logs Card -->
		<div class="card">
			<div class="card-header pb-0">
				<h5 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-history ml-1 text-primary"></i> سجل العمليات والتوثيق
				</h5>
			</div>
			<div class="card-body">
				<ul class="list-unstyled mb-0 timeline-widget">
					@forelse($sale->activityLogs()->latest('id')->get() as $log)
						<li class="border-bottom pb-2 mb-2">
							<div class="d-flex justify-content-between align-items-center mb-1">
								<span class="badge badge-light font-weight-bold tx-11">
									{{ $log->action === 'sale_created' ? 'إنشاء الفاتورة' : ($log->action === 'sale_cancelled' ? 'إلغاء الفاتورة' : $log->action) }}
								</span>
								<small class="text-muted tx-11">
									{{ $log->created_at ? $log->created_at->translatedFormat('Y-m-d h:i A') : '-' }}
								</small>
							</div>
							<p class="tx-12 text-dark mb-1">{{ $log->description }}</p>
							<small class="text-muted">بواسطة: {{ $log->user->name ?? 'النظام' }}</small>
						</li>
					@empty
						<li class="text-muted text-center py-3 tx-12">
							لا توجد سجلات نشاط إضافية لهذه الفاتورة.
						</li>
					@endforelse
				</ul>
			</div>
		</div>
	</div>
</div>

@if($sale->isCompleted())
	<!-- Modal: Cancel Sale -->
	<div class="modal fade" id="cancelSaleModal" tabindex="-1" role="dialog" aria-labelledby="cancelSaleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content text-right">
				<form action="{{ route('sales.cancel', $sale) }}" method="POST">
					@csrf
					@method('PATCH')
					<div class="modal-header bg-danger-transparent">
						<h5 class="modal-title text-danger font-weight-bold" id="cancelSaleModalLabel">
							<i class="fas fa-ban ml-1"></i> إلغاء الفاتورة رقم #{{ $sale->invoice_number }}
						</h5>
						<button type="button" class="close ml-0 mr-auto" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="alert alert-warning">
							<strong><i class="fas fa-exclamation-triangle ml-1"></i> تحذير مالي ومخزني:</strong>
							سيتم إلغاء الفاتورة وإعادة كميات كافة الأصناف ({{ $sale->items->count() }} صنف) إلى رصيد المخزون فوراً، وتحديث الفاتورة لتصبح ملغاة، ولن يمكن التراجع عن هذه الخطوة.
						</div>
						<p class="mb-2"><strong>إجمالي الفاتورة:</strong> {{ number_format($sale->total, 2) }} جنيه مصري</p>
						<div class="form-group">
							<label class="font-weight-semibold">سبب الإلغاء (اختياري / للتوثيق):</label>
							<textarea name="reason" rows="3" class="form-control" placeholder="يرجى كتابة سبب إلغاء الفاتورة بدقة..."></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">تراجع</button>
						<button type="submit" class="btn btn-danger font-weight-bold">
							<i class="fas fa-check ml-1"></i> تأكيد إلغاء الفاتورة وإرجاع المخزون
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endif

@endsection

