<?php

/**
 * @return string[]
 */
function generateTimeSlots(): array
{
    $slots = [];
    for ($hour = 0; $hour < 24; $hour++) {
        $slots[] = sprintf('%02d:00', $hour);
    }
    return $slots;
}

/**
 * generate a week from the specified $week_start to $week_start +7 days
 * @return DateTime[]
 */
function generateWeekDays(DateTime $week_start): array
{
    $days = [];
    $currentDay = clone $week_start;

    for ($i = 0; $i < 7; $i++) {
        $days[] = clone $currentDay;
        $currentDay->modify('+1 day');
    }

    return $days;
}

function renderTimeColumn(): string
{
    $time_column = '';

    foreach (generateTimeSlots() as $time_slot) {
        $time_column .= <<<HTML
            <div
                id="time-cell"
                class="h-[60px] relative p-2 border-t border-neutral-500"
            >
            $time_slot
            </div>\n
        HTML;
    }

    return $time_column;
}

function renderDayColumn(DateTime $day): string
{
    $week_day_name = strtolower($day->format('l'));
    $day_column = '';

    foreach (generateTimeSlots() as $time_slot) {
        $dateFmt = $day->format('Y-m-d');


        $day_column .= <<<HTML
            <div 
                id="time-cell"
                class="h-[60px] relative p-2 border-t border-neutral-500" 
                data-date="$dateFmt"
                data-time="$time_slot"
            >
            </div>\n
        HTML;
    }

    return <<<HTML
        <div id="day-column" data-week="$week_day_name" class="relative min-w-[80px] border-l border-neutral-500">
            $day_column
        </div>\n
    HTML;
}

function renderGrid(DateTime $week_start): string
{
    $time_column = renderTimeColumn();
    $week_grid = '';

    foreach (generateWeekDays($week_start) as $day) {
        $week_grid .= renderDayColumn($day);
    }

    return <<<HTML
        <div id="grid-container" class="grid relative grid-cols-8 gap-1 scrollbar-none overflow-auto h-[800px]">
            <div id="time-column">
                $time_column
            </div>

            $week_grid
        </div>\n
    HTML;
}

function renderGridHeader(DateTime $week_start): string
{
    $header = '';

    foreach (generateWeekDays($week_start) as $day) {
        $ymd = $day->format('Y-m-d');
        $week_day_name = strtolower($day->format('l'));
        $week_day = $day->format('D');
        $month_day_month = $day->format('j M');

        $header .= <<<HTML
        <div 
            id="grid-header-$week_day_name"
            class="p-2 text-center bg-neutral-800"
            data-date="$ymd"
        >
            $week_day
            <br>
            $month_day_month
        </div>
        HTML;
    }

    return <<<HTML
        <div id="grid-header-container">
            <div class="grid grid-cols-8 gap-1 bg-neutral-800 sticky top-0 z-10">
                <div class="flex relative justify-between bg-neutral-800">
                    <button id="task-add" class="border border-neutral-700 rounded opacity-50 hover:opacity-100 hover:bg-neutral-700 bg-neutral-900 w-full h-full">
                        <svg class="inline w-5 h-5 bg-transparent fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"/></svg>
                    </button>

                    <button id="tasks-refresh" class="border border-neutral-700 rounded opacity-50 hover:opacity-100 hover:bg-neutral-700 bg-neutral-900 w-full h-full">
                        <svg id="tasks-refresh-svg" class="inline w-5 h-5 bg-transparent fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M105.1 202.6c7.7-21.8 20.2-42.3 37.8-59.8c62.5-62.5 163.8-62.5 226.3 0L386.3 160 352 160c-17.7 0-32 14.3-32 32s14.3 32 32 32l111.5 0c0 0 0 0 0 0l.4 0c17.7 0 32-14.3 32-32l0-112c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 35.2L414.4 97.6c-87.5-87.5-229.3-87.5-316.8 0C73.2 122 55.6 150.7 44.8 181.4c-5.9 16.7 2.9 34.9 19.5 40.8s34.9-2.9 40.8-19.5zM39 289.3c-5 1.5-9.8 4.2-13.7 8.2c-4 4-6.7 8.8-8.1 14c-.3 1.2-.6 2.5-.8 3.8c-.3 1.7-.4 3.4-.4 5.1L16 432c0 17.7 14.3 32 32 32s32-14.3 32-32l0-35.1 17.6 17.5c0 0 0 0 0 0c87.5 87.4 229.3 87.4 316.7 0c24.4-24.4 42.1-53.1 52.9-83.8c5.9-16.7-2.9-34.9-19.5-40.8s-34.9 2.9-40.8 19.5c-7.7 21.8-20.2 42.3-37.8 59.8c-62.5 62.5-163.8 62.5-226.3 0l-.1-.1L125.6 352l34.4 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L48.4 288c-1.6 0-3.2 .1-4.8 .3s-3.1 .5-4.6 1z"/></svg>
                    </button>
                </div>

                $header
            </div>
        </div>
    HTML;
}

