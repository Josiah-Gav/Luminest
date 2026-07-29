<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/admin/admin_controller.php';

$allUsers = $admin->getAllUsers();
$totalUsers = count($allUsers);
$activeUsers = 0;
$roles = [];

foreach ($allUsers as $user) {
    if (isset($user['status']) && strtolower((string) $user['status']) === 'active') {
        $activeUsers++;
    }

    $roleName = $user['role'] ?? 'Unknown';
    $roles[$roleName] = ($roles[$roleName] ?? 0) + 1;
}
?>

<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1 text-accent-black">User management</h1>
      <p class="text-muted mb-0">Manage your platform users with quick, responsive actions.</p>
    </div>
    <a href="add_user.php" class="btn btn-primary">Add new user</a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="p-3 border rounded-3 bg-light">
        <div class="small text-muted">Total accounts</div>
        <div class="fw-semibold fs-5 text-accent-black"><?= (int) $totalUsers ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 border rounded-3 bg-light">
        <div class="small text-muted">Active users</div>
        <div class="fw-semibold fs-5 text-accent-blue"><?= (int) $activeUsers ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 border rounded-3 bg-light">
        <div class="small text-muted">Roles covered</div>
        <div class="fw-semibold fs-5 text-accent-red"><?= count($roles) ?></div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col">User ID</th>
              <th scope="col">Username</th>
              <th scope="col">Email</th>
              <th scope="col">Role</th>
              <th scope="col">Status</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
    <?php
    if (!empty($allUsers)) {
        foreach ($allUsers as $user) {
            echo "<tr>";
            echo "<td>" . (int) ($user['user_id'] ?? 0) . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['email'] ?? 'N/A') . "</td>";
            echo "<td><span class='badge rounded-pill bg-primary-subtle text-primary-emphasis'>" . htmlspecialchars($user['role'] ?? 'Unknown') . "</span></td>";
            echo "<td><span class='badge rounded-pill bg-light text-dark'>" . htmlspecialchars($user['status'] ?? 'Pending') . "</span></td>";
            echo "<td>
                    <div class='d-flex flex-wrap gap-2'>
                      <a href='edit_user.php?id=" . (int) ($user['user_id'] ?? 0) . "' class='btn btn-sm btn-outline-primary'>Edit</a>
                      <form action='../../controllers/admin/admin_controller.php' method='post' class='d-inline delete-user-form'>
                        <input type='hidden' name='user_id' value='" . (int) ($user['user_id'] ?? 0) . "'>
                        <input type='hidden' name='delete_user' value='1'>
                        <input type='hidden' name='ajax' value='1'>
                        <button type='button' class='btn btn-sm btn-outline-danger delete-user-btn' data-user-name='" . htmlspecialchars($user['full_name'] ?? 'this user', ENT_QUOTES, 'UTF-8') . "'>Delete</button>
                      </form>
                      <a href='view_user.php?id=" . (int) ($user['user_id'] ?? 0) . "' class='btn btn-sm btn-outline-info'>View</a>
                    </div>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No users found.</td></tr>";
    }
    ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
  $('.delete-user-btn').on('click', function () {
    var $button = $(this);
    var userName = $button.data('user-name') || 'this user';

    swal({
      title: 'Are you sure?',
      text: 'This will permanently delete ' + userName + '.',
      icon: 'warning',
      buttons: ['Cancel', 'Delete'],
      dangerMode: true
    }).then(function (willDelete) {
      if (willDelete) {
        var $form = $button.closest('form');
        $.ajax({
          url: $form.attr('action'),
          type: 'POST',
          data: $form.serialize(),
          success: function (response) {
            if (response && response.success) {
              $form.closest('tr').remove();
            }
          },
          error: function () {
          }
        });
      }
    });
  });
});
</script>