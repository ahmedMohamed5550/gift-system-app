<!-- Title -->
<title> @yield('title', 'نظام إدارة متجر بيت الهدايا - Gift House') </title>
<!-- Favicon -->
<link rel="icon" href="{{URL::asset('assets/img/brand/mini_logo.jpg')}}" type="image/x-icon"/>
<!-- Icons css -->
<link href="{{URL::asset('assets/css/icons.css')}}" rel="stylesheet">
<!--  Custom Scroll bar-->
<link href="{{URL::asset('assets/plugins/mscrollbar/jquery.mCustomScrollbar.css')}}" rel="stylesheet"/>
<!--  Sidebar css -->
<link href="{{URL::asset('assets/plugins/sidebar/sidebar.css')}}" rel="stylesheet">
<!-- Sidemenu css -->
<link rel="stylesheet" href="{{URL::asset('assets/css-rtl/sidemenu.css')}}">
@yield('css')
<!--- Style css -->
<link href="{{URL::asset('assets/css-rtl/style.css')}}" rel="stylesheet">
<!--- Dark-mode css -->
<link href="{{URL::asset('assets/css-rtl/style-dark.css')}}" rel="stylesheet">
<!---Skinmodes css-->
<link href="{{URL::asset('assets/css-rtl/skin-modes.css')}}" rel="stylesheet">

<!--- Print CSS --->
<style>
@media print {
	.app-sidebar,
	.main-header,
	.main-header-right,
	.app-sidebar__toggle,
	.breadcrumb-header .right-content,
	.btn,
	.no-print,
	#global-loader {
		display: none !important;
	}

	.main-content {
		margin-right: 0 !important;
		padding-top: 0 !important;
	}

	.print-only-header {
		display: block !important;
		text-align: center !important;
		margin-bottom: 20px !important;
		padding-bottom: 12px !important;
		border-bottom: 2px dashed #bbb !important;
	}

	.print-only-header img {
		max-height: 65px !important;
		width: auto !important;
		display: inline-block !important;
	}
}
</style>
