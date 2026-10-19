@extends('layouts.master')

@section('title', 'تفاصيل الملاحظة #' . $note->id . ' - متجر الهدايا')

@section('page-header')
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto">الملاحظات والتنبيهات</h4>
			<span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل الملاحظة #{{ $note->id }}</span>
		</div>
	</div>
	<div class="d-flex my-xl-auto right-content">
		<div class="mb-3 mb-xl-0 ml-2">
			<a href="{{ route('notes.edit', $note) }}" class="btn btn-info font-weight-bold">
				<i class="fas fa-edit ml-1"></i> تعديل الملاحظة
			</a>
		</div>
		<div class="mb-3 mb-xl-0">
			<a href="{{ route('notes.index') }}" class="btn btn-secondary font-weight-bold">
				<i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
			</a>
		</div>
	</div>
</div>
@endsection

@section('content')
<!-- Alerts -->
@if(session('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-check-circle ml-1"></i> تم بنجاح!</strong> {{ session('success') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(session('info'))
	<div class="alert alert-info alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-info-circle ml-1"></i> إشعار:</strong> {{ session('info') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<!-- Overdue Alert Banner -->
@if($note->isOverdue())
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong><i class="fas fa-exclamation-triangle ml-1"></i> تنبيه فوري:</strong>
		موعد متابعة هذه الملاحظة قد تجاوز الوقت المحدد (كان مقرراً في {{ $note->due_at->format('Y-m-d h:i A') }}). يرجى اتخاذ الإجراء اللازم وإكمالها أو جدولتها مجدداً.
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

<div class="row row-sm">
	<!-- Main Details Card -->
	<div class="col-lg-8">
		<div class="card custom-card">
			<div class="card-header pb-0 d-flex justify-content-between align-items-center">
				<div>
					<span class="badge badge-pill {{ $note->status_badge_class }} px-3 py-1 font-weight-bold mb-2">
						{{ $note->status_arabic }}
					</span>
					<h3 class="card-title font-weight-bold tx-18 mb-1">
						<i class="fas fa-sticky-note ml-1 text-primary"></i> {{ $note->title }}
					</h3>
				</div>
				<div class="text-left text-muted tx-12">
					<div><strong>رقم القيد:</strong> #{{ $note->id }}</div>
					<div>{{ $note->created_at->format('Y-m-d h:i A') }}</div>
				</div>
			</div>

			<div class="card-body">
				<h6 class="font-weight-bold text-dark mb-2">نص وتفاصيل الملاحظة:</h6>
				<div class="p-3 bg-light rounded" style="font-size: 15px; line-height: 1.8; white-space: pre-line; word-break: break-word;">
					{!! nl2br(e($note->content)) !!}
				</div>

				<!-- Quick State-Changing Actions -->
				<div class="mt-4 pt-3 border-top d-flex flex-wrap" style="gap: 8px;">
					@if($note->isOpen())
						<!-- Complete -->
						<form action="{{ route('notes.complete', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('هل تريد بالتأكيد تحديد هذه الملاحظة كمكتملة؟')">
							@csrf
							@method('PATCH')
							<button type="submit" class="btn btn-success font-weight-bold">
								<i class="fas fa-check ml-1"></i> تحديد كمكتملة ومنجزة
							</button>
						</form>

						<!-- Cancel -->
						<form action="{{ route('notes.cancel', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('هل تريد بالتأكيد إلغاء هذه الملاحظة؟')">
							@csrf
							@method('PATCH')
							<button type="submit" class="btn btn-warning font-weight-bold">
								<i class="fas fa-ban ml-1"></i> إلغاء الملاحظة
							</button>
						</form>
					@endif

					<a href="{{ route('notes.edit', $note) }}" class="btn btn-info font-weight-bold">
						<i class="fas fa-edit ml-1"></i> تعديل البيانات
					</a>

					<!-- Safe Delete / Archival -->
					<form action="{{ route('notes.destroy', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من أرشفة هذه الملاحظة ونقلها إلى المحذوفات؟')">
						@csrf
						@method('DELETE')
						<button type="submit" class="btn btn-outline-danger font-weight-bold">
							<i class="fas fa-trash-alt ml-1"></i> أرشفة / حذف آمن
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Sidebar Meta Information -->
	<div class="col-lg-4">
		<!-- Amount Card if present -->
		@if($note->amount !== null)
			<div class="card custom-card bg-primary-transparent border-primary">
				<div class="card-body">
					<h6 class="font-weight-bold text-primary mb-1">
						<i class="fas fa-money-bill-wave ml-1"></i> المبلغ المالي المرتبط:
					</h6>
					<h3 class="font-weight-bold text-dark mb-0 mt-2">
						{{ number_format($note->amount, 2) }} <span class="tx-14 text-muted">جنيه مصري</span>
					</h3>
					<small class="text-muted d-block mt-1">مبلغ معلق أو مستحق للمتابعة</small>
				</div>
			</div>
		@endif

		<!-- Follow-up Schedule Card -->
		<div class="card custom-card">
			<div class="card-header pb-0">
				<h6 class="card-title mb-1 font-weight-bold">
					<i class="fas fa-calendar-alt ml-1 text-info"></i> جدول وموعد المتابعة
				</h6>
			</div>
			<div class="card-body">
				@if($note->due_at)
					<div class="mb-2">
						<span class="text-muted tx-12 d-block">تاريخ المتابعة:</span>
						<span class="font-weight-bold tx-15 text-dark">{{ $note->due_at->format('Y-m-d') }}</span>
					</div>
					<div class="mb-2">
						<span class="text-muted tx-12 d-block">وقت المتابعة:</span>
						<span class="font-weight-bold tx-15 text-dark">{{ $note->due_at->format('h:i A') }}</span>
					</div>
					<div class="mt-2">
						@if($note->isOverdue())
							<span class="badge badge-danger font-weight-bold px-2 py-1">
								<i class="fas fa-clock ml-1"></i> متأخرة منذ {{ $note->due_at->diffForHumans(null, true) }}
							</span>
						@elseif($note->isDueToday())
							<span class="badge badge-warning font-weight-bold px-2 py-1">
								<i class="fas fa-bell ml-1"></i> موعد المتابعة اليوم ({{ $note->due_at->diffForHumans() }})
							</span>
						@else
							<span class="badge badge-light-primary font-weight-bold px-2 py-1">
								<i class="fas fa-calendar-check ml-1"></i> متبقي {{ $note->due_at->diffForHumans(null, true) }}
							</span>
						@endif
					</div>
				@else
					<div class="text-muted text-center py-3">
						<i class="fas fa-calendar-times tx-24 text-muted d-block mb-1"></i>
						لم يتم تعيين موعد متابعة محدد لهذه الملاحظة.
					</div>
				@endif
			</div>
		</div>

		<!-- Creator & Audit Info Card -->
		<div class="card custom-card">
			<div class="card-header pb-0">
				<h6 class="card-title mb-1 font-weight-bold">
					<i class="fas fa-user-shield ml-1 text-secondary"></i> بيانات التوثيق والإنشاء
				</h6>
			</div>
			<div class="card-body">
				<div class="d-flex align-items-center mb-3">
					<div class="avatar avatar-md brround bg-primary-transparent text-primary ml-2 font-weight-bold">
						{{ mb_substr($note->user->name ?? 'م', 0, 1) }}
					</div>
					<div>
						<h6 class="mb-0 font-weight-bold">{{ $note->user->name ?? '-' }}</h6>
						<span class="badge badge-light tx-11">{{ $note->user->role_arabic ?? '' }}</span>
					</div>
				</div>

				<ul class="list-unstyled mb-0 tx-13" style="line-height: 2;">
					<li>
						<strong>تاريخ الإنشاء:</strong> {{ $note->created_at->format('Y-m-d') }}
					</li>
					<li>
						<strong>وقت الإنشاء:</strong> {{ $note->created_at->format('h:i:s A') }}
					</li>
					<li>
						<strong>منذ:</strong> {{ $note->created_at->diffForHumans() }}
					</li>
					@if($note->updated_at->ne($note->created_at))
						<li class="border-top pt-2 mt-2">
							<strong>آخر تحديث:</strong> {{ $note->updated_at->diffForHumans() }}
						</li>
					@endif
				</ul>
			</div>
		</div>
	</div>
</div>
@endsection

