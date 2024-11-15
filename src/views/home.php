<?php
/**
 * @var array $tasks
 */

$email   = $_SESSION['email'];
$name    = $_SESSION['name'];
$picture = $_SESSION['picture'];


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
                    <div 
                        class="flex items-center justify-center"
                    >
                        <input 
                            class="border border-neutral-800 hover:bg-neutral-900"
                            id="update-task-title"
                            name='title'
                            value='$title'
                            required
                        >

                        <button 
                            id="update-button"
                            class="border border-neutral-800 hover:bg-neutral-800 text-x"
                        >
                            Apply
                        </button>
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
        <div class="flex gap-20 h-screen">
            <div class="flex flex-col ml-20 gap-5">
                <button 
                    class="flex w-20 h-8 justify-center items-center text-center mt-4 gap-2 border border-neutral-800 hover:bg-neutral-800 p-2"


                    id="refresh"
                >
                    refresh
                </button>

                <ul id="task-list">
                    <?php
                        foreach ($tasks as $task) {
                            echo generateListItem($task["id"], $task["title"]);
                        }
                    ?>
                </ul>

                <form id="create-form" action="ajax/task/create" method="POST">
                    <div class="flex-col items-center justify-center gap-5">
                        <input 
                            class="bg-black border border-neutral-800"
                            id="create-taks-title"
                            name='title' 
                            required
                        >
                        <button 
                            id="create-button"
                            class="bg-black border border-neutral-800 hover:bg-neutral-800 p-1"
                        >
                            Add task
                        </button>
                    </div>
                </form>
            </div>

            <div>
                <div>
                    <span class="text-2xl"><?=$name?></span>
                </div>
                <img 
                    class="w-24 h-24 object-contain" 
                    src="<?=$picture?>" 
                    alt="<?=$name?>"
                >

                <a
                    class="flex h-8 items-center mt-4 border border-neutral-800 hover:bg-neutral-800 gap-2 p-2"
                    href="/logout"
                >
                    <svg 
                        class="inline w-6 h-6 fill-current bg-transparent"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/>
                    </svg>
                    Logout
                </a>
            </div>

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
