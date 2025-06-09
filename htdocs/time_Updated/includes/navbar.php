<!-- Navbar -->

<style>
  body {
    font-family: "Poppins", sans-serif;
    font-size: 0.9rem;
  }
</style>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />
<nav class="main-header navbar navbar-expand navbar-black navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a id="toggleButton" class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-xmark"></i>
      </a>
    </li>
  </ul>

  <!-- Right navbar links -->
</nav>

<!-- toggle icon change -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Get the toggle button and the initial icon
    const toggleButton = document.getElementById("toggleButton");
    const initialIcon = toggleButton.querySelector("i").cloneNode(true);

    // Add a click event listener to the button
    toggleButton.addEventListener("click", function() {
      // Check the current icon class and toggle it
      const currentIcon = toggleButton.querySelector("i");

      if (currentIcon.classList.contains("fa-bars")) {
        // Change to close icon
        currentIcon.classList.remove("fa-bars");
        currentIcon.classList.add("fa-xmark");
      } else {
        // Change to bars icon
        currentIcon.classList.remove("fa-xmark");
        currentIcon.classList.add("fa-bars");
      }
    });
  });
</script>



<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-primary elevation-4">
  <!-- Brand Logo -->

  </a>
  <!-- Brand Logo -->
  <a href="index.php" class="brand-link">
    <img src="dist/img/logo.jpg" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light"><b>SLPA</b></span>
  </a>




  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-4 pb-3 mb-6 d-flex">
      <div>
        <!-- Display user's role and employee ID -->
        <?php if (isset($_SESSION['role']) && isset($_SESSION['employee_ID'])): ?>
          <a href="#" class="d-block" style="color:#050A57;"><span class="icon"><ion-icon name="person-circle"></ion-icon>&nbsp; </span><?php echo $_SESSION['role'] . " (" . $_SESSION['employee_ID'] . ")"; ?></a>
        <?php else: ?>
          <a href="#" class="d-block">No User Logged In</a>
        <?php endif; ?>
      </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>



    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <!-- Report Category -->
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-file"></i>
            <p>
              Report
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="unit.php" class="nav-link">
                <i class="fa fa-angle-left"></i>
                <p>Unit Attendance Report</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="audit.php" class="nav-link">
                <i class="fa fa-angle-left"></i>
                <p>Audit Report</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="meal.php" class="nav-link">
                <i class="fa fa-angle-left"></i>
                <p>Meal Report</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Admin Category -->
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-edit"></i>
            <p>
              Admin
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="#" class="nav-link">
                <p>
                  Manage Users
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="user.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Create User</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="user_status.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Users Status</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="userList.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p> Users List</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="userManage.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p> Manage Users</p>
                  </a>
                </li>

              </ul>
            </li>


            <li class="nav-item">
              <a href="#" class="nav-link">
                <p>
                  Manage Employees
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="master1.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Create Employee</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="master_records_view.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Employee List</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="master_records.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Manage Employees</p>
                  </a>
                </li>
              </ul>
            </li>


            <li class="nav-item">
              <a href="#" class="nav-link">
                <p>
                  Manage Divisions
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="division.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Create New Division</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="division_List.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>All Divisions</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="division_manage.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Manage Divisions</p>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-item">
              <a href="#" class="nav-link">
                <p>
                  Manage Sections
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="section.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Create New Section</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="section_List.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Sections List</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="section_Manage.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Manage Sections</p>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-item">
              <a href="#" class="nav-link">
                <p>
                  Manage Role Access
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="manage_role_access.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Restrict/Grand Access</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Add All Ports of Sri Lanka link here -->
            <li class="nav-item">
              <a href="all_ports.php" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>All Ports of Sri Lanka</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Others Category -->
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-table"></i>
            <p>
              Others
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="./device.php" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Device List</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="pages/tables/data.html" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>User Transfer Guide</p>
              </a>
            </li>
          </ul>
        </li>


        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="far fa-gear nav-icon"></i>
            <p>
              Setting
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="changePassword.php" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Change Password</p>
              </a>
            </li>

          </ul>
        </li>
      
      </ul>
    </nav>
    <!-- /.sidebar-menu -->

  </div>
  <!-- /.sidebar -->
</aside>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">

          <h1 class="m-0">Time Attendance System</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="./index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="logout.php">logout</a></li>
            <li class="breadcrumb-item active"></li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-blue">
    
  </aside>

  <!-- /.control-sidebar -->
</body>