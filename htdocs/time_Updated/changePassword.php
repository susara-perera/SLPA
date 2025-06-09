<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include('includes/header2.php');
include('includes/navbar.php');
include('./dbc.php');

// Retrieve error and success messages from the session, if they exist
$errorMessages = isset($_SESSION['error_messages']) ? $_SESSION['error_messages'] : [];
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear the messages after displaying them
unset($_SESSION['error_messages']);
unset($_SESSION['success_message']);
?>


<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>Change Password</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php foreach ($errorMessages as $message): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($message); ?></div>
                <?php endforeach; ?>
                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMessage); ?></div>
                <?php endif; ?>
                <form method="POST" action="ChangePasswordAction.php">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<br><br><br><br>
<?php
include('includes/scripts.php');
include('includes/footer.php');
?>