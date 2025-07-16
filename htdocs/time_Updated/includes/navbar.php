<!-- Navbar -->

<style>
  body {
    font-family: "Poppins", sans-serif;
    font-size: 0.9rem;
    padding-top: 0;
    margin: 0;
    overflow-x: hidden;
  }
  
  html {
    scroll-behavior: smooth;
  }
  
  /* Fix sidebar scrolling issues */
  .main-sidebar {
    position: fixed !important;
    top: 0;
    left: 0;
    height: 100vh;
    overflow: hidden;
    z-index: 1000;
    width: 250px;
    background: #002B5C !important;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
  }
  
  .sidebar-light-primary {
    background: #002B5C !important;
  }
  
  .sidebar {
    height: calc(100vh - 60px); /* Account for navbar height */
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 20px;
    padding-top: 60px; /* Space for navbar */
    background: transparent;
  }
  
  /* Custom scrollbar for webkit browsers */
  .sidebar::-webkit-scrollbar {
    width: 6px;
  }
  
  .sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }
  
  .sidebar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
  }
  
  .sidebar::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
  
  /* Ensure sidebar menu items are properly spaced */
  .nav-sidebar {
    padding-bottom: 50px;
  }
  
  /* Fix for nested menu items */
  .nav-treeview {
    max-height: none;
    overflow: visible;
  }
  
  /* Ensure proper spacing for brand logo */
  .brand-link {
    display: block;
    padding: 0.8125rem 1rem;
    transition: width 0.3s ease-in-out;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    position: fixed;
    top: 0;
    width: 250px;
    background: #002B5C !important;
    z-index: 1001;
    height: 60px;
    display: flex;
    align-items: center;
    color: #ffffff !important;
    text-decoration: none;
  }
  
  .brand-link:hover {
    color: #FFD700 !important;
    text-decoration: none;
  }
  
  .brand-image {
    float: left;
    line-height: 0.8;
    margin-left: 0;
    margin-right: 0.5rem;
    margin-top: -3px;
    max-height: 33px;
    width: auto;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  }
  
  .brand-text {
    font-weight: 600 !important;
    color: #ffffff;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    font-size: 16px;
  }
  
  .sidebar-collapse .brand-link {
    width: 4.6rem;
  }
  
  /* Fix user panel positioning */
  .user-panel {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    margin-bottom: 1rem;
  }
  
  /* Content wrapper adjustments */
  .content-wrapper {
    margin-left: 250px !important;
    min-height: 100vh;
    transition: margin-left 0.3s ease-in-out;
    padding-top: 60px; /* Space for fixed navbar */
    overflow-y: auto;
  }
  
  /* Fixed navbar */
  .main-header {
    position: fixed !important;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1010;
    height: 60px;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
    border-bottom: 3px solid #3498db;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }
  
  .professional-navbar {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%) !important;
    border-bottom: 3px solid #3498db;
  }
  
  .navbar-brand-wrapper {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    color: #ffffff;
    font-weight: 600;
    font-size: 18px;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  }
  
  .navbar-brand-text {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #ffffff;
  }
  
  .brand-icon {
    font-size: 20px;
    color: #3498db;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  }
  
  .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    transition: all 0.3s ease;
    padding: 8px 15px !important;
    border-radius: 6px;
    margin: 0 5px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .nav-link:hover {
    color: #ffffff !important;
    background: rgba(52, 152, 219, 0.2);
    transform: translateY(-2px);
  }
  
  .toggle-btn {
    background: rgba(52, 152, 219, 0.1);
    border: 1px solid rgba(52, 152, 219, 0.3);
  }
  
  .toggle-btn:hover {
    background: rgba(52, 152, 219, 0.3);
    border-color: #3498db;
  }
  
  .nav-home-link:hover {
    background: rgba(255, 215, 0, 0.1);
    color: #FFD700 !important;
  }
  
  .nav-home-link.active {
    background: #FFD700 !important;
    color: #002B5C !important;
    font-weight: 600;
  }
  
  .user-menu:hover {
    background: rgba(155, 89, 182, 0.2);
  }
  
  .nav-text {
    font-weight: 500;
    font-size: 14px;
  }
  
  @media (max-width: 767.98px) {
    .navbar-brand-wrapper {
      display: none;
    }
  }
  
  /* When sidebar is collapsed */
  .sidebar-collapse .content-wrapper {
    margin-left: 4.6rem !important;
  }
  
  .sidebar-collapse .main-sidebar {
    width: 4.6rem !important;
  }
  
  /* Responsive adjustments */
  @media (max-width: 767.98px) {
    .content-wrapper {
      margin-left: 0 !important;
      padding-top: 60px;
    }
    
    .main-sidebar {
      transform: translateX(-250px);
      transition: transform 0.3s ease-in-out;
      position: fixed !important;
    }
    
    .sidebar-open .main-sidebar {
      transform: translateX(0);
    }
    
    .main-header {
      left: 0 !important;
    }
  }
   /* Navigation menu improvements */
  .nav-link {
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
  }

  .nav-link:hover {
    background-color: rgba(0,0,0,0.1);
  }
  
  /* Sidebar navigation styling */
  .nav-sidebar .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    border-radius: 8px;
    margin: 2px 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
  }
  
  .nav-sidebar .nav-link:hover {
    background: rgba(255, 215, 0, 0.1);
    color: #FFD700 !important;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.2);
  }
  
  .nav-sidebar .nav-link.active {
    background: #FFD700 !important;
    color: #002B5C !important;
    box-shadow: 0 3px 12px rgba(255, 215, 0, 0.4);
    font-weight: 600;
  }
  
  .nav-sidebar .nav-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    font-size: 16px;
  }
  
  .nav-sidebar .nav-item > .nav-link p {
    margin-bottom: 0;
    font-weight: 500;
  }
  
  /* User panel styling */
  .user-panel {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 1rem;
    padding: 15px;
  }
  
  .user-panel .image img {
    border: 2px solid #FFD700;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
  }
  
  .user-panel .info a {
    color: #ffffff !important;
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
  }
  
  /* Submenu styling */
  .nav-treeview .nav-link {
    padding-left: 2rem;
  }
  
  .nav-treeview .nav-treeview .nav-link {
    padding-left: 3rem;
  }
