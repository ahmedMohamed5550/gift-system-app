@extends('layouts.master2')
@section('css')
<!--- Internal Fontawesome css-->
<link href="{{URL::asset('assets/plugins/fontawesome-free/css/all.min.css')}}" rel="stylesheet">
<!---Ionicons css-->
<link href="{{URL::asset('assets/plugins/ionicons/css/ionicons.min.css')}}" rel="stylesheet">
<!---Internal Typicons css-->
<link href="{{URL::asset('assets/plugins/typicons.font/typicons.css')}}" rel="stylesheet">
<!---Internal Feather css-->
<link href="{{URL::asset('assets/plugins/feather/feather.css')}}" rel="stylesheet">
<!---Internal Falg-icons css-->
<link href="{{URL::asset('assets/plugins/flag-icon-css/css/flag-icon.min.css')}}" rel="stylesheet">
@endsection
@section('content')
		<!-- Main-error-wrapper -->
		<div class="main-error-wrapper page page-h text-center">
			<h1 class="text-danger tx-80 mb-3 font-weight-bold">403</h1>
			<h2 class="tx-24 mb-2">عذراً! لا تملك الصلاحية للوصول إلى هذه الصفحة أو تنفيذ هذا الإجراء</h2>
			<h6 class="text-muted mb-4">{{ $exception->getMessage() ?: 'هذا القسم أو الإجراء مخصص لمالك النظام فقط ولا يقع ضمن نطاق صلاحيات حسابك الحالي.' }}</h6>
			<div>
				<a class="btn btn-primary" href="{{ auth()->check() && auth()->user()->isOwner() ? route('dashboard') : (auth()->check() ? route('pos.index') : route('login')) }}">
					<i class="fas fa-home ml-1"></i> العودة للرئيسية
				</a>
			</div>
		</div>
		<!-- /Main-error-wrapper -->
@endsection
@section('js')
@endsection