function renderTaskBlock(
    string $top,
    string $height,
    string $title,
    string $start_time,
    string $end_time,
    string $id
): string {
    return <<<HTML
        <div 
            id="task-block"
            class='
            left-1 right-0 hover:bg-neutral-700 bg-neutral-600
            opacity-80 hover:opacity-100 z-20 overflow-hidden transition-all
            duration-100 absolute border-t border-red-800 group
            '
            style="top: {$top}px; height: {$height}px;"
        >
            <div class="p-2 bg-transparent">
                <div class="flex justify-between bg-transparent w-full">
                    <div class="bg-transparent font-bold truncate">$title</div>
                    <button
                        id="task-delete"
                        data-id="{$id}"
                        class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 hover:text-red-800 text-xl cursor-pointer bg-transparent duration-200 text-red-500"
                    >x</button>

                    <button
                        id="task-edit"
                        data-id="{$id}"
                        data-title="{$title}"
                        data-start-time="{$start_time}"
                        data-end-time="{$end_time}"
                        class="absolute top-7 right-1 text-xl hover:fill-green-800 fill-green-500 opacity-0 group-hover:opacity-100 hover:text-red-800 cursor-pointer bg-transparent duration-200"
                    >
                        <svg class="inline w-3 h-3 bg-transparent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z"/></svg>
                    </button>
                </div>

                <div class="bg-transparent text-sm">$start_time - $end_time</div>
            </div>
        </div>
    HTML;
}

function genereteJSTasks(): string
{
    $js = '';

    foreach ($_SESSION["tasks"] as $task) {
        $js .= <<<JS
        {
            date: "{$task["date"]}",
            title: "{$task["title"]}",
            start: {$task["start"]},
            end: {$task["end"]},
        },
        JS;
    }

    return $js;
}


