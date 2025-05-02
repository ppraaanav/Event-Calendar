
@extends('layout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-calendar-alt me-2"></i> Events
            </a>
            
            <!-- Search Bar -->
            <div class="d-flex search-container">
                <input class="form-control me-2" type="search" placeholder="Search events..." id="searchInput">
                <button class="btn btn-outline-light" onclick="searchEvents()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <!-- User Dropdown - Updated with Auth Check -->
            <div class="dropdown ms-auto">
                <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i>
                    @auth
                        {{ Auth::user()->name }}
                    @else
                        Guest
                    @endauth
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @auth
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    @else
                        <li><a class="dropdown-item" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                        <li><a class="dropdown-item" href="{{ route('register') }}"><i class="fas fa-user-plus me-2"></i>Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
            

            
            </nav>
  

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="profile-section text-center py-4">
                @auth
                    <img src="{{ Auth::user()->avatar ?? 'https://via.placeholder.com/100' }}" 
                         class="profile-picture rounded-circle mb-3" alt="Profile">
                    <h5 class="text-white">{{ Auth::user()->name }}</h5>
                    <p class="text-muted">{{ Auth::user()->email }}</p>
                @else
                    <img src="https://via.placeholder.com/100" 
                         class="profile-picture rounded-circle mb-3" alt="Guest">
                    <h5 class="text-white">Guest User</h5>
                    <p class="text-muted">guest@example.com</p>
                @endauth
            </div>
            
            <div class="sidebar-menu">
                <a href="{{ url('/') }}" class="btn btn-sidebar {{ request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-home me-2"></i> Home
                </a>
                <a href="{{ url('/student/create') }}" class="btn btn-sidebar {{ request()->is('student/create') ? 'active' : '' }}">
                    <i class="fas fa-calendar me-2"></i>  Calendar</a>
                
                @auth
                    <div class="logout-section mt-3">
                        <a class="btn btn-logout" href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                        <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                @else
                    <div class="login-section mt-3">
                        <a href="{{ route('login') }}" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content">
            <div class="dashboard-header p-3">
                <h2><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h2>
                <div class="dashboard-actions">
                    <button class="btn btn-refresh" onclick="refreshEvents()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="dashboard-content p-4">
                <div class="row">
                    <!-- Upcoming Events Section -->
                    <div class="col-lg-8">
                        <div class="card events-card">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Upcoming Events</h4>
                            </div>
                            <div class="card-body">
                                <div id="eventList" class="event-list">
                                    <!-- Events will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats Section -->
                    <div class="col-lg-4">
                        <div class="card stats-card">
                            <div class="card-header bg-success text-white">
                                <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h4>
                            </div>
                            <div class="card-body">
                                <div class="stat-item">
                                    <h5><i class="fas fa-calendar-check text-primary me-2"></i>Total Events</h5>
                                    <p id="totalEvents" class="stat-value">0</p>
                                </div>
                                <div class="stat-item">
                                    <h5><i class="fas fa-bell text-warning me-2"></i>Upcoming</h5>
                                    <p id="upcomingEvents" class="stat-value">0</p>
                                </div>
                                <div class="stat-item">
                                    <h5><i class="fas fa-calendar-week text-info me-2"></i>This Month</h5>
                                    <p id="monthEvents" class="stat-value">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize elements
    const eventList = document.getElementById('eventList');
    const toastEl = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastEl);
    const toastMessage = document.getElementById('toastMessage');
    const searchInput = document.getElementById('searchInput');
    
    // Stats elements
    const totalEventsEl = document.getElementById('totalEvents');
    const upcomingEventsEl = document.getElementById('upcomingEvents');
    const monthEventsEl = document.getElementById('monthEvents');

    // Load events when page loads
    document.addEventListener('DOMContentLoaded', function() {
        renderEvents();
        updateStats();
    });

    function renderEvents(filter = '') {
        eventList.innerHTML = '';
        
        const savedEvents = JSON.parse(localStorage.getItem('events')) || [];
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Filter events if search term exists
        const filteredEvents = filter ? 
            savedEvents.filter(event => 
                event.title.toLowerCase().includes(filter.toLowerCase()) || 
                event.description.toLowerCase().includes(filter.toLowerCase())
            ) : 
            savedEvents;
        
        if (filteredEvents.length === 0) {
            const noEventsMsg = filter ? 
                'No events match your search' : 
                'No events found. Create your first event in the calendar!';
                
            eventList.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="fas fa-calendar-times fa-2x mb-3"></i>
                    <h4>${noEventsMsg}</h4>
                    ${!filter ? `<a href="/student/create" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Create Event
                    </a>` : ''}
                </div>
            `;
            return;
        }

        // Sort events by date (ascending)
        filteredEvents.sort((a, b) => new Date(a.date) - new Date(b.date));
        
        filteredEvents.forEach(event => {
            const eventDate = new Date(event.date);
            const isUpcoming = eventDate >= today;
            const isThisMonth = eventDate.getMonth() === today.getMonth() && 
                              eventDate.getFullYear() === today.getFullYear();
            
            const eventCard = document.createElement('div');
            eventCard.className = `event-item ${isUpcoming ? 'upcoming' : 'past'}`;
            eventCard.innerHTML = `
                <div class="event-header">
                    <h4>${event.title}</h4>
                    <span class="badge ${isUpcoming ? 'bg-primary' : 'bg-secondary'}">
                        ${eventDate.toLocaleDateString('en-US', { 
                            weekday: 'short', 
                            month: 'short', 
                            day: 'numeric' 
                        })}
                    </span>
                </div>
                <div class="event-body">
                    <p>${event.description || 'No description provided'}</p>
                    <div class="event-meta">
                        <span class="event-count">
                            <i class="fas fa-calendar-check"></i> ${event.count} event(s)
                        </span>
                        <div class="event-actions">
                            <button class="btn btn-sm btn-reminder" onclick="setReminder('${event.title}', '${event.date}')">
                                <i class="fas fa-bell"></i> Reminder
                            </button>
                            <button class="btn btn-sm btn-share" onclick="shareEvent('${event.title}', '${event.description}')">
                                <i class="fas fa-share-alt"></i> Share
                            </button>
                        </div>
                    </div>
                </div>
            `;
            eventList.appendChild(eventCard);
        });
    }

    function updateStats() {
        const savedEvents = JSON.parse(localStorage.getItem('events')) || [];
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();
        
        // Calculate stats
        const total = savedEvents.length;
        const upcoming = savedEvents.filter(event => new Date(event.date) >= today).length;
        const thisMonth = savedEvents.filter(event => {
            const eventDate = new Date(event.date);
            return eventDate.getMonth() === currentMonth && 
                   eventDate.getFullYear() === currentYear;
        }).length;
        
        // Update DOM
        totalEventsEl.textContent = total;
        upcomingEventsEl.textContent = upcoming;
        monthEventsEl.textContent = thisMonth;
    }

    function searchEvents() {
        renderEvents(searchInput.value);
    }

    function refreshEvents() {
        renderEvents();
        updateStats();
        showToast('Events refreshed successfully!');
    }

    function setReminder(title, date) {
        showToast(`Reminder set for "${title}" on ${date}`);
        // In a real app, you would integrate with the Notification API
        if (Notification.permission === 'granted') {
            new Notification(`Reminder: ${title}`, {
                body: `Scheduled for ${date}`,
                icon: '/path/to/icon.png'
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    new Notification(`Reminder: ${title}`, {
                        body: `Scheduled for ${date}`,
                        icon: '/path/to/icon.png'
                    });
                }
            });
        }
    }

    function shareEvent(title, description) {
        if (navigator.share) {
            navigator.share({
                title: title,
                text: description,
                url: window.location.href
            }).then(() => {
                showToast('Event shared successfully!');
            }).catch(err => {
                showToast('Sharing was cancelled');
            });
        } else {
            navigator.clipboard.writeText(`${title}\n${description}`);
            showToast('Event details copied to clipboard!');
        }
    }

    function logout() {
        // In a real app, you would handle actual logout logic
        showToast('Logged out successfully');
        setTimeout(() => {
            window.location.href = '/login'; // Redirect to login page
        }, 1500);
    }

    function showToast(message) {
        toastMessage.textContent = message;
        toast.show();
    }

    // Search when Enter key is pressed
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchEvents();
        }
    });

    // Listen for events from other tabs
    window.addEventListener('storage', function(event) {
        if (event.key === 'events') {
            renderEvents();
            updateStats();
            showToast('Events updated from another tab!');
        }
    });

    // Listen for events from same tab
    window.addEventListener('eventsUpdated', function() {
        renderEvents();
        updateStats();
        showToast('New event added!');
    });
</script>
@endpush
@endsection

