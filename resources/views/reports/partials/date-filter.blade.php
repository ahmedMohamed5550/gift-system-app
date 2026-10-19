@php
	$dateRange = $dateRange ?? $date_range ?? [];
	$period = $period ?? ($dateRange['period'] ?? 'this_month');
@endphp
<div class="card mb-3 shadow-sm border">
	<div class="card-header pb-2 bg-light d-flex justify-content-between align-items-center">
		<h6 class="card-title mb-0 font-weight-bold text-dark">
			<i class="fas fa-filter ml-1 text-primary"></i> تصفية التقرير حسب الفترة الزمنية
		</h6>
		<span class="badge badge-primary px-3 py-1 font-weight-bold tx-12">
			{{ $dateRange['label'] ?? '' }}
		</span>
	</div>
	<div class="card-body">
		<form method="GET" action="{{ url()->current() }}" id="reportFilterForm">
			<!-- Preserve any extra query parameters like category, user, status -->
			@foreach(request()->except(['period', 'date_from', 'date_to', 'page']) as $key => $val)
				@if(is_array($val))
					@foreach($val as $subVal)
						<input type="hidden" name="{{ $key }}[]" value="{{ $subVal }}">
					@endforeach
				@else
					<input type="hidden" name="{{ $key }}" value="{{ $val }}">
				@endif
			@endforeach

			<div class="row row-sm align-items-end">
				<!-- Period Preset -->
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="form-label tx-12 font-weight-semibold">الفترة الزمنية السريعة:</label>
					<select name="period" class="form-control select2" id="filterPeriodSelect" onchange="toggleCustomDates(this.value)">
						<option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>اليوم</option>
						<option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>أمس</option>
						<option value="this_week" {{ ($period ?? '') == 'this_week' ? 'selected' : '' }}>الأسبوع الحالي</option>
						<option value="this_month" {{ ($period ?? '') == 'this_month' ? 'selected' : '' }}>الشهر الحالي</option>
						<option value="last_month" {{ ($period ?? '') == 'last_month' ? 'selected' : '' }}>الشهر الماضي</option>
						<option value="this_year" {{ ($period ?? '') == 'this_year' ? 'selected' : '' }}>العام الحالي</option>
						<option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>فترة مخصصة (تحديد التواريخ)</option>
					</select>
				</div>

				<!-- Custom Date From -->
				<div class="col-md-3 col-sm-6 mb-2 custom-date-col" style="{{ ($period ?? '') == 'custom' ? '' : 'display:none;' }}">
					<label class="form-label tx-12 font-weight-semibold">من تاريخ:</label>
					<input type="date" name="date_from" class="form-control" value="{{ $dateRange['date_from'] ?? request('date_from') }}">
				</div>

				<!-- Custom Date To -->
				<div class="col-md-3 col-sm-6 mb-2 custom-date-col" style="{{ ($period ?? '') == 'custom' ? '' : 'display:none;' }}">
					<label class="form-label tx-12 font-weight-semibold">إلى تاريخ:</label>
					<input type="date" name="date_to" class="form-control" value="{{ $dateRange['date_to'] ?? request('date_to') }}">
				</div>

				<!-- Action Buttons -->
				<div class="col-md-3 col-sm-6 mb-2">
					<button type="submit" class="btn btn-primary font-weight-bold btn-block">
						<i class="fas fa-search ml-1"></i> عرض التقرير
					</button>
				</div>

				<div class="col-md-auto mb-2 mr-auto">
					<button type="button" onclick="window.print()" class="btn btn-outline-secondary font-weight-bold">
						<i class="fas fa-print ml-1"></i> طباعة
					</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
function toggleCustomDates(val) {
    const customCols = document.querySelectorAll('.custom-date-col');
    customCols.forEach(col => {
        col.style.display = (val === 'custom') ? '' : 'none';
    });
}
</script>

