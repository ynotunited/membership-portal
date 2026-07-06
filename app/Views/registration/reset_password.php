<h2>Reset Password</h2>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<form method="post">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <div class="mb-3">
        <label>New Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Strong password">
    </div>
    <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="password_confirm" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Reset Password</button>
</form> 