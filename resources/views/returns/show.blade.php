@extends('layouts.master')

@section('title', 'إشعار مرتجع مبيعات #' . $return->return_number . ' - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary"><i class="fas fa-undo ml-2"></i>إشعار مرتجع مبيعات #{{ $return->return_number }}</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل إشعار المرتجع والمخزون</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content" style="gap: 8px;">
		<a href="{{ route('returns.print', $return) }}" target="_blank" class="btn btn-primary font-weight-bold shadow-sm">
			<i class="fas fa-print ml-1"></i> طباعة الإشعار
		</a>
		@if($return->isApproved())
			<button type="button" class="btn btn-outline-danger font-weight-bold" data-toggle="modal" data-target="#cancelReturnModal">
				<i class="fas fa-ban ml-1"></i> إلغاء المرتجع وإعادة خصم المخزون
			</button>
		@endif
		<a href="{{ route('returns.index') }}" class="btn btn-outline-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لسجل المرتجعات
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

@if(session('error'))
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> تنبيه!</strong> {{ session('error') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if($return->isCancelled())
	<div class="alert alert-danger shadow-sm mb-3">
		<div class="d-flex align-items-center">
			<i class="fas fa-ban tx-28 ml-3 text-danger"></i>
			<div>
				<h5 class="alert-heading font-weight-bold mb-1">تم إلغاء هذا الإشعار</h5>
				<p class="mb-1 tx-13">تم إلغاء هذا المرتجع وإعادة خصم الكميات من المخزون واستبعاد مبلغه من تسوية الخزينة.</p>
				@if($return->cancellation_reason)
					<div class="tx-12 mt-1"><strong>سبب الإلغاء:</strong> {{ $return->cancellation_reason }}</div>
				@endif
				<div class="tx-11 text-muted mt-1">
					تم الإلغاء بواسطة: {{ $return->cancelledByUser?->name ?? 'مستخدم غير معروف' }} |
					في: {{ $return->cancelled_at ? $return->cancelled_at->format('Y-m-d g:i A') : '-' }}
				</div>
			</div>
		</div>
	</div>
@endif

<div class="row row-sm">
	<!-- Left Side: Returned Items (8 Cols) -->
	<div class="col-xl-8 col-lg-7 col-md-12">
		<!-- Returned Items Table -->
		<div class="card mb-3">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-boxes ml-1 text-primary"></i> الأصناف المسترجعة للمخزون
				</h5>
				<span class="badge badge-light border tx-12">
					عدد الأصناف: {{ $return->items->count() }} (إجمالي القطع: {{ rtrim(rtrim(number_format($return->items->sum('quantity'), 3), '0'), '.') }})
				</span>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-bordered text-center align-middle mb-0">
						<thead class="thead-light">
							<tr>
								<th>#</th>
								<th class="text-right">المنتج</th>
								<th>سعر الوحدة بالفاتورة</th>
								<th>الكمية المسترجعة</th>
								<th>إجمالي المبلغ المسترد</th>
							</tr>
						</thead>
						<tbody>
							@foreach($return->items as $item)
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
										<span class="badge badge-pill badge-warning text-dark font-weight-bold tx-13 px-3 py-1">
											+{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}
										</span>
									</td>
									<td class="font-weight-bold text-danger tx-14">
										-{{ number_format($item->total, 2) }} جنيه
									</td>
								</tr>
							@endforeach
						</tbody>
						<tfoot>
							<tr class="bg-light font-weight-bold">
								<td colspan="3" class="text-left font-weight-bold tx-14">الإجمالي الكلي للمرتجع:</td>
								<td class="text-center font-weight-bold tx-14">
									{{ rtrim(rtrim(number_format($return->items->sum('quantity'), 3), '0'), '.') }} قطعة
								</td>
								<td class="text-center font-weight-bold tx-16 text-danger">
									-{{ number_format($return->total_amount, 2) }} جنيه مصري
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<!-- Associated Original Sale Details -->
		<div class="card mb-3">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-file-invoice ml-1 text-primary"></i> بيانات الفاتورة الأصلية المرتبطة
				</h6>
				@if($return->sale)
					<a href="{{ route('sales.show', $return->sale) }}" class="btn btn-sm btn-outline-primary font-weight-bold">
						<i class="fas fa-eye ml-1"></i> عرض الفاتورة بالكامل
					</a>
				@endif
			</div>
			<div class="card-body">
				@if($return->sale)
					<div class="row row-sm">
						<div class="col-sm-6 mb-2">
							<span class="text-muted tx-12">رقم الفاتورة:</span>
							<strong class="text-dark d-block">#{{ $return->sale->invoice_number }}</strong>
						</div>
						<div class="col-sm-6 mb-2">
							<span class="text-muted tx-12">تاريخ إصدار الفاتورة:</span>
							<strong class="text-dark d-block">{{ $return->sale->created_at->format('Y-m-d g:i A') }}</strong>
						</div>
						<div class="col-sm-6 mb-2">
							<span class="text-muted tx-12">طريقة الدفع الأصلية:</span>
							<div class="mt-1">
								<span class="badge {{ $return->sale->payment_method_badge_class }} px-2 py-1 font-weight-bold">
									{{ $return->sale->payment_method_label }}
								</span>
							</div>
						</div>
						<div class="col-sm-6 mb-2">
							<span class="text-muted tx-12">إجمالي الفاتورة الأصلية:</span>
							<strong class="text-primary tx-15 d-block">{{ number_format($return->sale->total, 2) }} جنيه مصري</strong>
						</div>
					</div>
				@else
					<span class="text-muted">بيانات الفاتورة الأصلية غير متوفرة.</span>
				@endif
			</div>
		</div>

		<!-- Activity & Audit Log -->
		<div class="card mb-3">
			<div class="card-header pb-0">
				<h6 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-history ml-1 text-primary"></i> سجل التدقيق والأنشطة المرتبطة
				</h6>
			</div>
			<div class="card-body">
				@if($return->activityLogs->isEmpty())
					<p class="text-muted tx-12 mb-0">لا توجد سجلات تدقيق مسجلة لهذا الإشعار.</p>
				@else
					<div class="table-responsive">
						<table class="table table-sm table-striped text-center align-middle mb-0 tx-12">
							<thead class="thead-light">
								<tr>
									<th>العملية</th>
									<th>المسؤول</th>
									<th>التاريخ والوقت</th>
									<th>التفاصيل</th>
								</tr>
							</thead>
							<tbody>
								@foreach($return->activityLogs as $log)
									<tr>
										<td>
											<span class="badge badge-light border font-weight-bold">
												{{ $log->action }}
											</span>
										</td>
										<td class="font-weight-semibold">{{ $log->user?->name ?? 'النظام' }}</td>
										<td>{{ $log->created_at->format('Y-m-d g:i A') }}</td>
										<td class="text-right">{{ $log->description ?? '-' }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				@endif
			</div>
		</div>
	</div>

	<!-- Right Side: Return Metadata (4 Cols) -->
	<div class="col-xl-4 col-lg-5 col-md-12">
		<div class="card mb-3">
			<div class="card-header pb-0">
				<h5 class="card-title mb-0 font-weight-bold text-dark">
					<i class="fas fa-info-circle ml-1 text-primary"></i> بطاقة إشعار المرتجع
				</h5>
			</div>
			<div class="card-body">
				<ul class="list-group list-group-flush pr-0">
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">رقم الإشعار:</span>
						<strong class="text-primary font-weight-bold tx-15">{{ $return->return_number }}</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">حالة المرتجع:</span>
						<span class="badge {{ $return->status_badge_class }} px-2 py-1 font-weight-bold tx-12">
							{{ $return->status_label }}
						</span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">طريقة استرداد المبلغ:</span>
						<span class="badge {{ $return->refund_method_badge_class }} px-2 py-1 font-weight-bold tx-12">
							{{ $return->refund_method_label }}
						</span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">المسؤول (الكاشير):</span>
						<strong class="text-dark">{{ $return->user?->name ?? '-' }}</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">تاريخ ووقت الإرجاع:</span>
						<strong class="text-dark tx-12">{{ $return->created_at->format('Y-m-d g:i A') }}</strong>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<span class="text-muted">وردية العمل:</span>
						@if($return->shift)
							<a href="{{ route('shifts.show', $return->shift) }}" class="font-weight-bold">
								وردية رقم #{{ $return->shift_id }}
							</a>
						@else
							<span class="text-muted">-</span>
						@endif
					</li>
					@if($return->reason)
						<li class="list-group-item px-0">
							<span class="text-muted d-block tx-12">سبب المرتجع:</span>
							<p class="mb-0 text-dark font-weight-semibold mt-1">{{ $return->reason }}</p>
						</li>
					@endif
					<li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-light p-2 mt-2 rounded">
						<span class="font-weight-bold text-dark tx-14">المبلغ المسترد للعميل:</span>
						<strong class="tx-18 text-danger font-weight-bold">{{ number_format($return->total_amount, 2) }} جنيه مصري</strong>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<!-- Cancel Return Confirmation Modal -->
@if($return->isApproved())
<div class="modal fade" id="cancelReturnModal" tabindex="-1" role="dialog" aria-labelledby="cancelReturnModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<form method="POST" action="{{ route('returns.cancel', $return) }}">
				@csrf
				@method('PATCH')
				<div class="modal-header bg-danger text-white">
					<h5 class="modal-title font-weight-bold text-white" id="cancelReturnModalLabel">
						<i class="fas fa-exclamation-triangle ml-1"></i> تأكيد إلغاء إشعار المرتجع
					</h5>
					<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="alert alert-warning mb-3">
						<p class="mb-0 tx-13">
							<strong>تحذير هام:</strong> إلغاء هذا الإشعار سيؤدي إلى <strong>إعادة خصم الأصناف المسترجعة من المخزون</strong> وإعادة حسابات تسوية الخزينة للوضع السابق بدقة تامة.
						</p>
					</div>

					<div class="form-group">
						<label class="form-label font-weight-bold">سبب الإلغاء: <span class="text-danger">*</span></label>
						<textarea name="reason" class="form-control" rows="3" placeholder="اكتب سبب إلغاء هذا المرتجع لتوثيق السجل المالي والمخزني..." required></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger font-weight-bold">
						<i class="fas fa-ban ml-1"></i> نعم، تأكيد الإلغاء وخصم المخزون
					</button>
					<button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">
						تراجع
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif

@endsection

