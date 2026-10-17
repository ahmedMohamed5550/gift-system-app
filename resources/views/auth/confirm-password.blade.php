@extends('layouts.master2')

@section('title', 'تأكيد كلمة المرور - نظام إدارة متجر الهدايا')

@section('css')
<link href="{{ URL::asset('assets/plugins/sidemenu-responsive-tabs/css/sidemenu-responsive-tabs.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid">
	<div class="row no-gutter justify-content-center">
		<div class="col-md-6 col-lg-5 col-xl-4 bg-white my-5 p-4 rounded shadow-sm">
			<div class="main-signup-header text-right">
				<div class="text-center mb-4">
					<img src="{{ URL::asset('assets/img/brand/favicon.png') }}" class="ht-40 mb-2" alt="متجر الهدايا">
					<h3 class="text-primary font-weight-bold">منطقة آمنة</h3>
					<p class="text-muted tx-14">يرجى تأكيد كلمة المرور الخاصة بك للمتابعة.</p>
				</div>

				<form method="POST" action="{{ route('password.confirm') }}">
					@csrf

					<div class="form-group text-right">
						<label for="password" class="font-weight-semibold">كلمة المرور</label>
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
							</div>
							<input id="password"
								   type="password"
								   name="password"
								   class="form-control @error('password') is-invalid @enderror"
								   placeholder="أدخل كلمة المرور"
								   required
								   autocomplete="current-password">
						</div>
						@error('password')
							<span class="invalid-feedback d-block text-right mt-1" role="alert">
								<strong>{{ $message }}</strong>
							</span>
						@enderror
					</div>

					<button type="submit" class="btn btn-main-primary btn-block font-weight-bold py-2 mt-4">
						<i class="fas fa-check-circle ml-1"></i> تأكيد
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
