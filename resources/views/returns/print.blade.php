<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>إشعار مرتجع مبيعات #{{ $return->return_number }} - متجر بيت الهدايا</title>
	<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.rtl.min.css') }}">
	<style>
		body {
			background: #f8f9fa;
			color: #111;
			font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
			padding: 20px;
		}
		.receipt-container {
			max-width: 480px;
			margin: 0 auto;
			background: #fff;
			border: 1px dashed #bbb;
			padding: 20px;
			border-radius: 6px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.05);
		}
		@media print {
			body {
				background: #fff;
				padding: 0;
			}
			.receipt-container {
				border: none;
				box-shadow: none;
				max-width: 100%;
				width: 100%;
				padding: 5px;
			}
			.no-print {
				display: none !important;
			}
		}
		.table-sm th, .table-sm td {
			padding: 0.35rem 0.4rem;
			font-size: 13px;
		}
		.dashed-divider {
			border-top: 1px dashed #888;
			margin: 10px 0;
		}
	</style>
</head>
<body>
	<div class="no-print text-center mb-3">
		<button onclick="window.print()" class="btn btn-primary font-weight-bold px-4">
			<i class="fas fa-print ml-1"></i> أمر الطباعة الآن
		</button>
		<button onclick="window.close()" class="btn btn-outline-secondary px-3 mr-2">
			إغلاق النافذة
		</button>
	</div>

	<div class="receipt-container">
		<!-- Store Header -->
		<div class="text-center mb-2">
			<a class="desktop-logo logo-light active d-block mb-2" href="{{ route('dashboard') }}">
				<img src="{{URL::asset('assets/img/brand/gift_house_logo.jpg')}}" class="main-logo" alt="logo" style="max-height: 60px; max-width: 180px; object-fit: contain; margin: 0 auto;">
			</a>
			<h4 class="font-weight-bold mb-1">متجر بيت الهدايا</h4>
			<p class="text-muted tx-12 mb-1">Gift Shop POS System</p>
			<div class="dashed-divider"></div>
			<h6 class="font-weight-bold mb-1 text-danger">إشعار مرتجع مبيعات</h6>
			<strong class="tx-16 text-dark d-block">#{{ $return->return_number }}</strong>
			@if($return->isCancelled())
				<span class="badge badge-danger tx-13 px-3 py-1 mt-1 font-weight-bold">*** إشعار ملغى ***</span>
			@endif
		</div>

		<!-- Meta Details -->
		<div class="tx-12 mb-2 text-right">
			<div class="d-flex justify-content-between">
				<span>رقم الفاتورة الأصلية:</span>
				<strong>#{{ $return->sale?->invoice_number ?? '-' }}</strong>
			</div>
			<div class="d-flex justify-content-between">
				<span>تاريخ ووقت المرتجع:</span>
				<strong>{{ $return->created_at ? $return->created_at->translatedFormat('Y-m-d h:i A') : '-' }}</strong>
			</div>
			<div class="d-flex justify-content-between">
				<span>الكاشير المسؤول:</span>
				<strong>{{ $return->user->name ?? '-' }}</strong>
			</div>
			<div class="d-flex justify-content-between">
				<span>رقم الوردية:</span>
				<strong>#{{ $return->shift_id }}</strong>
			</div>
			<div class="d-flex justify-content-between">
				<span>طريقة الاسترداد:</span>
				<strong>{{ $return->refund_method_label }}</strong>
			</div>
			@if($return->reason)
				<div class="d-flex justify-content-between">
					<span>سبب المرتجع:</span>
					<strong>{{ $return->reason }}</strong>
				</div>
			@endif
		</div>

		<div class="dashed-divider"></div>

		<!-- Items Table -->
		<table class="table table-sm text-center mb-2">
			<thead>
				<tr class="border-bottom">
					<th class="text-right wd-45p">الصنف المسترجع</th>
					<th class="wd-15p">الكمية</th>
					<th class="wd-20p">السعر</th>
					<th class="text-left wd-20p">الإجمالي</th>
				</tr>
			</thead>
			<tbody>
				@foreach($return->items as $item)
					<tr>
						<td class="text-right">
							<strong>{{ $item->product->name ?? 'صنف #' . $item->product_id }}</strong>
						</td>
						<td>{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}</td>
						<td>{{ number_format($item->unit_price, 2) }}</td>
						<td class="text-left font-weight-bold text-danger">-{{ number_format($item->total, 2) }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<div class="dashed-divider"></div>

		<!-- Totals Table -->
		<table class="table table-sm mb-2 text-right">
			<tbody>
				<tr class="tx-15 font-weight-bold">
					<th class="border-top pt-2">إجمالي المبلغ المسترد:</th>
					<td class="text-left border-top pt-2 text-danger">-{{ number_format($return->total_amount, 2) }} جنيه مصري</td>
				</tr>
			</tbody>
		</table>

		<div class="dashed-divider"></div>

		<!-- Footer -->
		<div class="text-center tx-12 mt-3">
			<p class="mb-1 font-weight-bold">بيت الهدايا يرحب بكم دائماً</p>
			<p class="text-muted tx-11 mb-0">تم استلام البضاعة وإرجاع المبلغ للعميل حسب السياسة المتبعة</p>
			<small class="text-muted d-block mt-2 tx-10">طُبعت بتاريخ: {{ now()->translatedFormat('Y-m-d h:i A') }}</small>
		</div>
	</div>

	<script>
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('auto_print') === '1') {
			window.onload = function() {
				window.print();
			};
		}
	</script>
</body>
</html>

