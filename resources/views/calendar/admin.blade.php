<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - TN-Task-System</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.15/index.global.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<div class="max-w-7xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">
            TN-Task-System Admin
        </h1>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg"
            >
                Logout
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- MAIN CALENDAR -->
        <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow">

            <div class="flex justify-end mb-4">
                <button
                    onclick="saveCalendarChanges()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                >
                    Save Changes
                </button>
            </div>

            <div id="calendar"></div>

        </div>

        <!-- RIGHT SIDE EMPLOYEE PANEL -->
        <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow">

            <h2 class="text-2xl font-bold mb-4">
                Employees
            </h2>

            <form method="POST" action="{{ route('employees.store') }}" class="mb-4">
                @csrf

                <div class="flex gap-2">
                    <input
                        type="text"
                        name="name"
                        placeholder="Employee name"
                        required
                        class="w-full border rounded-lg p-2"
                    >

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-lg"
                    >
                        Add
                    </button>
                </div>
            </form>

            <div id="employee-list" class="space-y-3">

                @forelse($employees as $employee)
                    <div class="flex items-center gap-2">

                        <div class="employee-item flex-1 bg-blue-100 text-blue-700 p-3 rounded-lg cursor-move">
                            {{ $employee->name }}
                        </div>

                        <form
                            method="POST"
                            action="{{ route('employees.destroy', $employee->id) }}"
                            onsubmit="return confirm('Delete this employee?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg"
                            >
                                X
                            </button>
                        </form>

                    </div>
                @empty
                    <p class="text-gray-500 text-sm">
                        No employees yet.
                    </p>
                @endforelse

            </div>

            <div class="mt-6 text-sm text-gray-500">
                Drag employee into task popup later.
            </div>

        </div>

    </div>

</div>

<script>
let movedTasks = {};

function formatLocalDate(date) {

    const year = date.getFullYear();

    const month =
        String(date.getMonth() + 1).padStart(2, '0');

    const day =
        String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

document.addEventListener('DOMContentLoaded', function () {
    const containerEl = document.getElementById('employee-list');

    new FullCalendar.Draggable(containerEl, {
        itemSelector: '.employee-item',
        eventData: function(eventEl) {
            return {
                title: eventEl.innerText
            };
        }
    });
    const calendar = new FullCalendar.Calendar(
        document.getElementById('calendar'),
        {
            initialView: 'dayGridMonth',

            editable: true,
            droppable: true,

            height: 750,

            events: "{{ route('tasks.events') }}",

            eventContent: function(arg) {

                return {
                    html: `
                        <div class="text-xs font-semibold">
                            ${arg.event.extendedProps.display_title}
                        </div>
                    `
                };
            },
            dateClick: function(info) {

                document.getElementById('create_task_date').value = info.dateStr;
                document.getElementById('selectedDateText').innerText = info.dateStr;

                loadTasksForDate(info.dateStr);
                updateCreateAvailability();

                document.getElementById('dateTaskModal').classList.remove('hidden');
            },
           eventDrop: function(info) {

                const startDate =
                    formatLocalDate(info.event.start);

                let endDate = null;

                if (info.event.end) {

                    const realEnd =
                        new Date(info.event.end);

                    realEnd.setDate(realEnd.getDate() - 1);

                    endDate =
                        formatLocalDate(realEnd);
                }

                movedTasks[info.event.id] = {
                    id: info.event.id,
                    task_date: startDate,
                    end_date: endDate
                };
            },

        
            eventClick: function(info) {
                const task = info.event.extendedProps;

                document.getElementById('editTaskForm').action = info.event.extendedProps.url_update;
                document.getElementById('deleteTaskForm').action = info.event.extendedProps.url_delete;

                document.getElementById('edit_title').value = info.event.title;
                document.getElementById('edit_description').value = task.description ?? '';
                document.getElementById('edit_team_lead_id').value = task.team_lead_id ?? '';
                document.getElementById('edit_task_date').value = task.task_date;
                document.getElementById('edit_end_date').value = task.end_date ?? '';
                document.getElementById('edit_start_time').value = task.start_time ?? '';
                document.getElementById('edit_end_time').value = task.end_time ?? '';
                document.getElementById('edit_location').value = task.location ?? '';
                document.getElementById('edit_status').value = task.status;
                document.querySelectorAll('.edit-member-checkbox').forEach(function(checkbox) {
                    checkbox.checked = task.member_ids.includes(parseInt(checkbox.value));
                });

                updateEditAvailability(info.event.id);

                document.getElementById('taskModal').classList.remove('hidden');
            },

            eventDidMount: function(info) {

                const status = info.event.extendedProps.status;

                if(status === 'pending') {
                    info.el.style.backgroundColor = '#f59e0b';
                }

                if(status === 'in_progress') {
                    info.el.style.backgroundColor = '#3b82f6';
                }

                if(status === 'completed') {
                    info.el.style.backgroundColor = '#10b981';
                }
            }
            
            
        }
    );

    calendar.render();

});
function closeModal() {
    document.getElementById('taskModal').classList.add('hidden');
}

function deleteTask() {
    if (confirm('Are you sure you want to delete this task?')) {
        document.getElementById('deleteTaskForm').submit();
    }
}
function saveCalendarChanges() {
    const tasks = Object.values(movedTasks);

    if (tasks.length === 0) {
        alert('No calendar changes to save.');
        return;
    }

    fetch("{{ route('tasks.updateDates') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            tasks: tasks
        })
    })
    .then(response => response.json())
    .then(data => {
        alert('Calendar changes saved successfully.');
        movedTasks = {};
        location.reload();
    })
    .catch(error => {
        alert('Failed to save changes.');
        console.error(error);
    });
}