function generateTaskDialog(): string
{
    return <<<HTML
    <div id="task-dialog" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="p-6 rounded-lg shadow-xl w-96">
            <h3 id="task-header" class="text-xl font-bold mb-4" data-default="Add New Task">Add New Task</h3>

            <form id="task-form">

                <!--  TITLE  -->
                <div class="mb-4">
                    <label class="block mb-2">Title</label>
                    <input type="text" id="task-title" name="task-title" class="w-full px-3 py-2 border border-neutral-800 rounded" required>
                </div>

                <!--  DATE  -->
                <div class="mb-4 flex gap-[3px]">
                    <div>
                        <input type='radio' name='week-day' value="monday" id="task-monday" class='hidden peer' required checked>
                        <label for='task-monday' class="px-3 py-2 border border-neutral-800 rounded cursor-pointer peer-checked:bg-neutral-700 peer-checked:hover:bg-neutral-600 hover:bg-neutral-800 transition-colors duration-200">Mo</label>
                    </div>
                    <div>
                        <input type='radio' name='week-day' value="tuesday" id="task-tuesday" class='hidden peer' required>
                        <label for='task-tuesday' class="px-3 py-2 border border-neutral-800 rounded cursor-pointer peer-checked:bg-neutral-700 peer-checked:hover:bg-neutral-600 hover:bg-neutral-800 transition-colors duration-200">Tu</label>
                    </div>
                    <div>
                        <input type='radio' name='week-day' value="wednesday" id="task-wednesday" class='hidden peer' required>
                        <label for='task-wednesday' class="px-3 py-2 border border-neutral-800 rounded cursor-pointer peer-checked:bg-neutral-700 peer-checked:hover:bg-neutral-600 hover:bg-neutral-800 transition-colors duration-200">We</label>
                    </div>
                    <div>
                        <input type='radio' name='week-day' value="thursday" id="task-thursday" class='hidden peer' required>
                        <label for='task-thursday' class="px-3 py-2 border border-neutral-800 rounded cursor-pointer peer-checked:bg-neutral-700 peer-checked:hover:bg-neutral-600 hover:bg-neutral-800 transition-colors duration-200">Th</label>
                    </div>
                    <div>
                        <input type='radio' name='week-day' value="friday" id="task-friday" class='hidden peer' required>
                        <label for='task-friday' class="px-3 py-2 border border-neutral-800 rounded cursor-pointer peer-checked:bg-neutral-700 peer-checked:hover:bg-neutral-600 hover:bg-neutral-800 transition-colors duration-200">Fr</label>
                    </div>
                    <div>
                        <input type='radio' name='week-day' value="saturday" id="task-saturday" class='hidden peer' required>
                        <label for='task-saturday' class="px-3 py-2 border border-neutral-800 rounded cursor-pointer peer-checked:bg-neutral-700 peer-checked:hover:bg-neutral-600 hover:bg-neutral-800 transition-colors duration-200">Sa</label>
                    </div>
                    <div>
                        <input type='radio' name='week-day' value="sunday" id="task-sunday" class='hidden peer' required>
                        <label for='task-sunday' class="px-3 py-2 border border-neutral-800 rounded cursor-pointer peer-checked:bg-neutral-700 peer-checked:hover:bg-neutral-600 hover:bg-neutral-800 transition-colors duration-200">Su</label>
                    </div>
                </div>



                <!--  START TIME  -->
                <div class="mb-4">
                    <label class="block mb-2">Start Time</label>
                    <input type="time" id="task-start" name="task-start" class="w-full px-3 py-2 border border-neutral-800 rounded" value="00:00" required>
                </div>

                <!--  END TIME  -->
                <div class="mb-4">
                    <label class="block mb-2">End Time</label>
                    <input type="time" id="task-end" name="task-end" class="w-full px-3 py-2 border border-neutral-800 rounded" value="13:37" required>
                </div>


                <!--  UPDATE ONLY ID  -->
                <input id='task-id' class='hidden' type='text' name="task-id" value="-1">

                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        id='hide-task-dialog'
                        class="px-4 py-2 opacity-80 hover:opacity-100 hover:bg-neutral-800 border border-neutral-800 rounded"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 opacity-80 hover:opacity-100 hover:bg-neutral-800 border border-neutral-800 rounded"
                    >
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    HTML;
}

function renderAccountInfo(string $name, string $picture)
{
    return <<<HTML
    <svg id="acc-info-trigger" class="absolute flex w-12 h-12 top-0 right-10 fill-neutral-500 hover:fill-neutral-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M137.4 374.6c12.5 12.5 32.8 12.5 45.3 0l128-128c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8L32 192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l128 128z"/></svg>
    <div id="acc-info" class="hidden absolute top-10 right-5 grid items-center justify-items-end border rounded border-neutral-800 drop-shadow-2xl z-20 p-5 w-40 ">
        <span class="text-2xl">$name</span>
        <img class="w-14 h-14 object-contain" src="$picture" alt="$name">

        <a
            class="flex items-center mt-2 border border-neutral-800 hover:bg-neutral-800 gap-2 p-1"
            href="/logout"
        >
            <svg class="inline w-6 h-6 fill-current bg-transparent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/>
            </svg>
            Logout
        </a>
    </div>
    HTML;
}