</style>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />
<nav class="main-header navbar navbar-expand professional-navbar">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a id="toggleButton" class="nav-link toggle-btn" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-xmark"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="index.php" class="nav-link nav-home-link active">
        <i class="fas fa-tachometer-alt"></i>
        <span class="nav-text">Dashboard</span>
      </a>
    </li>
  </ul>

  <!-- Center brand/title -->
  <div class="navbar-brand-wrapper">
    <span class="navbar-brand-text">
      <i class="fas fa-clock brand-icon"></i>
      Time Attendance System
    </span>
  </div>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <a class="nav-link user-menu" href="#" title="User Profile">
        <i class="fas fa-user-circle"></i>
        <span class="nav-text d-none d-md-inline">Admin</span>
      </a>
    </li>
  </ul>
</nav>

<!-- toggle icon change -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Get the toggle button and the initial icon
    const toggleButton = document.getElementById("toggleButton");
    const body = document.body;

    // Add a click event listener to the button
    toggleButton.addEventListener("click", function(e) {
      e.preventDefault();
      
      // Check the current icon class and toggle it
      const currentIcon = toggleButton.querySelector("i");

      if (currentIcon.classList.contains("fa-bars")) {
        // Change to close icon and show sidebar
        currentIcon.classList.remove("fa-bars");
        currentIcon.classList.add("fa-xmark");
        body.classList.remove("sidebar-collapse");
        body.classList.add("sidebar-open");
      } else {
        // Change to bars icon and hide sidebar
        currentIcon.classList.remove("fa-xmark");
        currentIcon.classList.add("fa-bars");
        body.classList.remove("sidebar-open");
        body.classList.add("sidebar-collapse");
      }
    });

    // Handle responsive behavior
    function handleResize() {
      if (window.innerWidth <= 767) {
        body.classList.add("sidebar-collapse");
      } else if (window.innerWidth > 767 && !body.classList.contains("sidebar-collapse")) {
        body.classList.remove("sidebar-collapse");
      }
    }

    // Initial check
    handleResize();
    
    // Listen for window resize
    window.addEventListener("resize", handleResize);

    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", function(e) {
      if (window.innerWidth <= 767 && 
          !e.target.closest(".main-sidebar") && 
          !e.target.closest("#toggleButton") && 
          body.classList.contains("sidebar-open")) {
        body.classList.remove("sidebar-open");
        body.classList.add("sidebar-collapse");
        
        const icon = toggleButton.querySelector("i");
        icon.classList.remove("fa-xmark");
        icon.classList.add("fa-bars");
      }
    });
  });
</script>



<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-primary elevation-4">
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
          <a href="#" class="d-block" style="color:#ffffff;"><span class="icon"><ion-icon name="person-circle"></ion-icon>&nbsp; </span><?php echo $_SESSION['role'] . " (" . $_SESSION['employee_ID'] . ")"; ?></a>
        <?php else: ?>
          <a href="#" class="d-block" style="color:#ffffff;">No User Logged In</a>
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
              <a href="generate_report.php" class="nav-link">
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

         
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-blue">
    
  </aside>

  <!-- /.control-sidebar -->
</body>