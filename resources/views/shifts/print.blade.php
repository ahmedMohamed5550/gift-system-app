<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>تقرير تسوية الوردية #{{ $shift->id }} - متجر الهدايا</title>
	<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.rtl.min.css') }}">
	<style>
		body {
			background: #fff;
			color: #000;
			font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
			padding: 20px;
		}
		.receipt-container {
			max-width: 600px;
			margin: 0 auto;
			border: 1px solid #ccc;
			padding: 25px;
			border-radius: 8px;
		}
		@media print {
			body { padding: 0; }
			.receipt-container { border: none; max-width: 100%; }
			.no-print { display: none !important; }
		}
		.table-sm th, .table-sm td {
			padding: 0.4rem 0.5rem;
		}
	</style>
</head>
<body>
	<div class="receipt-container">
		<div class="text-center mb-3">
			<h3 class="font-weight-bold mb-1">متجر الهدايا (Gift Shop POS)</h3>
			<h5 class="text-muted">تقرير إغلاق وتسوية وردية العمل</h5>
			<span class="badge badge-dark tx-14 px-3 py-1">وردية رقم #{{ $shift->id }}</span>
		</div>

		<hr>

		<div class="row mb-3 text-right">
			<div class="col-6">
				<span class="text-muted d-block tx-12">الكاشير المسؤول:</span>
				<strong>{{ $shift->user->name }}</strong>
			</div>
			<div class="col-6 text-left">
				<span class="text-muted d-block tx-12">حالة الوردية:</span>
				<strong>{{ $shift->status_label_arabic }}</strong>
			</div>
			<div class="col-6 mt-2">
				<span class="text-muted d-block tx-12">وقت الافتتاح:</span>
				<span>{{ $shift->opened_at ? $shift->opened_at->translatedFormat('Y-m-d h:i A') : '-' }}</span>
			</div>
			<div class="col-6 mt-2 text-left">
				<span class="text-muted d-block tx-12">وقت الإغلاق:</span>
				<span>{{ $shift->closed_at ? $shift->closed_at->translatedFormat('Y-m-d h:i A') : '-' }}</span>
			</div>
		</div>

		<table class="table table-bordered table-sm text-right mb-3">
			<thead class="thead-light">
				<tr>
					<th>بند تسوية الخزينة (النقدية)</th>
					<th class="text-left">المبلغ</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>العهدة الافتتاحية في بداية الوردية (+)</td>
					<td class="text-left font-weight-bold">{{ number_format($summary['reconciliation']['opening_cash'], 2) }} ج.م</td>
				</tr>
				<tr>
					<td>مبيعات نقدية (الكاش فقط) (+)</td>
					<td class="text-left font-weight-bold">{{ number_format($summary['reconciliation']['cash_sales'], 2) }} ج.م</td>
				</tr>
				<tr>
					<td>مرتجعات نقدية مخصومة من الدرج (-)</td>
					<td class="text-left font-weight-bold text-danger">{{ number_format($summary['reconciliation']['cash_returns'], 2) }} ج.م</td>
				</tr>
				<tr>
					<td>مصروفات نقدية مدفوعة من الدرج (-)</td>
					<td class="text-left font-weight-bold text-danger">{{ number_format($summary['reconciliation']['cash_expenses'], 2) }} ج.م</td>
				</tr>
				<tr class="table-primary font-weight-bold">
					<td>النقدية المحسوبة المتوقعة بالدرج (=)</td>
					<td class="text-left">{{ number_format($summary['reconciliation']['expected_cash'], 2) }} ج.م</td>
				</tr>
				<tr class="font-weight-bold">
					<td>النقدية الفعلية المحصية عند الإغلاق</td>
					<td class="text-left">{{ number_format($summary['reconciliation']['actual_closing_cash'] ?? 0, 2) }} ج.م</td>
				</tr>
				@php
					$diff = $summary['reconciliation']['difference'] ?? 0;
				@endphp
				<tr class="{{ $diff > 0.001 ? 'table-success' : ($diff < -0.001 ? 'table-danger' : 'table-light') }} font-weight-bold">
					<td>فارق التسوية (عجز / زيادة)</td>
					<td class="text-left">
						@if($diff > 0.001)
							+{{ number_format($diff, 2) }} ج.م (فائض)
						@elseif($diff < -0.001)
							{{ number_format($diff, 2) }} ج.م (عجز)
						@else
							0.00 ج.م (مطابق)
						@endif
					</td>
				</tr>
			</tbody>
		</table>

		<!-- Electronic payments note -->
		<div class="p-2 border bg-light mb-3 text-right">
			<span class="tx-12 font-weight-bold d-block">مدفوعات إلكترونية (مستبعدة من النقدية الفعلية بالدرج):</span>
			<span class="tx-12">فودافون كاش: <strong>{{ number_format($summary['sales']['vodafone_cash'], 2) }} ج.م</strong> | انستا باي: <strong>{{ number_format($summary['sales']['instapay'], 2) }} ج.م</strong></span>
		</div>

		@if($shift->notes)
			<div class="mb-3 text-right">
				<span class="text-muted tx-12 d-block">ملاحظات:</span>
				<p class="border p-2 tx-12">{{ $shift->notes }}</p>
			</div>
		@endif

		<div class="row mt-4 pt-3 border-top text-center">
			<div class="col-6">
				<span class="d-block tx-12 text-muted">توقيع الكاشير المسلّم:</span>
				<div style="height: 50px;"></div>
				<strong>{{ $shift->user->name }}</strong>
			</div>
			<div class="col-6">
				<span class="d-block tx-12 text-muted">توقيع الإدارة المستلمة:</span>
				<div style="height: 50px;"></div>
				<strong>.......................................</strong>
			</div>
		</div>

		<div class="mt-4 text-center no-print">
			<button onclick="window.print()" class="btn btn-primary px-4 font-weight-bold">
				طباعة التقرير
			</button>
			<button onclick="window.close()" class="btn btn-secondary px-3 mr-2">
				إغلاق النافذة
			</button>
		</div>
	</div>

	<script>
		window.addEventListener('load', function() {
			// Auto trigger print dialog
			setTimeout(function() {
				window.print();
			}, 500);
		});
	</script>
</body>
</html>

