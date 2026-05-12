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

        <!-- LEFT SIDE FORM -->
        <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow">

            <h2 class="text-2xl font-bold mb-4">
                Add Task
            </h2>

            <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">

                @csrf

                <div>
                    <label class="font-semibold">Title</label>

                    <input
                        type="text"
                        name="title"
                        required
                        class="w-full border rounded-lg p-3"
                    >
                </div>

                <div>
                    <label class="font-semibold">Description</label>

                    <textarea
                        name="description"
                        rows="3"
                        class="w-full border rounded-lg p-3"
                    ></textarea>
                </div>

                <div>
                    <label class="font-semibold">
                        Assigned To (Separate names with commas)
                    </label>

                    <input
                        type="text"
                        name="assigned_to"
                        placeholder="Razman, Ali, Ahmad"
                        class="w-full border rounded-lg p-3"
                    >
                </div>

                <div>
                    <label class="font-semibold">Task Date</label>

                    <input
                        type="date"
                        name="task_date"
                        required
                        class="w-full border rounded-lg p-3"
                    >
                </div>
                <div>
                    <label class="font-semibold">End Date</label>

                    <input
                        type="date"
                        name="end_date"
                        class="w-full border rounded-lg p-3"
                    >
                </div>

                <div class="grid grid-cols-2 gap-3">

                    <div>
                        <label class="font-semibold">Start Time</label>

                        <input
                            type="time"
                            name="start_time"
                            class="w-full border rounded-lg p-3"
                        >
                    </div>

                    <div>
                        <label class="font-semibold">End Time</label>

                        <input
                            type="time"
                            name="end_time"
                            class="w-full border rounded-lg p-3"
                        >
                    </div>

                </div>

                <div>
                    <label class="font-semibold">Location</label>

                    <input
                        type="text"
                        name="location"
                        class="w-full border rounded-lg p-3"
                    >
                </div>

                <div>
                    <label class="font-semibold">Status</label>

                    <select
                        name="status"
                        class="w-full border rounded-lg p-3"
                    >
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg"
                >
                    Add Task
                </button>

            </form>

        </div>

        <!-- RIGHT SIDE CALENDAR -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow">

            <div id="calendar"></div>
            <!-- EMPLOYEE PANEL -->
            

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
                Drag employee onto calendar/date to fill Assigned To.
            </div>

        </div>

    </div>

</div>

<script>

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

            dateClick: function(info) {

                document.querySelector('input[name="task_date"]').value = info.dateStr;

                document.querySelector('input[name="title"]').focus();
            },
            drop: function(info) {

                const employeeName = info.draggedEl.innerText;

                const currentAssigned =
                    document.querySelector('input[name="assigned_to"]').value;

                if(currentAssigned.trim() === '') {

                    document.querySelector('input[name="assigned_to"]').value =
                        employeeName;

                } else {

                    document.querySelector('input[name="assigned_to"]').value +=
                        ', ' + employeeName;
                }

            },
            eventClick: function(info) {
                const task = info.event.extendedProps;

                document.getElementById('editTaskForm').action = info.event.extendedProps.url_update;
                document.getElementById('deleteTaskForm').action = info.event.extendedProps.url_delete;

                document.getElementById('edit_title').value = info.event.title;
                document.getElementById('edit_description').value = task.description ?? '';
                document.getElementById('edit_assigned_to').value = task.assigned_to ?? '';
                document.getElementById('edit_task_date').value = task.task_date;
                document.getElementById('edit_end_date').value = task.end_date ?? '';
                document.getElementById('edit_start_time').value = task.start_time ?? '';
                document.getElementById('edit_end_time').value = task.end_time ?? '';
                document.getElementById('edit_location').value = task.location ?? '';
                document.getElementById('edit_status').value = task.status;

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

                <input name="assigned_to" id="edit_assigned_to" class="w-full border rounded-lg p-2" placeholder="Assigned To">

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
</body>
</html>