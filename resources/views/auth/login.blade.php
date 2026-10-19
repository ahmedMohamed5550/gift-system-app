@extends('layouts.master2')

@section('title', 'تسجيل الدخول - نظام إدارة متجر الهدايا')

@section('css')
<!-- Sidemenu-responsive-tabs css -->
<link href="{{ URL::asset('assets/plugins/sidemenu-responsive-tabs/css/sidemenu-responsive-tabs.css') }}" rel="stylesheet">
<style>
	.sign-favicon {
		height: 40px;
	}
	.login-system-title {
		font-size: 22px;
		font-weight: 700;
		color: #0162e8;
		margin: auto 0;
	}
</style>
@endsection

@section('content')
<div class="container-fluid">
	<div class="row no-gutter">
		<!-- The image half -->
		<div class="col-md-6 col-lg-6 col-xl-7 d-none d-md-flex bg-primary-transparent">
			<div class="row wd-100p mx-auto text-center">
				<div class="col-md-12 col-lg-12 col-xl-12 my-auto mx-auto wd-100p">
					<img src="{{ URL::asset('assets/img/media/gift_house_login_img.jpg') }}" class="my-auto ht-xl-80p wd-md-100p wd-xl-80p mx-auto" alt="نظام إدارة متجر الهدايا">
				</div>
			</div>
		</div>

		<!-- The content half -->
		<div class="col-md-6 col-lg-6 col-xl-5 bg-white">
			<div class="login d-flex align-items-center py-2">
				<div class="container p-0">
					<div class="row">
						<div class="col-md-10 col-lg-10 col-xl-9 mx-auto">
							<div class="card-sigin">
								<div class="mb-4 d-flex align-items-center">
									<a href="{{ route('login') }}" class="d-flex align-items-center">
										{{-- <img src="{{URL::asset('assets/img/brand/mini_logo.jpg')}}" class="sign-favicon ml-2" alt="شعار متجر بيت الهدايا"> --}}
										{{-- <h1 class="login-system-title">متجر بيت <span>الهدايا</span></h1> --}}
									</a>
								</div>

								<div class="card-sigin">
									<div class="main-signup-header text-right">
										<h2 class="text-primary font-weight-bold">مرحباً بك مجدداً!</h2>
										<h5 class="font-weight-normal mb-4 text-muted tx-14">يرجى تسجيل الدخول للوصول إلى نظام نقطة البيع .</h5>

										{{-- Session Status / Alerts --}}
										@if (session('status'))
											<div class="alert alert-success alert-dismissible fade show" role="alert">
												<strong>{{ session('status') }}</strong>
												<button type="button" class="close" data-dismiss="alert" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>
										@endif

										@if ($errors->has('inactive'))
											<div class="alert alert-danger" role="alert">
												<i class="fas fa-ban ml-1"></i> {{ $errors->first('inactive') }}
											</div>
										@endif

										<form method="POST" action="{{ route('login') }}">
											@csrf

											{{-- Username --}}
											<div class="form-group text-right">
												<label for="username" class="font-weight-semibold">اسم المستخدم</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
													</div>
													<input id="username"
														   type="text"
														   name="username"
														   value="{{ old('username') }}"
														   class="form-control @error('username') is-invalid @enderror"
														   placeholder="أدخل اسم المستخدم"
														   required
														   autofocus
														   autocomplete="username">
												</div>
												@error('username')
													<span class="invalid-feedback d-block text-right mt-1" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror
											</div>

											{{-- Password --}}
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

											{{-- Remember Me --}}
											<div class="form-group d-flex justify-content-between align-items-center mb-4">
												<div class="custom-control custom-checkbox">
													<input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
													<label class="custom-control-label tx-13 text-muted" for="remember">تذكرني على هذا الجهاز</label>
												</div>
											</div>

											{{-- Submit Button --}}
											<button type="submit" class="btn btn-main-primary btn-block tx-16 font-weight-bold py-2">
												<i class="fas fa-sign-in-alt ml-1"></i> تسجيل الدخول
											</button>
										</form>

										<div class="main-signin-footer mt-5 text-center text-muted tx-13">
											<p class="mb-0">نظام إدارة نقطة البيع والمخزون &copy; 2026 Ahmed Abd El-Baky - جميع الحقوق محفوظة</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div><!-- End -->
			</div>
		</div><!-- End -->
	</div>
</div>
@endsection

@section('js')
@endsection
