CREATE DATABASE IF NOT EXISTS todo_app;
USE todo_app;

CREATE TABLE IF NOT EXISTS todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    deadline DATE NOT NULL,
    category ENUM('Personal', 'Shared') NOT NULL DEFAULT 'Personal',
    image VARCHAR(255) NULL DEFAULT '',
    completed TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO todos (title, description, deadline, category, image, completed) VALUES
('Tugas PWeb 1A', 'Buat web to do list pake html dan css', '2026-09-14', 'Personal', '', 0),
('Tugas PWeb 1B', 'Kerjain css games dari ppt', '2026-09-14', 'Personal', '', 0),
('Tugas KKA', 'Ambil jurnal, bikin ppt', '2026-10-18', 'Shared', '', 0),
('Tugas KPPL', 'Observasi parkiran', '2026-10-25', 'Shared', '', 0);
