<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$category = isset($_GET['category']) ? $_GET['category'] : 'Personal';

$res = mysqli_query($conn, "SELECT * FROM todos WHERE id = $id");
if (!$res || mysqli_num_rows($res) == 0) {
    header("Location: index.php?category=" . urlencode($category));
    exit;
}

$task = mysqli_fetch_assoc($res);

include 'header.php';
?>

<div class="layout-wrapper">
    <?php include 'menu-bar.php'; ?>
    <div class="layout">
        <aside class="tab-todo" style="grid-column: 1 / -1; max-width: 600px; margin: 0 auto;" role="region" aria-labelledby="edit-heading">
            <h2 id="edit-heading">Edit Task</h2>

            <form class="todo-form" action="actions.php?action=edit" method="POST" enctype="multipart/form-data" aria-label="Form Edit Tugas">
                <input type="hidden" name="id" value="<?php echo $task['id']; ?>">

                <div class="form">
                    <label for="nama">Title <span aria-hidden="true">*</span></label>
                    <input type="text" id="nama" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required aria-required="true">
                </div>

                <div class="form">
                    <label for="desc">Description <span aria-hidden="true">*</span></label>
                    <input type="text" id="desc" name="description" value="<?php echo htmlspecialchars($task['description']); ?>" required aria-required="true">
                </div>

                <div class="form">
                    <label for="dl">Deadline <span aria-hidden="true">*</span></label>
                    <input type="date" id="dl" name="deadline" value="<?php echo htmlspecialchars($task['deadline']); ?>" required aria-required="true">
                </div>

                <div class="form">
                    <label for="cat">Category <span aria-hidden="true">*</span></label>
                    <select id="cat" name="category" required class="form-select">
                        <option value="Personal" <?php echo $task['category'] == 'Personal' ? 'selected' : ''; ?>>Personal</option>
                        <option value="Shared" <?php echo $task['category'] == 'Shared' ? 'selected' : ''; ?>>Shared</option>
                    </select>
                </div>

                <div class="form">
                    <label for="foto">Image</label>
                    <?php if (!empty($task['image']) && file_exists("uploads/" . $task['image'])) { ?>
                        <div style="margin-bottom: 6px;">
                            <img src="uploads/<?php echo htmlspecialchars($task['image']); ?>" alt="Foto saat ini" style="max-height: 100px; border-radius: 6px; display: block; margin-bottom: 4px;">
                        </div>
                    <?php } ?>
                    <input type="file" id="foto" name="image" accept="image/*">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" class="subut" style="margin-top: 0;">Update Task</button>
                    <a href="index.php?category=<?php echo urlencode($category); ?>" class="btn-cancel" style="display: inline-block; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; background-color: #94a3b8; color: #ffffff;">Cancel</a>
                </div>
            </form>
        </aside>
    </div>
</div>

<?php include 'footer.php'; ?>