function renderCurrTimeBar(string $curr_time): string
{
    return <<<HTML
        <div
            id="curr-time-bar"
            class="border border-red-500 w-full absolute"
            style="top: {$curr_time}px;"
        ></div>
    HTML;
}

$date       = new DateTime();
$week_start = clone $date->modify('monday this week');
$name       = $_SESSION['name'];
$picture    = $_SESSION['picture'];

?>

<!DOCTYPE html>
<html>
    <head>
        <title>new</title>
        <link rel="stylesheet" href="global.css"/>
        <link rel="stylesheet" href="tailwind.css"/>
    </head>
    <body>
        <div class="container mx-auto px-4 py-8 h-screen">
            <div id="navigation-container">
                <?= renderNavigationControls($week_start) ?>
            </div>
            <div
                id='grid'
                class="shadow-lg rounded-lg overflow-hidden mt-4"
            >
                <?= renderGridHeader($week_start) ?>

                <?= renderGrid($week_start) ?>
            </div>
        </div>

        <?= renderAccountInfo($name, $picture); ?>
        <?= generateTaskDialog(); ?>

        <script>
            function toggleTaskDialog() {
                const dialog = document.getElementById('task-dialog')

                if (dialog.classList.contains('hidden')) {
                    dialog.classList.remove('hidden');
                    return;
                }

                const taskHeader = document.querySelector('#task-header')
                taskHeader.textContent = taskHeader.dataset.default
                dialog.classList.add('hidden');
            }

            function getStartEndTimeTaskDialog(formData) {
                const date = document.getElementById('grid-header-' + formData["week-day"]).dataset.date

                const startDate = new Date(`${date}T${formData["task-start"]}:00`)
                const start = Math.floor(startDate.getTime() / 1000)

                const endDate = new Date(`${date}T${formData["task-end"]}:00`)
                const end = Math.floor(endDate.getTime() / 1000)

                return {start, end}
            }

            function startUpdateTastDialog(b) {
                const form = document.querySelector('#task-form');

                const id = b.dataset.id
                const title = b.dataset.title
                const start = b.dataset["startTime"]
                const end = b.dataset["endTime"]

                const week = b.parentNode.parentNode.parentNode.parentNode.dataset.week;

                form.querySelector('#task-title').value = title
                form.querySelector(`#task-${week}`).checked = true;
                form.querySelector(`#task-start`).value = start
                form.querySelector(`#task-end`).value = end
                form.querySelector(`#task-id`).value = id

                document.querySelector('#task-header').textContent = `Edit Task: ${title}`

                toggleTaskDialog();
            }

            function initTaskBlockListeners() {
                document.querySelectorAll('#task-delete').forEach((b) => {
                    b.addEventListener('click', () => deleteTask(b.dataset.id))
                })

                document.querySelectorAll('#task-edit').forEach((b) => {
                    b.addEventListener('click', () => {
                        startUpdateTastDialog(b);
                    })
                })
            }

            function addAccInfoListeners() {
                const accInfoTrigger = document.getElementById('acc-info-trigger');
                const accInfo = document.getElementById('acc-info');

                accInfo.addEventListener('mouseenter', (e) => {
                    accInfo.classList.toggle('hidden', false);
                });
                accInfo.addEventListener('mouseleave', (e) => {
                    accInfo.classList.toggle('hidden', true);
                });

                accInfoTrigger.addEventListener('mouseenter', (e) => {
                    accInfo.classList.toggle('hidden', false);
                });
                accInfoTrigger.addEventListener('mouseleave', (e) => {
                    accInfo.classList.toggle('hidden', true);
                });
            }

            const weekdays = ["", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];

            function highlightDay(day) {
                const currDayColumn = document.querySelector(`#day-column[data-week="${weekdays[day || 7]}"]`)
                currDayColumn.classList.remove("border-neutral-500")
                currDayColumn.classList.add("border-red-500")

                if (day) {
                    const nextDayColumn = document.querySelector(`#day-column[data-week="${weekdays[(day || 7) + 1]}"]`)
                    nextDayColumn.classList.remove("border-neutral-500")
                    nextDayColumn.classList.add("border-red-500")
                }
            }

            function renderCurrTimeBar() {
                let date = new Date();

                // remove old if present
                const oldCurrTimeBar = document.getElementById('curr-time-bar')
                if (oldCurrTimeBar) {
                    oldCurrTimeBar.remove()

                    const day = date.getDay();
                    document.querySelector(`#day-column[data-week="${weekdays[day || 7]}"]`)
                        .classList
                        .remove("border-red-500")

                    if (day) {
                        document.querySelector(`#day-column[data-week="${weekdays[(day || 7) + 1] }"]`)
                            .classList
                            .remove("border-red-500")
                    }
                }


                const sel_week_start = document.querySelectorAll('#grid-header-container .grid > div:not(:first-child)')[0].dataset.date;
                const cur_week_start = new Date(Date.now() - ((new Date().getDay() || 7) - 1) * 86400000).toLocaleDateString('en-CA');

                if (sel_week_start != cur_week_start)
                    return;

                const startHours = date.getHours() + date.getMinutes() / 60;
                const TIME_CELL_HEIGHT = 60;
                let currTime = startHours * TIME_CELL_HEIGHT;

                const line = `
                    <?= renderCurrTimeBar('${currTime}') ?>
                `;

                const grid = document.getElementById('grid-container');
                grid.insertAdjacentHTML("beforeend", line)

                highlightDay(date.getDay())
            }

            function toggleThrobbler() {
                const svg = document.getElementById('tasks-refresh-svg');
                svg.classList.toggle('animate-spin')
            }

            function loadTask(date, title, start, end, id) {
                const calculateTaskPosition = (startTime, endTime) => {
                    const parseTime = (unixTimestamp) => {
                        const date = new Date(unixTimestamp * 1000);
                        return date.getHours() + date.getMinutes() / 60;
                    }

                    const TIME_CELL_HEIGHT = 60;

                    const startHours = parseTime(startTime);
                    const endHours = parseTime(endTime);

                    const top = startHours * TIME_CELL_HEIGHT;
                    const height = (endHours - startHours) * TIME_CELL_HEIGHT;

                    return { top, height };
                }

                const dayColumn = document.querySelector(`#day-column:has([data-date="${date}"])`);
                if (!dayColumn) return;

                const { top, height } = calculateTaskPosition(start, end);

                const formatTime = (unixTimestamp) => {
                    const time = new Date(unixTimestamp * 1000);
                    return time.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    });
                };

                // this is the most disgusting thing ive done and I like it
                const task = `<?= renderTaskBlock(
                    '${top}',
                    '${height}',
                    '${title}',
                    '${formatTime(start)}',
                    '${formatTime(end)}',
                    '${id}'
                ) ?>`;

                dayColumn.insertAdjacentHTML("beforeend", task);
            }

            const sendRequest = (path, method, data) => {
                toggleThrobbler();

                fetch(path, {
                    method: method,
                    body: data,
                    headers: {
                        "X_WEEK_START": Math.floor(
                            new Date(document
                                .querySelector('#grid-header-container .grid > div:not(:first-child)')
                                .dataset.date
                            ).getTime() / 1000
                        ),
                        "X_WEEK_END": Math.floor(
                            new Date(document
                                .querySelector('#grid-header-container .grid > div:last-child')
                                .dataset.date
                            ).getTime() / 1000
                        ),
                    }
                })
                .then(response => response.json())
                .then(resp => loadTasks(resp))
                .catch(error => console.error('Error: ', error));
            }

            const addTask = (formData) => {
                const {start, end} = getStartEndTimeTaskDialog(formData)

                sendRequest('ajax/task/create',
                    'POST',
                    JSON.stringify({
                        title: formData["task-title"],
                        start_time: start,
                        end_time: end,
                    })
                )
            }

            const deleteTask = (id) => {
                sendRequest('ajax/task/delete', 'POST',
                    JSON.stringify({ id: id }) 
                )
            }

            const updateTask = (formData) => {
                const {start, end} = getStartEndTimeTaskDialog(formData)

                sendRequest('ajax/task/update', 'POST',
                    JSON.stringify({
                        title: formData["task-title"],
                        start_time: start,
                        end_time: end,
                        id: formData["task-id"]
                    }) 
                )
            }


            function loadTasks(resp) {
                document.querySelectorAll('#task-block').forEach(task => task.remove());

                for (task of resp) {
                    const start = parseInt(task.start_time);
                    const end = parseInt(task.end_time);
                    const date = new Date(start * 1000).toLocaleDateString('en-CA');
                    const title = task.title;
                    const id = task.id;

                    loadTask(date, title,  start, end, id)
                }

                initTaskBlockListeners();
                toggleThrobbler();
                renderCurrTimeBar();
            }

            function fetchTasks() {
                sendRequest('ajax/task/fetchall', 'GET', null)
            }

            document.getElementById('tasks-refresh').addEventListener("click", () => {
                fetchTasks();
            })

            document.getElementById('task-add').addEventListener("click", () => {
                document.querySelector('#task-form').reset()
                toggleTaskDialog();
            })

            document.getElementById('hide-task-dialog').addEventListener("click", () => {
                toggleTaskDialog();
            })

            document.getElementById('task-form').addEventListener('submit', (e) => {
                e.preventDefault()
                const formData = Object.fromEntries(new FormData(e.target).entries())
                const isCreateForm = formData["task-id"] == -1

                if (isCreateForm) {
                    addTask(formData);
                } else {
                    updateTask(formData)
                }

                toggleTaskDialog();
            });


            addAccInfoListeners();
            fetchTasks();
        </script>
        <?= generateCalendarDialogScript() ?>
    </body>
