@extends('layouts.master')

@section('title', 'نقطة البيع - متجر بيت الهدايا')

@section('css')
<style>
	.pos-catalog-container {
		height: calc(100vh - 220px);
		overflow-y: auto;
		padding-right: 5px;
	}
	.pos-cart-container {
		height: calc(100vh - 220px);
		display: flex;
		flex-direction: column;
	}
	.pos-cart-items {
		flex: 1;
		overflow-y: auto;
		min-height: 200px;
	}
	.product-card {
		cursor: pointer;
		transition: all 0.2s ease-in-out;
		border: 1px solid #e1e6f1;
		border-radius: 8px;
	}
	.product-card:hover {
		transform: translateY(-3px);
		box-shadow: 0 4px 15px rgba(0,0,0,0.08);
		border-color: #0162e8;
	}
	.product-card.out-of-stock {
		opacity: 0.55;
		cursor: not-allowed;
		background-color: #f8f9fa;
	}
	.category-pill {
		cursor: pointer;
		white-space: nowrap;
		transition: all 0.2s;
	}
	.category-pill.active {
		background-color: #0162e8 !important;
		color: #fff !important;
		border-color: #0162e8 !important;
	}
	.cart-item-row {
		border-bottom: 1px solid #f1f2f6;
		padding: 8px 0;
	}
	.qty-btn {
		width: 28px;
		height: 28px;
		padding: 0;
		line-height: 26px;
		text-align: center;
		font-weight: bold;
	}
	.qty-input {
		width: 48px;
		text-align: center;
		font-weight: bold;
	}
	.payment-option-label {
		cursor: pointer;
		border: 2px solid #e1e6f1;
		border-radius: 8px;
		padding: 10px;
		text-align: center;
		transition: all 0.2s;
	}
	.payment-option-input:checked + .payment-option-label {
		border-color: #0162e8;
		background-color: #f0f6ff;
		color: #0162e8;
		font-weight: bold;
	}
	.quick-cash-btn {
		cursor: pointer;
	}
</style>
@endsection

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-2">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary"><i class="fas fa-cash-register ml-2"></i>نقطة البيع</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ شاشة الكاشير السريعة</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		@if($activeShift)
			<div class="pr-1 mb-3 mb-xl-0">
				<a href="{{ route('shifts.current') }}" class="btn btn-sm btn-outline-success font-weight-bold">
					<i class="fas fa-dot-circle text-success ml-1"></i> الوردية #{{ $activeShift->id }} مفتوحة
				</a>
			</div>
		@else
			<div class="pr-1 mb-3 mb-xl-0">
				<button type="button" class="btn btn-sm btn-danger font-weight-bold" data-toggle="modal" data-target="#openShiftModal">
					<i class="fas fa-lock-open ml-1"></i> فتح وردية لبدء البيع
				</button>
			</div>
		@endif
		<div class="pr-1 mb-3 mb-xl-0 mr-2">
			<a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
				<i class="fas fa-list-alt ml-1"></i> سجل الفواتير
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

@if(!$activeShift)
	<div class="alert alert-warning border-warning shadow-sm mb-3">
		<div class="d-flex align-items-center justify-content-between flex-wrap">
			<div class="d-flex align-items-center">
				<i class="fas fa-exclamation-triangle tx-30 text-warning ml-3"></i>
				<div>
					<h5 class="alert-heading font-weight-bold mb-1">لا توجد وردية عمل مفتوحة للكاشير حالياً!</h5>
					<p class="mb-0 tx-13">يجب فتح وردية كاشير برصيد نقدية الدرج لتتمكن من إتمام عمليات البيع وتسجيل الفواتير وحساب الأرباح.</p>
				</div>
			</div>
			<button type="button" class="btn btn-warning font-weight-bold mt-2 mt-md-0" data-toggle="modal" data-target="#openShiftModal">
				<i class="fas fa-key ml-1"></i> فتح وردية العمل الآن
			</button>
		</div>
	</div>
@endif

