let db;
let swRegistration = null;
let capturedImageData = "";

let todoContainer = document.getElementById("todo-list");
let form = document.querySelector(".todo-form");
let themeBtn = document.getElementById("toggle-theme");
let inputNama = document.getElementById("nama");
let inputDesc = document.getElementById("desc");
let inputDl = document.getElementById("dl");
let inputNotifTime = document.getElementById("notif-time");
let inputFoto = document.getElementById("foto");
let previewContainer = document.getElementById("preview-container");
let imgPreview = document.getElementById("img-preview");
let btnHapusFoto = document.getElementById("btn-hapus-foto");
let ariaStatus = document.getElementById("aria-status");

let savedTheme = localStorage.getItem("theme");
if (savedTheme === "dark") {
  document.body.classList.add("dark-mode");
  themeBtn.textContent = "Light Mode";
}

themeBtn.addEventListener("click", function() {
  document.body.classList.toggle("dark-mode");
  if (document.body.classList.contains("dark-mode")) {
    themeBtn.textContent = "Light Mode";
    localStorage.setItem("theme", "dark");
  } else {
    themeBtn.textContent = "Dark Mode";
    localStorage.setItem("theme", "light");
  }
});

if ("serviceWorker" in navigator) {
  navigator.serviceWorker.register("sw.js").then(function(reg) {
    swRegistration = reg;
  });
}

if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
  Notification.requestPermission();
}

function bukaIndexedDB() {
  let request = indexedDB.open("TodoAppDB", 1);

  request.onupgradeneeded = function(e) {
    let database = e.target.result;
    if (!database.objectStoreNames.contains("todos")) {
      database.createObjectStore("todos", { keyPath: "id", autoIncrement: true });
    }
  };

  request.onsuccess = function(e) {
    db = e.target.result;
    muatTodos();
  };

  request.onerror = function() {
    alert("Gagal mengakses IndexedDB pada browser ini.");
  };
}

function muatTodos() {
  let tx = db.transaction("todos", "readonly");
  let store = tx.objectStore("todos");
  let req = store.getAll();

  req.onsuccess = function() {
    let data = req.result;
    if (data.length === 0) {
      let defaultTodos = [
        {
          title: "Tugas PWeb 1A",
          desc: "Buat web to do list dengan html dan css",
          deadline: "2026-09-14",
          notifTime: "",
          image: "",
          completed: false
        },
        {
          title: "Tugas PWeb 1B",
          desc: "Kerjain beberapa css games dari ppt",
          deadline: "2026-09-14",
          notifTime: "",
          image: "",
          completed: false
        },
        {
          title: "Tugas 1 KKA",
          desc: "Kerjain tugas 2 dengan algoritma UCS, BFS, dan DFS",
          deadline: "2026-09-14",
          notifTime: "",
          image: "",
          completed: false
        }
      ];

      let txAdd = db.transaction("todos", "readwrite");
      let storeAdd = txAdd.objectStore("todos");
      for (let i = 0; i < defaultTodos.length; i++) {
        storeAdd.add(defaultTodos[i]);
      }
      txAdd.oncomplete = function() {
        muatTodos();
      };
    } else {
      renderTodos(data);
    }
  };
}

