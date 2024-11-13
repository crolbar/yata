<?php
/**
 * @var array $tasks
 */


function generateListItem(string $id, string $title): string
{
    return <<<HTML
        <li class='flex gap-2'>
            <button
                id='show-update-button'
                value='$id'
                class='bg-black text-green-800'
            >
                U
            </button>

            <button
                id='delete-button'
                value='$id'
                class='bg-black text-red-800'
            >
                X
            </button>

            <div class='block' id='task-title'>
                $title
            </div>
            <div class='hidden' id='task-id' data-id='$id'></div>

            <div class='hidden' id='task-update'>
                <form id="update-form" action="ajax/task/update" method="POST">
                    <div class="flex items-center justify-center">
                        <input class="bg-black border" id="update-task-title" name='title' value='$title' required>
                        <button id="update-button" class="bg-black border">apply</button>
                    </div>
                </form>
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

                    const title = item.querySelector("div#task-title").innerText
                    const id = parseInt(item.querySelector("div#task-id").dataset.id)

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
                        let addedElement = list.lastElementChild;

                        addDeleteButtonListener(addedElement.querySelector('button#delete-button'))
                        addShowUpdateButtonListener(addedElement.querySelector('button#show-update-button'))
                        addUpdateFormListener(addedElement.querySelector('form#update-form'))
                    });
                }
            }

            const toggleUpdateFormVisability = (listItem) => {
                let hiddenDivClass = listItem.querySelector('div#task-update').attributes["class"]
                hiddenDivClass.nodeValue = (hiddenDivClass.nodeValue === "hidden") ? "block" : "hidden"

                let titleDivClass = listItem.querySelector('div#task-title').attributes["class"]
                titleDivClass.nodeValue = (titleDivClass.nodeValue === "hidden") ? "block" : "hidden"
            }

            const addShowUpdateButtonListener = (button) => {
                button.addEventListener("click", () => {
                    let listItem = button.parentNode;
                    toggleUpdateFormVisability(listItem)
                })
            }

            const addDeleteButtonListener = (button) => {
                button.addEventListener("click", () => {deleteTask(button.value)})
            }

            const addUpdateFormListener = (updateForm) => {
                updateForm.addEventListener("submit", (e) => {
                    e.preventDefault();

                    let updatedTitle = updateForm.querySelector("input#update-task-title").value;
                    let id = updateForm.parentNode.parentNode.querySelector('div#task-id').dataset.id;

                    updateTask(id, updatedTitle)

                    toggleUpdateFormVisability(updateForm.parentNode.parentNode)
                })
            }

            const onLoad = (resp) => {
                let fetchedTasks = new Map();

                for (let task of resp) {
                    fetchedTasks.set(task.id, task.title);
                }

                updateList(fetchedTasks);

                console.log("refresh finished")
            }

            const sendRequest = (path, method, data) => {
                fetch(path, {
                    method: method,
                    body: data,
                })
                .then(response => response.json())
                .then(resp => onLoad(resp))
                .catch(error => console.error('Error: ', error));
            }

            const refreshTasks = () => {
                sendRequest('ajax/task/fetchall', 'GET', null)
            }

            const createTask = (e) => {
                e.preventDefault();

                sendRequest('ajax/task/create', 'POST',
                    JSON.stringify({
                        title: document.getElementById("create-taks-title").value
                    }) 
                )
            }

            const deleteTask = (id) => {
                sendRequest('ajax/task/delete', 'POST',
                    JSON.stringify({
                        id: id
                    }) 
                )
            }

            const updateTask = (id, title) => {
                sendRequest('ajax/task/update', 'POST',
                    JSON.stringify({
                        title: title,
                        id: id
                    }) 
                )
            }
        </script>
    </head>

    <body>
        <div class="flex flex-col ml-20 gap-5">
            <button class="flex w-14 border" id="refresh">refresh</button>

            <ul id="task-list">
                <?php
                    foreach ($tasks as $task) {
                        echo generateListItem($task["id"], $task["title"]);
                    }
                ?>
            </ul>

            <form id="create-form" action="ajax/task/create" method="POST">
                <div class="flex-col items-center justify-center gap-5">
                    <input class="bg-black border" id="create-taks-title" name='title' required>
                    <button id="create-button" class="bg-black border p-1">Add task</button>
                </div>
            </form>
        </div>

        <script>
            // refresh button
            document.getElementById("refresh").addEventListener("click", refreshTasks);

            // create from submit
            document.getElementById("create-form").addEventListener("submit", createTask);

            // delete task
            document.querySelectorAll('button#delete-button').forEach((button) => {
                addDeleteButtonListener(button)
            })

            // hide/show update form
            document.querySelectorAll('button#show-update-button').forEach((button) => {
                addShowUpdateButtonListener(button)
            })

            // update form
            document.querySelectorAll('form#update-form').forEach((updateForm) => {
                addUpdateFormListener(updateForm)
            })
        </script>
    </body>
</html>
