<?php
$current_category = isset($category) ? $category : 'Personal';

$count_personal_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM todos WHERE category = 'Personal'");
$count_personal = $count_personal_res ? mysqli_fetch_assoc($count_personal_res)['total'] : 0;

$count_shared_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM todos WHERE category = 'Shared'");
$count_shared = $count_shared_res ? mysqli_fetch_assoc($count_shared_res)['total'] : 0;
?>
<nav class="sidebar-nav" aria-label="Menu Kategori Tugas">
    <div class="sidebar-header">
        <h2>Menu</h2>
    </div>
    <ul class="nav-list">
        <li>
            <a href="index.php?category=Personal" class="nav-item <?php echo $current_category == 'Personal' ? 'active' : ''; ?>">
                <span class="nav-label">Personal</span>
                <span class="nav-badge"><?php echo $count_personal; ?></span>
            </a>
        </li>
        <li>
            <a href="index.php?category=Shared" class="nav-item <?php echo $current_category == 'Shared' ? 'active' : ''; ?>">
                <span class="nav-label">Shared</span>
                <span class="nav-badge"><?php echo $count_shared; ?></span>
            </a>
        </li>
    </ul>
</nav>