function closeDateTaskModal() {
    document.getElementById('dateTaskModal').classList.add('hidden');
}

function loadTasksForDate(date) {

    fetch("{{ route('tasks.events') }}")
        .then(response => response.json())
        .then(tasks => {

            const container = document.getElementById('tasksForSelectedDate');

            container.innerHTML = '';

            const filteredTasks = tasks.filter(task => {

                const startDate =
                    task.extendedProps.task_date;

                const endDate =
                    task.extendedProps.end_date
                        ? task.extendedProps.end_date
                        : startDate;

                return startDate <= date && endDate >= date;
            });

            if(filteredTasks.length === 0) {

                container.innerHTML = `
                    <div class="text-gray-500 text-sm">
                        No tasks on this date.
                    </div>
                `;

                return;
            }

            filteredTasks.forEach(task => {

                const div = document.createElement('div');

                div.className =
                    'border rounded-lg p-3 bg-gray-50';

                div.innerHTML = `
                    <div class="font-bold">
                        ${task.extendedProps.display_title}
                    </div>

                    <div class="text-sm text-gray-500 mt-1">
                        ${task.extendedProps.status}
                    </div>
                `;

                container.appendChild(div);

            });

        });
}
function updateCreateAvailability() {
    const taskDate = document.getElementById('create_task_date').value;
    const endDateInput = document.querySelector('#dateTaskModal input[name="end_date"]');
    const endDate = endDateInput ? endDateInput.value : '';

    if (!taskDate) {
        return;
    }

    fetch(`{{ route('employees.availability') }}?date=${taskDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(employees => {
            employees.forEach(employee => {
                const checkbox = document.querySelector(`.create-member-checkbox[value="${employee.id}"]`);
                const status = document.getElementById(`create_employee_status_${employee.id}`);
                const leadOption = document.querySelector(`#create_team_lead_id option[value="${employee.id}"]`);

                if (!checkbox || !status || !leadOption) {
                    return;
                }

                checkbox.disabled = !employee.available;
                leadOption.disabled = !employee.available;

                if (employee.available) {
                    status.innerText = '(available)';
                    status.className = 'text-xs text-green-600';
                } else {
                    status.innerText = `(already assigned: ${employee.assigned_task})`;
                    status.className = 'text-xs text-red-600';
                    checkbox.checked = false;
                }
            });
        });
}

