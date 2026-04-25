  <aside

      class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 "

      id="sidenav-main">

      <div class="sidenav-header">

          <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"

              aria-hidden="true" id="iconSidenav"></i>

          <a class="" href="http://alyasser-center.com:8080/" target="_blank">

              <img src="../assets/img/logo-ct-dark.png" width="250px" height="170px" class="" alt="main_logo">



          </a>

      </div>

      <hr class="horizontal dark mt-0">

      <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">

          <ul class="navbar-nav">

              <li class="nav-item">

                  <a class="nav-link  {{ request()->routeIs('dashboard_admin.*') ? 'active' : '' }}"

                      href="{{ route('dashboard_admin') }}">

                      <div

                          class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                          <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>

                      </div>

                      <span class="nav-link-text ms-1">لوحة التحكم</span>

                  </a>

              </li>

              <li class="nav-item">

                  <a class="nav-link {{ request()->routeIs('users') ? 'active' : '' }}" href="{{ route('users') }}">

                      <div

                          class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                          <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>

                      </div>

                      <span class="nav-link-text ms-1">إدارة المستخدمين</span>

                  </a>

              </li>

              <li class="nav-item">

                  <a class="nav-link {{ request()->routeIs('categories') ? 'active' : '' }}"

                      href="{{ route('categories') }}">

                      <div

                          class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                          <i class="ni ni-credit-card text-dark text-sm opacity-10"></i>

                      </div>

                      <span class="nav-link-text ms-1">إدارة الأصناف</span>

                  </a>

              </li>

              <li class="nav-item">

                  <a class="nav-link {{ request()->routeIs('Products') ? 'active' : '' }}"

                      href="{{ route('Products') }}">

                      <div

                          class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">



                          <i class="ni ni-single-copy-04 text-dark text-sm opacity-10"></i>

                      </div>

                      <span class="nav-link-text ms-1">إدارة المنتجات</span>

                  </a>

              </li>

              <li class="nav-item">

                  <a class="nav-link {{ request()->routeIs('offers') ? 'active' : '' }}" href="{{ route('offers') }}">

                      <div

                          class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                          <i class="ni ni-world-2 text-dark text-sm opacity-10"></i>

                      </div>

                      <span class="nav-link-text ms-1">إدارة العروضات</span>

                  </a>

              </li>

              <li class="nav-item">

                  <a class="nav-link {{ request()->routeIs('get_order') ? 'active' : '' }}" href="{{ route('get_order') }}">

                      <div

                          class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">



                          <i class="ni ni-app text-dark text-sm opacity-10"></i>

                      </div>

                      <span class="nav-link-text ms-1">إدارة الطلبات</span>

                  </a>

              </li>

              <li class="nav-item">

                  <a class="nav-link {{ request()->routeIs('warehouse.index') ? 'active' : '' }}"

                      href="{{ route('warehouse.index') }}">

                      <div

                          class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                          <i class="ni ni-world-2 text-dark text-sm opacity-10"></i>

                      </div>

                      <span class="nav-link-text ms-1">إدارة المستودع</span>

                  </a>

              </li>

              <li class="nav-item">

                  <a class="nav-link {{ request()->routeIs('Notifications') ? 'active' : '' }}"

                      href="{{ route('Notifications') }}">

                      <div

                          class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                          <i class="ni ni-bell-55 text-dark text-sm opacity-10"></i>

                      </div>

                      <span class="nav-link-text ms-1">إدارة الإشعارات</span>

                  </a>

              </li>

          </ul>

      </div>



      <div class="">

        <form action="{{ route('logout_web') }}" method="post">

            @csrf

          <button type="submit" style="width: 90%; margin: 10px" type="button" class="btn btn-danger">تسجيل الخروج</button>

        </form>

          <button style="width: 90%; margin: 10px" type="button" class="btn btn-primary">تعديل الملف الشخصي</button>

      </div>

  </aside>

