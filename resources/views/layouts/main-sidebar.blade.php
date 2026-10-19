<!-- main-sidebar -->
		<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
		<aside class="app-sidebar sidebar-scroll">
			<div class="main-sidebar-header active">
				<a class="desktop-logo logo-light active" href="{{ auth()->check() && auth()->user()->isOwner() ? route('dashboard') : route('pos.index') }}"><img src="{{URL::asset('assets/img/brand/gift_house_logo.jpg')}}" class="main-logo" alt="logo"></a>
				<a class="desktop-logo logo-dark active" href="{{ auth()->check() && auth()->user()->isOwner() ? route('dashboard') : route('pos.index') }}"><img src="{{URL::asset('assets/img/brand/mini_logo.jpg')}}" class="main-logo dark-theme" alt="logo"></a>
				<a class="logo-icon mobile-logo icon-light active" href="{{ auth()->check() && auth()->user()->isOwner() ? route('dashboard') : route('pos.index') }}"><img src="{{URL::asset('assets/img/brand/mini_logo.jpg')}}" class="logo-icon" alt="logo"></a>
				<a class="logo-icon mobile-logo icon-dark active" href="{{ auth()->check() && auth()->user()->isOwner() ? route('dashboard') : route('pos.index') }}"><img src="{{URL::asset('assets/img/brand/mini_logo.jpg')}}" class="logo-icon dark-theme" alt="logo"></a>
			</div>
			<div class="main-sidemenu">
				<div class="app-sidebar__user clearfix">
					<div class="dropdown user-pro-body">
						<div class="">
							<img alt="user-img" class="avatar avatar-xl brround" src="{{URL::asset('assets/img/media/Unknown_person.jpg')}}"><span class="avatar-status profile-status bg-green"></span>
						</div>
						<div class="user-info">
							<h4 class="font-weight-semibold mt-3 mb-0">{{ Auth::check() ? Auth::user()->name : 'المستخدم' }}</h4>
							<span class="mb-0 text-muted">{{ Auth::check() ? Auth::user()->role_arabic : '' }}</span>
						</div>
					</div>
				</div>
				<ul class="side-menu">
					@if(auth()->check() && auth()->user()->isOwner())
					<li class="side-item side-item-category">الرئيسية</li>
					<li class="slide">
						<a class="side-menu__item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5h4v6H5zm10 8h4v6h-4zM5 17h4v2H5zM15 5h4v2h-4z" opacity=".3"/><path d="M3 13h8V3H3v10zm2-8h4v6H5V5zm8 16h8V11h-8v10zm2-8h4v6h-4v-6zM13 3v6h8V3h-8zm6 4h-4V5h4v2zM3 21h8v-6H3v6zm2-4h4v2H5v-2z"/></svg>
							<span class="side-menu__label">لوحة التحكم</span>
						</a>
					</li>
					@endif

					<li class="side-item side-item-category">إدارة البيانات الأساسية</li>

					<!-- Products -->
					<li class="slide {{ request()->routeIs('products.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('products.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M3.31 11l2.2 8.01L18.5 19l2.2-8H3.31zM12 17c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" opacity=".3"/><path d="M22 9h-4.79l-4.38-6.56c-.19-.28-.51-.42-.83-.42s-.64.14-.83.43L6.79 9H2c-.55 0-1 .45-1 1 0 .09.01.18.04.27l2.54 9.27c.23.84 1 1.46 1.92 1.46h13c.92 0 1.69-.62 1.93-1.46l2.54-9.27L23 10c0-.55-.45-1-1-1z"/></svg>
							<span class="side-menu__label">المنتجات</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('products.index') ? 'active' : '' }}" href="{{ route('products.index') }}">سجل المنتجات</a></li>
							<li><a class="slide-item {{ request()->routeIs('products.create') ? 'active' : '' }}" href="{{ route('products.create') }}">إضافة منتج جديد</a></li>
						</ul>
					</li>

					<!-- Inventory & Stock Management -->
					<li class="slide {{ request()->routeIs('inventory.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('inventory.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 4h16v4H4z" opacity=".3"/><path d="M20 2H4c-1.1 0-2 .9-2 2v4c0 .8.5 1.5 1.2 1.8V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V9.8c.7-.3 1.2-1 1.2-1.8V4c0-1.1-.9-2-2-2zm0 6H4V4h16v4zm-2 11H6V10h12v9zm-7-7h2v5h-2z"/></svg>
							<span class="side-menu__label">المخزون والجرد</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('inventory.index') ? 'active' : '' }}" href="{{ route('inventory.index') }}">مراقبة المخزون والنواقص</a></li>
							<li><a class="slide-item {{ request()->routeIs('inventory.movements') ? 'active' : '' }}" href="{{ route('inventory.movements') }}">سجل حركات المخزون</a></li>
							<li><a class="slide-item {{ request()->routeIs('inventory.adjustments.create') ? 'active' : '' }}" href="{{ route('inventory.adjustments.create') }}">تسوية جردية جديدة</a></li>
						</ul>
					</li>

					<!-- Categories -->
					<li class="slide {{ request()->routeIs('categories.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('categories.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 6h16v12H4z" opacity=".3"/><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V6h16v12z"/></svg>
							<span class="side-menu__label">الأقسام الرئيسية</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('categories.index') ? 'active' : '' }}" href="{{ route('categories.index') }}">قائمة الأقسام</a></li>
							<li><a class="slide-item {{ request()->routeIs('categories.create') ? 'active' : '' }}" href="{{ route('categories.create') }}">إضافة قسم رئيسي</a></li>
						</ul>
					</li>

					<!-- Subcategories -->
					<li class="slide {{ request()->routeIs('subcategories.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('subcategories.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 4c-4.42 0-8 3.58-8 8s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8zm3.5 4c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5zm-7 0c.83 0 1.5.67 1.5 1.5S9.33 11 8.5 11 7 10.33 7 9.5 7.67 8 8.5 8zm3.5 9.5c-2.33 0-4.32-1.45-5.12-3.5h1.67c.7 1.19 1.97 2 3.45 2s2.76-.81 3.45-2h1.67c-.8 2.05-2.79 3.5-5.12 3.5z" opacity=".3"/></svg>
							<span class="side-menu__label">الأقسام الفرعية</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('subcategories.index') ? 'active' : '' }}" href="{{ route('subcategories.index') }}">قائمة الأقسام الفرعية</a></li>
							<li><a class="slide-item {{ request()->routeIs('subcategories.create') ? 'active' : '' }}" href="{{ route('subcategories.create') }}">إضافة قسم فرعي</a></li>
						</ul>
					</li>

					<!-- Users Management -->
					@if(auth()->check() && auth()->user()->isOwner())
					<li class="slide {{ request()->routeIs('users.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('users.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8c.28 0 .5-.22.5-.5 0-.16-.08-.28-.14-.35-.41-.46-.63-1.05-.63-1.65 0-1.38 1.12-2.5 2.5-2.5H16c2.21 0 4-1.79 4-4 0-3.86-3.59-7-8-7z" opacity=".3"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
							<span class="side-menu__label">إدارة المستخدمين</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">قائمة المستخدمين</a></li>
							<li><a class="slide-item {{ request()->routeIs('users.create') ? 'active' : '' }}" href="{{ route('users.create') }}">إضافة مستخدم جديد</a></li>
						</ul>
					</li>
					@endif

					<li class="side-item side-item-category">المبيعات والمالية</li>

					<!-- POS Terminal -->
					<li class="slide {{ request()->routeIs('pos.*') ? 'active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 17h16v2H4z" opacity=".3"/><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 15H4v-2h16v2zm0-5H4V8h16v6z"/></svg>
							<span class="side-menu__label">نقطة البيع</span>
							<span class="badge badge-success side-badge">كاشير</span>
						</a>
					</li>

					<!-- Sales & Invoices -->
					<li class="slide {{ request()->routeIs('sales.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('sales.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M7 15h10v2H7zm0-4h10v2H7zm0-4h10v2H7z" opacity=".3"/><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/></svg>
							<span class="side-menu__label">فواتير المبيعات</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('sales.index') ? 'active' : '' }}" href="{{ route('sales.index') }}">سجل الفواتير</a></li>
							<li><a class="slide-item" href="{{ route('pos.index') }}">فاتورة بيع جديدة</a></li>
						</ul>
					</li>

					<!-- Sales Returns -->
					<li class="slide {{ request()->routeIs('returns.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('returns.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M7.11 8.53L5.7 7.11C4.8 8.27 4.24 9.61 4.07 11h2.02c.14-.87.49-1.72 1.02-2.47zM6.09 13H4.07c.17 1.39.72 2.73 1.62 3.89l1.41-1.42c-.52-.75-.87-1.59-1.01-2.47zm1.01 5.32c1.16.9 2.51 1.44 3.9 1.61V17.9c-.87-.15-1.71-.49-2.46-1.03L7.1 18.32zM13 4.07V1L8.45 5.55 13 10V6.09c3.37 0 6.09 2.72 6.09 6.09s-2.72 6.09-6.09 6.09v2c4.47 0 8.09-3.62 8.09-8.09S17.47 4.07 13 4.07z"/></svg>
							<span class="side-menu__label">مرتجعات المبيعات</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('returns.index') ? 'active' : '' }}" href="{{ route('returns.index') }}">سجل المرتجعات</a></li>
							<li><a class="slide-item {{ request()->routeIs('returns.create') ? 'active' : '' }}" href="{{ route('returns.create') }}">إرجاع فاتورة مبيعات</a></li>
						</ul>
					</li>

					<!-- Shifts & Cash Reconciliation -->
					<li class="slide {{ request()->routeIs('shifts.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('shifts.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 4c-4.42 0-8 3.58-8 8s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8zm4.25 12.15L11 13V7h1.5v5.25l4.5 2.67-.75 1.23z" opacity=".3"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
							<span class="side-menu__label">الورديات والخزينة</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('shifts.current') ? 'active' : '' }}" href="{{ route('shifts.current') }}">الوردية الحالية</a></li>
							<li><a class="slide-item {{ request()->routeIs('shifts.index') ? 'active' : '' }}" href="{{ route('shifts.index') }}">سجل الورديات والتسويات</a></li>
						</ul>
					</li>

					<!-- Expenses Management -->
					<li class="slide {{ request()->routeIs('expenses.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('expenses.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 6h16v12H4z" opacity=".3"/><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4V6h16v12zm-6-7h-4v2h4v-2z"/></svg>
							<span class="side-menu__label">المصروفات</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('expenses.index') ? 'active' : '' }}" href="{{ route('expenses.index') }}">قائمة المصروفات</a></li>
							<li><a class="slide-item {{ request()->routeIs('expenses.create') ? 'active' : '' }}" href="{{ route('expenses.create') }}">تسجيل مصروف جديد</a></li>
						</ul>
					</li>

					<!-- Notes Management -->
					<li class="slide {{ request()->routeIs('notes.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('notes.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5v14h14V7.83L16.17 5H5zm7 13c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-8H6V6h9v4z" opacity=".3"/><path d="M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h11.17L19 7.83V19zm-7-7c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zm-6-6h9v4H6z"/></svg>
							<span class="side-menu__label">الملاحظات والتنبيهات</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('notes.index') ? 'active' : '' }}" href="{{ route('notes.index') }}">سجل الملاحظات</a></li>
							<li><a class="slide-item {{ request()->routeIs('notes.create') ? 'active' : '' }}" href="{{ route('notes.create') }}">إضافة ملاحظة جديدة</a></li>
						</ul>
					</li>

					<!-- Reports & Analytics -->
					@if(auth()->check() && auth()->user()->isOwner())
					<li class="side-item side-item-category">التقارير والتحليلات</li>
					<li class="slide {{ request()->routeIs('reports.*') ? 'is-expanded active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-toggle="slide" href="{{ url('/#') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 19h14V5H5v14zm10-7h2v5h-2v-5zm-4-3h2v8h-2V9zm-4 6h2v2H7v-2z" opacity=".3"/><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 15h2v2H7zm4-6h2v8h-2zm4 3h2v5h-2z"/></svg>
							<span class="side-menu__label">تقارير النظام</span>
							<i class="angle fe fe-chevron-down"></i>
						</a>
						<ul class="slide-menu">
							<li><a class="slide-item {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}">مركز التقارير العام</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.sales') ? 'active' : '' }}" href="{{ route('reports.sales') }}">تقرير المبيعات</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.payment-methods') ? 'active' : '' }}" href="{{ route('reports.payment-methods') }}">المبيعات حسب طرق الدفع</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.products') ? 'active' : '' }}" href="{{ route('reports.products') }}">تقرير مبيعات المنتجات</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.employees') ? 'active' : '' }}" href="{{ route('reports.employees') }}">تقرير مبيعات الموظفين</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.shifts') ? 'active' : '' }}" href="{{ route('reports.shifts') }}">تقرير الورديات والتسويات</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.expenses') ? 'active' : '' }}" href="{{ route('reports.expenses') }}">تقرير المصروفات</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.inventory') ? 'active' : '' }}" href="{{ route('reports.inventory') }}">تقرير حركة المخزون</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.low-stock') ? 'active' : '' }}" href="{{ route('reports.low-stock') }}">تقرير النواقص والحد الأدنى</a></li>
							<li><a class="slide-item {{ request()->routeIs('reports.profit') ? 'active' : '' }}" href="{{ route('reports.profit') }}">تقرير الأرباح والخسائر</a></li>
						</ul>
					</li>
					@endif

					<!-- Activity & Audit Logs -->
					@if(auth()->check() && auth()->user()->isOwner())
					<li class="side-item side-item-category">الأمان والتدقيق</li>
					<li class="slide {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
						<a class="side-menu__item {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}" href="{{ route('activity-logs.index') }}">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 4.14L6 6.82v4.8c0 3.88 2.56 7.51 6 8.51 3.44-1 6-4.63 6-8.51v-4.8l-6-2.68z" opacity=".3"/><path d="M12 2L4 5.5v6.12c0 5.11 3.41 9.89 8 11.12 4.59-1.23 8-6.01 8-11.12V5.5L12 2zm6 9.62c0 3.88-2.56 7.51-6 8.51-3.44-1-6-4.63-6-8.51v-4.8l6-2.68 6 2.68v4.8z"/></svg>
							<span class="side-menu__label">سجل العمليات والنشاطات</span>
							<span class="badge badge-info side-badge">أمان</span>
						</a>
					</li>
					@endif
				</ul>
			</div>
		</aside>
<!-- main-sidebar -->
