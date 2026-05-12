<!DOCTYPE html>
<html>
<head>
    <title>TN-Task-System</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">TN-Task-System Calendar</h1>
        <a href="{{ route('login') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Admin Login</a>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <div id="calendar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        events: "{{ route('tasks.events') }}",
        eventClick: function(info) {

            const task = info.event.extendedProps;

            document.getElementById('modal_title').innerText = info.event.title;
            document.getElementById('modal_assigned').innerHTML =
                task.assigned_to
                    ? task.assigned_to
                        .split(',')
                        .map(name =>
                            `<span class="inline-block bg-blue-100 text-blue-700 px-2 py-1 rounded mr-1 mb-1">
                                ${name.trim()}
                            </span>`
                        )
                        .join('')
                    : '-';
            document.getElementById('modal_date').innerText =
                task.task_date +
                (task.end_date ? ' until ' + task.end_date : '');

            document.getElementById('modal_time').innerText =
                (task.start_time ?? '-') + ' - ' + (task.end_time ?? '-');

            document.getElementById('modal_location').innerText = task.location ?? '-';
            document.getElementById('modal_status').innerText = task.status;
            document.getElementById('modal_description').innerText = task.description ?? '-';

            document.getElementById('viewTaskModal').classList.remove('hidden');
        },
    });

    calendar.render();
});
function closeViewModal() {
    document.getElementById('viewTaskModal').classList.add('hidden');
}
</script>
<!-- VIEW TASK MODAL -->
<div
    id="viewTaskModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

        <div class="flex justify-between items-center mb-4">

            <h2
                id="modal_title"
                class="text-2xl font-bold"
            >
            </h2>

            <button
                onclick="closeViewModal()"
                class="text-gray-500 hover:text-black text-2xl"
            >
                &times;
            </button>

        </div>

        <div class="space-y-3">

            <div>
                <span class="font-bold">Assigned To:</span>
                <span id="modal_assigned"></span>
            </div>

            <div>
                <span class="font-bold">Date:</span>
                <span id="modal_date"></span>
            </div>

            <div>
                <span class="font-bold">Time:</span>
                <span id="modal_time"></span>
            </div>

            <div>
                <span class="font-bold">Location:</span>
                <span id="modal_location"></span>
            </div>

            <div>
                <span class="font-bold">Status:</span>
                <span id="modal_status"></span>
            </div>

            <div>
                <span class="font-bold">Description:</span>

                <div
                    id="modal_description"
                    class="mt-2 bg-gray-100 p-3 rounded-lg"
                ></div>
            </div>

        </div>

        <div class="mt-6 text-right">

            <button
                onclick="closeViewModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg"
            >
                Close
            </button>

        </div>

    </div>

</div>
</body>
</html>