</html>

<?php
function renderNavigationControls(DateTime $week_start): string
{
    return <<<HTML
    <div class="flex justify-between items-center p-4">
        <button 
            id='prev-week'
            class='px-4 py-2 opacity-80 hover:opacity-100 hover:bg-neutral-800 border border-neutral-800 rounded'
        >
            Previous Week
        </button>

        <h2 
            id="nav-header"
            class='text-xl font-bold cursor-pointer hover:text-neutral-600'
        >
            {$week_start->format('F j, Y')}
        </h2>

        <button
            id='next-week'
            class='px-4 py-2 opacity-80 hover:opacity-100 hover:bg-neutral-800 border border-neutral-800 rounded'
        >
            Next Week
        </button>
    </div>

    <div
        id="calendar-dialog"
        class="hidden fixed inset-0 bg-gray-500 bg-opacity-20 flex justify-center z-50"
    >
        <div class="p-6 absolute top-[20%]  rounded-lg shadow-xl ">
            <div class="flex justify-between items-center mb-4">
                <button id='calendar-dialog-prev-month' class="p-2">
                    &lt;
                </button>
                <h3 id="calendar-header" class="text-xl font-bold"></h3>
                <button id='calendar-dialog-next-month' class="p-2">
                    &gt;
                </button>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center mb-2">
                <div class="font-bold">Mo</div>
                <div class="font-bold">Tu</div>
                <div class="font-bold">We</div>
                <div class="font-bold">Th</div>
                <div class="font-bold">Fr</div>
                <div class="font-bold">Sa</div>
                <div class="font-bold">Su</div>
            </div>
            <div id="calendar-days" class="grid grid-cols-7 gap-1">
            </div>
            <div class="mt-4 flex justify-end">
                <button id="hide-calendar-button" class="px-4 py-2 opacity-80 hover:opacity-100 hover:bg-neutral-800 border border-neutral-800 rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
    HTML;
}


