<?php
session_start();

include '../Connection.php';
// Handle slot creation...
if (isset($_POST['create_slot'])) {
  $price = $_POST['price'];
  $total_slots = $_POST['total_slots'];
  $available_slots = $total_slots;
  $vendor_id = $_SESSION['vendor_id'];

  $location_query = "SELECT city, area, location FROM vendors WHERE id = ?";
  $stmt = $conn->prepare($location_query);
  $stmt->bind_param("i", $vendor_id);
  $stmt->execute();
  $location_result = $stmt->get_result();
  $vendor_details = $location_result->fetch_assoc();
  $stmt->close();

  if ($vendor_details) {
    $city = $vendor_details['city'];
    $area = $vendor_details['area'];
    $location = $vendor_details['location'];

    error_log("City: $city, Area: $area, Location: $location");

    $insert_query = "INSERT INTO slots (id, price, total_slots, available_slots, city, area, location) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insert_query);
    if (!$stmt) {
      error_log("Prepare failed: " . $conn->error);
      $message = "Error: Database preparation failed.";
    } else {
      // All string values should use 's' in bind_param
      $stmt->bind_param(
        "isiisss",
        $vendor_id,
        $price,
        $total_slots,
        $available_slots,
        $city,
        $area,
        $location
      );

      if ($stmt->execute()) {
        $message = "Slot added successfully!";
      } else {
        error_log("Execute failed: " . $stmt->error);
        $message = "Error: Slot could not be added. " . $stmt->error;
      }
      $stmt->close();
    }
  } else {
    $message = "Error: Vendor location details not found.";
  }
}

$slots = [];

// Handle slot deletion...
if (isset($_POST['delete_slot'])) {
  $slot_id = $_POST['slot_id'];
  $vendor_id = $_SESSION['vendor_id'];

  $stmt = $conn->prepare("DELETE FROM slots WHERE id = ? AND id = ?");
  $stmt->bind_param("ii", $slot_id, $vendor_id);

  if ($stmt->execute()) {
    $message = "Slot deleted successfully!";
  } else {
    $message = "Error: Slot could not be deleted.";
  }
  $stmt->close();
}

// Handle slot update...
if (isset($_POST['update_slot'])) {
  $slot_id = $_POST['slot_id'];
  $price = $_POST['price'];
  $total_slots = $_POST['total_slots'];
  $available_slots = $_POST['available_slots'];
  $vendor_id = $_SESSION['vendor_id'];

  $location_query = "SELECT city, area, location FROM vendors WHERE id = ?";
  $stmt = $conn->prepare($location_query);
  $stmt->bind_param("i", $vendor_id);
  $stmt->execute();
  $location_result = $stmt->get_result();
  $vendor_details = $location_result->fetch_assoc();
  $stmt->close();

  if ($vendor_details) {
    $stmt = $conn->prepare("UPDATE slots SET price = ?, total_slots = ?, available_slots = ?, city = ?, area = ?, location = ? WHERE id = ? AND id = ?");
    $stmt->bind_param(
      "siisssii",
      $price,
      $total_slots,
      $available_slots,
      $vendor_details['city'],
      $vendor_details['area'],
      $vendor_details['location'],
      $slot_id,
      $vendor_id
    );

    if ($stmt->execute()) {
      $message = "Slot updated successfully!";
    } else {
      $message = "Error: Slot could not be updated. " . $stmt->error;
    }
    $stmt->close();
  } else {
    $message = "Error: Vendor location details not found.";
  }
}