<div class="row row-sm">
	<!-- Left Side: Products Catalog & Search (7 Cols) -->
	<div class="col-xl-7 col-lg-6 col-md-12">
		<div class="card mb-3">
			<div class="card-body p-3">
				<!-- Search and Scanner Input -->
				<div class="row row-sm mb-2">
					<div class="col-md-8 col-sm-12 mb-2 mb-md-0">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
							</div>
							<input type="text" id="posSearchInput" class="form-control form-control-lg text-right"
								   placeholder="بحث باسم المنتج ..." autofocus>
							<div class="input-group-append">
								<button class="btn btn-outline-light text-muted" type="button" id="clearSearchBtn">
									<i class="fas fa-times"></i>
								</button>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-sm-12">
						<div class="d-flex align-items-center justify-content-end h-100">
							<span class="tx-12 text-muted ml-2">المنتجات المعروضة:</span>
							<span id="displayedProductsCount" class="badge badge-primary tx-13 font-weight-bold px-2 py-1">{{ $products->count() }}</span>
						</div>
					</div>
				</div>

				<!-- Category Filter Pills -->
				<div class="d-flex align-items-center overflow-auto pb-2 border-bottom mb-3" style="gap: 6px;">
					<button type="button" class="btn btn-sm btn-outline-primary category-pill active px-3" data-category-id="all">
						الكل ({{ $products->count() }})
					</button>
					@foreach($categories as $cat)
						<button type="button" class="btn btn-sm btn-outline-secondary category-pill px-3" data-category-id="{{ $cat->id }}">
							{{ $cat->name }}
						</button>
					@endforeach
				</div>

				<!-- Products Catalog Grid -->
				<div class="pos-catalog-container" id="productsCatalogGrid">
					<div class="row row-sm" id="productsCardContainer">
						@forelse($products as $product)
							<div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-3 product-item-col"
								 data-id="{{ $product->id }}"
								 data-name="{{ $product->name }}"
								 data-category-id="{{ $product->subcategory?->category_id ?? '' }}"
								 data-category-name="{{ $product->subcategory?->name ?? 'عام' }}"
								 data-price="{{ (float) $product->selling_price }}"
								 data-stock="{{ (float) $product->stock_quantity }}">
								<div class="card product-card mb-0 h-100 {{ (float) $product->stock_quantity <= 0 ? 'out-of-stock' : '' }}"
									 onclick="posCart.addItem({{ $product->id }})">
									<div class="card-body p-3 d-flex flex-column justify-content-between">
										<div>
											<div class="d-flex justify-content-between align-items-start mb-1">
												<span class="badge badge-light tx-11 border text-truncate" style="max-width: 100px;">
													{{ $product->subcategory?->name ?? 'عام' }}
												</span>
												@if((float) $product->stock_quantity <= 0)
													<span class="badge badge-danger tx-10">نفذ من المخزون</span>
												@elseif($product->is_low_stock)
													<span class="badge badge-warning tx-10">مخزون: {{ rtrim(rtrim(number_format($product->stock_quantity, 3), '0'), '.') }}</span>
												@else
													<span class="badge badge-success-light tx-10 text-success font-weight-bold">
														متوفر: {{ rtrim(rtrim(number_format($product->stock_quantity, 3), '0'), '.') }}
													</span>
												@endif
											</div>
											<h6 class="font-weight-bold text-dark tx-14 mb-2 text-truncate" title="{{ $product->name }}">
												{{ $product->name }}
											</h6>
										</div>
										<div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
											<span class="tx-14 font-weight-bold text-primary">
												{{ number_format($product->selling_price, 2) }} <small class="tx-11">جنيه مصري</small>
											</span>
											<span class="btn btn-xs btn-primary rounded-circle">
												<i class="fas fa-plus"></i>
											</span>
										</div>
									</div>
								</div>
							</div>
						@empty
							<div class="col-12 text-center py-5">
								<i class="fas fa-box-open tx-40 text-muted mb-2"></i>
								<h5 class="text-muted">لا توجد منتجات مفعلة في المتجر حالياً.</h5>
								<a href="{{ route('products.create') }}" class="btn btn-sm btn-primary mt-2">إضافة منتج جديد</a>
							</div>
						@endforelse
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Right Side: POS Cart & Checkout (5 Cols) -->
	<div class="col-xl-5 col-lg-6 col-md-12">
		<div class="card mb-3 pos-cart-container">
			<!-- Cart Header -->
			<div class="card-header pb-2 d-flex justify-content-between align-items-center bg-light">
				<div>
					<h5 class="card-title mb-0 font-weight-bold text-dark">
						<i class="fas fa-shopping-basket ml-1 text-primary"></i> سلة الفاتورة الحالية
					</h5>
					<small class="text-muted">الكاشير: {{ Auth::user()->name }} | الوردية: {{ $activeShift ? '#' . $activeShift->id : 'غير محددة' }}</small>
				</div>
				<button type="button" class="btn btn-sm btn-outline-danger" onclick="posCart.clearCart()" title="تفريغ السلة">
					<i class="fas fa-trash-alt ml-1"></i> مسح السلة
				</button>
			</div>

			<!-- Cart Items Table / Scrollable List -->
			<div class="card-body p-2 pos-cart-items">
				<div id="cartEmptyState" class="text-center py-5 text-muted">
					<i class="fas fa-shopping-cart tx-40 mb-2 op-4"></i>
					<p class="tx-14 font-weight-semibold mb-1">السلة فارغة</p>
					<small class="tx-12">اختر منتجات من القائمة أو امسح الباركود لإضافتها للفاتورة</small>
				</div>

				<div class="table-responsive" id="cartTableWrapper" style="display: none;">
					<table class="table table-sm text-center mb-0 align-middle">
						<thead class="thead-light">
							<tr>
								<th class="text-right wd-40p">المنتج</th>
								<th class="wd-15p">السعر</th>
								<th class="wd-25p">الكمية</th>
								<th class="wd-15p">الإجمالي</th>
								<th class="wd-5p"></th>
							</tr>
						</thead>
						<tbody id="cartItemsTableBody">
							<!-- Populated via Javascript -->
						</tbody>
					</table>
				</div>
			</div>

			<!-- Cart Financial Summary & Checkout Options -->
			<div class="card-footer bg-light p-3 border-top">
				<!-- Subtotal & Discount -->
				<div class="d-flex justify-content-between mb-1">
					<span class="text-muted tx-13">المجموع الفرعي:</span>
					<strong class="tx-14 text-dark" id="summarySubtotal">0.00 جنيه مصري</strong>
				</div>
				<div class="d-flex justify-content-between align-items-center mb-2">
					<span class="text-muted tx-13">خصم إضافي:</span>
					<div class="input-group input-group-sm" style="max-width: 140px;">
						<input type="number" step="0.5" min="0" id="cartDiscountInput" class="form-control text-left font-weight-bold" value="0.00">
						<div class="input-group-append">
							<span class="input-group-text tx-11">جنيه</span>
						</div>
					</div>
				</div>

				<!-- Final Total Display -->
				<div class="bg-primary-transparent border border-primary rounded p-2 text-center mb-3">
					<span class="tx-12 text-primary font-weight-semibold d-block">الإجمالي النهائي المطلوب سداده</span>
					<h3 class="tx-24 font-weight-bold text-primary mb-0" id="summaryFinalTotal">0.00 جنيه مصري</h3>
				</div>

				<!-- Payment Methods Selector -->
				<div class="mb-3">
					<label class="font-weight-semibold tx-12 text-dark d-block mb-1">طريقة الدفع:</label>
					<div class="row row-sm">
						<div class="col-4">
							<input type="radio" name="pos_payment_method" value="cash" id="payCash" class="payment-option-input d-none" checked>
							<label for="payCash" class="payment-option-label w-100 mb-0">
								<i class="fas fa-money-bill-wave d-block tx-18 mb-1 text-success"></i>
								<span class="tx-12">نقداً (كاش)</span>
							</label>
						</div>
						<div class="col-4">
							<input type="radio" name="pos_payment_method" value="vodafone_cash" id="payVodafone" class="payment-option-input d-none">
							<label for="payVodafone" class="payment-option-label w-100 mb-0">
								<i class="fas fa-mobile-alt d-block tx-18 mb-1 text-danger"></i>
								<span class="tx-12">فودافون كاش</span>
							</label>
						</div>
						<div class="col-4">
							<input type="radio" name="pos_payment_method" value="instapay" id="payInstapay" class="payment-option-input d-none">
							<label for="payInstapay" class="payment-option-label w-100 mb-0">
								<i class="fas fa-university d-block tx-18 mb-1 text-primary"></i>
								<span class="tx-12">انستا باي</span>
							</label>
						</div>
					</div>
				</div>

				<!-- Cash Tender & Change Calculator (Visible only when cash is selected) -->
				<div id="cashCalculatorBox" class="bg-white p-2 rounded border mb-3">
					<div class="row row-sm align-items-center">
						<div class="col-6">
							<label class="tx-11 text-muted mb-1 d-block">المبلغ المدفوع من العميل:</label>
							<div class="input-group input-group-sm">
								<input type="number" step="1" min="0" id="amountPaidInput" class="form-control text-left font-weight-bold" placeholder="0.00">
								<div class="input-group-append">
									<span class="input-group-text tx-11">جنيه</span>
								</div>
							</div>
						</div>
						<div class="col-6 text-left">
							<span class="tx-11 text-muted mb-1 d-block">الباقي للعميل:</span>
							<strong class="tx-16 text-success d-block" id="changeAmountDisplay">0.00 جنيه مصري</strong>
						</div>
					</div>
					<!-- Quick Tender Buttons -->
					<div class="d-flex justify-content-between mt-2 pt-1 border-top" style="gap: 4px;">
						<button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn flex-fill" data-amount="exact">المبلغ بالضبط</button>
						<button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn flex-fill" data-amount="50">50 جنيه</button>
						<button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn flex-fill" data-amount="100">100 جنيه</button>
						<button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn flex-fill" data-amount="200">200 جنيه</button>
						<button type="button" class="btn btn-xs btn-outline-secondary quick-cash-btn flex-fill" data-amount="500">500 جنيه</button>
					</div>
				</div>

				<!-- Checkout Action Button -->
				<button type="button" id="btnCheckoutSubmit" class="btn btn-success btn-lg btn-block font-weight-bold py-2"
						onclick="posCart.submitOrder()" {{ !$activeShift ? 'disabled' : '' }}>
					<i class="fas fa-check-circle ml-1"></i> إتمام البيع وطباعة الفاتورة (F9)
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Open Shift (When Cashier Has No Active Shift) -->
<div class="modal fade" id="openShiftModal" tabindex="-1" role="dialog" aria-labelledby="openShiftModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form action="{{ route('shifts.open') }}" method="POST">
				@csrf
				<div class="modal-header bg-success-transparent">
					<h5 class="modal-title text-success font-weight-bold" id="openShiftModalLabel">
						<i class="fas fa-lock-open ml-1"></i> فتح وردية كاشير جديدة
					</h5>
					<button type="button" class="close ml-0 mr-auto" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-right">
					<div class="alert alert-info">
						<i class="fas fa-info-circle ml-1"></i> أدخل رصيد النقدية الافتتاحي (الفكة) الموجود في الدرج لبدء تسجيل الفواتير.
					</div>
					<div class="form-group">
						<label for="opening_cash_modal" class="font-weight-semibold">العهدة النقدية الافتتاحية في الدرج <span class="text-danger">*</span></label>
						<div class="input-group">
							<input type="number" step="0.01" min="0" name="opening_cash" id="opening_cash_modal"
								   class="form-control form-control-lg font-weight-bold text-left" value="0.00" required>
							<div class="input-group-append">
								<span class="input-group-text font-weight-bold">جنيه مصري</span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="notes_modal" class="font-weight-semibold">ملاحظات افتتاحية (اختياري)</label>
						<textarea name="notes" id="notes_modal" rows="2" class="form-control" placeholder="أي ملاحظات حول استلام الدرج..."></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
					<button type="submit" class="btn btn-success font-weight-bold">
						<i class="fas fa-check ml-1"></i> تأكيد فتح الوردية
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal: Sale Success and Receipt Prompt -->
<div class="modal fade" id="saleSuccessModal" tabindex="-1" role="dialog" aria-labelledby="saleSuccessModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content text-center p-4">
			<div class="mb-3">
				<div class="avatar avatar-xxl bg-success-transparent text-success rounded-circle mx-auto">
					<i class="fas fa-check tx-40"></i>
				</div>
			</div>
			<h4 class="font-weight-bold text-dark mb-1">تم إتمام الفاتورة بنجاح!</h4>
			<p class="text-muted tx-14 mb-3">رقم الفاتورة: <strong id="modalSuccessInvoiceNumber" class="text-primary tx-18">INV-0000</strong></p>

			<div class="bg-light rounded p-3 mb-4 text-right">
				<div class="d-flex justify-content-between mb-1">
					<span class="text-muted">طريقة الدفع:</span>
					<strong id="modalSuccessPaymentMethod" class="text-dark">-</strong>
				</div>
				<div class="d-flex justify-content-between mb-1">
					<span class="text-muted">إجمالي الفاتورة:</span>
					<strong id="modalSuccessTotal" class="text-success tx-16">0.00 جنيه مصري</strong>
				</div>
				<div class="d-flex justify-content-between" id="modalSuccessChangeRow">
					<span class="text-muted">المتبقي للعميل (الباقي):</span>
					<strong id="modalSuccessChange" class="text-primary tx-16">0.00 جنيه مصري</strong>
				</div>
			</div>

			<div class="d-flex justify-content-center" style="gap: 10px;">
				<a href="#" id="modalPrintReceiptBtn" target="_blank" class="btn btn-primary font-weight-bold px-4">
					<i class="fas fa-print ml-1"></i> طباعة الإيصال
				</a>
				<button type="button" class="btn btn-outline-secondary font-weight-bold px-4" onclick="posCart.resetAfterSuccess()">
					<i class="fas fa-plus ml-1"></i> فاتورة جديدة
				</button>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')
