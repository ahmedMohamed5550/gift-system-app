@extends('layouts.master')

@section('title', 'تسوية جردية يدوية - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">إدارة المخزون والجرد</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تسوية جردية يدوية</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('inventory.index') }}" class="btn btn-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لمراقبة المخزون
		</a>
	</div>
</div>
@endsection

@section('content')
<!-- Error Bag Alerts -->
@if($errors->any())
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> تعذر تنفيذ التسوية:</strong>
		<ul class="mb-0 mt-2 pr-3">
			@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<div class="row row-sm justify-content-center">
	<div class="col-lg-8 col-xl-7">
		<div class="card">
			<div class="card-header pb-0">
				<h4 class="card-title mg-b-0"><i class="fas fa-sliders-h ml-1 text-primary"></i> بيانات التسوية الجردية</h4>
				<p class="tx-12 tx-gray-500 mb-2">قم باختيار المنتج وتحديد نوع التسوية لتحديث رصيد المخزون وتوثيق العملية في سجل الحركات.</p>
			</div>
			<div class="card-body">
				<form action="{{ route('inventory.adjustments.store') }}" method="POST" id="adjustmentForm">
					@csrf

					<!-- Product Selection -->
					<div class="form-group text-right">
						<label for="product_id" class="font-weight-semibold">اختر المنتج <span class="text-danger">*</span></label>
						<select id="product_id" name="product_id" class="form-control select2 @error('product_id') is-invalid @enderror" required>
							<option value="">-- ابحث أو اختر المنتج --</option>
							@foreach($products as $prod)
								<option value="{{ $prod->id }}"
										data-stock="{{ $prod->stock_quantity }}"
										data-min="{{ $prod->minimum_stock }}"
										data-price="{{ $prod->purchase_price }}"
										{{ old('product_id', $selectedProductId ?? '') == $prod->id ? 'selected' : '' }}>
									{{ $prod->name }} (الرصيد الحالي: {{ number_format($prod->stock_quantity, 3) }})
								</option>
							@endforeach
						</select>
						@error('product_id')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Live Product Details Banner -->
					<div id="productInfoBanner" class="alert alert-light border d-none mb-3">
						<div class="row text-center">
							<div class="col-4">
								<span class="tx-11 text-muted d-block">الرصيد الحالي</span>
								<strong id="bannerCurrentStock" class="tx-16 text-primary">0.000</strong>
							</div>
							<div class="col-4 border-right border-left">
								<span class="tx-11 text-muted d-block">حد الطلب الأدنى</span>
								<strong id="bannerMinStock" class="tx-16 text-warning">0.000</strong>
							</div>
							<div class="col-4">
								<span class="tx-11 text-muted d-block">سعر التكلفة</span>
								<strong id="bannerCostPrice" class="tx-16 text-dark">0.00 ج.م</strong>
							</div>
						</div>
					</div>

					<!-- Adjustment Mode Selection -->
					@php
						$oldMode = old('adjustment_mode', 'relative');
						$oldDir = old('direction', 'add');
						$initialRadio = ($oldMode === 'absolute') ? 'absolute' : (($oldDir === 'subtract') ? 'subtract' : 'add');
					@endphp
					<div class="form-group text-right">
						<label class="font-weight-semibold d-block">نوع التسوية <span class="text-danger">*</span></label>
						<div class="btn-group btn-group-toggle w-100" id="adjustmentModeBtnGroup">
							<label class="btn btn-outline-success font-weight-bold {{ $initialRadio === 'add' ? 'active' : '' }}" id="modeAddLabel" style="cursor: pointer;">
								<input type="radio" name="adjustment_mode_radio" value="add" {{ $initialRadio === 'add' ? 'checked' : '' }} style="position: absolute; clip: rect(0,0,0,0); pointer-events: none;">
								<i class="fas fa-plus-circle ml-1"></i> إضافة للمخزون (+)
							</label>
							<label class="btn btn-outline-danger font-weight-bold {{ $initialRadio === 'subtract' ? 'active' : '' }}" id="modeSubtractLabel" style="cursor: pointer;">
								<input type="radio" name="adjustment_mode_radio" value="subtract" {{ $initialRadio === 'subtract' ? 'checked' : '' }} style="position: absolute; clip: rect(0,0,0,0); pointer-events: none;">
								<i class="fas fa-minus-circle ml-1"></i> خصم من المخزون (-)
							</label>
							<label class="btn btn-outline-primary font-weight-bold {{ $initialRadio === 'absolute' ? 'active' : '' }}" id="modeAbsoluteLabel" style="cursor: pointer;">
								<input type="radio" name="adjustment_mode_radio" value="absolute" {{ $initialRadio === 'absolute' ? 'checked' : '' }} style="position: absolute; clip: rect(0,0,0,0); pointer-events: none;">
								<i class="fas fa-clipboard-check ml-1"></i> جرد فعلي (تعديل للرصيد الفعلي)
							</label>
						</div>
					</div>

					<!-- Hidden Real Form Fields for Mode & Direction -->
					<input type="hidden" name="adjustment_mode" id="adjustment_mode" value="{{ $oldMode }}">
					<input type="hidden" name="direction" id="direction" value="{{ $oldDir }}">

					<!-- Quantity Inputs (Toggled based on mode) -->
					<div id="relativeQuantityGroup" class="form-group text-right">
						<label for="quantity" id="quantityLabel" class="font-weight-semibold">
							كمية الإضافة <span class="text-danger">*</span>
						</label>
						<div class="input-group">
							<input type="number" step="0.001" min="0.001" id="quantity" name="quantity"
								   class="form-control @error('quantity') is-invalid @enderror"
								   value="{{ old('quantity') }}" placeholder="أدخل الكمية المطلوبة">
							<div class="input-group-append">
								<span class="input-group-text">قطعة / وحدة</span>
							</div>
						</div>
						@error('quantity')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<div id="absoluteQuantityGroup" class="form-group text-right d-none">
						<label for="counted_quantity" class="font-weight-semibold">
							الرصيد الفعلي بعد الجرد <span class="text-danger">*</span>
						</label>
						<div class="input-group">
							<input type="number" step="0.001" min="0" id="counted_quantity" name="counted_quantity"
								   class="form-control @error('counted_quantity') is-invalid @enderror"
								   value="{{ old('counted_quantity') }}" placeholder="أدخل إجمالي العدد الفعلي الموجود">
							<div class="input-group-append">
								<span class="input-group-text">الرصيد الكلي</span>
							</div>
						</div>
						<small class="text-muted d-block mt-1">سيقوم النظام باحتساب الفارق تلقائياً وتسجيل حركة زيادة أو عجز مطابقة.</small>
						@error('counted_quantity')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Expected Stock Result Live Preview -->
					<div class="card bg-gray-100 border mb-3">
						<div class="card-body p-3">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<span class="tx-12 text-muted d-block">الرصيد المتوقع بعد حفظ التسوية:</span>
									<h4 id="expectedStockBadge" class="mb-0 font-weight-bold text-dark">0.000</h4>
								</div>
								<div id="deltaStockBadge" class="tx-14 font-weight-bold">
									--
								</div>
							</div>
						</div>
					</div>

					<!-- Reason & Quick Presets -->
					<div class="form-group text-right">
						<label for="reason" class="font-weight-semibold">سبب التسوية الجردية <span class="text-danger">*</span></label>
						<div class="mb-2">
							<span class="tx-11 text-muted d-block mb-1">أسباب شائعة جاهزة:</span>
							<button type="button" class="btn btn-xs btn-outline-secondary preset-reason mb-1">جرد دوري شهري</button>
							<button type="button" class="btn btn-xs btn-outline-secondary preset-reason mb-1">تلف بضاعة أثناء العرض</button>
							<button type="button" class="btn btn-xs btn-outline-secondary preset-reason mb-1">عجز جردي غير مبرر</button>
							<button type="button" class="btn btn-xs btn-outline-secondary preset-reason mb-1">زيادة بضاعة غير مسجلة</button>
							<button type="button" class="btn btn-xs btn-outline-secondary preset-reason mb-1">تعديل رصيد افتتاحي</button>
							<button type="button" class="btn btn-xs btn-outline-secondary preset-reason mb-1">عينات وهدايا ترويجية</button>
						</div>
						<textarea id="reason" name="reason" rows="3"
								  class="form-control @error('reason') is-invalid @enderror"
								  placeholder="اكتب توضيحاً دقيقاً لسبب التسوية لتوثيق السجل المحاسبي..." required>{{ old('reason') }}</textarea>
						@error('reason')
							<span class="invalid-feedback d-block mt-1"><strong>{{ $message }}</strong></span>
						@enderror
					</div>

					<!-- Form Actions -->
					<div class="form-group text-left mt-4 mb-0">
						<button type="submit" class="btn btn-primary font-weight-bold px-4">
							<i class="fas fa-check-circle ml-1"></i> اعتماد وحفظ التسوية
						</button>
						<a href="{{ route('inventory.index') }}" class="btn btn-secondary mr-2">
							إلغاء
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
	const productSelect = document.getElementById('product_id');
	const infoBanner = document.getElementById('productInfoBanner');
	const bannerStock = document.getElementById('bannerCurrentStock');
	const bannerMin = document.getElementById('bannerMinStock');
	const bannerCost = document.getElementById('bannerCostPrice');

	const modeHidden = document.getElementById('adjustment_mode');
	const dirHidden = document.getElementById('direction');

	const relativeGroup = document.getElementById('relativeQuantityGroup');
	const absoluteGroup = document.getElementById('absoluteQuantityGroup');
	const quantityInput = document.getElementById('quantity');
	const countedInput = document.getElementById('counted_quantity');
	const quantityLabel = document.getElementById('quantityLabel');

	const expectedStockBadge = document.getElementById('expectedStockBadge');
	const deltaStockBadge = document.getElementById('deltaStockBadge');
	const reasonTextarea = document.getElementById('reason');
	const adjustmentForm = document.getElementById('adjustmentForm');

	function updateProductBanner() {
		const selected = productSelect.options[productSelect.selectedIndex];
		if (selected && selected.value) {
			const stock = parseFloat(selected.dataset.stock || 0);
			const min = parseFloat(selected.dataset.min || 0);
			const price = parseFloat(selected.dataset.price || 0);

			bannerStock.textContent = stock.toFixed(3);
			bannerMin.textContent = min.toFixed(3);
			bannerCost.textContent = price.toFixed(2) + ' ج.م';
			infoBanner.classList.remove('d-none');
		} else {
			infoBanner.classList.add('d-none');
		}
		calculateExpectedStock();
	}

	function setAdjustmentMode(val) {
		if (val === 'subtract') {
			modeHidden.value = 'relative';
			dirHidden.value = 'subtract';
			relativeGroup.classList.remove('d-none');
			absoluteGroup.classList.add('d-none');
			quantityLabel.innerHTML = 'كمية الخصم من المخزون <span class="text-danger">*</span>';
		} else if (val === 'absolute') {
			modeHidden.value = 'absolute';
			dirHidden.value = '';
			relativeGroup.classList.add('d-none');
			absoluteGroup.classList.remove('d-none');
		} else {
			// default: 'add'
			val = 'add';
			modeHidden.value = 'relative';
			dirHidden.value = 'add';
			relativeGroup.classList.remove('d-none');
			absoluteGroup.classList.add('d-none');
			quantityLabel.innerHTML = 'كمية الإضافة للمخزون <span class="text-danger">*</span>';
		}

		// Update active classes on buttons
		document.getElementById('modeAddLabel').classList.toggle('active', val === 'add');
		document.getElementById('modeSubtractLabel').classList.toggle('active', val === 'subtract');
		document.getElementById('modeAbsoluteLabel').classList.toggle('active', val === 'absolute');

		// Sync radio inputs
		document.querySelectorAll('input[name="adjustment_mode_radio"]').forEach(r => {
			r.checked = (r.value === val);
		});

		calculateExpectedStock();
	}

	function calculateExpectedStock() {
		const selected = productSelect.options[productSelect.selectedIndex];
		const currentStock = (selected && selected.value) ? parseFloat(selected.dataset.stock || 0) : 0;
		const mode = modeHidden.value;
		const dir = dirHidden.value;

		let expected = currentStock;
		let delta = 0;

		if (mode === 'relative') {
			const qty = parseFloat(quantityInput.value || 0);
			if (dir === 'subtract') {
				delta = -qty;
				expected = currentStock - qty;
			} else {
				delta = qty;
				expected = currentStock + qty;
			}
		} else {
			const counted = parseFloat(countedInput.value || 0);
			expected = counted;
			delta = counted - currentStock;
		}

		expectedStockBadge.textContent = expected.toFixed(3);

		if (delta > 0) {
			deltaStockBadge.className = 'tx-14 font-weight-bold text-success';
			deltaStockBadge.textContent = 'زيادة (+ ' + delta.toFixed(3) + ')';
		} else if (delta < 0) {
			deltaStockBadge.className = 'tx-14 font-weight-bold text-danger';
			deltaStockBadge.textContent = 'خصم (' + delta.toFixed(3) + ')';
		} else {
			deltaStockBadge.className = 'tx-14 font-weight-bold text-muted';
			deltaStockBadge.textContent = 'لا يوجد تغيير (0.000)';
		}

		if (expected < 0) {
			expectedStockBadge.className = 'mb-0 font-weight-bold text-danger';
			deltaStockBadge.textContent += ' ⚠️ (تجاوز الرصيد المتوفر)';
		} else {
			expectedStockBadge.className = 'mb-0 font-weight-bold text-dark';
		}
	}

	// Button Click Handlers
	document.getElementById('modeAddLabel').addEventListener('click', function (e) {
		e.preventDefault();
		setAdjustmentMode('add');
	});
	document.getElementById('modeSubtractLabel').addEventListener('click', function (e) {
		e.preventDefault();
		setAdjustmentMode('subtract');
	});
	document.getElementById('modeAbsoluteLabel').addEventListener('click', function (e) {
		e.preventDefault();
		setAdjustmentMode('absolute');
	});

	// Radio Change Fallback
	document.querySelectorAll('input[name="adjustment_mode_radio"]').forEach(r => {
		r.addEventListener('change', function () {
			setAdjustmentMode(this.value);
		});
	});

	// jQuery integration for Bootstrap/Select2
	if (window.jQuery) {
		jQuery('#modeAddLabel').on('click', function (e) { e.preventDefault(); setAdjustmentMode('add'); });
		jQuery('#modeSubtractLabel').on('click', function (e) { e.preventDefault(); setAdjustmentMode('subtract'); });
		jQuery('#modeAbsoluteLabel').on('click', function (e) { e.preventDefault(); setAdjustmentMode('absolute'); });
		jQuery('input[name="adjustment_mode_radio"]').on('change', function () { setAdjustmentMode(this.value); });
		jQuery('#product_id').on('change select2:select', function () { updateProductBanner(); });
	}

	productSelect.addEventListener('change', updateProductBanner);

	['input', 'change', 'keyup'].forEach(evt => {
		quantityInput.addEventListener(evt, calculateExpectedStock);
		countedInput.addEventListener(evt, calculateExpectedStock);
	});

	// Preset reason buttons
	document.querySelectorAll('.preset-reason').forEach(btn => {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			reasonTextarea.value = this.textContent.trim();
		});
	});

	// Client-side Validation Before Submit
	if (adjustmentForm) {
		adjustmentForm.addEventListener('submit', function (e) {
			const selected = productSelect.options[productSelect.selectedIndex];
			if (!selected || !selected.value) {
				e.preventDefault();
				alert('يرجى اختيار المنتج أولاً.');
				return;
			}

			const currentStock = parseFloat(selected.dataset.stock || 0);
			const mode = modeHidden.value;
			const dir = dirHidden.value;

			if (mode === 'relative') {
				const qty = parseFloat(quantityInput.value || 0);
				if (isNaN(qty) || qty <= 0) {
					e.preventDefault();
					alert('يرجى إدخال كمية تسوية صحيحة أكبر من الصفر.');
					quantityInput.focus();
					return;
				}
				if (dir === 'subtract' && qty > currentStock) {
					e.preventDefault();
					alert('تنبيه: الكمية المراد خصمها (' + qty + ') تتجاوز الرصيد المتوفر حالياً بالمخزن (' + currentStock + '). لا يمكن أن يصبح رصيد المخزون سالباً.');
					quantityInput.focus();
					return;
				}
			} else if (mode === 'absolute') {
				const counted = parseFloat(countedInput.value);
				if (isNaN(counted) || counted < 0) {
					e.preventDefault();
					alert('يرجى إدخال الرصيد الفعلي بعد الجرد بشكل صحيح (صفر أو أكثر).');
					countedInput.focus();
					return;
				}
				if (counted === currentStock) {
					e.preventDefault();
					alert('الرصيد الفعلي المدخل (' + counted + ') مطابق تماماً للرصيد المسجل بالنظام، لا توجد تسوية لازمة.');
					countedInput.focus();
					return;
				}
			}
		});
	}

	// Initialize on page load
	updateProductBanner();
	setAdjustmentMode('{{ $initialRadio }}');
});
</script>
@endsection

