  <!-- Navbar -->
  <!--div id="app"-->
  <!-- Right Side Of Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
          <li class="nav-item">
              <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
      </ul>
      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
          <!-- Messages Dropdown Menu -->
          <div class="v-popover open"><span aria-describedby="popover_g29x3z8724" tabindex="-1" class="trigger"
                  style="display:inline-block;"><a class="tooltip-target">
                      <span class="bot-bar__icon"><img
                              src="https://web.neo.id/images/icon-notifications.svg"></span>
                  </a></span></div>
          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#"><b>Login
                      as:</b>{{ Auth::user()->name }}<span class="caret"></span></a>
              <div class="dropdown-menu">
                  <a class="dropdown-item" href="/welcome">
                      <svg role="presentation" width="14" height="14" viewBox="0 0 512 512" class="fa-icon">
                          <path
                              d="M478.2 334.1L336 256 478.2 177.9C490 171.4 494.2 156.5 487.4 144.9L468 111.1C461.2 99.5 446.2 95.6 434.7 102.6L296 186.7 299.5 24.5C299.8 11.1 288.9 0 275.5 0H236.5C223.1 0 212.2 11.1 212.5 24.5L216 186.7 77.3 102.6C65.8 95.6 50.8 99.5 44 111.1L24.6 144.9C17.8 156.5 22 171.4 33.8 177.9L176 256 33.8 334.1C22 340.6 17.8 355.5 24.6 367.1L44 400.9C50.8 412.5 65.8 416.4 77.3 409.4L216 325.3 212.5 487.5C212.2 500.9 223.1 512 236.5 512H275.5C288.9 512 299.8 500.9 299.5 487.5L296 325.3 434.7 409.4C446.2 416.4 461.2 412.5 468 400.9L487.4 367.1C494.2 355.5 490 340.6 478.2 334.1z">
                          </path>
                      </svg>
                      {{ 'My Account' }}</a>
                  <a class="dropdown-item" href="{{ route('logout') }}"
                      onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                      <svg role="presentation" width="14" height="14" viewBox="0 0 512 512" class="fa-icon">
                          <path
                              d="M497 273L329 441C314 456 288 445.5 288 424V328H152C138.7 328 128 317.3 128 304V208C128 194.7 138.7 184 152 184H288V88C288 66.6 313.9 56 329 71L497 239C506.3 248.4 506.3 263.6 497 273zM192 436V396C192 389.4 186.6 384 180 384H96C78.3 384 64 369.7 64 352V160C64 142.3 78.3 128 96 128H180C186.6 128 192 122.6 192 116V76C192 69.4 186.6 64 180 64H96C43 64 0 107 0 160V352C0 405 43 448 96 448H180C186.6 448 192 442.6 192 436z">
                          </path>
                      </svg>
                      {{ __('Logout') }}
                  </a>

                  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                      @csrf
                  </form>

                  <a class="dropdown-item" href="/about-us">
                      {{ 'About Us' }}
                  </a>

              </div>
          </li>
          <!-- Notifications Dropdown Menu -->
      </ul>

  </nav>
  <!-- /.navbar -->