function updateEditAvailability(taskId) {
    const taskDate = document.getElementById('edit_task_date').value;
    const endDate = document.getElementById('edit_end_date').value;

    if (!taskDate) {
        return;
    }

    fetch(`{{ route('employees.availability') }}?date=${taskDate}&end_date=${endDate}&ignore_task_id=${taskId}`)
        .then(response => response.json())
        .then(employees => {
            employees.forEach(employee => {
                const checkbox = document.querySelector(`.edit-member-checkbox[value="${employee.id}"]`);
                const status = document.getElementById(`edit_employee_status_${employee.id}`);
                const leadOption = document.querySelector(`#edit_team_lead_id option[value="${employee.id}"]`);

                if (!checkbox || !status || !leadOption) {
                    return;
                }

                checkbox.disabled = !employee.available;
                leadOption.disabled = !employee.available;

                if (employee.available) {
                    status.innerText = '(available)';
                    status.className = 'text-xs text-green-600';
                } else {
                    status.innerText = `(already assigned: ${employee.assigned_task})`;
                    status.className = 'text-xs text-red-600';
                    checkbox.checked = false;
                }
            });
        });
}
</script>
<div id="taskModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg">
        <h2 class="text-2xl font-bold mb-4">Edit Task</h2>

        <form id="editTaskForm" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <input name="title" id="edit_title" class="w-full border rounded-lg p-2" placeholder="Title">

                <textarea name="description" id="edit_description" class="w-full border rounded-lg p-2" placeholder="Description"></textarea>

                <div>
                    <label class="font-semibold">Team Lead</label>

                    <select
                        name="team_lead_id"
                        id="edit_team_lead_id"
                        class="w-full border rounded-lg p-2"
                    >
                        <option value="">Select Team Lead</option>

                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold">Team Members</label>

                    <div
                        id="edit_member_checklist"
                        class="border rounded-lg p-3 bg-gray-50 space-y-2 max-h-48 overflow-y-auto"
                    >
                        @foreach($employees as $employee)
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="member_ids[]"
                                    value="{{ $employee->id }}"
                                    class="edit-member-checkbox"
                                >

                                <span>{{ $employee->name }}</span>

                                <span
                                    id="edit_employee_status_{{ $employee->id }}"
                                    class="text-xs text-gray-500"
                                ></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <input type="date" name="task_date" id="edit_task_date" class="w-full border rounded-lg p-2">

                <input type="date" name="end_date" id="edit_end_date" class="w-full border rounded-lg p-2">

                <div class="grid grid-cols-2 gap-2">
                    <input type="time" name="start_time" id="edit_start_time" class="w-full border rounded-lg p-2">
                    <input type="time" name="end_time" id="edit_end_time" class="w-full border rounded-lg p-2">
                </div>

                <input name="location" id="edit_location" class="w-full border rounded-lg p-2" placeholder="Location">

                <select name="status" id="edit_status" class="w-full border rounded-lg p-2">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Update
                </button>

                <button type="button" onclick="deleteTask()" class="bg-red-600 text-white px-4 py-2 rounded-lg">
                    Delete
                </button>

                <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg">
                    Cancel
                </button>
            </div>
        </form>

        <form id="deleteTaskForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
<!-- CREATE TASK MODAL -->
<div
    id="dateTaskModal"
    class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">

        <div class="flex justify-between items-center mb-6">

            <div>
                <h2 class="text-3xl font-bold">
                    Tasks on
                    <span id="selectedDateText"></span>
                </h2>
            </div>

            <button
                onclick="closeDateTaskModal()"
                class="text-3xl text-gray-500 hover:text-black"
            >
                &times;
            </button>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- LEFT: ADD TASK -->
            <div>

                <h3 class="text-xl font-bold mb-4">
                    Add Task
                </h3>

                <form
                    method="POST"
                    action="{{ route('tasks.store') }}"
                    class="space-y-4"
                >

                    @csrf

                    <input
                        type="text"
                        name="title"
                        placeholder="Task Title"
                        required
                        class="w-full border rounded-lg p-3"
                    >

                    <textarea
                        name="description"
                        placeholder="Description"
                        class="w-full border rounded-lg p-3"
                    ></textarea>

                    <div>
                        <label class="font-semibold">Team Lead</label>

                        <select
                            name="team_lead_id"
                            id="create_team_lead_id"
                            class="w-full border rounded-lg p-3"
                        >
                            <option value="">Select Team Lead</option>

                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="font-semibold">Team Members</label>

                        <div
                            id="create_member_checklist"
                            class="border rounded-lg p-3 bg-gray-50 space-y-2 max-h-48 overflow-y-auto"
                        >
                            @foreach($employees as $employee)
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="member_ids[]"
                                        value="{{ $employee->id }}"
                                        class="create-member-checkbox"
                                    >

                                    <span>{{ $employee->name }}</span>

                                    <span
                                        id="create_employee_status_{{ $employee->id }}"
                                        class="text-xs text-gray-500"
                                    ></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <input
                        type="date"
                        name="task_date"
                        id="create_task_date"
                        onchange="updateCreateAvailability()"
                        required
                        class="w-full border rounded-lg p-3"
                    >

                    <input
                        type="date"
                        name="end_date"
                        onchange="updateCreateAvailability()"
                        class="w-full border rounded-lg p-3"
                    >

                    <div class="grid grid-cols-2 gap-3">

                        <input
                            type="time"
                            name="start_time"
                            class="w-full border rounded-lg p-3"
                        >

                        <input
                            type="time"
                            name="end_time"
                            class="w-full border rounded-lg p-3"
                        >

                    </div>

                    <input
                        type="text"
                        name="location"
                        placeholder="Location"
                        class="w-full border rounded-lg p-3"
                    >

                    <select
                        name="status"
                        class="w-full border rounded-lg p-3"
                    >
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg"
                    >
                        Create Task
                    </button>

                </form>

            </div>

            <!-- RIGHT: TASKS ON DATE -->
            <div>

                <h3 class="text-xl font-bold mb-4">
                    Existing Tasks
                </h3>

                <div
                    id="tasksForSelectedDate"
                    class="space-y-3"
                ></div>

            </div>

        </div>

    </div>

</div>
</body>
</html>