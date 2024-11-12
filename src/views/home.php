<?php
/**
 * @var array $tasks
 */

$styles = [];
?>

<!DOCTYPE html>
<html>
    <head>
        <title>yo</title>

        <link rel="stylesheet" href="global.css">
        <link rel="stylesheet" href="tailwind.css">
        <style> <?php foreach ($styles as $style) {
            require $style;
        } ?> </style>
    </head>
    <body>
        <div class="flex flex-col items-center justify-center gap-10">
            <button id="button">refresh</button>

            <ul id="task-list">
                <?php foreach ($tasks as $task): ?>
                    <li class="flex gap-2">
                        <button onclick='deleteTask(<?php echo $task["id"] ?>)' class="bg-black text-red-800">X</button>

                        <div id="task-title">
                            <?php echo $task["title"] ?>
                        </div>
                        <div class="hidden" id="task-id">
                            <?php echo $task["id"] ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <form id="create-form" action="ajax/task/create" method="POST">
                <div class="flex flex-col items-center justify-center gap-5">
                    <input class="bg-black border" id="create-taks-title" name='title' required>
                    <button class="bg-black border p-1">Add task</button>
                </div>
            </form>
        </div>

        <script>
            const updateList = (fetchedTasks) => {
                const list = document.getElementById("task-list");

                const childs = list.children;
                let len = childs.length;
                for (let i = 0; i < len; i++) {
                    let item = childs[i];

                    const title = item.querySelector("#task-title").innerText
                    const id = parseInt(item.querySelector("#task-id").innerText)

                    // removal
                    if (!fetchedTasks.has(id)) {
                        list.removeChild(item);
                        i--;
                        len--;
                    }

                    // update
                    if (fetchedTasks.has(id) && fetchedTasks.get(id) !== title) {
                        item.querySelector("#task-title").textContent = fetchedTasks.get(id)
                    }

                    // remove so we can see which ones are new at the end
                    // and append them
                    fetchedTasks.delete(id);
                }

                // append
                if (fetchedTasks.size > 0) {
                    fetchedTasks.forEach((v, k) => {
                        const newTitle  = v;
                        const newId     = k;

                        const newItemHTML = `
                        <li class="flex gap-2">
                            <button onclick='deleteTask(${newId})' class="bg-black text-red-800">X</button>

                            <div id="task-title">
                                ${newTitle}
                            </div>
                            <div class="hidden" id="task-id">
                                ${newId}
                            </div>
                        </li>
                        `;
                        list.insertAdjacentHTML("beforeend", newItemHTML);
                    });
                }
            }

            const onLoad = (xhr) => {
                if (xhr.status === 200) {
                    let tasks = JSON.parse(xhr.responseText);
                    let fetchedTasks = new Map();

                    for (let task of tasks) {
                        fetchedTasks.set(task.id, task.title);
                    }

                    updateList(fetchedTasks);

                    console.log("refresh finished")
                }
            }

            const refreshTasks = () => {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", 'ajax/task/fetchall', true);
                xhr.onload = () => {onLoad(xhr)};
                xhr.send();
            }

            const createTask = (e) => {
                e.preventDefault();

                var xhr = new XMLHttpRequest();
                xhr.open("POST", 'ajax/task/create', true);
                xhr.onload = () => {onLoad(xhr)};

                let data = JSON.stringify({
                    title: document.getElementById("create-taks-title").value
                })

                xhr.send(data);
            }

            const deleteTask = (id) => {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", 'ajax/task/delete', true);
                xhr.onload = () => {onLoad(xhr)};

                let data = JSON.stringify({
                    id: id
                })

                xhr.send(data);
            }

            document.getElementById("button").addEventListener("click", refreshTasks);
            document.getElementById("create-form").addEventListener("submit", createTask);
        </script>
    </body>
</html>
