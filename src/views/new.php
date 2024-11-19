<?php

// REMOVE THIS !!!!
date_default_timezone_set('EET');

function setTasks()
{

    if (isset($_SESSION['tasks'])) {
        return;
    }
    $date = new DateTime();

    $monday     = clone $date->modify('2024-10-30');
    $wednesday  = clone $date->modify('2024-11-30');
    $thursday   = clone $date->modify('2024-11-20');


    $_SESSION["tasks"] = [
        [ "date" => $monday->format('Y-m-d'),    "title" => 'Team Meeting',      "start" => $monday->modify('10:15')->format('U'),      "end" => $monday->modify('14:30')->format('U') ],
        [ "date" => $wednesday->format('Y-m-d'), "title" => 'Lunch Break',       "start" => $wednesday->modify('12:30')->format('U'),   "end" => $wednesday->modify('15:00')->format('U') ],
        [ "date" => $thursday->format('Y-m-d'),  "title" => 'Project Review',    "start" => $thursday->modify('17:00')->format('U'),    "end" => $thursday->modify('20:30')->format('U') ]
    ];

    //echo json_encode($_SESSION["tasks"]);
    //exit;
}


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
        <div id="day-column" class="relative min-w-[80px] border-l border-neutral-500">
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
        <div id="grid-container" class="grid grid-cols-8 gap-1 overflow-auto h-[800px]">
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
        $week_day = $day->format('D');
        $month_day_month = $day->format('j M');

        $header .= <<<HTML
        <div 
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
                <div class="p-2 bg-neutral-800"></div>
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
    string $end_time
): string {
    return <<<HTML
        <div 
            id="task-block"
            class='
            left-1 right-0 hover:bg-neutral-700 bg-neutral-600
            opacity-80 hover:opacity-100 z-20 overflow-hidden transition-all
            duration-100 absolute border-t border-red-800
            '
            style="top: {$top}px; height: {$height}px;"
        >
            <div class="p-2 bg-transparent">
                <div class="bg-transparent font-bold truncate">$title</div>
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


$date       = new DateTime();
$week_start = clone $date->modify('monday this week');
setTasks();
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

            <div id="navigationContainer">
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

        <script>
            function loadTask(date, title, start, end) {
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
                    '${formatTime(end)}'
                ) ?>`;

                dayColumn.insertAdjacentHTML("beforeend", task);
            }


            function loadInitTasks() {
                const tasks = [
                    <?= genereteJSTasks() ?>
                ];

                for (task of tasks) {
                    loadTask(task.date, task.title, task.start, task.end);
                }
            }

            loadInitTasks();
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
                <h3 id="calendarMonth" class="text-xl font-bold"></h3>
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
            <div id="calendarDays" class="grid grid-cols-7 gap-1">
            </div>
            <div class="mt-4 flex justify-end">
                <button id="hide-calendar-button" class="px-4 py-2 bg-neutral-800 rounded" >
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
            let currentCalendarDate = new Date();


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

                document.querySelectorAll('#task-block').forEach(task => task.remove());

                // TODO
                loadInitTasks();
            }


            function changeMonth(offset) {
                currentCalendarDate.setMonth(currentCalendarDate.getMonth() + offset);
                renderCalendar();
            }

            function selectWeek(dateString) {
                console.log(dateString)

                const selectedDate = new Date(dateString);
                const currentFirstDay = new Date(document.querySelector('#grid-header-container .grid > div:not(:first-child)').dataset.date);

                const weeksDiff = Math.round((selectedDate - currentFirstDay) / (7 * 24 * 60 * 60 * 1000));

                changeWeek(weeksDiff);

                toggleCalendarDialog();
            }


            function renderCalendar() {
                const getBoundaries = () => {
                    const year = currentCalendarDate.getFullYear();
                    const month = currentCalendarDate.getMonth();

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
                                day.classList.toggle('bg-neutral-600', shouldAdd);
                            });
                        };

                        weekRow.addEventListener('mouseenter', (e) => handleHover(e, true));
                        weekRow.addEventListener('mouseleave', (e) => handleHover(e, false));
                    });
                }

                const setHeader = () => {
                    const year = currentCalendarDate.getFullYear();
                    const month = currentCalendarDate.getMonth();
                    const monthName = new Date(year, month).toLocaleString(
                        'default',
                        {
                            month: 'long',
                            year: 'numeric',
                        }
                    );

                    document.getElementById('calendarMonth').textContent = monthName;

                }

                const {start, end} = getBoundaries();
                const selectedWeek = getWeekStart(
                    new Date(document.querySelector('#grid-header-container .grid > div:not(:first-child)').dataset.date)
                );

                let html = '';
                let currentDate = new Date(start);
                let weekCounter = 0;

                while (currentDate <= end) {
                    const isCurrentMonth = currentDate.getMonth() === currentCalendarDate.getMonth();
                    const isWeekStart = currentDate.getDay() === 1;
                    const isWeekEnd = currentDate.getDay() === 0;
                    const dateString = currentDate.toLocaleDateString('en-CA');

                    const currentWeekStart = getWeekStart(currentDate);
                    const currentWeekStartString = currentWeekStart.toLocaleDateString('en-CA');

                    const isSelectedWeek = currentWeekStart.getTime() === selectedWeek.getTime();

                    if (isWeekStart) {
                        html += `<div class="week-row col-span-7 grid grid-cols-7 gap-1" 
                                     data-week="\${weekCounter}" 
                                     data-start-date="\${currentWeekStartString}">`;
                        weekCounter++;
                    }

                    html += `
                        <div 
                            onclick="selectWeek('\${currentWeekStartString}')"
                            class="calendar-day p-2 text-center cursor-pointer rounded
                                   \${isCurrentMonth ? '' : 'text-gray-400'} 
                                   \${isSelectedWeek ? 'bg-neutral-500 text-white' : ''}"
                            data-date="\${dateString}"
                        >
                            \${currentDate.getDate()}
                        </div>
                    `;


                    if (isWeekEnd) {
                        html += `</div>`;
                    }

                    currentDate.setDate(currentDate.getDate() + 1);
                }

                document.getElementById('calendarDays').innerHTML = html;
                addWeekHoverEffect();
                setHeader();
            }

            function toggleCalendarDialog() {
                const dialog = document.getElementById('calendar-dialog');

                if (dialog.classList.contains('hidden')) {
                    const headerDate = document.querySelector('#grid-header-container .grid > div:not(:first-child)').dataset.date;
                    currentCalendarDate = new Date(headerDate);
                    renderCalendar();
                    dialog.classList.remove('hidden');
                } else {
                    dialog.classList.add('hidden');
                }
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
