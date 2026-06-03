@extends('layouts.admin')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@php
    $setting = App\Models\Utility::settings();
@endphp

@section('content')
<style>
    :root {
        --primary: #e8590c;
        --primary-dark: #c04a08;
        --primary-light: #ff7a3d;
        --primary-bg: rgba(232, 89, 12, 0.08);
        --primary-bg-hover: rgba(232, 89, 12, 0.15);
        --surface: #ffffff;
        --surface-2: #f8f9fc;
        --border: #e9ecef;
        --text-main: #1a1d23;
        --text-muted: #6c757d;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
        --shadow-lg: 0 8px 32px rgba(0,0,0,0.10), 0 4px 12px rgba(0,0,0,0.06);
        --radius: 14px;
        --radius-sm: 8px;
    }

    .co-wrapper { background: var(--surface-2); min-height: 100vh; }

    /* ---- Stat Cards ---- */
    .stat-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow 0.22s ease, transform 0.22s ease;
        overflow: hidden;
        position: relative;
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: -18px; right: -18px;
        width: 80px; height: 80px;
        background: var(--primary-bg);
        border-radius: 50%;
        pointer-events: none;
    }
    .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .stat-icon {
        width: 52px; height: 52px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }
    .stat-icon.ic-orange  { background: linear-gradient(135deg, #e8590c, #ff7a3d); box-shadow: 0 4px 12px rgba(232,89,12,0.35); }
    .stat-icon.ic-blue    { background: linear-gradient(135deg, #299dc6, #4db8df); box-shadow: 0 4px 12px rgba(41,157,198,0.35); }
    .stat-icon.ic-teal    { background: linear-gradient(135deg, #3B7080, #56a5bd); box-shadow: 0 4px 12px rgba(59,112,128,0.35); }
    .stat-icon.ic-purple  { background: linear-gradient(135deg, #B55CC4, #d07ae0); box-shadow: 0 4px 12px rgba(181,92,196,0.35); }
    .stat-icon.ic-green   { background: linear-gradient(135deg, #22c55e, #4ade80); box-shadow: 0 4px 12px rgba(34,197,94,0.35); }
    .stat-icon.ic-pink    { background: linear-gradient(135deg, #FD3995, #ff6ab6); box-shadow: 0 4px 12px rgba(253,57,149,0.35); }
    .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--text-main); line-height: 1; }
    .stat-sub   { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-top: 3px; }

    /* ---- Panel Cards ---- */
    .db-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: box-shadow 0.22s ease, transform 0.22s ease;
    }
    .db-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
    .db-card-header {
        padding: 16px 22px 14px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .db-card-header .card-title {
        font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0; letter-spacing: -0.2px;
    }
    .db-card-header .card-icon {
        width: 34px; height: 34px;
        background: var(--primary-bg);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary);
        font-size: 14px; flex-shrink: 0;
    }

    /* ---- Tables ---- */
    .db-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .db-table thead th {
        background: var(--surface-2);
        color: var(--text-muted);
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.6px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .db-table tbody td {
        padding: 11px 14px; font-size: 13px; color: var(--text-main);
        border-bottom: 1px solid var(--border); vertical-align: middle;
    }
    .db-table tbody tr:last-child td { border-bottom: none; }
    .db-table tbody tr { transition: background 0.15s ease; }
    .db-table tbody tr:hover td { background: var(--primary-bg); }

    /* ---- Status badges ---- */
    .badge-absent { background: rgba(239,68,68,0.1); color: #dc2626; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
    .badge-present { background: rgba(34,197,94,0.1); color: #16a34a; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
    .badge-leave { background: rgba(245,158,11,0.1); color: #d97706; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }

    /* ---- Events Panel ---- */
    .event-item-new {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 14px; border-radius: var(--radius-sm);
        border: 1px solid transparent;
        transition: all 0.2s ease; cursor: default;
    }
    .event-item-new:hover { background: var(--surface-2); border-color: var(--border); transform: translateX(2px); }
    .event-item-new.birthday-event:hover  { border-color: rgba(232,89,12,0.2); background: var(--primary-bg); }
    .event-item-new.anniversary-event:hover { border-color: rgba(34,197,94,0.2); background: rgba(34,197,94,0.05); }
    .event-avatar-new img {
        width: 42px; height: 42px; border-radius: 50%;
        object-fit: cover; border: 2px solid var(--border);
    }
    .event-item-new.birthday-event .event-avatar-new img   { border-color: rgba(232,89,12,0.35); }
    .event-item-new.anniversary-event .event-avatar-new img { border-color: rgba(34,197,94,0.35); }
    .event-name { font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 2px; }
    .event-msg  { font-size: 12px; font-weight: 600; }
    .event-msg.birthday-msg    { color: var(--primary); }
    .event-msg.anniversary-msg { color: #16a34a; }
    .event-meta { font-size: 11px; color: var(--text-muted); }
    .event-badge { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
    .birthday-badge    { background: var(--primary-bg); }
    .anniversary-badge { background: rgba(34,197,94,0.1); }
    .events-scroll { max-height: 340px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px; }
    .events-scroll::-webkit-scrollbar { width: 4px; }
    .events-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* ---- Calendar ---- */
    .fc-prev-button, .fc-next-button {
        padding: 4px 8px !important;
        background: var(--primary) !important;
        border: none !important;
        border-radius: var(--radius-sm) !important;
        color: white !important;
    }
    .fc-prev-button:hover, .fc-next-button:hover { background: var(--primary-dark) !important; }
    .fc-toolbar-title { font-size: 14px !important; font-weight: 700 !important; }

    /* ---- Empty states ---- */
    .empty-state { text-align: center; padding: 28px 16px; }
    .empty-state i { font-size: 2rem; color: #d1d5db; margin-bottom: 10px; }
    .empty-state p { font-size: 13px; color: var(--text-muted); margin: 0; }

    /* ---- Table scroll container ---- */
    .table-panel { height: 300px; overflow-y: auto; }
    .table-panel::-webkit-scrollbar { width: 4px; }
    .table-panel::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* ---- Section label ---- */
    .section-label {
        display: flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; color: var(--text-muted); margin-bottom: 12px;
    }
    .section-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }
</style>

<div class="co-wrapper">
    <div class="row g-3">

        @if (session('status'))
            <div class="col-12">
                <div class="alert alert-success border-0 rounded-3 d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-check-circle text-success"></i>
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @if (\Auth::user()->type == 'employee')
            {{-- Employee has no content here --}}
        @else

        {{-- ===== STAT CARDS ROW ===== --}}
        <div class="col-12">
            <div class="row g-3">

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon ic-purple"><i class="fa-solid fa-user-tie"></i></div>
                        <div>
                            <div class="stat-label">Total Employees</div>
                            <div class="stat-value">{{ $countUser + $countEmployee }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon ic-blue"><i class="fa-solid fa-clipboard-question"></i></div>
                        <div>
                            <div class="stat-label">Today's TimeSheet</div>
                            <div class="stat-value">{{ $todayEnquiryCount }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon ic-teal"><i class="fa-solid fa-calendar-check"></i></div>
                        <div>
                            <div class="stat-label">Today's Leaves</div>
                            <div class="stat-value">{{ $todayLeaves }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon ic-blue"><i class="fa-solid fa-sitemap"></i></div>
                        <div>
                            <div class="stat-label">Total Departments</div>
                            <div class="stat-value">{{ $totalDepartment }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon ic-orange"><i class="fa-solid fa-diagram-project"></i></div>
                        <div>
                            <div class="stat-label">Total Projects</div>
                            <div class="stat-value">{{ $totalProjects }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon ic-pink"><i class="fa-solid fa-ticket"></i></div>
                        <div>
                            <div class="stat-label">Total Tickets</div>
                            <div class="stat-value">{{ $countTicket }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ===== MAIN CONTENT + SIDEBAR ===== --}}
        <div class="col-xxl-9">
            <div class="row g-3">

                {{-- Today's Attendance --}}
                <div class="col-12">
                    <div class="db-card">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-fingerprint"></i></div>
                            <span class="card-title">Today's Attendance</span>
                        </div>
                        <div class="table-panel">
                            @if(count($presentEmployeesWithClockIn) > 0)
                                <table class="db-table">
                                    <thead>
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>Clock-In Time</th>
                                            <th>Clock-In Location</th>
                                            <th>Clock-Out Time</th>
                                            <th>Clock-Out Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($presentEmployeesWithClockIn as $data)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="fas fa-user-circle" style="color:var(--text-muted);font-size:16px;"></i>
                                                        {{ $data['employee']->name ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($data['clock_in'] ?? false)
                                                        <span class="badge-present">{{ $data['clock_in'] }}</span>
                                                    @else
                                                        <span style="color:var(--text-muted);">--:--</span>
                                                    @endif
                                                </td>
                                                <td style="font-size:12px;color:var(--text-muted);">
                                                    @php
                                                        $location = $data['clock_in_location'] ?? null;
                                                        if ($location) {
                                                            $parts = explode(',', $location);
                                                            echo '<i class="fas fa-map-marker-alt me-1" style="color:var(--primary);"></i>' . e(trim($parts[0]));
                                                        } else {
                                                            echo '<span style="color:var(--text-muted);">--</span>';
                                                        }
                                                    @endphp
                                                </td>
                                                <td>
                                                    @if(($data['clock_out'] ?? '--:--') !== '--:--')
                                                        <span class="badge-absent">{{ $data['clock_out'] }}</span>
                                                    @else
                                                        <span style="color:var(--text-muted);">--:--</span>
                                                    @endif
                                                </td>
                                                <td style="font-size:12px;color:var(--text-muted);">
                                                    @php
                                                        $outLocation = $data['clock_out_location'] ?? null;
                                                        if ($outLocation) {
                                                            $parts = explode(',', $outLocation);
                                                            echo '<i class="fas fa-map-marker-alt me-1" style="color:#dc2626;"></i>' . e(trim($parts[0]));
                                                        } else {
                                                            echo '<span style="color:var(--text-muted);">--</span>';
                                                        }
                                                    @endphp
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-clock d-block"></i>
                                    <p>No employees have clocked in yet today</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Today's Site Attendance --}}
                @if(isset($hasTodaySiteVisits) && $hasTodaySiteVisits)
                <div class="col-12">
                    <div class="db-card">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-map-marked-alt"></i></div>
                            <span class="card-title">Today's Site Attendance</span>
                        </div>
                        <div class="table-panel">
                            <table class="db-table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Clock-In</th>
                                        <th>Location</th>
                                        <th>Site In</th>
                                        <th>Site In Loc</th>
                                        <th>Site Out</th>
                                        <th>Site Out Loc</th>
                                        <th>Punch Out</th>
                                        <th>Punch Out Loc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($siteAttendanceEmployees as $data)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-user-circle" style="color:var(--text-muted);font-size:16px;"></i>
                                                    {{ $data['employee']->name ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td><span class="badge-present">{{ $data['clock_in'] }}</span></td>
                                            <td style="font-size:12px;color:var(--text-muted);">
                                                @php $loc = $data['clock_in_location']; $parts = explode(',', $loc); echo !empty($loc) && $loc != '--:--' ? '<i class="fas fa-map-marker-alt me-1" style="color:var(--primary);"></i>'.e(trim($parts[0])) : '--'; @endphp
                                            </td>
                                            <td>{{ $data['clock_in_2'] ?? '--' }}</td>
                                            <td style="font-size:12px;color:var(--text-muted);">
                                                @php $loc2 = $data['clock_in_2_location']; $parts = explode(',', $loc2); echo !empty($loc2) && $loc2 != '--:--' ? e(trim($parts[0])) : '--'; @endphp
                                            </td>
                                            <td>{{ $data['clock_out_2'] ?? '--' }}</td>
                                            <td style="font-size:12px;color:var(--text-muted);">
                                                @php $outLoc2 = $data['clock_out_2_location']; $parts = explode(',', $outLoc2); echo !empty($outLoc2) && $outLoc2 != '--:--' ? e(trim($parts[0])) : '--'; @endphp
                                            </td>
                                            <td>{{ $data['clock_out'] ?? '--' }}</td>
                                            <td style="font-size:12px;color:var(--text-muted);">
                                                @php $outLoc = $data['clock_out_location']; $parts = explode(',', $outLoc); echo !empty($outLoc) && $outLoc != '--:--' ? e(trim($parts[0])) : '--'; @endphp
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center" style="color:var(--text-muted);padding:24px;">
                                                <i class="fas fa-map-signs me-2"></i>No employees have punched in for today's site visits.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Leave & Not Clock-In --}}
                <div class="col-lg-6">
                    <div class="db-card h-100">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-user-clock"></i></div>
                            <span class="card-title">Today's Leave Employees</span>
                        </div>
                        <div class="table-panel">
                            @if($todayLeaveEmployees->count() > 0)
                                <table class="db-table">
                                    <thead>
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>Leave Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($todayLeaveEmployees as $leave)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="fas fa-user-circle" style="color:var(--text-muted);font-size:16px;"></i>
                                                        {{ $leave->employees->name ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td><span class="badge-leave">{{ $leave->leaveType->title ?? 'N/A' }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center" style="color:var(--text-muted);padding:24px;">No employees on leave today</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-umbrella-beach d-block"></i>
                                    <p>No employees on leave today</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="db-card h-100">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-user-times"></i></div>
                            <span class="card-title">Not Clocked In</span>
                        </div>
                        <div class="table-panel">
                            @if(!$notClockIns->isEmpty())
                                <table class="db-table">
                                    <thead>
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($notClockIns as $employee)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="fas fa-user-circle" style="color:var(--text-muted);font-size:16px;"></i>
                                                        {{ $employee->name ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td><span class="badge-absent">Absent</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-check-circle d-block" style="color:#22c55e;"></i>
                                    <p>All employees are present today!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Notices --}}
                <div class="col-12">
                    <div class="db-card">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-bullhorn"></i></div>
                            <span class="card-title">Notices</span>
                        </div>
                        <div class="table-panel">
                            @if(count($notices) > 0)
                                <table class="db-table">
                                    <thead>
                                        <tr>
                                            <th style="width:65%;">Title</th>
                                            <th style="width:35%;">Date Range</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($notices as $notice)
                                            <tr>
                                                <td>{{ Str::limit($notice->title, 60, '...') }}</td>
                                                <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">
                                                    {{ \Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y') }}
                                                    &ndash;
                                                    {{ \Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-bell-slash d-block"></i>
                                    <p>No notices at the moment</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ===== RIGHT SIDEBAR ===== --}}
        <div class="col-xxl-3">
            <div class="sticky-top" style="top: 20px; z-index: 100; max-height: calc(100vh - 40px); overflow-y: auto;">
                <div class="d-flex flex-column gap-3">

                {{-- Events --}}
                <div class="db-card">
                    <div class="db-card-header">
                        <div class="card-icon"><i class="fas fa-star"></i></div>
                        <span class="card-title">This Month's Events</span>
                    </div>
                    <div style="padding: 12px 14px;">
                        @if(isset($monthlyEvents) && count($monthlyEvents) > 0)
                            <div class="events-scroll">
                                @foreach($monthlyEvents as $event)
                                <div class="event-item-new {{ $event['type'] == 'birthday' ? 'birthday-event' : 'anniversary-event' }}">
                                    <div class="event-avatar-new">
                                        @php $avatarFile = $event['avatar'] ?? 'avatar.png'; @endphp
                                        <img src="{{ asset('storage/uploads/avatar/' . $avatarFile) }}"
                                             alt="{{ $event['employee_name'] }}"
                                             onerror="this.src='{{ asset('storage/avatars/avatar.png') }}'">
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div class="event-name">{{ $event['employee_name'] }}</div>
                                        <div class="event-msg {{ $event['type'] == 'birthday' ? 'birthday-msg' : 'anniversary-msg' }}">
                                            {{ $event['message'] }}
                                        </div>
                                        <div class="event-meta">
                                            <i class="fas fa-calendar-alt me-1"></i>{{ $event['date'] }}
                                            &bull;
                                            <i class="fas fa-building me-1"></i>{{ $event['department'] }}
                                        </div>
                                    </div>
                                    <div class="event-badge {{ $event['type'] == 'birthday' ? 'birthday-badge' : 'anniversary-badge' }}">
                                        {{ $event['type'] == 'birthday' ? '🎂' : '🏆' }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-calendar-times d-block"></i>
                                <p>No events this month</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Calendar --}}
                <div class="db-card">
                    <div class="db-card-header">
                        <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
                        <span class="card-title">Calendar</span>
                        <input type="hidden" id="path_admin" value="{{ url('/') }}">
                        @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
                            <select class="form-control form-control-sm ms-auto" name="calender_type" id="calender_type"
                                style="width:auto;" onchange="get_data()">
                                <option value="local_calender" selected>{{ __('Local Calendar') }}</option>
                            </select>
                        @endif
                    </div>
                    <div style="padding:12px;">
                        <div id='calendar' class='calendar'></div>
                    </div>
                </div>

                </div>
            </div>
        </div>

        @endif
    </div>
</div>

@endsection

@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>

    @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr')
    <script type="text/javascript">
    $(document).ready(function() { get_data(); });

    function get_data() {
        var calender_type = $('#calender_type :selected').val();
        if (!calender_type) calender_type = 'local_calender';

        $('#calendar').removeClass('local_calender google_calender').addClass(calender_type);

        $.ajax({
            data: { "_token": "{{ csrf_token() }}", 'calender_type': calender_type },
            success: function(data) {
                var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                    headerToolbar: { left: 'prev', center: 'title', right: 'next' },
                    themeSystem: 'bootstrap',
                    slotDuration: '00:10:00',
                    allDaySlot: true,
                    navLinks: false,
                    droppable: true,
                    selectable: true,
                    selectMirror: true,
                    editable: true,
                    dayMaxEvents: true,
                    handleWindowResize: true,
                    height: '320px',
                });
                calendar.render();
            }
        });
    }
    </script>
    @else
    <script>
    $(document).ready(function() { get_data(); });

    function get_data() {
        var calender_type = $('#calender_type :selected').val();
        if (!calender_type) calender_type = 'local_calender';

        $('#event_calendar').removeClass('local_calender google_calender').addClass(calender_type);

        $.ajax({
            url: $("#path_admin").val() + "/event/get_event_data",
            method: "POST",
            data: { "_token": "{{ csrf_token() }}", 'calender_type': calender_type },
            success: function(data) {
                var calendar = new FullCalendar.Calendar(document.getElementById('event_calendar'), {
                    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                    themeSystem: 'bootstrap',
                    height: '320px',
                });
                calendar.render();
            }
        });
    }
    </script>
    @endif
@endpush