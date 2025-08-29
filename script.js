const apiUrl = 'api.php';

async function fetchTasks() {
  const res = await fetch(apiUrl);
  const tasks = await res.json();
  displayTasks(tasks);
}

function displayTasks(tasks) {
  const list = document.getElementById('taskList');
  list.innerHTML = '';
  tasks.forEach(task => {
    const li = document.createElement('li');
    li.className = task.completed ? 'completed' : '';
    li.innerHTML = `
      <strong>${task.title}</strong> (Importance: ${task.importance}, Priorité: ${task.priority_score.toFixed(2)})<br/>
      Date limite: ${task.due_date ? task.due_date : 'Aucune'}<br/>
      <button onclick="toggleComplete(${task.id}, ${task.completed})">${task.completed ? 'Marquer non fait' : 'Marquer fait'}</button>
      <button onclick="deleteTask(${task.id})">Supprimer</button>
    `;
    list.appendChild(li);
  });
}

async function addTask() {
  const title = document.getElementById('title').value.trim();
  const importance = parseInt(document.getElementById('importance').value);
  const due_date = document.getElementById('due_date').value;

  if (!title) {
    alert('Veuillez entrer un titre');
    return;
  }

  const res = await fetch(apiUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ title, importance, due_date })
  });

  if (res.ok) {
    document.getElementById('title').value = '';
    document.getElementById('importance').value = '3';
    document.getElementById('due_date').value = '';
    fetchTasks();
  } else {
    alert('Erreur lors de l\'ajout');
  }
}

async function toggleComplete(id, currentStatus) {
  const formData = new URLSearchParams();
  formData.append('id', id);
  formData.append('completed', currentStatus ? 0 : 1);

  const res = await fetch(apiUrl, {
    method: 'PUT',
    body: formData
  });

  if (res.ok) {
    fetchTasks();
  } else {
    alert('Erreur lors de la mise à jour');
  }
}

async function deleteTask(id) {
  const formData = new URLSearchParams();
  formData.append('id', id);

  const res = await fetch(apiUrl, {
    method: 'DELETE',
    body: formData
  });

  if (res.ok) {
    fetchTasks();
  } else {
    alert('Erreur lors de la suppression');
  }
}

// Initial load
fetchTasks();