function renderTodos(todos) {
  todoContainer.innerHTML = "";

  let belum = [];
  let sudah = [];

  for (let i = 0; i < todos.length; i++) {
    if (todos[i].completed) {
      sudah.push(todos[i]);
    } else {
      belum.push(todos[i]);
    }
  }

  let list = belum.concat(sudah);

  for (let i = 0; i < list.length; i++) {
    let item = list[i];
    let detail = document.createElement("details");
    detail.className = "todo";
    if (!item.completed) {
      detail.open = true;
    }

    let checkedAttr = "";
    let classCompleted = "";
    if (item.completed) {
      checkedAttr = "checked";
      classCompleted = "completed";
    }

    let imageHtml = "";
    if (item.image) {
      imageHtml = '<div class="todo-img-wrapper"><img src="' + item.image + '" alt="Foto tugas ' + item.title + '" class="todo-img"></div>';
    }

    let notifHtml = "";
    if (item.notifTime) {
      notifHtml = '<p><strong>Waktu Pengingat:</strong> ' + item.notifTime + '</p>';
    }

    detail.innerHTML =
      '<summary class="todo-sum" aria-label="Ringkasan tugas: ' + item.title + '">' +
        '<div class="task-header">' +
          '<input type="checkbox" class="task-checkbox" ' + checkedAttr + ' aria-label="Tandai selesai untuk ' + item.title + '">' +
          '<label class="task-name ' + classCompleted + '">' + item.title + '</label>' +
        '</div>' +
        '<span class="click-hint" aria-hidden="true">▼</span>' +
      '</summary>' +
      '<div class="todo-details">' +
        '<p><strong>Deskripsi:</strong> ' + item.desc + '</p>' +
        '<p><strong>Deadline:</strong> ' + item.deadline + '</p>' +
        notifHtml +
        imageHtml +
        '<div class="action-buttons">' +
          '<button type="button" class="btn-edit" aria-label="Edit tugas ' + item.title + '">Edit</button>' +
          '<button type="button" class="btn-delete" aria-label="Hapus tugas ' + item.title + '">Delete</button>' +
        '</div>' +
      '</div>';

    let checkbox = detail.querySelector(".task-checkbox");
    checkbox.addEventListener("change", function() {
      item.completed = checkbox.checked;
      let tx = db.transaction("todos", "readwrite");
      tx.objectStore("todos").put(item);
      tx.oncomplete = function() {
        umumkanStatus(item.completed ? "Tugas selesai" : "Tugas diaktifkan kembali");
        muatTodos();
      };
    });

    let deleteBtn = detail.querySelector(".btn-delete");
    deleteBtn.addEventListener("click", function() {
      if (confirm("Yakin ingin menghapus tugas ini?")) {
        let tx = db.transaction("todos", "readwrite");
        tx.objectStore("todos").delete(item.id);
        tx.oncomplete = function() {
          umumkanStatus("Tugas berhasil dihapus");
          muatTodos();
        };
      }
    });

    let editBtn = detail.querySelector(".btn-edit");
    editBtn.addEventListener("click", function() {
      let gantiJudul = prompt("Edit judul tugas:", item.title);
      if (gantiJudul !== null && gantiJudul.trim() !== "") {
        item.title = gantiJudul.trim();
      }

      let gantiDesc = prompt("Edit deskripsi tugas:", item.desc);
      if (gantiDesc !== null) {
        item.desc = gantiDesc.trim();
      }

      let gantiDl = prompt("Edit deadline (YYYY-MM-DD):", item.deadline);
      if (gantiDl !== null && gantiDl.trim() !== "") {
        item.deadline = gantiDl.trim();
      }

      let tx = db.transaction("todos", "readwrite");
      tx.objectStore("todos").put(item);
      tx.oncomplete = function() {
        umumkanStatus("Tugas berhasil diperbarui");
        muatTodos();
      };
    });

    todoContainer.appendChild(detail);
  }
}

inputFoto.addEventListener("change", function() {
  let file = inputFoto.files[0];
  if (file) {
    let reader = new FileReader();
    reader.onload = function(e) {
      capturedImageData = e.target.result;
      imgPreview.src = capturedImageData;
      previewContainer.style.display = "block";
    };
    reader.readAsDataURL(file);
  }
});


btnHapusFoto.addEventListener("click", function() {
  capturedImageData = "";
  imgPreview.src = "";
  previewContainer.style.display = "none";
  inputFoto.value = "";
});

form.addEventListener("submit", function(e) {
  e.preventDefault();

  let tugasBaru = {
    title: inputNama.value.trim(),
    desc: inputDesc.value.trim(),
    deadline: inputDl.value,
    notifTime: inputNotifTime.value,
    image: capturedImageData,
    completed: false
  };

  let tx = db.transaction("todos", "readwrite");
  tx.objectStore("todos").add(tugasBaru);
  tx.oncomplete = function() {
    umumkanStatus("Tugas baru berhasil ditambahkan");
    muatTodos();
  };

  inputNama.value = "";
  inputDesc.value = "";
  inputDl.value = "";
  inputNotifTime.value = "";
  capturedImageData = "";
  imgPreview.src = "";
  previewContainer.style.display = "none";
  inputFoto.value = "";
});

function umumkanStatus(pesan) {
  ariaStatus.textContent = pesan;
  setTimeout(function() {
    ariaStatus.textContent = "";
  }, 2000);
}

setInterval(function() {
  if (!db) return;
  let sekarang = new Date();
  let jam = String(sekarang.getHours()).padStart(2, "0");
  let menit = String(sekarang.getMinutes()).padStart(2, "0");
  let waktuSekarang = jam + ":" + menit;

  let tx = db.transaction("todos", "readonly");
  let store = tx.objectStore("todos");
  let req = store.getAll();

  req.onsuccess = function() {
    let data = req.result;
    for (let i = 0; i < data.length; i++) {
      if (!data[i].completed && data[i].notifTime === waktuSekarang && !data[i].notified) {
        data[i].notified = true;
        let updateTx = db.transaction("todos", "readwrite");
        updateTx.objectStore("todos").put(data[i]);

        if (Notification.permission === "granted") {
          if (swRegistration) {
            swRegistration.showNotification("Pengingat Tugas: " + data[i].title, {
              body: data[i].desc || "Waktunya mengerjakan tugas!",
              icon: data[i].image || ""
            });
          } else {
            new Notification("Pengingat Tugas: " + data[i].title, {
              body: data[i].desc || "Waktunya mengerjakan tugas!"
            });
          }
        }
      }
    }
  };
}, 10000);

bukaIndexedDB();
