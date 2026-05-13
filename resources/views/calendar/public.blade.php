<!DOCTYPE html>
<html>
<head>
    <title>TN-Task-System</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-slate-200 text-slate-800">

<div class="max-w-[1600px] mx-auto px-6 py-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">
            TN-Task-System Calendar
        </h1>

        <a
            href="{{ route('login') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
        >
            Admin Login
        </a>
    </div>

    <div class="bg-white/90 backdrop-blur p-6 rounded-2xl shadow-lg border border-white/60">
        <div id="calendar"></div>
    </div>

</div>

<!-- PUBLIC TASK MODAL -->
<div
    id="publicTaskModal"
    class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

        <div class="flex justify-between items-center mb-4">
            <h2 id="modal_title" class="text-2xl font-bold"></h2>

            <button
                onclick="closePublicModal()"
                class="text-3xl text-gray-500 hover:text-black"
            >
                &times;
            </button>
        </div>

        <div class="space-y-3 text-sm">

            <div>
                <span class="font-bold">Team Lead:</span>
                <span id="modal_team_lead"></span>
            </div>

            <div>
                <span class="font-bold">Team Members:</span>
                <div id="modal_members" class="mt-2 flex flex-wrap gap-2"></div>
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
                onclick="closePublicModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg"
            >
                Close
            </button>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendar = new FullCalendar.Calendar(
        document.getElementById('calendar'),
        {
            initialView: 'dayGridMonth',

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

            eventClick: function(info) {
                const task = info.event.extendedProps;

                document.getElementById('modal_title').innerText =
                    info.event.title;

                document.getElementById('modal_team_lead').innerText =
                    task.team_lead_name ?? 'No Team Lead';

                document.getElementById('modal_date').innerText =
                    task.task_date +
                    (task.end_date ? ' until ' + task.end_date : '');

                document.getElementById('modal_time').innerText =
                    (task.start_time ?? '-') + ' - ' + (task.end_time ?? '-');

                document.getElementById('modal_location').innerText =
                    task.location ?? '-';

                document.getElementById('modal_status').innerText =
                    task.status ?? '-';

                document.getElementById('modal_description').innerText =
                    task.description ?? '-';

                const membersContainer =
                    document.getElementById('modal_members');

                membersContainer.innerHTML = '';

                if (task.member_names && task.member_names.trim() !== '') {

                    task.member_names.split(',').forEach(function(name) {
                        const badge = document.createElement('span');

                        badge.className =
                            'bg-blue-100 text-blue-700 px-3 py-1 rounded-lg';

                        badge.innerText = name.trim();

                        membersContainer.appendChild(badge);
                    });

                } else {
                    membersContainer.innerHTML =
                        '<span class="text-gray-500">No team members</span>';
                }

                document.getElementById('publicTaskModal').classList.remove('hidden');
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

function closePublicModal() {
    document.getElementById('publicTaskModal').classList.add('hidden');
}
</script>

</body>
</html>