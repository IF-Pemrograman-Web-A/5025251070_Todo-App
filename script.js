let todos = [
  {
    title: "Tugas PWeb 1A",
    desc: "Buat web to do list dengan html dan css",
    deadline: "2026-09-14",
    completed: false
  },
  {
    title: "Tugas PWeb 1B",
    desc: "Kerjain beberapa css games dari ppt",
    deadline: "2026-09-14",
    completed: false
  },
  {
    title: "Tugas 1 KKA",
    desc: "Kerjain tugas 2 dengan algoritma UCS, BFS, dan DFS",
    deadline: "2026-09-14",
    completed: false
  }
];

let todoContainer = document.getElementById("todo-list");
let form = document.querySelector(".todo-form");
let themeBtn = document.getElementById("toggle-theme");

function renderTodos() {
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

    detail.innerHTML = 
      '<summary class="todo-sum">' +
        '<div class="task-header">' +
          '<input type="checkbox" class="task-checkbox" ' + checkedAttr + '>' +
          '<label class="task-name ' + classCompleted + '">' + item.title + '</label>' +
        '</div>' +
        '<span class="click-hint">▼</span>' +
      '</summary>' +
      '<div class="todo-details">' +
        '<p><strong>Deskripsi:</strong> ' + item.desc + '</p>' +
        '<p><strong>Deadline:</strong> ' + item.deadline + '</p>' +
        '<div class="action-buttons">' +
          '<button type="button" class="btn-edit">Edit</button>' +
          '<button type="button" class="btn-delete">Delete</button>' +
        '</div>' +
      '</div>';

    let checkbox = detail.querySelector(".task-checkbox");
    checkbox.addEventListener("change", function() {
      item.completed = checkbox.checked;
      renderTodos();
    });

    let deleteBtn = detail.querySelector(".btn-delete");
    deleteBtn.addEventListener("click", function() {
      for (let j = 0; j < todos.length; j++) {
        if (todos[j] === item) {
          todos.splice(j, 1);
          break;
        }
      }
      renderTodos();
    });

    let editBtn = detail.querySelector(".btn-edit");
    editBtn.addEventListener("click", function() {
      let gantiJudul = prompt("Edit judul tugas:", item.title);
      if (gantiJudul !== null && gantiJudul !== "") {
        item.title = gantiJudul;
      }

      let gantiDesc = prompt("Edit deskripsi tugas:", item.desc);
      if (gantiDesc !== null) {
        item.desc = gantiDesc;
      }

      let gantiDl = prompt("Edit deadline:", item.deadline);
      if (gantiDl !== null && gantiDl !== "") {
        item.deadline = gantiDl;
      }

      renderTodos();
    });

    todoContainer.appendChild(detail);
  }
}

form.addEventListener("submit", function(e) {
  e.preventDefault();

  let inputNama = document.getElementById("nama");
  let inputDesc = document.getElementById("desc");
  let inputDl = document.getElementById("dl");

  let tugasBaru = {
    title: inputNama.value,
    desc: inputDesc.value,
    deadline: inputDl.value,
    completed: false
  };

  todos.push(tugasBaru);
  renderTodos();

  inputNama.value = "";
  inputDesc.value = "";
  inputDl.value = "";
});

themeBtn.addEventListener("click", function() {
  document.body.classList.toggle("dark-mode");
  if (document.body.classList.contains("dark-mode")) {
    themeBtn.textContent = "Light Mode";
  } else {
    themeBtn.textContent = "Dark Mode";
  }
});

renderTodos();
