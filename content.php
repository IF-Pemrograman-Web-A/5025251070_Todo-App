<aside class="tab-todo" role="region" aria-labelledby="form-heading">
    <h2 id="form-heading">Add a New Task</h2>

    <form class="todo-form" action="actions.php?action=add" method="POST" enctype="multipart/form-data" aria-label="Form Tambah Tugas">
        <div class="form">
            <label for="nama">Title <span aria-hidden="true">*</span></label>
            <input type="text" id="nama" name="title" required aria-required="true" placeholder="Nama tugas...">
        </div>

        <div class="form">
            <label for="desc">Description <span aria-hidden="true">*</span></label>
            <input type="text" id="desc" name="description" required aria-required="true" placeholder="Deskripsi tugas...">
        </div>

        <div class="form">
            <label for="dl">Deadline <span aria-hidden="true">*</span></label>
            <input type="date" id="dl" name="deadline" required aria-required="true">
        </div>

        <div class="form">
            <label for="cat">Category <span aria-hidden="true">*</span></label>
            <select id="cat" name="category" required class="form-select">
                <option value="Personal" <?php echo $category == 'Personal' ? 'selected' : ''; ?>>Personal</option>
                <option value="Shared" <?php echo $category == 'Shared' ? 'selected' : ''; ?>>Shared</option>
            </select>
        </div>

        <div class="form">
            <label for="foto">Image</label>
            <input type="file" id="foto" name="image" accept="image/*">
        </div>

        <button type="submit" class="subut" aria-label="Tambahkan tugas baru">Add Task</button>
    </form>
</aside>

<main id="main-content" class="mainscrn" role="main" aria-labelledby="tasks-heading">
    <div class="mainscrn-header">
        <h2 id="tasks-heading"><?php echo htmlspecialchars($category); ?> Tasks</h2>
        <span class="category-badge <?php echo strtolower($category); ?>"><?php echo htmlspecialchars($category); ?></span>
    </div>

    <div id="todo-list" role="feed" aria-label="Daftar tugas">
        <?php
        $safe_cat = mysqli_real_escape_string($conn, $category);
        $query = "SELECT * FROM todos WHERE category = '$safe_cat' ORDER BY completed ASC, id DESC";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $is_completed = $row['completed'] == 1;
                ?>
                <details class="todo" <?php echo !$is_completed ? 'open' : ''; ?>>
                    <summary class="todo-sum" aria-label="Ringkasan tugas: <?php echo htmlspecialchars($row['title']); ?>">
                        <div class="task-header">
                            <input type="checkbox" class="task-checkbox" <?php echo $is_completed ? 'checked' : ''; ?> 
                                   onclick="window.location.href='actions.php?action=toggle&id=<?php echo $row['id']; ?>&category=<?php echo urlencode($category); ?>'"
                                   aria-label="Tandai selesai untuk <?php echo htmlspecialchars($row['title']); ?>">
                            <label class="task-name <?php echo $is_completed ? 'completed' : ''; ?>">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </label>
                        </div>
                        <span class="click-hint" aria-hidden="true">▼</span>
                    </summary>
                    <div class="todo-details">
                        <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        <p><strong>Deadline:</strong> <?php echo htmlspecialchars($row['deadline']); ?></p>
                        <p><strong>Kategori:</strong> <span class="badge-tag <?php echo strtolower($row['category']); ?>"><?php echo htmlspecialchars($row['category']); ?></span></p>
                        
                        <?php if (!empty($row['image']) && file_exists("uploads/" . $row['image'])) { ?>
                            <div class="todo-img-wrapper">
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Foto tugas <?php echo htmlspecialchars($row['title']); ?>" class="todo-img">
                            </div>
                        <?php } ?>

                        <div class="action-buttons">
                            <a href="edit.php?id=<?php echo $row['id']; ?>&category=<?php echo urlencode($category); ?>" class="btn-edit" aria-label="Edit tugas">Edit</a>
                            <a href="actions.php?action=delete&id=<?php echo $row['id']; ?>&category=<?php echo urlencode($category); ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus tugas ini?');" aria-label="Hapus tugas">Delete</a>
                        </div>
                    </div>
                </details>
                <?php
            }
        } else {
            ?>
            <div class="empty-state">
                <p>Belum ada tugas di kategori <strong><?php echo htmlspecialchars($category); ?></strong>.</p>
            </div>
            <?php
        }
        ?>
    </div>
</main>
