<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>فاتورة مبيعات #{{ $sale->invoice_number }} - متجر بيت الهدايا</title>
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
			<h4 class="font-weight-bold mb-1">متجر بيت الهدايا</h4>
			<p class="text-muted tx-12 mb-1">Gift Shop POS System</p>
			<div class="dashed-divider"></div>
			<h6 class="font-weight-bold mb-1">إيصال فاتورة مبيعات</h6>
			<strong class="tx-16 text-dark d-block">#{{ $sale->invoice_number }}</strong>
			@if($sale->isCancelled())
				<span class="badge badge-danger tx-13 px-3 py-1 mt-1 font-weight-bold">*** فاتورة ملغاة ***</span>
			@endif
		</div>

		<!-- Meta Details -->
		<div class="tx-12 mb-2 text-right">
			<div class="d-flex justify-content-between">
				<span>التاريخ والوقت:</span>
				<strong>{{ $sale->created_at ? $sale->created_at->translatedFormat('Y-m-d h:i A') : '-' }}</strong>
			</div>
			<div class="d-flex justify-content-between">
				<span>الكاشير المسؤول:</span>
				<strong>{{ $sale->user->name ?? '-' }}</strong>
			</div>
			<div class="d-flex justify-content-between">
				<span>رقم الوردية:</span>
				<strong>#{{ $sale->shift_id }}</strong>
			</div>
			<div class="d-flex justify-content-between">
				<span>طريقة السداد:</span>
				<strong>{{ $sale->payment_method_label }}</strong>
			</div>
		</div>

		<div class="dashed-divider"></div>

		<!-- Items Table -->
		<table class="table table-sm text-center mb-2">
			<thead>
				<tr class="border-bottom">
					<th class="text-right wd-45p">الصنف</th>
					<th class="wd-15p">الكمية</th>
					<th class="wd-20p">السعر</th>
					<th class="text-left wd-20p">الإجمالي</th>
				</tr>
			</thead>
			<tbody>
				@foreach($sale->items as $item)
					<tr>
						<td class="text-right">
							<strong>{{ $item->product->name ?? 'صنف #' . $item->product_id }}</strong>
						</td>
						<td>{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}</td>
						<td>{{ number_format($item->unit_price, 2) }}</td>
						<td class="text-left font-weight-bold">{{ number_format($item->total, 2) }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<div class="dashed-divider"></div>

		<!-- Totals Table -->
		<table class="table table-sm mb-2 text-right">
			<tbody>
				<tr>
					<th>المجموع الفرعي:</th>
					<td class="text-left">{{ number_format($sale->subtotal, 2) }} جنيه مصري</td>
				</tr>
				@if($sale->discount > 0)
					<tr>
						<th class="text-danger">الخصم:</th>
						<td class="text-left text-danger font-weight-bold">-{{ number_format($sale->discount, 2) }} جنيه مصري</td>
					</tr>
				@endif
				<tr class="tx-15 font-weight-bold">
					<th class="border-top pt-2">الإجمالي النهائي:</th>
					<td class="text-left border-top pt-2">{{ number_format($sale->total, 2) }} جنيه مصري</td>
				</tr>
			</tbody>
		</table>

		<div class="dashed-divider"></div>

		<!-- Footer -->
		<div class="text-center tx-12 mt-3">
			<p class="mb-1 font-weight-bold">شكراً لتسوقكم من بيت الهدايا!</p>
			<p class="text-muted tx-11 mb-0">نسعد بخدمتكم وزيارتكم دائماً</p>
			<small class="text-muted d-block mt-2 tx-10">طُبعت بتاريخ: {{ now()->translatedFormat('Y-m-d h:i A') }}</small>
		</div>
	</div>

	<script>
		// Auto print if auto_print parameter is passed in URL
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('auto_print') === '1') {
			window.onload = function() {
				window.print();
			};
		}
	</script>
</body>
</html>