<script>
	// Products catalog registry in memory for fast local filtering & lookups
	const PRODUCTS_DATA = {!! json_encode($productsData) !!};

	const CHECKOUT_ROUTE = "{{ route('pos.checkout') }}";
	const CSRF_TOKEN = "{{ csrf_token() }}";
	const HAS_ACTIVE_SHIFT = {{ $activeShift ? 'true' : 'false' }};

	// POS Cart Object
	const posCart = {
		items: {}, // productId => { product, quantity }
		discount: 0.0,
		paymentMethod: 'cash',
		amountPaid: 0.0,

		addItem: function(productId) {
			const product = PRODUCTS_DATA[productId];
			if (!product) return;

			if (product.stock <= 0) {
				alert('عذراً، هذا المنتج نفذ بالكامل من المخزون.');
				return;
			}

			const currentQty = this.items[productId] ? this.items[productId].quantity : 0;
			const nextQty = currentQty + 1;

			if (nextQty > product.stock) {
				alert('عذراً، الكمية المطلوبة تتجاوز الرصيد المتوفر بالمخزون (' + product.stock + ').');
				return;
			}

			if (this.items[productId]) {
				this.items[productId].quantity = nextQty;
			} else {
				this.items[productId] = {
					product: product,
					quantity: 1
				};
			}

			this.render();
		},

		updateQuantity: function(productId, qty) {
			const product = PRODUCTS_DATA[productId];
			if (!product || !this.items[productId]) return;

			const parsedQty = parseFloat(qty);
			if (isNaN(parsedQty) || parsedQty <= 0) {
				this.removeItem(productId);
				return;
			}

			if (parsedQty > product.stock) {
				alert('الكمية المطلوبة (' + parsedQty + ') تتجاوز الرصيد المتوفر بالمخزون (' + product.stock + ').');
				this.items[productId].quantity = product.stock;
			} else {
				this.items[productId].quantity = parsedQty;
			}

			this.render();
		},

		changeQuantityDelta: function(productId, delta) {
			if (!this.items[productId]) return;
			const newQty = this.items[productId].quantity + delta;
			this.updateQuantity(productId, newQty);
		},

		removeItem: function(productId) {
			if (this.items[productId]) {
				delete this.items[productId];
				this.render();
			}
		},

		clearCart: function() {
			if (Object.keys(this.items).length === 0) return;
			if (confirm('هل أنت متأكد من رغبتك في مسح كافة الأصناف من السلة؟')) {
				this.items = {};
				this.discount = 0.0;
				document.getElementById('cartDiscountInput').value = '0.00';
				document.getElementById('amountPaidInput').value = '';
				this.render();
			}
		},

		calculateTotals: function() {
			let subtotal = 0.0;
			for (const id in this.items) {
				const item = this.items[id];
				subtotal += item.product.price * item.quantity;
			}
			subtotal = Math.round(subtotal * 100) / 100;

			let discount = parseFloat(document.getElementById('cartDiscountInput').value) || 0.0;
			if (discount < 0) discount = 0.0;
			if (discount > subtotal) {
				discount = subtotal;
				document.getElementById('cartDiscountInput').value = discount.toFixed(2);
			}

			const finalTotal = Math.max(0, Math.round((subtotal - discount) * 100) / 100);

			return { subtotal, discount, finalTotal };
		},

		render: function() {
			const tbody = document.getElementById('cartItemsTableBody');
			const tableWrapper = document.getElementById('cartTableWrapper');
			const emptyState = document.getElementById('cartEmptyState');
			const itemKeys = Object.keys(this.items);

			if (itemKeys.length === 0) {
				emptyState.style.display = 'block';
				tableWrapper.style.display = 'none';
				tbody.innerHTML = '';
			} else {
				emptyState.style.display = 'none';
				tableWrapper.style.display = 'block';

				let html = '';
				itemKeys.forEach(id => {
					const item = this.items[id];
					const lineTotal = (item.product.price * item.quantity).toFixed(2);
					html += `
						<tr class="cart-item-row" data-id="${id}">
							<td class="text-right py-2">
								<strong class="d-block text-dark tx-13">${item.product.name}</strong>
								<small class="text-muted">${item.product.category_name}</small>
							</td>
							<td class="py-2 tx-12 font-weight-semibold text-muted">
								${item.product.price.toFixed(2)}
							</td>
							<td class="py-2">
								<div class="d-flex align-items-center justify-content-center" style="gap: 2px;">
									<button type="button" class="btn btn-sm btn-light border qty-btn" onclick="posCart.changeQuantityDelta(${id}, -1)">-</button>
									<input type="number" step="1" min="1" max="${item.product.stock}" class="form-control form-control-sm qty-input px-1"
										   value="${item.quantity}" onchange="posCart.updateQuantity(${id}, this.value)">
									<button type="button" class="btn btn-sm btn-light border qty-btn" onclick="posCart.changeQuantityDelta(${id}, 1)">+</button>
								</div>
							</td>
							<td class="py-2 font-weight-bold text-dark tx-13">
								${lineTotal}
							</td>
							<td class="py-2 text-center">
								<button type="button" class="btn btn-sm text-danger p-0" onclick="posCart.removeItem(${id})" title="حذف">
									<i class="fas fa-times-circle tx-16"></i>
								</button>
							</td>
						</tr>
					`;
				});
				tbody.innerHTML = html;
			}

			// Render summary
			const totals = this.calculateTotals();
			document.getElementById('summarySubtotal').textContent = totals.subtotal.toFixed(2) + ' جنيه مصري';
			document.getElementById('summaryFinalTotal').textContent = totals.finalTotal.toFixed(2) + ' جنيه مصري';

			// Recalculate change
			this.renderChange(totals.finalTotal);
		},

		renderChange: function(finalTotal) {
			const paidInput = document.getElementById('amountPaidInput');
			const changeDisplay = document.getElementById('changeAmountDisplay');
			const paid = parseFloat(paidInput.value) || 0.0;

			if (paid > 0 && finalTotal > 0) {
				const change = paid - finalTotal;
				if (change >= 0) {
					changeDisplay.textContent = change.toFixed(2) + ' جنيه مصري';
					changeDisplay.className = 'tx-16 text-success d-block';
				} else {
					changeDisplay.textContent = 'متبقي ' + Math.abs(change).toFixed(2) + ' جنيه';
					changeDisplay.className = 'tx-16 text-danger d-block';
				}
			} else {
				changeDisplay.textContent = '0.00 جنيه مصري';
				changeDisplay.className = 'tx-16 text-muted d-block';
			}
		},

		submitOrder: function() {
			if (!HAS_ACTIVE_SHIFT) {
				$('#openShiftModal').modal('show');
				return;
			}

			const itemKeys = Object.keys(this.items);
			if (itemKeys.length === 0) {
				alert('يرجى إضافة منتج واحد على الأقل للسلة قبل تأكيد البيع.');
				return;
			}

			const totals = this.calculateTotals();
			const selectedPaymentMethod = document.querySelector('input[name="pos_payment_method"]:checked').value;

			// Prepare payload
			const itemsPayload = itemKeys.map(id => ({
				product_id: parseInt(id),
				quantity: parseFloat(this.items[id].quantity)
			}));

			const payload = {
				payment_method: selectedPaymentMethod,
				discount: totals.discount,
				items: itemsPayload
			};

			const submitBtn = document.getElementById('btnCheckoutSubmit');
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i> جاري حفظ الفاتورة...';

			fetch(CHECKOUT_ROUTE, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': CSRF_TOKEN,
					'Accept': 'application/json'
				},
				body: JSON.stringify(payload)
			})
			.then(response => response.json().then(data => ({ status: response.status, body: data })))
			.then(res => {
				submitBtn.disabled = false;
				submitBtn.innerHTML = '<i class="fas fa-check-circle ml-1"></i> إتمام البيع وطباعة الفاتورة (F9)';

				if (res.status === 200 && res.body.success) {
					// Success Modal
					const paid = parseFloat(document.getElementById('amountPaidInput').value) || totals.finalTotal;
					const change = Math.max(0, paid - totals.finalTotal);

					document.getElementById('modalSuccessInvoiceNumber').textContent = res.body.invoice_number;
					document.getElementById('modalSuccessTotal').textContent = totals.finalTotal.toFixed(2) + ' جنيه مصري';
					document.getElementById('modalSuccessPaymentMethod').textContent =
						selectedPaymentMethod === 'cash' ? 'نقداً (كاش)' : (selectedPaymentMethod === 'vodafone_cash' ? 'فودافون كاش' : 'انستا باي');

					if (selectedPaymentMethod === 'cash' && paid > totals.finalTotal) {
						document.getElementById('modalSuccessChangeRow').style.display = 'flex';
						document.getElementById('modalSuccessChange').textContent = change.toFixed(2) + ' جنيه مصري';
					} else {
						document.getElementById('modalSuccessChangeRow').style.display = 'none';
					}

					document.getElementById('modalPrintReceiptBtn').href = res.body.print_url;

					$('#saleSuccessModal').modal('show');
				} else {
					let errorMsg = 'تعذر إتمام عملية البيع: ';
					if (res.body.errors) {
						errorMsg += Object.values(res.body.errors).flat().join('\n');
					} else if (res.body.message) {
						errorMsg += res.body.message;
					}
					alert(errorMsg);
				}
			})
			.catch(err => {
				submitBtn.disabled = false;
				submitBtn.innerHTML = '<i class="fas fa-check-circle ml-1"></i> إتمام البيع وطباعة الفاتورة (F9)';
				alert('حدث خطأ أثناء إرسال البيانات إلى الخادم. يرجى التحقق من اتصالك والمحاولة مجدداً.');
				console.error(err);
			});
		},

		resetAfterSuccess: function() {
			$('#saleSuccessModal').modal('hide');
			this.items = {};
			this.discount = 0.0;
			document.getElementById('cartDiscountInput').value = '0.00';
			document.getElementById('amountPaidInput').value = '';
			this.render();
			document.getElementById('posSearchInput').focus();
		}
	};

	// Event Listeners for POS Terminal
	document.addEventListener('DOMContentLoaded', function() {
		// Category Pills Filtering
		const categoryPills = document.querySelectorAll('.category-pill');
		const productCols = document.querySelectorAll('.product-item-col');

		categoryPills.forEach(pill => {
			pill.addEventListener('click', function() {
				categoryPills.forEach(p => p.classList.remove('active'));
				this.classList.add('active');

				const selectedCat = this.getAttribute('data-category-id');
				filterProductsCatalog();
			});
		});

		// Search Input Filtering
		const searchInput = document.getElementById('posSearchInput');
		const clearSearchBtn = document.getElementById('clearSearchBtn');

		function filterProductsCatalog() {
			const activePill = document.querySelector('.category-pill.active');
			const selectedCat = activePill ? activePill.getAttribute('data-category-id') : 'all';
			const searchVal = searchInput.value.trim().toLowerCase();

			let visibleCount = 0;

			productCols.forEach(col => {
				const colCat = col.getAttribute('data-category-id');
				const colName = col.getAttribute('data-name').toLowerCase();
				const colId = col.getAttribute('data-id');

				const matchCat = (selectedCat === 'all' || colCat === selectedCat);
				const matchSearch = (searchVal === '' || colName.includes(searchVal) || colId === searchVal);

				if (matchCat && matchSearch) {
					col.style.display = 'block';
					visibleCount++;
				} else {
					col.style.display = 'none';
				}
			});

			document.getElementById('displayedProductsCount').textContent = visibleCount;
		}

		searchInput.addEventListener('input', filterProductsCatalog);

		clearSearchBtn.addEventListener('click', function() {
			searchInput.value = '';
			filterProductsCatalog();
			searchInput.focus();
		});

		// Enter on search input: if exactly one product visible, add it!
		searchInput.addEventListener('keydown', function(e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				const visibleCols = Array.from(productCols).filter(col => col.style.display !== 'none');
				if (visibleCols.length === 1) {
					const pid = parseInt(visibleCols[0].getAttribute('data-id'));
					posCart.addItem(pid);
					searchInput.value = '';
					filterProductsCatalog();
				}
			}
		});

		// Discount Input Change
		document.getElementById('cartDiscountInput').addEventListener('input', function() {
			posCart.render();
		});

		// Payment Method Radio Change
		const paymentRadios = document.querySelectorAll('input[name="pos_payment_method"]');
		const cashBox = document.getElementById('cashCalculatorBox');

		paymentRadios.forEach(radio => {
			radio.addEventListener('change', function() {
				if (this.value === 'cash') {
					cashBox.style.display = 'block';
				} else {
					cashBox.style.display = 'none';
				}
			});
		});

		// Amount Paid Input & Quick Cash Buttons
		const paidInput = document.getElementById('amountPaidInput');
		paidInput.addEventListener('input', function() {
			const totals = posCart.calculateTotals();
			posCart.renderChange(totals.finalTotal);
		});

		document.querySelectorAll('.quick-cash-btn').forEach(btn => {
			btn.addEventListener('click', function() {
				const totals = posCart.calculateTotals();
				const val = this.getAttribute('data-amount');
				if (val === 'exact') {
					paidInput.value = totals.finalTotal.toFixed(2);
				} else {
					paidInput.value = parseFloat(val).toFixed(2);
				}
				posCart.renderChange(totals.finalTotal);
			});
		});

		// Keyboard Shortcuts (F9 for Checkout)
		document.addEventListener('keydown', function(e) {
			if (e.key === 'F9') {
				e.preventDefault();
				posCart.submitOrder();
			}
		});
	});
</script>
@endsection