$vendor_id = $_SESSION['vendor_id'];
$result = $conn->query("SELECT id, price, total_slots, available_slots, city, area, location FROM slots WHERE id = $vendor_id");
if ($result) {
  $slots = $result->fetch_all(MYSQLI_ASSOC);
} else {
  $message = "Error fetching slots: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <link rel="shortcut icon" type="image/x-icon" href="../../Car/Images/Main_Image.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vendor - Parking Management</title>
  <link rel="stylesheet" href="../Styles.css">
  <link rel="stylesheet" href="../bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .sidebar {
      height: 100vh;
      padding: 20px;
      position: fixed;
      top: 0;
      left: 0;
    }

    .form-control {
      height: 45px;
      font-size: 1.1rem;
    }

    .modal-content {
      font-size: 1.1rem;
    }

    .d-flex.flex-column span {
      font-size: 18px;
    }

    table th,
    table td {
      font-size: 20px;
    }

    #btn {
      font-size: 20px;
      padding: 8px 12px;
      height: auto;
      border-radius: 8px;
    }

    .error-message {
      color: red;
      font-size: 0.9rem;
      margin-top: 10px;
    }

    .error-message i {
      margin-right: 5px;
      font-size: 1.0rem;
    }

    .modal-body .form-group {
      margin-bottom: 1rem;
    }

    .modal-body input[type="number"] {
      padding: 0.375rem 0.75rem;
    }

    .dropdown-menu {
      width: 100%;
      max-height: 300px;
      overflow-y: auto;
    }

    .dropdown-item {
      padding: 0.5rem 1rem;
      cursor: pointer;
    }

    .dropdown-item:hover {
      background-color: #f8f9fa;
    }

    .dropdown-item.active {
      background-color: #007bff;
    }

    .search-dropdown {
      position: relative;
    }

    .search-dropdown-menu {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      z-index: 1000;
      background: white;
      border: 1px solid rgba(0, 0, 0, .15);
      border-radius: 4px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
      max-height: 300px;
      overflow-y: auto;
    }

    .search-dropdown-menu.show {
      display: block;
    }

    .search-box {
      padding: 8px;
      position: sticky;
      top: 0;
      background: white;
      z-index: 1;
    }

    .dropdown-items {
      max-height: 250px;
      overflow-y: auto;
    }

    .dropdown-item {
      padding: 8px 16px;
      cursor: pointer;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .dropdown-item:hover {
      background-color: #f8f9fa;
    }

    .dropdown-item.active {
      background-color: #007bff;
      color: white;
    }

    .dropdown-divider {
      margin: 0;
      border-top: 1px solid #e9ecef;
    }

    .search-dropdown-menu:empty {
      display: none;
    }

    .no-results {
      padding: 8px 16px;
      color: #6c757d;
      font-style: italic;
    }
  </style>
</head>

<body class="bg-content">
  <div class="sidebar">
    <?php include '../Includes/Vsidebar.php'; ?>
  </div>

  <div class="main-content">
    <div class="container col-11">
      <h1 class="text-center mb-4">Add Parking Slots</h1>
      <hr class="w-25 mx-auto border-dark">

      <?php
      $vendor_id = $_SESSION['vendor_id'];
      $location_query = "SELECT city, area, location FROM vendors WHERE id = $vendor_id";
      $location_result = $conn->query($location_query);
      if ($location_result && $location_result->num_rows > 0) {
        $vendor_details = $location_result->fetch_assoc();
        ?>
        <div class="card mb-4 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="location-icon mr-4">
                <i class="fas fa-map-marker-alt fa-3x text-primary"></i>
              </div>
              <div class="location-details">
                <div class="location-text">
                  <div class="mb-2">
                    <strong class="text-muted" style="font-size: 1.1rem;">City:</strong>
                    <span class="ml-2"
                      style="font-size: 1.3rem;"><?php echo htmlspecialchars($vendor_details['city']); ?></span>
                  </div>
                  <div class="mb-2">
                    <strong class="text-muted" style="font-size: 1.1rem;">Area:</strong>
                    <span class="ml-2"
                      style="font-size: 1.3rem;"><?php echo htmlspecialchars($vendor_details['area']); ?></span>
                  </div>
                  <div>
                    <strong class="text-muted" style="font-size: 1.1rem;">Location:</strong>
                    <span class="ml-2"
                      style="font-size: 1.3rem;"><?php echo htmlspecialchars($vendor_details['location']); ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>

      <?php if (isset($message)): ?>
        <div id="message"
          class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <?php if (empty($slots)): ?>
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Note: You can create slots only once. After creating, you can only update or
          delete them.
        </div>
      <?php endif; ?>

      <?php if (empty($slots)): ?>
        <form method="POST" class="mb-4" id="createSlotForm">
          <div class="form-group">
            <label for="price" class="h5">Price:</label>
            <input type="text" name="price" id="price" class="form-control" autocomplete="off">
            <div id="price_error" class="error-message pt-1"></div>
          </div>
          <div class="form-group">
            <label for="total_slots" class="h5">Total Slots:</label>
            <input type="number" name="total_slots" id="total_slots" class="form-control" autocomplete="off">
            <div id="total_slots_error" class="error-message pt-1"></div>
          </div>
          <button type="submit" name="create_slot" id="btn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Slot
          </button>
        </form>
      <?php endif; ?>


      <h2 class="mt-5 mb-3">Your Slots</h2>
      <?php if (empty($slots)): ?>
        <div class="text-center">
          <img src="../../Car/Images/Empty.gif" width="250" height="250" alt="No slots available" />
          <p>No booking slots available.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">

          <table class="table table-striped">
            <thead>
              <tr>
                <th>Price</th>
                <th>Total Slots</th>
                <th>Available Slots</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($slots)): ?>
                <?php foreach ($slots as $slot): ?>
                  <tr>
                    <td><?= htmlspecialchars($slot['price']) ?></td>
                    <td><?= htmlspecialchars($slot['total_slots']) ?></td>
                    <td><?= htmlspecialchars($slot['available_slots']) ?></td>
                    <td>
                      <div class="d-flex gap-2">
                        <button type="button" id="btn" class="btn btn-secondary mr-2" data-toggle="modal"
                          data-target="#updateModal-<?= htmlspecialchars($slot['id']) ?>">
                          <i class="fas fa-edit"></i>
                        </button>

                        <form method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this slot?');">
                          <input type="hidden" name="slot_id" value="<?= htmlspecialchars($slot['id']) ?>">
                          <button type="submit" name="delete_slot" id="btn" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                        </form>
                      </div>

                      <div class="modal fade" id="updateModal-<?= htmlspecialchars($slot['id']) ?>" tabindex="-1"
                        role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="updateModalLabel">Update Slot</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <form method="POST" class="updateSlotForm">
                              <div class="modal-body">
                                <input type="hidden" name="slot_id" value="<?= htmlspecialchars($slot['id']) ?>">
                                <div class="form-group">
                                  <label for="price_<?= htmlspecialchars($slot['id']) ?>">Price:</label>
                                  <input type="text" name="price" id="price_<?= htmlspecialchars($slot['id']) ?>"
                                    class="form-control" value="<?= htmlspecialchars($slot['price']) ?>">
                                </div>
                                <div class="form-group">
                                  <label for="total_slots_<?= htmlspecialchars($slot['id']) ?>">Total Slots:</label>
                                  <input type="number" name="total_slots"
                                    id="total_slots_<?= htmlspecialchars($slot['id']) ?>" class="form-control"
                                    value="<?= htmlspecialchars($slot['total_slots']) ?>">
                                </div>
                                <div class="error-message" id="update_error_<?= htmlspecialchars($slot['id']) ?>">
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="btn" data-dismiss="modal">
                                  <i class="fas fa-times"></i> Close
                                </button>
                                <button type="submit" name="update_slot" id="btn" class="btn btn-primary">
                                  <i class="fas fa-save"></i> Save changes
                                </button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center">No slots available</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var messageDiv = document.getElementById("message");
      if (messageDiv) {
        setTimeout(function () {
          messageDiv.style.display = "none";
        }, 2000);
      }

      function validateSlots(totalSlots, availableSlots, errorElement) {
        if (isNaN(totalSlots) || totalSlots <= 0) {
          errorElement.innerHTML = '<i class="fas fa-info-circle"></i> Total Slots must be a positive number';
          return false;
        }

        if (isNaN(availableSlots) || availableSlots < 0) {
          errorElement.innerHTML = '<i class="fas fa-info-circle"></i> Available Slots must be a non-negative number';
          return false;
        }

        if (parseInt(availableSlots) > parseInt(totalSlots)) {
          errorElement.innerHTML = '<i class="fas fa-info-circle"></i> Available Slots (' + availableSlots + ') cannot be greater than Total Slots (' + totalSlots + ')';
          return false;
        }

        return true;
      }

      document.getElementById("createSlotForm").addEventListener("submit", function (event) {
        let valid = true;
        document.getElementById("price_error").innerText = '';
        document.getElementById("total_slots_error").innerText = '';

        const price = document.getElementById("price").value;
        if (price === '') {
          document.getElementById("price_error").innerHTML = '<i class="fas fa-info-circle"></i> Price is required';
          valid = false;
        } else if (isNaN(price) || price <= 0) {
          document.getElementById("price_error").innerHTML = '<i class="fas fa-info-circle"></i> Price must be a positive number';
          valid = false;
        }

        const total_slots = document.getElementById("total_slots").value;
        if (isNaN(total_slots) || total_slots <= 0) {
          document.getElementById("total_slots_error").innerHTML = '<i class="fas fa-info-circle"></i> Total Slots must be a positive number';
          valid = false;
        }

        if (!valid) {
          event.preventDefault();
        }
      });

      document.querySelectorAll('.updateSlotForm').forEach(form => {
        form.addEventListener('submit', function (event) {
          let valid = true;
          const slotId = this.querySelector('input[name="slot_id"]').value;
          const errorDiv = document.getElementById(`update_error_${slotId}`);
          let errorMessage = '';

          errorDiv.innerHTML = '';

          const price = this.querySelector('input[name="price"]').value;
          if (price === '') {
            errorMessage = '<i class="fas fa-info-circle"></i> Price is required';
            valid = false;
          } else if (isNaN(price) || price <= 0) {
            errorMessage = '<i class="fas fa-info-circle"></i> Price must be a positive number';
            valid = false;
          }

          const totalSlots = this.querySelector('input[name="total_slots"]').value;
          if (totalSlots === '') {
            errorMessage = '<i class="fas fa-info-circle"></i> Total Slots is required';
            valid = false;
          } else if (isNaN(totalSlots) || totalSlots <= 0) {
            errorMessage = '<i class="fas fa-info-circle"></i> Total Slots must be a positive number';
            valid = false;
          }

          if (!valid) {
            errorDiv.innerHTML = errorMessage;
            event.preventDefault();
          }
        });
      });
    });
  </script>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>