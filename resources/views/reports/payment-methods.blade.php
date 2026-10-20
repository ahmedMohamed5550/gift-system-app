@extends('layouts.master')

@section('title', 'تقرير المبيعات حسب طرق الدفع - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary">
				<i class="fas fa-wallet ml-2"></i>تقرير المبيعات حسب طرق الدفع
			</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ مقارنة النقدية بالمحافظ الإلكترونية والتحويلات</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<a href="{{ route('reports.index') }}" class="btn btn-outline-secondary font-weight-bold">
			<i class="fas fa-arrow-right ml-1"></i> العودة لمركز التقارير
		</a>
	</div>
</div>
@endsection

@section('content')

@include('reports.partials.date-filter', ['dateRange' => $dateRange, 'period' => $period])

<!-- Physical Cash vs Electronic Wallets Overview -->
<div class="row row-sm mb-3">
	<!-- Physical Cash Box -->
	<div class="col-xl-6 col-lg-6 col-12 mb-3">
		<div class="card border-success shadow-sm h-100">
			<div class="card-header bg-success-transparent pb-2 d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0 font-weight-bold text-success">
					<i class="fas fa-money-bill-wave ml-1"></i> النقدية الفعلية بالدرج
				</h5>
				<span class="badge badge-success px-3 py-1 font-weight-bold tx-13">
					{{ $physical_cash['percentage'] }}% من إجمالي المبيعات
				</span>
			</div>
			<div class="card-body">
				<div class="row text-center mb-3">
					<div class="col-4">
						<span class="text-muted tx-12 d-block">إجمالي المبيعات:</span>
						<strong class="tx-16 text-dark">{{ number_format($physical_cash['gross_amount'], 2) }} ج.م</strong>
					</div>
					<div class="col-4">
						<span class="text-muted tx-12 d-block">المرتجعات النقدية:</span>
						<strong class="tx-16 text-danger">-{{ number_format($physical_cash['refunds_amount'], 2) }} ج.م</strong>
					</div>
					<div class="col-4">
						<span class="text-muted tx-12 d-block">صافي المحصل:</span>
						<strong class="tx-18 text-success font-weight-bold">{{ number_format($physical_cash['net_amount'], 2) }} ج.م</strong>
					</div>
				</div>
				<div class="alert alert-light border mb-0 tx-12 text-muted">
					<i class="fas fa-info-circle ml-1 text-success"></i>
					<strong>ملاحظة تسوية:</strong> المبالغ النقدية تدخل في رصيد درج الخزينة الفعلي وتخضع للمطابقة اليومية في الورديات.
				</div>
			</div>
		</div>
	</div>

	<!-- Electronic Wallets & Bank Box -->
	<div class="col-xl-6 col-lg-6 col-12 mb-3">
		<div class="card border-info shadow-sm h-100">
			<div class="card-header bg-info-transparent pb-2 d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0 font-weight-bold text-info">
					<i class="fas fa-mobile-alt ml-1"></i> المدفوعات الإلكترونية (محافظ وبنك)
				</h5>
				<span class="badge badge-info px-3 py-1 font-weight-bold tx-13">
					{{ $electronic['percentage'] }}% من إجمالي المبيعات
				</span>
			</div>
			<div class="card-body">
				<div class="row text-center mb-3">
					<div class="col-4">
						<span class="text-muted tx-12 d-block">إجمالي المبيعات:</span>
						<strong class="tx-16 text-dark">{{ number_format($electronic['gross_amount'], 2) }} ج.م</strong>
					</div>
					<div class="col-4">
						<span class="text-muted tx-12 d-block">المرتجعات الإلكترونية:</span>
						<strong class="tx-16 text-danger">-{{ number_format($electronic['refunds_amount'], 2) }} ج.م</strong>
					</div>
					<div class="col-4">
						<span class="text-muted tx-12 d-block">صافي المحصل:</span>
						<strong class="tx-18 text-info font-weight-bold">{{ number_format($electronic['net_amount'], 2) }} ج.م</strong>
					</div>
				</div>
				<div class="alert alert-light border mb-0 tx-12 text-muted">
					<i class="fas fa-info-circle ml-1 text-info"></i>
					<strong>ملاحظة تسوية:</strong> لا تضاف هذه المبالغ لدرج النقدية الفعلي بل تودع مباشرة في المحافظ الإلكترونية والحساب البنكي.
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Three Payment Methods Detailed Cards -->
<div class="row row-sm mb-3">
	@foreach($methods as $methodKey => $methodData)
		<div class="col-xl-4 col-lg-4 col-12 mb-3">
			<div class="card border shadow-sm">
				<div class="card-header pb-2 d-flex justify-content-between align-items-center">
					<h6 class="card-title mb-0 font-weight-bold text-dark">
						@if($methodKey == 'cash')
							<i class="fas fa-money-bill-alt ml-1 text-success"></i>
						@elseif($methodKey == 'vodafone_cash')
							<i class="fas fa-mobile-alt ml-1 text-danger"></i>
						@else
							<i class="fas fa-university ml-1 text-primary"></i>
						@endif
						{{ $methodData['label'] }}
					</h6>
					<span class="badge {{ $methodKey == 'cash' ? 'badge-success' : ($methodKey == 'vodafone_cash' ? 'badge-danger' : 'badge-primary') }} font-weight-bold">
						{{ $methodData['percentage'] }}%
					</span>
				</div>
				<div class="card-body">
					<ul class="list-group list-group-flush pr-0 tx-13">
						<li class="list-group-item d-flex justify-content-between align-items-center px-0">
							<span class="text-muted">عدد الفواتير الناجحة:</span>
							<strong>{{ $methodData['sales_count'] }} فاتورة</strong>
						</li>
						<li class="list-group-item d-flex justify-content-between align-items-center px-0">
							<span class="text-muted">إجمالي المبيعات:</span>
							<strong class="text-dark">{{ number_format($methodData['gross_amount'], 2) }} جنيه</strong>
						</li>
						<li class="list-group-item d-flex justify-content-between align-items-center px-0">
							<span class="text-muted">المرتجعات ({{ $methodData['refunds_count'] }} إشعار):</span>
							<strong class="text-danger">-{{ number_format($methodData['refunds_amount'], 2) }} جنيه</strong>
						</li>
						<li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-light p-2 rounded mt-2">
							<span class="font-weight-bold text-dark">صافي المحصل:</span>
							<strong class="tx-16 text-primary font-weight-bold">{{ number_format($methodData['net_amount'], 2) }} جنيه</strong>
						</li>
					</ul>
				</div>
			</div>
		</div>
	@endforeach
</div>

<!-- Comparison Table -->
<div class="card border shadow-sm">
	<div class="card-header pb-0">
		<h5 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-table ml-1 text-primary"></i> جدول مقارنة طرق السداد
		</h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped table-bordered text-center align-middle mb-0">
				<thead class="thead-light">
					<tr>
						<th>طريقة الدفع</th>
						<th>نوع المعاملة</th>
						<th>عدد الفواتير</th>
						<th>إجمالي المبيعات</th>
						<th>إجمالي المرتجعات</th>
						<th>صافي المبيعات</th>
						<th>نسبة المساهمة</th>
						<th>متوسط المعاملة</th>
					</tr>
				</thead>
				<tbody>
					@foreach($methods as $methodKey => $methodData)
						<tr>
							<td class="font-weight-bold text-dark text-right">
								{{ $methodData['label'] }}
							</td>
							<td>
								@if($methodData['is_cash'])
									<span class="badge badge-success">نقدي (درج الخزينة)</span>
								@else
									<span class="badge badge-info">إلكتروني (محفظة / بنك)</span>
								@endif
							</td>
							<td>{{ $methodData['sales_count'] }}</td>
							<td>{{ number_format($methodData['gross_amount'], 2) }} جنيه</td>
							<td class="text-danger">-{{ number_format($methodData['refunds_amount'], 2) }} جنيه</td>
							<td class="font-weight-bold tx-14 text-primary">{{ number_format($methodData['net_amount'], 2) }} جنيه</td>
							<td>
								<span class="font-weight-bold">{{ $methodData['percentage'] }}%</span>
								<div class="progress progress-xs mt-1">
									<div class="progress-bar {{ $methodKey == 'cash' ? 'bg-success' : ($methodKey == 'vodafone_cash' ? 'bg-danger' : 'bg-primary') }}"
										 style="width: {{ $methodData['percentage'] }}%"></div>
								</div>
							</td>
							<td>
								{{ $methodData['sales_count'] > 0 ? number_format($methodData['net_amount'] / $methodData['sales_count'], 2) . ' جنيه' : '-' }}
							</td>
						</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr class="bg-light font-weight-bold tx-14">
						<td colspan="2" class="text-left">الإجمالي الكلي:</td>
						<td>{{ array_sum(array_column($methods, 'sales_count')) }}</td>
						<td>{{ number_format($totals['gross'], 2) }} جنيه</td>
						<td class="text-danger">-{{ number_format($totals['refunds'], 2) }} جنيه</td>
						<td class="text-primary tx-16">{{ number_format($totals['net'], 2) }} جنيه</td>
						<td>100%</td>
						<td>-</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

@endsection

