@extends('layouts.master')

@section('title', 'سجل مرتجعات المبيعات - متجر بيت الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between mb-3">
	<div class="my-auto">
		<div class="d-flex align-items-center">
			<h4 class="content-title mb-0 my-auto text-primary"><i class="fas fa-undo-alt ml-2"></i>مرتجعات المبيعات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ إدارة واسترجاع فواتير المبيعات</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="pr-1 mb-3 mb-xl-0">
			<a href="{{ route('returns.create') }}" class="btn btn-warning font-weight-bold shadow-sm">
				<i class="fas fa-plus-circle ml-1"></i> إرجاع فاتورة جديدة
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

<!-- Returns KPI Cards -->
<div class="row row-sm mb-2">
	<!-- Total Approved Returns -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-primary-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">إجمالي المرتجعات المعتمدة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($metrics['total_refunded'], 2) }} جنيه مصري</h3>
							<p class="mb-0 tx-12 text-white op-7">{{ $metrics['approved_count'] }} إشعار مرتجع معتمد</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-undo-alt text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Cash Refunds (Physical Drawer) -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-danger-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">استرداد نقدي (من الدرج)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($metrics['cash_refunded'], 2) }} جنيه مصري</h3>
							<p class="mb-0 tx-12 text-white op-7">يخصم من نقدية الدرج الفعلي</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-money-bill-wave text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Electronic Refunds -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-info-gradient">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">استرداد إلكتروني (محافظ / بنك)</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ number_format($metrics['electronic_refunded'], 2) }} جنيه مصري</h3>
							<p class="mb-0 tx-12 text-white op-7">فودافون كاش / انستا باي (لا يخصم من الدرج)</p>
						</div>
						<span class="float-right my-auto mr-auto">
							<i class="fas fa-mobile-alt text-white tx-24"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Cancelled Returns -->
	<div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
		<div class="card overflow-hidden sales-card bg-secondary">
			<div class="pl-3 pt-3 pr-3 pb-2 pt-0">
				<div>
					<h6 class="mb-3 tx-12 text-white">المرتجعات الملغاة</h6>
				</div>
				<div class="pb-0 mt-0">
					<div class="d-flex">
						<div>
							<h3 class="tx-20 font-weight-bold mb-1 text-white">{{ $metrics['cancelled_count'] }} إشعار ملغى</h3>
							<p class="mb-0 tx-12 text-white op-7">تم التراجع عنها وخصم مخزونها</p>
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
	<div class="card-header pb-0">
		<h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-filter ml-1 text-primary"></i> تصفية وبحث في سجل المرتجعات</h6>
	</div>
	<div class="card-body">
		<form method="GET" action="{{ route('returns.index') }}">
			<div class="row row-sm">
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">بحث برقم المرتجع أو الفاتورة:</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-search"></i></span>
						</div>
						<input type="text" name="search" class="form-control" placeholder="مثال: RET-... أو INV-..." value="{{ request('search') }}">
					</div>
				</div>

				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">طريقة الاسترداد:</label>
					<select name="refund_method" class="form-control select2">
						<option value="">-- كافة الطرق --</option>
						<option value="cash" {{ request('refund_method') == 'cash' ? 'selected' : '' }}>نقداً (كاش)</option>
						<option value="vodafone_cash" {{ request('refund_method') == 'vodafone_cash' ? 'selected' : '' }}>فودافون كاش</option>
						<option value="instapay" {{ request('refund_method') == 'instapay' ? 'selected' : '' }}>انستا باي</option>
					</select>
				</div>

				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">الحالة:</label>
					<select name="status" class="form-control select2">
						<option value="">-- كافة الحالات --</option>
						<option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد / مكتمل</option>
						<option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغى</option>
					</select>
				</div>

				<div class="col-md-2 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">المسؤول (الكاشير):</label>
					<select name="user_id" class="form-control select2">
						<option value="">-- الجميع --</option>
						@foreach($users as $user)
							<option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
								{{ $user->name }}
							</option>
						@endforeach
					</select>
				</div>

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
					<a href="{{ route('returns.index') }}" class="btn btn-outline-secondary font-weight-bold">
						<i class="fas fa-times ml-1"></i> إلغاء التصفية
					</a>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Table Card -->
