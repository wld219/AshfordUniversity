<?php
declare(strict_types=1);
?>

<div class="users-directory-header">
    <h3>User Directory</h3>
</div>

<form method="get" action="users.php"
      class="users-directory-search">

    <div class="search-field">
        <label for="search">Search users</label>
        <input
            type="search"
            name="search"
            id="search"
            value="<?= e($search) ?>"
            placeholder="Name, user ID, or email"
        >
    </div>

    <div class="users-role-filter">
        <label for="users-role">Account type</label>
        <select id="users-role" name="role">
            <option value="">All university users</option>
            <?php foreach (['Student'=>'Students', 'Faculty'=>'Faculty', 'Admin'=>'Administrators', 'StatStaff'=>'Statistics Staff'] as $value=>$label): ?>
                <option value="<?= e($value) ?>" <?= $roleFilter === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="search-actions">
        <button type="submit" class="btn">
            Search
        </button>

        <a href="users.php" class="btn light">
            Clear
        </a>
    </div>

</form>
