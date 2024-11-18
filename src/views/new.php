<?php

// REMOVE THIS !!!!
date_default_timezone_set('EET');

function setTasks()
{

    if (isset($_SESSION['tasks'])) {
        return;
    }
    $date = new DateTime();

    $monday     = clone $date->modify('monday this week');
    $wednesday  = clone $date->modify('wednesday this week');
    $thursday   = clone $date->modify('thursday this week');


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
            <div class="time-cell h-[60px] relative p-2 border-t border-neutral-500">
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
                class="time-cell h-[60px] relative p-2 border-t border-neutral-500" 
                data-date="$dateFmt"
                data-time="$time_slot"
            >
            </div>\n
        HTML;
    }

    return <<<HTML
        <div class="day-column relative min-w-[120px] border-l border-neutral-500">
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

function renderGridHeader($week_start): string
{
    $header = '<div class="p-2 bg-neutral-800"></div>';

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
        <div id="headerContainer">
            <div class="grid grid-cols-8 gap-1 bg-neutral-800 sticky top-0 z-10">
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
            class='
            task-block left-1 right-0 hover:bg-neutral-700 bg-neutral-600
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
            <div class="shadow-lg rounded-lg overflow-hidden mt-4">
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

                const { top, height } = calculateTaskPosition(start, end);

                const dayColumn = document.querySelector(`.day-column:has([data-date="${date}"])`);
                if (!dayColumn) return;

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
    </body>
</html>
