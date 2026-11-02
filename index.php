<?php
require_once 'config.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'Personal';
if ($category != 'Personal' && $category != 'Shared') {
    $category = 'Personal';
}

include 'header.php';
?>

<div class="layout-wrapper">
    <?php include 'menu-bar.php'; ?>
    <div class="layout">
        <?php include 'content.php'; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
