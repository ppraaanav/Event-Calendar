@extends('layout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calendar.css') }}">
@endpush

@section('content')


<div class="container-fluid calendar-container">

<a href="{{ url('/') }}" class="btn btn-secondary">
        <i class="fas fa-home"></i> Home
    </a>
    <h1 class="text-center mb-4">Calendar</h1>


    

    <div class="calendar-controls d-flex justify-content-center align-items-center mb-4">
        <button class="btn btn-outline-primary me-3" onclick="prevMonth()">
            <i class="fas fa-chevron-left"></i> Prev
        </button>
        <h4 id="monthYear" class="mb-0 text-center"></h4>
        <button class="btn btn-outline-primary ms-3" onclick="nextMonth()">
            Next <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <div class="calendar-grid">
        <div class="day-name">Sun</div>
        <div class="day-name">Mon</div>
        <div class="day-name">Tue</div>
        <div class="day-name">Wed</div>
        <div class="day-name">Thu</div>
        <div class="day-name">Fri</div>
        <div class="day-name">Sat</div>
        <div id="calendarDays" class="calendar-days"></div>
    </div>

    <!-- Event Form Modal -->
    <div id="eventModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formTitle">Add Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <input type="hidden" id="eventDate">
                        <div class="mb-3">
                            <label for="eventTitle" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="eventTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventCount" class="form-label">Number of Events</label>
                            <input type="number" class="form-control" id="eventCount" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="deleteEvent()">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveEvent()">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentDate = new Date();
    let selectedEvent = null;

    // Initialize calendar
    document.addEventListener('DOMContentLoaded', function() {
        renderCalendar();
    });

    function renderCalendar() {
        const monthYear = document.getElementById('monthYear');
        const calendarDays = document.getElementById('calendarDays');
        
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();

        monthYear.textContent = currentDate.toLocaleString('default', { 
            month: 'long', 
            year: 'numeric' 
        });

        calendarDays.innerHTML = '';

        let savedEvents = JSON.parse(localStorage.getItem('events')) || [];

        // Blank spaces for days before the 1st of the month
        for (let i = 0; i < firstDay; i++) {
            calendarDays.appendChild(createEmptyDay());
        }

        // Add the actual days
        for (let day = 1; day <= lastDate; day++) {
            const dateString = formatDate(year, month + 1, day);
            const dayEvents = savedEvents.filter(event => event.date === dateString);
            
            calendarDays.appendChild(createDayBox(day, dateString, dayEvents.length > 0));
        }
    }

    function createEmptyDay() {
        const emptyDiv = document.createElement('div');
        emptyDiv.classList.add('day-box', 'empty');
        return emptyDiv;
    }

    function createDayBox(day, dateString, hasEvent) {
    const dayBox = document.createElement('div');
    dayBox.classList.add('day-box');

    const button = document.createElement('button');
    const isPastDate = new Date(dateString) < new Date().setHours(0, 0, 0, 0);

    button.classList.add('btn');
    button.innerHTML = hasEvent ? '<i class="fas fa-check"></i>' : '<i class="fas fa-plus"></i>';
    button.disabled = isPastDate; // 🔒 Disable button if date is in the past
    button.classList.add(hasEvent ? 'btn-success' : 'btn-outline-primary');

    // Only attach click event if not disabled
    if (!isPastDate) {
        button.onclick = () => openEventModal(dateString);
    }

    dayBox.innerHTML = `<div class="date-number">${day}</div>`;
    dayBox.appendChild(button);

    return dayBox;
}


    function formatDate(year, month, day) {
        return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    }

    function prevMonth() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    }

    function nextMonth() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    }

    function openEventModal(date) {
        const modal = new bootstrap.Modal(document.getElementById('eventModal'));
        const savedEvents = JSON.parse(localStorage.getItem('events')) || [];
        selectedEvent = savedEvents.find(event => event.date === date);

        document.getElementById('eventDate').value = date;
        
        if (selectedEvent) {
            document.getElementById('formTitle').textContent = "Edit Event";
            document.getElementById('eventTitle').value = selectedEvent.title;
            document.getElementById('eventDescription').value = selectedEvent.description;
            document.getElementById('eventCount').value = selectedEvent.count;
        } else {
            document.getElementById('formTitle').textContent = "Add Event";
            document.getElementById('eventForm').reset();
        }

        modal.show();
    }

    function saveEvent() {
    const eventDate = document.getElementById('eventDate').value;
    const eventTitle = document.getElementById('eventTitle').value;
    const eventDescription = document.getElementById('eventDescription').value;
    const eventCount = document.getElementById('eventCount').value;

    const today = new Date();
    today.setHours(0, 0, 0, 0); // ignore time

    const selectedDate = new Date(eventDate);
    selectedDate.setHours(0, 0, 0, 0); // ignore time in selected date too

    if (selectedDate < today) {
        alert("You cannot create or edit events in the past.");
        return;
    }

    let savedEvents = JSON.parse(localStorage.getItem('events')) || [];

    if (selectedEvent) {
        // Update existing event
        const index = savedEvents.findIndex(e => e.date === selectedEvent.date);
        savedEvents[index] = {
            date: eventDate,
            title: eventTitle,
            description: eventDescription,
            count: eventCount
        };
    } else {
        // Add new event
        savedEvents.push({
            date: eventDate,
            title: eventTitle,
            description: eventDescription,
            count: eventCount
        });
    }

    localStorage.setItem('events', JSON.stringify(savedEvents));
    bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
    renderCalendar();
    window.dispatchEvent(new Event('eventsUpdated'));
}

    function deleteEvent() {
        if (!selectedEvent) return;
        
        let savedEvents = JSON.parse(localStorage.getItem('events')) || [];
        savedEvents = savedEvents.filter(event => event.date !== selectedEvent.date);
        
        localStorage.setItem('events', JSON.stringify(savedEvents));
        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
        renderCalendar();
        window.dispatchEvent(new Event('eventsUpdated'));
    }
</script>
@endpush
@endsection