<div class="card">
	<div class="card-header pb-0 d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0 font-weight-bold text-dark"><i class="fas fa-list ml-1 text-primary"></i> قائمة إشعارات المرتجعات</h5>
		<span class="text-muted tx-13">إجمالي السجلات: {{ $returns->total() }}</span>
	</div>
	<div class="card-body">
		@if($returns->isEmpty())
			<div class="text-center py-5">
				<div class="mb-3">
					<i class="fas fa-undo-alt text-muted fa-4x"></i>
				</div>
				<h5 class="text-muted font-weight-bold">لا توجد إشعارات مرتجعات مسجلة حتى الآن</h5>
				<p class="text-muted tx-13">يمكنك تسجيل أول عملية مرتجع لفاتورة مبيعات باستخدام الزر أدناه.</p>
				<a href="{{ route('returns.create') }}" class="btn btn-warning mt-2 font-weight-bold">
					<i class="fas fa-plus-circle ml-1"></i> إرجاع فاتورة جديدة
				</a>
			</div>
		@else
			<div class="table-responsive">
				<table class="table table-hover table-striped text-center align-middle mb-0">
					<thead class="thead-light">
						<tr>
							<th>#</th>
							<th>رقم المرتجع</th>
							<th>الفاتورة الأصلية</th>
							<th>تاريخ المرتجع</th>
							<th>المسؤول</th>
							<th>طريقة الاسترداد</th>
							<th>المبلغ المسترد</th>
							<th>الحالة</th>
							<th>الإجراءات</th>
						</tr>
					</thead>
					<tbody>
						@foreach($returns as $return)
							<tr class="{{ $return->isCancelled() ? 'table-danger' : '' }}">
								<td>{{ $loop->iteration + ($returns->currentPage() - 1) * $returns->perPage() }}</td>
								<td class="font-weight-bold text-primary">
									<a href="{{ route('returns.show', $return) }}" class="text-primary">
										{{ $return->return_number }}
									</a>
								</td>
								<td class="font-weight-bold">
									@if($return->sale)
										<a href="{{ route('sales.show', $return->sale) }}" class="text-dark" title="عرض الفاتورة الأصلية">
											#{{ $return->sale->invoice_number }}
										</a>
									@else
										<span class="text-muted">-</span>
									@endif
								</td>
								<td class="tx-12">
									<div>{{ $return->created_at->format('Y-m-d') }}</div>
									<div class="text-muted tx-11">{{ $return->created_at->format('g:i A') }}</div>
								</td>
								<td class="font-weight-semibold">
									{{ $return->user?->name ?? '-' }}
								</td>
								<td>
									<span class="badge {{ $return->refund_method_badge_class }} px-2 py-1 tx-12 font-weight-bold">
										{{ $return->refund_method_label }}
									</span>
								</td>
								<td class="font-weight-bold text-danger">
									-{{ number_format($return->total_amount, 2) }} جنيه مصري
								</td>
								<td>
									<span class="badge {{ $return->status_badge_class }} px-2 py-1 tx-12 font-weight-bold">
										{{ $return->status_label }}
									</span>
								</td>
								<td>
									<div class="btn-group" role="group">
										<a href="{{ route('returns.show', $return) }}" class="btn btn-sm btn-info" title="عرض التفاصيل">
											<i class="fas fa-eye"></i>
										</a>
										<a href="{{ route('returns.print', $return) }}" target="_blank" class="btn btn-sm btn-primary" title="طباعة الإشعار">
											<i class="fas fa-print"></i>
										</a>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			<div class="mt-3 d-flex justify-content-between align-items-center">
				<span class="text-muted tx-12">
					عرض السجلات من {{ $returns->firstItem() }} إلى {{ $returns->lastItem() }} من إجمالي {{ $returns->total() }}
				</span>
				<div>
					{{ $returns->links() }}
				</div>
			</div>
		@endif
	</div>
</div>

@endsection

