<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/Styles.css">
  <link rel="stylesheet" href="./bootstrap.min.css">
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
  <style>
    .sidenav {
      height: 100%;
      position: fixed;
      z-index: 1;
      top: 0;
      left: 0;
      background-color: #111;
      overflow-x: hidden;
      transition: 0.5s;
      padding-top: 60px;
    }

    .E-class {
      border-left: solid 6px #00C1FE;
    }

    .bg-sidebar a:hover {
      width: 210px;
    }

    a:hover i,
    a:hover span {
      color: #00C1FE;
    }

    @media screen and (min-width: 400px) {
      .sidenav {
        width: 200px;
      }
    }

    .main-content {
      margin-left: 300px;
      padding: 16px;
      transition: margin-left 0.5s;
    }

    @media screen and (min-width: 400px) {
      .main-content {
        margin-left: 200px;
      }
    }

    .main-content.sidebar-closed {
      margin-left: 0;
    }

    .navbar a {
      padding: 12px 15px;
      margin: 5px 10px;
      text-decoration: none;
      color: #818181;
      display: block;
      transition: 0.3s;
      text-align: left;
      border-radius: 5px;
    }

    .navbar a:hover {
      color: #f1f1f1;
      background-color: rgba(255, 255, 255, 0.1);
    }

    .sidenav .closebtn {
      position: absolute;
      top: 0;
      text-decoration: none;
      color: #818181;
      right: 25px;
      font-size: 40px;
      margin-left: 50px;
      padding: 0;
      margin: 0;
    }

    .nav-link {
      padding: 10px 15px;
      margin: 8px 0;
      border-radius: 4px;
      transition: all 0.3s ease;
    }

    @media screen and (max-width: 400px) {
      h1 {
        margin-top: 55px;
      }

      .sidenav {
        padding-top: 15px;
        text-align: center;
      }

      .navbar a {
        padding: 10px 5px;
        margin: 3px 5px;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <aside id='left-panel' class='left-panel'>
      <div class='d-flex justify-content-center align-items-center p-2 w-100'>
        <h1 class='E-class text-start text-white h3 fw-bold'>Park Heaven</h1>
      </div>
      <nav class='navbar navbar-expand-sm navbar-default d-flex flex-column vh-100'>
        <div id='main-menu' class='main-menu collapse navbar-collapse show d-flex flex-column align-items-center'>
          <div class='img-admin d-flex flex-column align-items-center text-center gap-2 mt-3'>
            <img class='rounded-circle' src='./Images/admin.jpg' alt='img-admin' height='170' width='170'>
            <h2 class='h6 fw-bold mt-2'>
              <?php
              if (isset($_SESSION['name'])) {
                echo htmlspecialchars($_SESSION['name']);
              } else {
                echo '<span class="text-white" style="font-size:25px">Admin</span>';
              }
              ?>
            </h2>
          </div>
          <div class='d-flex flex-column align-items-start fw-bold gap-2 mt-4 w-100'>
            <ul class='list-unstyled w-100'>
              <li class='h5'>
                <a class='nav-link text-light d-flex align-items-center text-nowrap <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? "fw-bolder" : ""; ?>'
                  style='<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? "color: #00C1FE !important;" : ""; ?>'
                  href='dashboard.php'>
                  <i class='fal fa-home-lg-alt me-2'></i>
                  <span class="ml-1">Dashboard</span>
                </a>
              </li>
              <li class='h5'>
                <a class='nav-link text-light d-flex align-items-center text-nowrap <?php echo (basename($_SERVER['PHP_SELF']) == 'AddVenders.php') ? "fw-bolder" : ""; ?>'
                  style='<?php echo (basename($_SERVER['PHP_SELF']) == 'AddVenders.php') ? "color: #00C1FE !important;" : ""; ?>'
                  href='AddVenders.php'>
                  <i class="fa fa-store me-2"></i>
                  <span class="ml-1">Add Vendors</span>
                </a>
              </li>
              <li class='h5'>
                <a class='nav-link text-light d-flex align-items-center text-nowrap <?php echo (basename($_SERVER['PHP_SELF']) == 'Viewbooking.php') ? "fw-bolder" : ""; ?>'
                  style='<?php echo (basename($_SERVER['PHP_SELF']) == 'Viewbooking.php') ? "color: #00C1FE !important;" : ""; ?>'
                  href='Viewbooking.php'>
                  <i class='fal fa-clipboard-list me-2'></i>
                  <span class="ml-2">View Booking</span>
                </a>
              </li>
              <li class='h5'>
                <a class='nav-link text-light d-flex align-items-center text-nowrap <?php echo (basename($_SERVER['PHP_SELF']) == 'Userdetails.php') ? "fw-bolder" : ""; ?>'
                  style='<?php echo (basename($_SERVER['PHP_SELF']) == 'Userdetails.php') ? "color: #00C1FE !important;" : ""; ?>'
                  href='Userdetails.php'>
                  <i class='fal fa-users me-2'></i>
                  <span class="ml-1">User Details</span>
                </a>
              </li>
              <li class='h5'>
                <a class='nav-link text-light d-flex align-items-center text-nowrap <?php echo (basename($_SERVER['PHP_SELF']) == 'Reports.php') ? "fw-bolder" : ""; ?>'
                  style='<?php echo (basename($_SERVER['PHP_SELF']) == 'Reports.php') ? "color: #00C1FE !important;" : ""; ?>'
                  href='Reports.php'>
                  <i class='fal fa-chart-bar ml-1'></i>
                  <span class="ml-1">Reports</span>
                </a>
              </li>
            </ul>
            <ul class='logout list-unstyled w-100 mt-auto'>
              <li class='h5'>
                <a class='nav-link text-light d-flex align-items-center text-nowrap <?php echo (basename($_SERVER['PHP_SELF']) == 'Logout.php') ? "fw-bolder" : ""; ?>'
                  style='<?php echo (basename($_SERVER['PHP_SELF']) == 'Logout.php') ? "color: #00C1FE !important;" : ""; ?>'
                  href='Logout.php'>
                  <i class='fal fa-sign-out-alt me-2'></i>
                  <span class="ml-1">Logout</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </aside>
  </div>

  <span style="font-size:40px;cursor:pointer" class="togglr-btn ml-1 fixed top-4 left-4 z-50"
    onclick="openNav()">&#9776;</span>


  <script>
    function updateSidebar() {
      const sidebar = document.getElementById("mySidenav");
      const mainContent = document.querySelector('.main-content');

      if (window.innerWidth <= 400) {
        sidebar.style.width = "0";
        mainContent.style.marginLeft = "0";
      } else {
        sidebar.style.width = "270px";
        mainContent.style.marginLeft = "210px";
      }
    }

    function openNav() {
      if (window.innerWidth >= 400) {
        document.getElementById("mySidenav").style.width = "270px";
        document.querySelector('.main-content').style.marginLeft = "210px";
      } else {
        document.getElementById("mySidenav").style.width = "100%";
        document.querySelector('.main-content').style.marginLeft = "0";
      }
    }

    function closeNav() {
      document.getElementById("mySidenav").style.width = "0";
      document.querySelector('.main-content').style.marginLeft = "0";
    }

    window.onload = function () {
      updateSidebar();
    };

    window.onresize = updateSidebar;
  </script>

</body>

</html>