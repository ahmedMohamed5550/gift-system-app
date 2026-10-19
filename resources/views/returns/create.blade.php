@extends('layouts.master')

@section('title', 'إرجاع فاتورة مبيعات - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary"><i class="fas fa-undo ml-2"></i>إرجاع فاتورة مبيعات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ البحث عن الفاتورة وتحديد الأصناف المرتجعة</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('returns.index') }}" class="btn btn-outline-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لسجل المرتجعات
		</a>
	</div>
</div>
@endsection

@section('content')

@if(session('warning'))
	<div class="alert alert-warning alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> تنبيه!</strong> {{ session('warning') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if($errors->any())
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-times-circle ml-1"></i> حدثت أخطاء أثناء الحفظ:</strong>
		<ul class="mb-0 mt-1 pr-3">
			@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(!$activeShift || !$activeShift->isOpen)
	<div class="alert alert-danger shadow-sm mb-4">
		<div class="d-flex align-items-center">
			<i class="fas fa-lock tx-30 ml-3 text-danger"></i>
			<div>
				<h5 class="alert-heading font-weight-bold mb-1">لا توجد وردية عمل مفتوحة حالياً للكاشير!</h5>
				<p class="mb-2 tx-13">يتطلب تسجيل عمليات الإرجاع واسترداد المبالغ المالية وجود وردية مفتوحة لربط العمليات بحساب الخزينة.</p>
				<a href="{{ route('shifts.current') }}" class="btn btn-sm btn-danger font-weight-bold">
					<i class="fas fa-key ml-1"></i> الانتقال لفتح وردية عمل جديدة
				</a>
			</div>
		</div>
	</div>
@endif

<!-- Step 1: Search Invoice Card -->
<div class="card mb-4 border shadow-sm">
	<div class="card-header bg-light pb-2">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-search ml-1 text-primary"></i> الخطوة الأولى: البحث عن الفاتورة الأصلية
		</h5>
	</div>
	<div class="card-body">
		<form method="GET" action="{{ route('returns.create') }}">
			<div class="row">
				<div class="col-lg-8 col-md-10 col-12">
					<label class="form-label font-weight-bold text-dark mb-2">
						رقم فاتورة المبيعات المراد إرجاعها: <span class="text-danger">*</span>
					</label>
					<div class="input-group input-group-lg shadow-sm">
						<div class="input-group-prepend">
							<span class="input-group-text bg-white"><i class="fas fa-receipt text-primary tx-18"></i></span>
						</div>
						<input type="text" name="invoice" class="form-control font-weight-bold"
							   placeholder="أدخل رقم الفاتورة (مثال: INV-GF-123)..."
							   value="{{ $searchQuery }}" required autofocus>
						<div class="input-group-append">
							<button type="submit" class="btn btn-primary font-weight-bold px-4">
								<i class="fas fa-search ml-1"></i> بحث عن الفاتورة
							</button>
							@if(!empty($searchQuery))
								<a href="{{ route('returns.create') }}" class="btn btn-outline-secondary font-weight-bold px-3 d-flex align-items-center" title="إلغاء البحث">
									<i class="fas fa-times ml-1"></i> إلغاء
								</a>
							@endif
						</div>
					</div>
					<small class="text-muted tx-12 mt-2 d-block">
						<i class="fas fa-info-circle ml-1 text-primary"></i> يمكنك إدخال رقم الفاتورة كاملاً أو البحث بالباركود لعرض الأصناف المتاحة للإرجاع.
					</small>
				</div>
			</div>
		</form>
	</div>
</div>

@if($sale)
	<!-- Step 2: Invoice Details & Returnable Items -->
	<div class="card mb-4 border shadow-sm">
		<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
			<h5 class="card-title mb-0 font-weight-bold text-white">
				<i class="fas fa-file-invoice ml-1"></i> بيانات الفاتورة الأصلية #{{ $sale->invoice_number }}
			</h5>
			<div>
				<a href="{{ route('sales.show', $sale) }}" target="_blank" class="btn btn-sm btn-light font-weight-bold">
					<i class="fas fa-external-link-alt ml-1"></i> فتح الفاتورة في نافذة جديدة
				</a>
			</div>
		</div>
		<div class="card-body bg-light-50">
			<div class="row row-sm text-center">
				<div class="col-md-3 col-6 mb-2">
					<div class="p-2 border rounded bg-white">
						<span class="text-muted tx-12 d-block">تاريخ ووقت البيع:</span>
						<strong class="text-dark tx-14">{{ $sale->created_at->format('Y-m-d g:i A') }}</strong>
					</div>
				</div>
				<div class="col-md-3 col-6 mb-2">
					<div class="p-2 border rounded bg-white">
						<span class="text-muted tx-12 d-block">الكاشير البائع:</span>
						<strong class="text-dark tx-14">{{ $sale->user?->name ?? '-' }}</strong>
					</div>
				</div>
				<div class="col-md-3 col-6 mb-2">
					<div class="p-2 border rounded bg-white">
						<span class="text-muted tx-12 d-block">طريقة الدفع الأصلية:</span>
						<strong class="text-dark tx-14">
							<span class="badge {{ $sale->payment_method_badge_class }} px-2 py-1">
								{{ $sale->payment_method_label }}
							</span>
						</strong>
					</div>
				</div>
				<div class="col-md-3 col-6 mb-2">
					<div class="p-2 border rounded bg-white">
						<span class="text-muted tx-12 d-block">إجمالي الفاتورة الأصلية:</span>
						<strong class="text-primary tx-16">{{ number_format($sale->total, 2) }} جنيه مصري</strong>
					</div>
				</div>
			</div>
		</div>
	</div>

	@if($sale->total_returnable_items <= 0)
		<div class="alert alert-info shadow-sm text-center py-4">
			<i class="fas fa-check-circle tx-36 text-info mb-2 d-block"></i>
			<h5 class="font-weight-bold">تم إرجاع كافة أصناف هذه الفاتورة بالكامل مسبقاً</h5>
			<p class="text-muted mb-0">لا توجد أي كميات متبقية قابلة للإرجاع لهذه الفاتورة.</p>
		</div>
	@else
		<!-- Return Creation Form -->
		<form method="POST" action="{{ route('returns.store') }}" id="returnForm">
			@csrf
			<input type="hidden" name="sale_id" value="{{ $sale->id }}">

			<div class="row row-sm">
				<!-- Return Options Card -->
				<div class="col-12 mb-3">
					<div class="card border shadow-sm">
						<div class="card-header pb-2">
							<h6 class="card-title mb-0 font-weight-bold text-dark">
								<i class="fas fa-cogs ml-1 text-primary"></i> بيانات استرداد المبلغ
							</h6>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label font-weight-bold">
										طريقة رد المبلغ للعميل: <span class="text-danger">*</span>
									</label>
									<select name="refund_method" class="form-control select2" required>
										<option value="cash" {{ old('refund_method', $sale->payment_method) == 'cash' ? 'selected' : '' }}>
											نقداً (كاش) - يخصم من نقدية الدرج
										</option>
										<option value="vodafone_cash" {{ old('refund_method', $sale->payment_method) == 'vodafone_cash' ? 'selected' : '' }}>
											فودافون كاش - تحويل محفظة إلكترونية
										</option>
										<option value="instapay" {{ old('refund_method', $sale->payment_method) == 'instapay' ? 'selected' : '' }}>
											انستا باي (InstaPay) - تحويل بنكي فوري
										</option>
									</select>
									<small class="text-muted">ملاحظة: الاسترداد النقدي فقط هو الذي يقلل رصيد الدرج الفعلي.</small>
								</div>

								<div class="col-md-8 mb-3">
									<label class="form-label font-weight-bold">
										سبب الإرجاع:
									</label>
									<input type="text" name="reason" class="form-control"
										   placeholder="مثال: عيب صناعة، استبدال منتج، رغبة العميل..."
										   value="{{ old('reason') }}">
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Items Table Card -->
				<div class="col-12 mb-3">
					<div class="card border shadow-sm">
						<div class="card-header pb-2 d-flex justify-content-between align-items-center">
							<h6 class="card-title mb-0 font-weight-bold text-dark">
								<i class="fas fa-boxes ml-1 text-primary"></i> تحديد الأصناف والكميات المراد إرجاعها
							</h6>
							<button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" id="btnReturnAll">
								<i class="fas fa-check-double ml-1"></i> إرجاع كافة الكميات المتبقية
							</button>
						</div>
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-bordered table-striped text-center align-middle mb-0" id="itemsTable">
									<thead class="thead-light">
										<tr>
											<th>#</th>
											<th class="text-right">المنتج</th>
											<th>سعر البيع الأصلي</th>
											<th>الكمية المباعة</th>
											<th>المرتجع سابقاً</th>
											<th>المتبقي القابل للإرجاع</th>
											<th style="width: 170px;">الكمية المرتجعة حالياً</th>
											<th>إجمالي الاسترداد</th>
										</tr>
									</thead>
									<tbody>
										@foreach($sale->items as $index => $item)
											@php
												$returnable = $item->returnable_quantity_calculated;
											@endphp
											<tr class="{{ $returnable <= 0 ? 'table-secondary opacity-6' : '' }}">
												<td>{{ $loop->iteration }}</td>
												<td class="text-right">
													<div class="font-weight-bold text-dark">
														{{ $item->product?->name ?? 'منتج غير معروف' }}
													</div>
													<span class="tx-11 text-muted">#{{ $item->product_id }}</span>
												</td>
												<td class="font-weight-semibold">
													{{ number_format($item->unit_price, 2) }} جنيه
												</td>
												<td>
													<span class="badge badge-light border font-weight-bold">
														{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}
													</span>
												</td>
												<td>
													<span class="badge badge-light border text-danger font-weight-bold">
														{{ rtrim(rtrim(number_format($item->returned_quantity_calculated, 3), '0'), '.') }}
													</span>
												</td>
												<td>
													<span class="badge {{ $returnable > 0 ? 'badge-success' : 'badge-secondary' }} px-2 py-1 font-weight-bold">
														{{ rtrim(rtrim(number_format($returnable, 3), '0'), '.') }}
													</span>
												</td>
												<td>
													<input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}">

													@if($returnable > 0)
														<div class="input-group">
															<input type="number"
																   name="items[{{ $index }}][quantity]"
																   class="form-control text-center font-weight-bold return-qty-input"
																   min="0"
																   max="{{ $returnable }}"
																   step="any"
																   value="{{ old("items.{$index}.quantity", 0) }}"
																   data-price="{{ $item->unit_price }}"
																   data-max="{{ $returnable }}"
																   data-index="{{ $index }}">
															<div class="input-group-append">
																<button type="button" class="btn btn-outline-secondary btn-sm btn-set-max" title="الحد الأقصى" data-index="{{ $index }}">
																	الكل
																</button>
															</div>
														</div>
													@else
														<span class="text-muted tx-12">مسترجع بالكامل</span>
														<input type="hidden" name="items[{{ $index }}][quantity]" value="0">
													@endif
												</td>
												<td class="font-weight-bold text-danger">
													<span class="item-refund-total" id="refund_total_{{ $index }}">0.00</span> جنيه
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

				<!-- Summary & Submit Bar -->
				<div class="col-12">
					<div class="card bg-light border shadow-sm">
						<div class="card-body">
							<div class="row align-items-center">
								<div class="col-md-6 mb-3 mb-md-0">
									<div class="d-flex align-items-center">
										<div class="p-3 bg-white border rounded ml-3">
											<span class="tx-12 text-muted d-block">إجمالي الأصناف المرتجعة:</span>
											<strong class="tx-18 text-dark" id="totalReturnQty">0</strong> قطعة
										</div>
										<div class="p-3 bg-white border rounded">
											<span class="tx-12 text-muted d-block">إجمالي المبلغ المسترد للعميل:</span>
											<strong class="tx-20 text-danger" id="totalRefundAmount">0.00</strong> جنيه مصري
										</div>
									</div>
								</div>

								<div class="col-md-6 text-left">
									<button type="submit" class="btn btn-warning btn-lg font-weight-bold px-4 shadow-sm" id="btnSubmitReturn" disabled>
										<i class="fas fa-check-circle ml-1"></i> تأكيد وتسجيل المرتجع
									</button>
									<a href="{{ route('returns.index') }}" class="btn btn-outline-secondary btn-lg font-weight-bold mr-2">
										إلغاء
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	@endif
@endif

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInputs = document.querySelectorAll('.return-qty-input');
    const totalReturnQtyEl = document.getElementById('totalReturnQty');
    const totalRefundAmountEl = document.getElementById('totalRefundAmount');
    const btnSubmit = document.getElementById('btnSubmitReturn');
    const btnReturnAll = document.getElementById('btnReturnAll');

    function calculateTotals() {
        let grandTotalRefund = 0;
        let grandTotalQty = 0;

        qtyInputs.forEach(input => {
            const max = parseFloat(input.dataset.max) || 0;
            let val = parseFloat(input.value) || 0;

            if (val < 0) {
                val = 0;
                input.value = 0;
            }

            if (val > max) {
                val = max;
                input.value = max;
            }

            const price = parseFloat(input.dataset.price) || 0;
            const lineTotal = val * price;
            const index = input.dataset.index;
            const lineTotalEl = document.getElementById('refund_total_' + index);

            if (lineTotalEl) {
                lineTotalEl.textContent = lineTotal.toFixed(2);
            }

            grandTotalRefund += lineTotal;
            grandTotalQty += val;
        });

        if (totalReturnQtyEl) {
            totalReturnQtyEl.textContent = grandTotalQty.toFixed(grandTotalQty % 1 === 0 ? 0 : 3);
        }

        if (totalRefundAmountEl) {
            totalRefundAmountEl.textContent = grandTotalRefund.toFixed(2);
        }

        if (btnSubmit) {
            btnSubmit.disabled = grandTotalQty <= 0;
        }
    }

    qtyInputs.forEach(input => {
        input.addEventListener('input', calculateTotals);
        input.addEventListener('change', calculateTotals);
    });

    document.querySelectorAll('.btn-set-max').forEach(btn => {
        btn.addEventListener('click', function () {
            const index = this.dataset.index;
            const input = document.querySelector(`.return-qty-input[data-index="${index}"]`);
            if (input) {
                input.value = input.dataset.max;
                calculateTotals();
            }
        });
    });

    if (btnReturnAll) {
        btnReturnAll.addEventListener('click', function () {
            qtyInputs.forEach(input => {
                input.value = input.dataset.max;
            });
            calculateTotals();
        });
    }

    calculateTotals();
});
</script>
@endsection

