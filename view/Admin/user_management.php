<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/admin/admin_controller.php';

$allUsers = $admin->getAllUsers();
?>

<h1 class="h3 fw-bold mb-3">User Management</h1>
<span>
  <a href="add_user.php" class="btn btn-primary">Add New User</a>
</span>

<table class="table table-striped">
  <thead>
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
            echo "<td>{$user['user_id']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "<td>
                    <a href='edit_user.php?id={$user['user_id']}' class='btn btn-sm btn-primary'>Edit</a>
            <form action='../../controllers/admin/admin_controller.php' method='post' class='d-inline delete-user-form'>
              <input type='hidden' name='user_id' value='{$user['user_id']}'>
              <input type='hidden' name='delete_user' value='1'>
              <button type='button' class='btn btn-sm btn-danger delete-user-btn' data-user-name='" . htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') . "'>Delete</button>
            </form>
                    <a href='view_user.php?id={$user['user_id']}' class='btn btn-sm btn-info'>View</a>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center'>No users found.</td></tr>";
    }
    ?>
  </tbody>
</table>

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
          $button.closest('form').trigger('submit');
        }
      });
    });
  });
  </script>