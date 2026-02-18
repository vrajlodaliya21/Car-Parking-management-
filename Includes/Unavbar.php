<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class=''>
  <nav class='navbar navbar-expand-lg navbar-dark bg-dark fixed-top'>
    <a class='navbar-brand' href='#' style='font-family: mv boli; font-size: 1.75rem;'>Park Heaven</a>
    <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarNav'
      aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
      <span class='navbar-toggler-icon'></span>
    </button>
    <div class='collapse navbar-collapse' id='navbarNav'>
      <ul class='navbar-nav ml-auto'>
        <li class='nav-item mt-2'>
          <a class='nav-link <?php echo ($current_page == "UDashboard.php") ? "active" : ""; ?>' href='UDashboard.php'
            style='font-size: 1.1rem;'>Home</a>
        </li>
        <li class='nav-item mt-2'>
          <a class='nav-link <?php echo ($current_page == "UserBookSlot.php") ? "active" : ""; ?>'
            href='UserBookSlot.php' style='font-size: 1.1rem;'>Book Slots</a>
        </li>
        <li class='nav-item mt-2'>
          <a class='nav-link <?php echo ($current_page == "Contact.php") ? "active" : ""; ?>' href='Contact.php'
            style='font-size: 1.1rem;'>Contact Us</a>
        </li>
        <li class='nav-item mt-2'>
          <a class='nav-link <?php echo ($current_page == "About.php") ? "active" : ""; ?>' href='About.php'
            style='font-size: 1.1rem;'>About Us</a>
        </li>
        <li class='nav-item dropdown'>
          <a class='nav-link dropdown-toggle show-on-hover' href='#' id='profileDropdown' role='button'
            data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
            <img src='./Images/users.jpg' alt='Profile Picture' class='profile-picture'
              style='width: 40px; height: 40px;'>
          </a>
          <div class='dropdown-menu dropdown-menu-right' aria-labelledby='profileDropdown'>
            <a class='dropdown-item <?php echo ($current_page == "Profile.php") ? "active" : ""; ?>' href='Profile.php'
              style='font-size: 20px;'>Profile</a>
            <a class='dropdown-item <?php echo ($current_page == "UserBookingChart.php") ? "active" : ""; ?>'
              href='UserBookingChart.php' style='font-size: 20px;'>Booking Chart</a>
            <a class='dropdown-item <?php echo ($current_page == "Changepass.php") ? "active" : ""; ?>'
              href='Changepass.php' style='font-size: 20px;'>Change Password</a>
            <div class='dropdown-divider'></div>
            <a class='dropdown-item <?php echo ($current_page == "Logout.php") ? "active" : ""; ?>' href='Logout.php'
              style='font-size: 20px;'>Logout</a>
          </div>
        </li>
      </ul>
    </div>
  </nav>

</div>

<style>
  .nav-link.active {
    text-decoration: underline;
    text-underline-offset: 10px;
    font-weight: bold;
    background-color: #343a40;
    border-radius: 5px;
    padding: 8px 15px;
  }

  .dropdown-item:hover {
    background-color: #343a40;
    color: white;
  }

  .dropdown-item.active {
    text-decoration: underline;
    text-underline-offset: 8px;
    font-weight: bold;
    background-color: #343a40;
    color: white;
  }

  .show-on-hover:hover+.dropdown-menu {
    display: block;
  }

  .dropdown-menu:hover {
    display: block;
  }

  .dropdown-menu {
    transition: all 0.2s ease-in-out;
  }
</style>