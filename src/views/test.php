<?php
date_default_timezone_set('UTC');

class Calendar
{
    private $currentWeekStart;

    public function __construct()
    {
        $this->currentWeekStart = new DateTime();
        $this->currentWeekStart->modify('monday this week');
    }

    public function changeWeek(int $offset): void
    {
        $this->currentWeekStart->modify($offset . ' week');
    }

    public function generateWeekDays(): array
    {
        $days = [];
        $currentDay = clone $this->currentWeekStart;

        for ($i = 0; $i < 7; $i++) {
            $days[] = clone $currentDay;
            $currentDay->modify('+1 day');
        }

        return $days;
    }

    /**
     * @return string[]
     */
    public function generateTimeSlots(): array
    {
        $slots = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $slots[] = sprintf('%02d:00', $hour);
        }
        return $slots;
    }

    public function generateWeekHeader(): string
    {
        $html = '<div class="grid grid-cols-8 gap-1 bg-gray-100 sticky top-0 z-10">';
        $html .= '<div class="p-2">Time</div>';

        foreach ($this->generateWeekDays() as $day) {
            $html .= sprintf(
                '<div class="p-2 text-center" data-date="%s">%s<br>%s</div>',
                $day->format('Y-m-d'),
                $day->format('D'),
                $day->format('j M')
            );
        }

        $html .= '</div>';
        return $html;
    }

    public function generateNavigationControls(): string
    {
        return <<<HTML
        <div class="flex justify-between items-center p-4 bg-white">
            <button onclick="changeWeek(-1)" class="px-4 py-2 bg-blue-500 text-white rounded">
                Previous Week
            </button>
            <h2 class="text-xl font-bold cursor-pointer hover:text-blue-600" onclick="toggleCalendarDialog()">
                {$this->currentWeekStart->format('F j, Y')}
            </h2>
            <button onclick="changeWeek(1)" class="px-4 py-2 bg-blue-500 text-white rounded">
                Next Week
            </button>
        </div>
        <div id="calendarDialog" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <button onclick="changeMonth(-1)" class="p-2">&lt;</button>
                    <h3 id="calendarMonth" class="text-xl font-bold"></h3>
                    <button onclick="changeMonth(1)" class="p-2">&gt;</button>
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
                    <button onclick="hideCalendarDialog()" class="px-4 py-2 bg-gray-200 rounded">
                        Close
                    </button>
                </div>
            </div>
        </div>
        HTML;
    }

    public function generateTaskDialog(): string
    {
        return <<<HTML
        <div id="taskDialog" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl w-96">
                <h3 class="text-xl font-bold mb-4">Add New Task</h3>
                <form id="taskForm" onsubmit="saveTask(event)">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Title</label>
                        <input type="text" id="taskTitle" required
                               class="w-full px-3 py-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Start Time</label>
                        <input type="time" id="taskStart" required
                               class="w-full px-3 py-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">End Time</label>
                        <input type="time" id="taskEnd" required
                               class="w-full px-3 py-2 border rounded">
                    </div>
                    <input type="hidden" id="taskDate">
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideTaskDialog()"
                                class="px-4 py-2 bg-gray-200 rounded">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
        HTML;
    }

    public function generateTimeGrid(): string
    {
        $html = '<div class="grid grid-cols-8 gap-1">';

        // Time column
        $html .= '<div class="time-column">';
        foreach ($this->generateTimeSlots() as $timeSlot) {
            $html .= sprintf(
                '<div class="p-2 border-t border-gray-200 time-cell">%s</div>',
                $timeSlot
            );
        }
        $html .= '</div>';

        // Day columns
        foreach ($this->generateWeekDays() as $day) {
            $html .= sprintf('<div class="day-column relative border-l">');
            foreach ($this->generateTimeSlots() as $timeSlot) {
                $html .= sprintf(
                    '<div class="p-2 border-t border-gray-200 time-cell" 
                          onclick="showTaskDialog(this)" 
                          data-date="%s" 
                          data-time="%s"></div>',
                    $day->format('Y-m-d'),
                    $timeSlot
                );
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    public function generateSampleTasks(): string
    {
        $monday = clone $this->currentWeekStart;
        $wednesday = clone $this->currentWeekStart;
        $wednesday->modify('+2 days');
        $thursday = clone $this->currentWeekStart;
        $thursday->modify('+3 days');

        return <<<HTML
        <script>
            const sampleTasks = [
                { date: '{$monday->format('Y-m-d')}', title: 'Team Meeting', start: '10:00', end: '11:30' },
                { date: '{$wednesday->format('Y-m-d')}', title: 'Lunch Break', start: '12:00', end: '13:00' },
                { date: '{$thursday->format('Y-m-d')}', title: 'Project Review', start: '15:00', end: '16:30' }
            ];
        </script>
        HTML;
    }

}

$calendar = new Calendar();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .time-cell {
            height: 60px;
            position: relative;
        }

        .time-column {
            position: sticky;
            left: 0;
            background: white;
            z-index: 10;
        }

        .task-block {
            position: absolute;
            left: 8px;
            right: 8px;
            background-color: #93C5FD;
            border-radius: 0.25rem;
            z-index: 20;
            overflow: hidden;
            transition: all 0.2s;
        }
        
        .task-block:hover {
            background-color: #60A5FA;
        }

        .day-column {
            min-width: 120px;
        }

        .hover\:text-blue-600:hover {
            color: #2563eb;
        }
    </style>
    <?php echo $calendar->generateSampleTasks(); ?>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div id="navigationContainer">
            <?php echo $calendar->generateNavigationControls(); ?>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-4">
            <div id="headerContainer">
                <?php echo $calendar->generateWeekHeader(); ?>
            </div>
            
            <div id="gridContainer" class="overflow-auto" style="height: 600px;">
                <?php echo $calendar->generateTimeGrid(); ?>
            </div>
        </div>
    </div>
    
    <?php echo $calendar->generateTaskDialog(); ?>

    <script>
        function showTaskDialog(cell) {
            const dialog = document.getElementById('taskDialog');
            const dateInput = document.getElementById('taskDate');
            const timeInput = document.getElementById('taskStart');
            
            dateInput.value = cell.dataset.date;
            timeInput.value = cell.dataset.time;
            
            dialog.classList.remove('hidden');
        }
        
        function hideTaskDialog() {
            document.getElementById('taskDialog').classList.add('hidden');
            document.getElementById('taskForm').reset();
        }
        
        function parseTime(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours + minutes / 60;
        }

        function calculateTaskPosition(startTime, endTime) {
            const HOUR_HEIGHT = 60;
            
            // Convert times to decimal hours
            const startHours = parseTime(startTime);
            const endHours = parseTime(endTime);
            
            // Calculate positions
            const top = startHours * HOUR_HEIGHT;
            const height = (endHours - startHours) * HOUR_HEIGHT;
            
            return { top, height };
        }
        
        function saveTask(event) {
            event.preventDefault();
            
            const title = document.getElementById('taskTitle').value;
            const date = document.getElementById('taskDate').value;
            const start = document.getElementById('taskStart').value;
            const end = document.getElementById('taskEnd').value;
            
            // Find the correct day column
            const dayColumn = document.querySelector(`.day-column:has([data-date="${date}"])`);
            if (!dayColumn) return;
            
            // Calculate position and height
            const { top, height } = calculateTaskPosition(start, end);
            
            // Create task block
            const task = document.createElement('div');
            task.className = 'task-block';
            task.style.top = `${top}px`;
            task.style.height = `${height}px`;
            
            // Format times for display
            const formatTime = (timeStr) => {
                const [hours, minutes] = timeStr.split(':');
                const time = new Date(2000, 0, 1, hours, minutes);
                return time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            };
            
            task.innerHTML = `
                <div class="p-2">
                    <div class="font-bold truncate">${title}</div>
                    <div class="text-sm">${formatTime(start)} - ${formatTime(end)}</div>
                </div>
            `;
            
            dayColumn.appendChild(task);
            hideTaskDialog();
        }
        
        function changeWeek(offset) {
            // Update current date
            const headerCells = document.querySelectorAll('#headerContainer .grid > div:not(:first-child)');
            const currentDate = new Date(headerCells[0].dataset.date || headerCells[0].innerText.split('\n')[1]);
            
            // Get to Monday of the current week first
            const day = currentDate.getDay();
            const diff = currentDate.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is sunday
            currentDate.setDate(diff);
            
            // Then add the offset weeks
            currentDate.setDate(currentDate.getDate() + (offset * 7));

            // Update header
            const days = [];
            for (let i = 0; i < 7; i++) {
                const date = new Date(currentDate);
                date.setDate(date.getDate() + i);
                days.push(date);
            }

            headerCells.forEach((cell, index) => {
                const date = days[index];
                cell.innerHTML = `${date.toLocaleDateString('en-US', { weekday: 'short' })}<br>${date.getDate()} ${date.toLocaleDateString('en-US', { month: 'short' })}`;
                cell.dataset.date = date.toISOString().split('T')[0];
            });

            // Update navigation title
            const navigationTitle = document.querySelector('#navigationContainer h2');
            navigationTitle.textContent = days[0].toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });

            // Update grid date attributes
            const dayColumns = document.querySelectorAll('.day-column');
            dayColumns.forEach((column, index) => {
                const cells = column.querySelectorAll('.time-cell');
                const date = days[index].toISOString().split('T')[0];
                cells.forEach(cell => {
                    cell.dataset.date = date;
                });
            });

            // Clear existing tasks
            document.querySelectorAll('.task-block').forEach(task => task.remove());

            // Render sample tasks
            renderTasks();
        }

        function renderTasks() {
            sampleTasks.forEach(task => {
                const dayColumn = document.querySelector(`.day-column:has([data-date="${task.date}"])`);
                if (!dayColumn) return;

                const { top, height } = calculateTaskPosition(task.start, task.end);
                
                const taskElement = document.createElement('div');
                taskElement.className = 'task-block';
                taskElement.style.top = `${top}px`;
                taskElement.style.height = `${height}px`;
                
                taskElement.innerHTML = `
                    <div class="p-2">
                        <div class="font-bold truncate">${task.title}</div>
                        <div class="text-sm">${formatTime(task.start)} - ${formatTime(task.end)}</div>
                    </div>
                `;
                
                dayColumn.appendChild(taskElement);
            });
        }

        function formatTime(timeStr) {
            const [hours, minutes] = timeStr.split(':');
            const time = new Date(2000, 0, 1, hours, minutes);
            return time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        // Initial render of sample tasks
        renderTasks();

        let currentCalendarDate = new Date();

        function toggleCalendarDialog() {
            const dialog = document.getElementById('calendarDialog');
            if (dialog.classList.contains('hidden')) {
                // Get the current week's Monday from the header
                const headerDate = document.querySelector('#headerContainer .grid > div:not(:first-child)').dataset.date;
                currentCalendarDate = new Date(headerDate);
                renderCalendar();
                dialog.classList.remove('hidden');
            } else {
                dialog.classList.add('hidden');
            }
        }

        function hideCalendarDialog() {
            document.getElementById('calendarDialog').classList.add('hidden');
        }

        function changeMonth(offset) {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() + offset);
            renderCalendar();
        }

        function renderCalendar() {
            const year = currentCalendarDate.getFullYear();
            const month = currentCalendarDate.getMonth();
            
            // Update month/year display
            const monthName = new Date(year, month).toLocaleString('default', { month: 'long', year: 'numeric' });
            document.getElementById('calendarMonth').textContent = monthName;

            // Get the first day of the month
            const firstDay = new Date(year, month, 1);
            // Get the last day of the month
            const lastDay = new Date(year, month + 1, 0);
            
            // Get Monday of the first week
            let start = new Date(firstDay);
            start.setDate(start.getDate() - (start.getDay() || 7) + 1);
            
            // Get Sunday of the last week
            let end = new Date(lastDay);
            end.setDate(end.getDate() + (7 - end.getDay()));

            // Get current selected week's Monday
            const selectedDate = new Date(document.querySelector('#headerContainer .grid > div:not(:first-child)').dataset.date);
            const selectedWeekStart = new Date(selectedDate);
            // Fix: Correctly set to Monday of the selected week
            selectedWeekStart.setDate(selectedWeekStart.getDate() - (selectedWeekStart.getDay() || 7) + 1);
            selectedWeekStart.setHours(0, 0, 0, 0);  // Normalize time
            
            const selectedWeekEnd = new Date(selectedWeekStart);
            selectedWeekEnd.setDate(selectedWeekEnd.getDate() + 6);
            selectedWeekEnd.setHours(23, 59, 59, 999);  // End of day
            
            let html = '';
            let currentDate = new Date(start);
            let weekCounter = 0;

            while (currentDate <= end) {
                const isCurrentMonth = currentDate.getMonth() === month;
                const isWeekStart = currentDate.getDay() === 1;
                const dateString = currentDate.toISOString().split('T')[0];
                
                // Get Monday of current date's week for comparison
                const currentWeekStart = new Date(currentDate);
                currentWeekStart.setDate(currentWeekStart.getDate() - (currentWeekStart.getDay() || 7) + 1);
                currentWeekStart.setHours(0, 0, 0, 0);
                
                const isSelectedWeek = currentWeekStart.getTime() === selectedWeekStart.getTime();

                if (isWeekStart) {
                    html += `<div class="week-row col-span-7 grid grid-cols-7 gap-1" 
                                 data-week="${weekCounter}" 
                                 data-start-date="${currentWeekStart.toISOString().split('T')[0]}">`;
                    weekCounter++;
                }

                html += `
                    <div 
                        onclick="selectWeek('${dateString}')"
                        class="calendar-day p-2 text-center cursor-pointer rounded
                               ${isCurrentMonth ? '' : 'text-gray-400'} 
                               ${isSelectedWeek ? 'selected-day bg-blue-500 text-white hover:bg-blue-600' : 'hover:bg-blue-100'}"
                        data-date="${dateString}"
                    >
                        ${currentDate.getDate()}
                    </div>
                `;

                if (currentDate.getDay() === 0) {
                    html += `</div>`;
                }

                currentDate.setDate(currentDate.getDate() + 1);
            }

            document.getElementById('calendarDays').innerHTML = html;

            // Add hover effect for entire week
            document.querySelectorAll('.week-row').forEach(weekRow => {
                const handleHover = (event, shouldAdd) => {
                    if (!weekRow.querySelector('.selected-day')) {
                        weekRow.querySelectorAll('.calendar-day').forEach(day => {
                            if (!day.classList.contains('text-gray-400')) {
                                day.classList.toggle('bg-blue-100', shouldAdd);
                            }
                        });
                    }
                };

                weekRow.addEventListener('mouseenter', (e) => handleHover(e, true));
                weekRow.addEventListener('mouseleave', (e) => handleHover(e, false));
            });
        }

        function selectWeek(dateString) {
            const selectedDate = new Date(dateString);
            const currentFirstDay = new Date(document.querySelector('#headerContainer .grid > div:not(:first-child)').dataset.date);
            
            // Calculate weeks difference
            const weeksDiff = Math.round((selectedDate - currentFirstDay) / (7 * 24 * 60 * 60 * 1000));
            
            changeWeek(weeksDiff);
            hideCalendarDialog();
        }
    </script>
</body>
</html>
