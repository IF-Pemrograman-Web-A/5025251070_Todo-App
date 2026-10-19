<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="darkreader-lock">
    <meta name="color-scheme" content="light">
    <title>To Do List - Personal & Shared</title>
    <link rel="stylesheet" href="style.css">
    <script>
        let savedTheme = localStorage.getItem("theme");
        if (savedTheme === "dark") {
            document.addEventListener("DOMContentLoaded", function() {
                document.body.classList.add("dark-mode");
                let btn = document.getElementById("toggle-theme");
                if (btn) btn.textContent = "Light Mode";
            });
        }
        function toggleTheme() {
            document.body.classList.toggle("dark-mode");
            let btn = document.getElementById("toggle-theme");
            if (document.body.classList.contains("dark-mode")) {
                btn.textContent = "Light Mode";
                localStorage.setItem("theme", "dark");
            } else {
                btn.textContent = "Dark Mode";
                localStorage.setItem("theme", "light");
            }
        }
    </script>
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <header class="header-container" role="banner">
        <h1>To Do List</h1>
        <button type="button" id="toggle-theme" class="btn-theme" onclick="toggleTheme()" aria-label="Ganti mode tampilan">Dark Mode</button>
    </header>