function generateCalendarDialogScript(): string
{
    return <<<HTML
        <script>
            const currentDate = new Date();
            let selectedWeekStart = new Date();

            function changeWeek(offset) {
                const getNewWeek = (headerCells) => {
                    const currentDate = new Date(headerCells[0].dataset.date);
                    currentDate.setDate(currentDate.getDate() + (offset * 7));

                    let week = [];
                    for (let i = 0; i < 7; i++) {
                        const date = new Date(currentDate);
                        date.setDate(date.getDate() + i);
                        week.push(date);
                    }

                    return week;
                }

                const updateGridHeader = (week, headerCells) => {
                    headerCells.forEach((cell, index) => {
                        const date = week[index];

                        const weekDay = date.toLocaleDateString('en-US', { weekday: 'short' })
                        const monthDay = date.getDate();
                        const month = date.toLocaleDateString('en-US', { month: 'short' });

                        cell.innerHTML = `\${weekDay}<br>\${monthDay} \${month}`;
                        cell.dataset.date = date.toLocaleDateString('en-CA');
                    });
                }

                const updateNavigationHeader = (week) => {
                    const navigationHeader = document.querySelector('#nav-header');
                    navigationHeader.textContent = week[0].toLocaleDateString(
                        'en-US', { day: 'numeric', month: 'long', year: 'numeric' }
                    );
                }

                const updateGrid = (week) => {
                    const dayColumns = document.querySelectorAll('#day-column');

                    dayColumns.forEach((column, index) => {
                        const cells = column.querySelectorAll('#time-cell');
                        const date = week[index].toLocaleDateString('en-CA');
                        cells.forEach(cell => {
                            cell.dataset.date = date;
                        });
                    });

                }

                const headerCells = document.querySelectorAll('#grid-header-container .grid > div:not(:first-child)');

                const week = getNewWeek(headerCells);

                updateGridHeader(week, headerCells);
                updateNavigationHeader(week);
                updateGrid(week);

                fetchTasks();
            }



            function renderCalendar() {
                const selectWeek = (dateString) => {
                    const selectedDate = new Date(dateString);
                    const currentFirstDay = new Date(document.querySelector('#grid-header-container .grid > div:not(:first-child)').dataset.date);

                    const weeksDiff = Math.round((selectedDate - currentFirstDay) / (7 * 24 * 60 * 60 * 1000));

                    changeWeek(weeksDiff);

                    toggleCalendarDialog();
                }

                const getBoundaries = () => {
                    const year = selectedWeekStart.getFullYear();
                    const month = selectedWeekStart.getMonth();

                    const start = new Date(year, month, 1);
                    const end = new Date(year, month + 1, 0);

                    start.setDate(start.getDate() - (start.getDay() || 7) + 1);
                    end.setDate(end.getDate() + (7 - end.getDay()));
                    return {start, end}
                }

                const getWeekStart = (date) => {
                    const weekStart = new Date(date);
                    weekStart.setDate(weekStart.getDate() - (weekStart.getDay() || 7) + 1);
                    weekStart.setHours(0, 0, 0, 0);
                    return weekStart;
                }

                const addWeekHoverEffect = () => {
                    document.querySelectorAll('.week-row').forEach(weekRow => {
                        const handleHover = (event, shouldAdd) => {
                            weekRow.querySelectorAll('.calendar-day').forEach(day => {
                                day.classList.toggle('bg-neutral-700', shouldAdd);
                            });
                        };

                        weekRow.addEventListener('mouseenter', (e) => handleHover(e, true));
                        weekRow.addEventListener('mouseleave', (e) => handleHover(e, false));
                    });
                }

                const setHeader = () => {
                    const year = selectedWeekStart.getFullYear();
                    const month = selectedWeekStart.getMonth();
                    const monthName = new Date(year, month).toLocaleString(
                        'default',
                        {
                            month: 'long',
                            year: 'numeric',
                        }
                    );

                    document.getElementById('calendar-header').textContent = monthName;
                }

                const {start, end} = getBoundaries();
                const selectedWeek = getWeekStart(
                    new Date(document.querySelector('#grid-header-container .grid > div:not(:first-child)').dataset.date)
                );

                let html = '';
                let iterDate = new Date(start);
                const actualWeekStart = getWeekStart(currentDate)

                while (iterDate <= end) {
                    const isCurrentMonth = iterDate.getMonth() === selectedWeekStart.getMonth();
                    const isWeekStart = iterDate.getDay() === 1;
                    const isWeekEnd = iterDate.getDay() === 0;
                    const dateString = iterDate.toLocaleDateString('en-CA');

                    const weekStart = getWeekStart(iterDate);
                    const weekStartString = weekStart.toLocaleDateString('en-CA');

                    const isSelectedWeek = weekStart.getTime() === selectedWeek.getTime();
                    const isCurrentWeek = weekStart.getTime() === actualWeekStart.getTime();

                    if (isWeekStart) {
                        html += `<div class="week-row col-span-7 grid grid-cols-7 gap-1">`;
                    }

                    html += `
                        <button
                            id='select-week'
                            class="calendar-day p-2 text-center cursor-pointer rounded
                                   \${isCurrentMonth ? '' : 'text-neutral-400'}
                                   \${isSelectedWeek ? 'bg-neutral-800' : ''}
                                   \${isCurrentWeek ? 'text-red-400' : ''}"
                            data-date="\${dateString}"
                            data-weekstart='\${weekStartString}'
                        >
                            \${iterDate.getDate()}
                        </button>
                    `;


                    if (isWeekEnd) {
                        html += `</div>`;
                    }

                    iterDate.setDate(iterDate.getDate() + 1);
                }

                document.getElementById('calendar-days').innerHTML = html;

                document.querySelectorAll('#select-week').forEach(button => 
                    button.addEventListener('click', () => { selectWeek(button.dataset.weekstart) })
                )

                addWeekHoverEffect();
                setHeader();
            }

            function toggleCalendarDialog() {
                const dialog = document.getElementById('calendar-dialog');

                if (dialog.classList.contains('hidden')) {
                    const headerDate = document.querySelector('#grid-header-container .grid > div:not(:first-child)').dataset.date;
                    selectedWeekStart = new Date(headerDate);
                    renderCalendar();
                    dialog.classList.remove('hidden');
                } else {
                    dialog.classList.add('hidden');
                }
            }

            function changeMonth(offset) {
                selectedWeekStart.setMonth(selectedWeekStart.getMonth() + offset);
                renderCalendar();
            }

            function InitListeners() {
                document.getElementById('nav-header').addEventListener("click", toggleCalendarDialog);
                document.getElementById('hide-calendar-button').addEventListener("click", toggleCalendarDialog);

                document.getElementById('calendar-dialog-prev-month').addEventListener("click", () => {
                    changeMonth(-1);
                })

                document.getElementById('calendar-dialog-next-month').addEventListener("click", () => {
                    changeMonth(1);
                })

                document.getElementById('prev-week').addEventListener("click", () => {
                    changeWeek(-1);
                })

                document.getElementById('next-week').addEventListener("click", () => {
                    changeWeek(1);
                })
            }

            InitListeners();
        </script>
    HTML;
}
?>
