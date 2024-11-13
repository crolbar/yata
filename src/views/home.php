<?php
/**
 * @var array $tasks
 */


function generateListItem(string $id, string $title): string {
    return <<<HTML
        <li class='flex gap-2'>
            <button
                id='delete-button'
                value='$id'
                class='bg-black text-red-800'
            >
                X
            </button>

            <div id='task-title'>
                $title
            </div>
            <div class='hidden' id='task-id'>
                $id
            </div>
        </li>\n
    HTML;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>yo</title>

        <link rel="stylesheet" href="global.css">
        <link rel="stylesheet" href="tailwind.css">

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
                    fetchedTasks.forEach((title, id) => {
                        const itemHTML = `
                            <?php echo generateListItem("\${id}", "\${title}")?>
                        `
                        list.insertAdjacentHTML("beforeend", itemHTML);
                        list.lastElementChild.
                            querySelector('button#delete-button').
                            addEventListener("click", () => {
                                deleteTask(id)
                            })
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
        </script>
    </head>

    <body>
        <div class="flex flex-col items-center justify-center gap-10">
            <button id="button">refresh</button>

            <ul id="task-list">
                <?php
                    foreach($tasks as $task) {
                        echo generateListItem($task["id"], $task["title"]);
                    }
                ?>
            </ul>

            <form id="create-form" action="ajax/task/create" method="POST">
                <div class="flex flex-col items-center justify-center gap-5">
                    <input class="bg-black border" id="create-taks-title" name='title' required>
                    <button id="create-button" class="bg-black border p-1">Add task</button>
                </div>
            </form>
        </div>

        <script>
            document.getElementById("button").addEventListener("click", refreshTasks);
            document.getElementById("create-form").addEventListener("submit", createTask);

            document.querySelectorAll('button#delete-button').forEach((button) => {
                button.addEventListener("click", () => {deleteTask(button.value)})
            })
        </script>
    </body>
</html>
