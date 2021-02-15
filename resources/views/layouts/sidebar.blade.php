@php 
$tenant = explode('/', $_SERVER['REQUEST_URI'])[1];
echo $tenant;
@endphp
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="https://bmaas.arch.biznetgio.xyz/" class="brand-link">
      <span class="brand-text font-weight-light"></span>
	<img src="bgn-bw.svg" width="200" height="84" </a>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-1 pb-1 mb-1 d-flex">
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
	<ul class="sidebar-menu nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <!--<li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Starter Pages
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Active Page</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inactive Page</p>
                </a>
              </li>
            </ul>
          </li>-->
          <li class="nav-item">
	  <a href="/listmachines" class="nav-link">
              <i class="nav-icon fas fa-desktop"></i>
              <p>
		My Machine
              </p>
            </a>
          </li>

          <li class="nav-item">
	  <!--a href= "/{{Request::segment(1)}}/orderpage" class="nav-link"-->
	  <a href= "/orderpage" class="nav-link">
              <i class="nav-icon fas fa-server"></i>
              <p>
		Order BareMetal
              </p>
            </a>
	  </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-tree"></i>
              <p>
                Account Profile
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/sshkey" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>SSH Keys</p>
                </a>
              </li>
            </ul>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/activity" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Activity Audit</p>
                </a>
              </li>
            </ul>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/billing" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Billing Usage</p>
                </a>
              </li>
            </ul>

	  </li>
          <li class="nav-item">
          <!--a href= "/{{Request::segment(1)}}/orderpage" class="nav-link"-->
          <a href= "/contact" class="nav-link">
              <i class="nav-icon fas fa-headphones"></i>
              <p>
                Support

              </p>
            </a>
          </li>


        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
