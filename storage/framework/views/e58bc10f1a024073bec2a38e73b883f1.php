<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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

    /* ---- Base ---- */
    .db-wrapper { background: var(--surface-2); min-height: 100vh; }

    /* ---- Cards ---- */
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
        padding: 18px 22px 14px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .db-card-header .card-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
        letter-spacing: -0.2px;
    }
    .db-card-header .card-icon {
        width: 34px; height: 34px;
        background: var(--primary-bg);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary);
        font-size: 14px;
        flex-shrink: 0;
    }
    .db-card-body { padding: 20px 22px; }

    /* ---- Profile Card ---- */
    .profile-banner {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 24px 22px 18px;
        position: relative;
        overflow: hidden;
    }
    .profile-banner::before {
        content: '';
        position: absolute;
        top: -30px; right: -30px;
        width: 120px; height: 120px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .profile-banner::after {
        content: '';
        position: absolute;
        bottom: -20px; right: 40px;
        width: 70px; height: 70px;
        background: rgba(255,255,255,0.07);
        border-radius: 50%;
    }
    .profile-avatar-wrap {
        position: relative;
        display: inline-block;
    }
    .profile-avatar-wrap img {
        width: 72px; height: 72px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.8);
        object-fit: cover;
        box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    }
    .profile-online-dot {
        position: absolute;
        bottom: 4px; right: 2px;
        width: 14px; height: 14px;
        background: #22c55e;
        border-radius: 50%;
        border: 2px solid white;
    }
    .profile-name { font-size: 19px; font-weight: 700; color: white; margin: 0; line-height: 1.2; }
    .profile-role { font-size: 12px; color: rgba(255,255,255,0.82); margin-top: 4px; }
    .profile-dept-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        color: white;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        margin-top: 8px;
        backdrop-filter: blur(4px);
    }
    .profile-info-row {
        padding: 16px 22px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .profile-info-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .profile-info-item .pi-icon {
        width: 36px; height: 36px;
        background: var(--primary-bg);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary);
        font-size: 13px;
        flex-shrink: 0;
    }
    .profile-info-item .pi-label {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 500;
        margin-bottom: 2px;
    }
    .profile-info-item .pi-value {
        font-size: 13.5px;
        color: var(--text-main);
        font-weight: 600;
    }

    /* ---- Attendance Card ---- */
    .attendance-card-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px 22px;
        gap: 14px;
    }
    .clock-ring-wrap {
        position: relative;
        width: 160px; height: 160px;
        flex-shrink: 0;
    }
    .clock-ring-wrap svg {
        transform: rotate(-90deg);
        filter: drop-shadow(0 4px 12px rgba(232,89,12,0.18));
    }
    .clock-ring-wrap .clock-inner {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    .clock-inner .ct-time {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -1px;
        font-variant-numeric: tabular-nums;
    }
    .clock-inner .ct-label {
        font-size: 10px;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Live dot */
    .live-dot {
        display: inline-block;
        width: 9px; height: 9px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulseDot 1.5s infinite;
        margin-right: 5px;
        vertical-align: middle;
    }
    .live-dot.punched-out { background: #ef4444; animation: none; }
    @keyframes pulseDot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.75); }
    }

    .att-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        border-radius: 30px;
        font-size: 12.5px;
        font-weight: 600;
    }
    .att-status-badge.status-in  { background: rgba(34,197,94,0.1);  color: #16a34a; }
    .att-status-badge.status-out { background: rgba(239,68,68,0.1);   color: #dc2626; }
    .att-status-badge.status-idle{ background: rgba(107,114,128,0.1); color: #6b7280; }
    .att-status-badge.status-warn{ background: rgba(234,179,8,0.1);   color: #a16207; }

    /* Datetime strip */
    .datetime-strip {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
        background: var(--surface-2);
        padding: 6px 14px;
        border-radius: 30px;
    }

    /* Punch buttons */
    .punch-btn {
        width: 100%;
        padding: 12px 24px;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.3px;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .punch-btn-in {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        box-shadow: 0 4px 14px rgba(232,89,12,0.35);
    }
    .punch-btn-in:hover {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        box-shadow: 0 6px 20px rgba(232,89,12,0.45);
        transform: translateY(-1px);
        color: white;
    }
    .punch-btn-out {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        color: white;
        box-shadow: 0 4px 14px rgba(239,68,68,0.3);
    }
    .punch-btn-out:hover {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        box-shadow: 0 6px 20px rgba(239,68,68,0.4);
        transform: translateY(-1px);
        color: white;
    }
    .punch-btn-warn {
        background: linear-gradient(135deg, #f59e0b 0%, #fcd34d 100%);
        color: white;
        box-shadow: 0 4px 14px rgba(245,158,11,0.3);
    }
    .punch-btn-disabled {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* ---- Data Tables ---- */
    .db-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .db-table thead th {
        background: var(--surface-2);
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--border);
    }
    .db-table tbody td {
        padding: 11px 14px;
        font-size: 13px;
        color: var(--text-main);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .db-table tbody tr:last-child td { border-bottom: none; }
    .db-table tbody tr { transition: background 0.15s ease; }
    .db-table tbody tr:hover td { background: var(--primary-bg); }

    /* ---- Events Panel ---- */
    .event-item-new {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: var(--radius-sm);
        border: 1px solid transparent;
        transition: all 0.2s ease;
        cursor: default;
    }
    .event-item-new:hover {
        background: var(--surface-2);
        border-color: var(--border);
        transform: translateX(2px);
    }
    .event-item-new.birthday-event:hover  { border-color: rgba(232,89,12,0.2); background: var(--primary-bg); }
    .event-item-new.anniversary-event:hover { border-color: rgba(34,197,94,0.2); background: rgba(34,197,94,0.05); }
    .event-avatar-new img {
        width: 42px; height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border);
    }
    .event-item-new.birthday-event .event-avatar-new img   { border-color: rgba(232,89,12,0.35); }
    .event-item-new.anniversary-event .event-avatar-new img { border-color: rgba(34,197,94,0.35); }
    .event-name { font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 2px; }
    .event-msg  { font-size: 12px; font-weight: 600; }
    .event-msg.birthday-msg    { color: var(--primary); }
    .event-msg.anniversary-msg { color: #16a34a; }
    .event-meta { font-size: 11px; color: var(--text-muted); }
    .event-badge {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .birthday-badge    { background: var(--primary-bg); }
    .anniversary-badge { background: rgba(34,197,94,0.1); }
    .events-scroll {
        max-height: 340px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
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

    /* ---- Priority badges ---- */
    .prio-high   { background: rgba(239,68,68,0.12);   color: #dc2626; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
    .prio-medium { background: rgba(245,158,11,0.12);  color: #d97706; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
    .prio-low    { background: rgba(34,197,94,0.12);   color: #16a34a; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
    .status-done { background: rgba(34,197,94,0.12);   color: #16a34a; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
    .status-pend { background: rgba(239,68,68,0.12);   color: #dc2626; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }

    /* ---- Section label ---- */
    .section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-muted);
        margin-bottom: 12px;
    }
    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ---- Modal ---- */
    .db-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        backdrop-filter: blur(3px);
        z-index: 1050;
        align-items: center;
        justify-content: center;
    }
    .db-modal-overlay.active { display: flex; }
    .db-modal-box {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 32px;
        max-width: 380px;
        width: 92%;
        box-shadow: var(--shadow-lg);
        text-align: center;
        animation: modalIn 0.22s ease;
    }
    @keyframes modalIn { from { opacity: 0; transform: scale(0.93) translateY(10px); } to { opacity: 1; transform: none; } }
    .db-modal-icon {
        width: 64px; height: 64px;
        background: rgba(239,68,68,0.1);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
        font-size: 26px;
        color: #dc2626;
    }
    .db-modal-title { font-size: 18px; font-weight: 800; color: var(--text-main); margin-bottom: 8px; }
    .db-modal-desc  { font-size: 13.5px; color: var(--text-muted); margin-bottom: 24px; }
    .db-modal-actions { display: flex; gap: 10px; justify-content: center; }
    .db-modal-actions button {
        flex: 1;
        padding: 11px 18px;
        border-radius: var(--radius-sm);
        font-size: 13.5px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-cancel-modal { background: var(--surface-2); color: var(--text-muted); border: 1px solid var(--border) !important; }
    .btn-cancel-modal:hover { background: var(--border); }
    .btn-confirm-modal { background: linear-gradient(135deg, #ef4444, #f87171); color: white; box-shadow: 0 4px 12px rgba(239,68,68,0.3); }
    .btn-confirm-modal:hover { background: linear-gradient(135deg, #dc2626, #ef4444); }

    /* ---- Empty states ---- */
    .empty-state { text-align: center; padding: 28px 16px; }
    .empty-state i { font-size: 2rem; color: #d1d5db; margin-bottom: 10px; }
    .empty-state p { font-size: 13px; color: var(--text-muted); margin: 0; }
</style>

<div class="db-wrapper">
    <div class="row g-3">
        <?php if(session('status')): ?>
            <div class="col-12">
                <div class="alert alert-success border-0 rounded-3 d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-check-circle text-success"></i>
                    <?php echo e(session('status')); ?>

                </div>
            </div>
        <?php endif; ?>

        <?php if(\Auth::user()->type == 'employee'): ?>

        
        <div class="col-xxl-9">
            <div class="row g-3">

                
                <div class="col-lg-5">
                    <div class="db-card h-100">
                        
                        <div class="profile-banner">
                            <div class="d-flex align-items-center gap-3">
                                <div class="profile-avatar-wrap">
                                    <?php $profile = \App\Models\Utility::get_file('uploads/avatar/'); ?>
                                    <img src="<?php echo e(!empty($emp->user->avatar) ? $profile . $emp->user->avatar : $profile . 'avatar.png'); ?>" alt="Profile">
                                    <span class="profile-online-dot"></span>
                                </div>
                                <div>
                                    <div class="profile-name"><?php echo e($emp->name); ?></div>
                                    <div class="profile-role"><?php echo e($emp->designation->name ?? 'No Designation'); ?></div>
                                    <span class="profile-dept-badge">
                                        <i class="fas fa-layer-group me-1" style="font-size:10px;"></i>
                                        <?php echo e($emp->department->name ?? 'No Department'); ?>

                                    </span>
                                </div>
                            </div>
                        </div>

                        
                        <div class="profile-info-row">
                            <div class="profile-info-item">
                                <div class="pi-icon"><i class="fas fa-phone"></i></div>
                                <div>
                                    <div class="pi-label">Phone Number</div>
                                    <div class="pi-value"><?php echo e($emp->phone ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="profile-info-item">
                                <div class="pi-icon"><i class="fas fa-envelope"></i></div>
                                <div>
                                    <div class="pi-label">Email Address</div>
                                    <div class="pi-value" style="font-size:12.5px;"><?php echo e($emp->email ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="profile-info-item">
                                <div class="pi-icon"><i class="fas fa-calendar-check"></i></div>
                                <div>
                                    <div class="pi-label">Joined On</div>
                                    <div class="pi-value"><?php echo e(\Carbon\Carbon::parse($emp->company_doj)->format('d M Y')); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="col-lg-7">
                    <div class="db-card h-100">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="card-title">Attendance</div>
                                <div id="currentDateTime" style="font-size:11px;color:var(--text-muted);margin-top:1px;"></div>
                            </div>
                        </div>
                        <div class="attendance-card-body">

                            
                            <div class="clock-ring-wrap">
                                <svg width="160" height="160" viewBox="0 0 160 160">
                                    <circle cx="80" cy="80" r="68" stroke="#f0f0f0" stroke-width="10" fill="none"/>
                                    <circle id="progressCircle" cx="80" cy="80" r="68"
                                        stroke="url(#ringGrad)" stroke-width="10" fill="none"
                                        stroke-dasharray="427" stroke-dashoffset="427"
                                        stroke-linecap="round"/>
                                    <defs>
                                        <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" style="stop-color:#e8590c"/>
                                            <stop offset="100%" style="stop-color:#ff7a3d"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="clock-inner">
                                    <div id="progressTime" class="ct-time">00:00:00</div>
                                    <div class="ct-label">Elapsed</div>
                                </div>
                            </div>

                            
                            <?php
                                $siteVisit = \App\Models\SiteVisit::where('employee_id', $emp->id)
                                    ->where('start_date', '<=', date('Y-m-d'))
                                    ->where('end_date', '>=', date('Y-m-d'))
                                    ->where('status', 'Approved')
                                    ->first();
                            ?>

                            <div id="attendanceStatusWrap">
                                <?php if(!isset($employeeAttendance) || !$employeeAttendance->clock_in): ?>
                                    <span class="att-status-badge status-idle">
                                        <i class="fas fa-fingerprint"></i> Not Punched In
                                    </span>
                                <?php else: ?>
                                    <?php if($siteVisit): ?>
                                        <?php if(empty($employeeAttendance->clock_in_2) || $employeeAttendance->clock_in_2 == '00:00:00'): ?>
                                            <span class="att-status-badge status-warn">
                                                <i class="fas fa-map-marker-alt"></i>
                                                Site Visit Pending • Punched In at <?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_in)->format('h:i A')); ?>

                                            </span>
                                        <?php elseif(empty($employeeAttendance->clock_out_2) || $employeeAttendance->clock_out_2 == '00:00:00'): ?>
                                            <span class="att-status-badge status-in">
                                                <span class="live-dot"></span>
                                                Site Visit In at <?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_in_2)->format('h:i A')); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="att-status-badge status-out">
                                                <i class="fas fa-check-circle"></i> Site Visit Completed
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if($employeeAttendance->clock_out == '00:00:00' || !$employeeAttendance->clock_out): ?>
                                            <span class="att-status-badge status-in">
                                                <span class="live-dot"></span>
                                                Punched In at <?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_in)->format('h:i A')); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="att-status-badge status-out">
                                                <i class="fas fa-sign-out-alt"></i>
                                                Punched Out at <?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_out)->format('h:i A')); ?>

                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            
                            <?php echo e(Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post', 'id' => 'attendanceForm', 'style' => 'width:100%'])); ?>

                                <input type="hidden" id="latitude"  name="latitude">
                                <input type="hidden" id="longitude" name="longitude">
                                <input type="hidden" id="location"  name="location">

                                <?php if(!isset($employeeAttendance) || !$employeeAttendance->clock_in): ?>
                                    <button type="submit" value="0" name="in" id="clock_in" class="punch-btn punch-btn-in">
                                        <i class="fas fa-fingerprint"></i> Punch In
                                    </button>
                                <?php elseif($siteVisit && (empty($employeeAttendance->clock_in_2) || $employeeAttendance->clock_in_2 == '00:00:00')): ?>
                                    <button type="submit" value="0" name="in" id="clock_in_2" class="punch-btn punch-btn-warn">
                                        <i class="fas fa-map-marker-alt"></i> Site Visit In
                                    </button>
                                <?php elseif($siteVisit && (empty($employeeAttendance->clock_out_2) || $employeeAttendance->clock_out_2 == '00:00:00')): ?>
                                    <button type="button" value="1" name="out" id="clock_out_2" class="punch-btn punch-btn-out" onclick="showClockOutModal()">
                                        <i class="fas fa-map-marker-alt"></i> Site Visit Out
                                    </button>
                                <?php elseif($employeeAttendance->clock_out == '00:00:00' || !$employeeAttendance->clock_out): ?>
                                    <button type="button" value="1" name="out" id="clock_out" class="punch-btn punch-btn-out" onclick="showClockOutModal()">
                                        <i class="fas fa-sign-out-alt"></i> Punch Out
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="punch-btn punch-btn-disabled" disabled>
                                        <i class="fas fa-check"></i> Completed for Today
                                    </button>
                                <?php endif; ?>
                            <?php echo e(Form::close()); ?>


                        </div>
                    </div>
                </div>

                
                <div class="col-lg-6 d-flex">
                    <div class="db-card w-100" style="display:flex;flex-direction:column;">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-map-marked-alt"></i></div>
                            <span class="card-title">Today's Site Visits</span>
                        </div>
                        <div style="height:260px;overflow-y:auto;flex:1;">
                            <?php
                                $currentDate = date('Y-m-d');
                                $todaySiteVisits = \App\Models\SiteVisit::where('start_date', '<=', $currentDate)
                                    ->where('end_date', '>=', $currentDate)
                                    ->where('status', 'Approved')->get();
                                $siteAttendanceEmployees = collect();
                                if ($todaySiteVisits->count() > 0) {
                                    $siteAttendanceEmployees = $todaySiteVisits->map(function ($visit) use ($currentDate) {
                                        $attendance = \App\Models\AttendanceEmployee::where('employee_id', $visit->employee_id)->where('date', $currentDate)->first();
                                        if ($attendance && !empty($attendance->clock_in) && $attendance->clock_in != '00:00:00') {
                                            return ['employee' => $visit->employee];
                                        }
                                        return null;
                                    })->filter();
                                }
                                $hasTodaySiteVisits = $todaySiteVisits->count() > 0;
                            ?>
                            <?php if($hasTodaySiteVisits): ?>
                                <table class="db-table">
                                    <thead>
                                        <tr><th>Employee</th><th>Location</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $siteAttendanceEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svAtt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $siteLocation = \App\Models\SiteVisit::where('employee_id', $svAtt['employee']->id)
                                                    ->where('start_date', '<=', date('Y-m-d'))
                                                    ->where('end_date', '>=', date('Y-m-d'))
                                                    ->where('status', 'Approved')->value('location');
                                            ?>
                                            <tr>
                                                <td><i class="fas fa-user-circle text-muted me-1"></i><?php echo e($svAtt['employee']->name ?? 'Unknown'); ?></td>
                                                <td style="font-size:12px;color:var(--text-muted);"><?php echo e($siteLocation ?? '--'); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $presentIds = $siteAttendanceEmployees->pluck('employee.id')->toArray();
                                            $pendingVisits = \App\Models\SiteVisit::where('start_date', '<=', $currentDate)
                                                ->where('end_date', '>=', $currentDate)
                                                ->where('status', 'Approved')
                                                ->whereNotIn('employee_id', $presentIds)->get();
                                        ?>
                                        <?php $__currentLoopData = $pendingVisits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><i class="fas fa-user-circle text-muted me-1"></i><?php echo e($visit->employee->name ?? 'Unknown'); ?></td>
                                                <td style="font-size:12px;color:var(--text-muted);"><?php echo e($visit->location ?? '--'); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-map-signs d-block"></i>
                                    <p>No site visits today</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 d-flex">
                    <div class="db-card w-100" style="display:flex;flex-direction:column;">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-user-clock"></i></div>
                            <span class="card-title">Today's Leave</span>
                        </div>
                        <div style="height:260px;overflow-y:auto;flex:1;">
                            <?php if(isset($todayLeaveEmployees) && $todayLeaveEmployees->count() > 0): ?>
                                <table class="db-table">
                                    <thead>
                                        <tr><th>Employee</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $todayLeaveEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><i class="fas fa-user-circle text-muted me-1"></i><?php echo e($leave->employees->name ?? 'N/A'); ?></td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-umbrella-beach d-block"></i>
                                    <p>No employees on leave today</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="col-lg-6 d-flex">
                    <div class="db-card w-100" style="display:flex;flex-direction:column;">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-bullhorn"></i></div>
                            <span class="card-title">Notices</span>
                        </div>
                        <div style="height:260px; overflow-y:auto;flex:1;">
                            <?php if(isset($notices) && $notices->count() > 0): ?>
                                <table class="db-table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e(Str::limit($notice->title, 40, '...')); ?></td>
                                            <td style="white-space:nowrap;font-size:12px;color:var(--text-muted);">
                                                <?php echo e(\Carbon\Carbon::parse($notice->notice_startdate)->format('d M')); ?>

                                                –
                                                <?php echo e(\Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y')); ?>

                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-bell-slash d-block"></i>
                                    <p>No notices right now</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 d-flex">
                    <div class="db-card w-100" style="display:flex;flex-direction:column;">
                        <div class="db-card-header">
                            <div class="card-icon"><i class="fas fa-tasks"></i></div>
                            <span class="card-title">TO-DO Lists</span>
                        </div>
                        <div style="height:260px; overflow-y:auto;flex:1;">
                            <?php if(isset($todos) && $todos->count() > 0): ?>
                                <table class="db-table">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Priority</th>
                                            <th>Due</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $todos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $todo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e(Str::limit($todo->task, 28, '...')); ?></td>
                                            <td>
                                                <?php if($todo->priority == 1): ?>
                                                    <span class="prio-high">High</span>
                                                <?php elseif($todo->priority == 2): ?>
                                                    <span class="prio-medium">Medium</span>
                                                <?php else: ?>
                                                    <span class="prio-low">Low</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">
                                                <?php echo e(\Carbon\Carbon::parse($todo->expires_at)->format('d M Y')); ?>

                                            </td>
                                            <td>
                                                <?php if($todo->is_completed): ?>
                                                    <span class="status-done">Done</span>
                                                <?php else: ?>
                                                    <span class="status-pend">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list d-block"></i>
                                    <p>No tasks found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        
        <div class="col-xxl-3">
            <div class="d-flex flex-column gap-3 sticky-top" style="top:12px;">

                
                <div class="db-card">
                    <div class="db-card-header">
                        <div class="card-icon"><i class="fas fa-star"></i></div>
                        <span class="card-title">This Month's Events</span>
                    </div>
                    <div class="db-card-body pt-2 pb-3 px-3">
                        <?php if(isset($monthlyEvents) && count($monthlyEvents) > 0): ?>
                            <div class="events-scroll">
                                <?php $__currentLoopData = $monthlyEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="event-item-new <?php echo e($event['type'] == 'birthday' ? 'birthday-event' : 'anniversary-event'); ?>">
                                    <div class="event-avatar-new">
                                        <img src="<?php echo e(asset('storage/uploads/avatar/' . $event['avatar'])); ?>"
                                             alt="<?php echo e($event['employee_name']); ?>"
                                             onerror="this.onerror=null; this.src='<?php echo e(asset('storage/uploads/avatar/avatar.png')); ?>'">
                                    </div>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="event-name"><?php echo e($event['employee_name']); ?></div>
                                        <div class="event-msg <?php echo e($event['type'] == 'birthday' ? 'birthday-msg' : 'anniversary-msg'); ?>">
                                            <?php echo e($event['message']); ?>

                                        </div>
                                        <div class="event-meta">
                                            <i class="fas fa-calendar-alt me-1"></i><?php echo e($event['date']); ?>

                                            &bull;
                                            <i class="fas fa-building me-1"></i><?php echo e($event['department']); ?>

                                        </div>
                                    </div>
                                    <div class="event-badge <?php echo e($event['type'] == 'birthday' ? 'birthday-badge' : 'anniversary-badge'); ?>">
                                        <?php if($event['type'] == 'birthday'): ?>
                                            🎂
                                        <?php else: ?>
                                            🏆
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times d-block"></i>
                                <p>No events this month</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="db-card">
                    <div class="db-card-header">
                        <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
                        <span class="card-title">Calendar</span>
                    </div>
                    <div class="db-card-body" style="padding:12px;">
                        <div id="calendar"></div>
                    </div>
                </div>

            </div>
        </div>

        <?php endif; ?> 
    </div>
</div>


<div class="db-modal-overlay" id="clockOutModal">
    <div class="db-modal-box">
        <div class="db-modal-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="db-modal-title">Confirm Punch Out</div>
        <div class="db-modal-desc">Are you sure you want to punch out? Your working hours will be recorded.</div>
        <div class="db-modal-actions">
            <button class="btn-cancel-modal" onclick="hideClockOutModal()">Cancel</button>
            <button class="btn-confirm-modal" id="confirmClockOutBtn">Yes, Punch Out</button>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('assets/js/plugins/main.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>

    <?php if(Auth::user()->type == 'employee'): ?>
    <script type="text/javascript">
    $(document).ready(function() { get_data(); });
    function get_data() {
        var calender_type = 'local_calender';
        $('#calendar').removeClass('local_calender google_calender').addClass(calender_type);
        $.ajax({
            data: { "_token": "<?php echo e(csrf_token()); ?>", 'calender_type': calender_type },
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
    <?php endif; ?>

    <script>
    // Modal helpers
    function showClockOutModal() {
        document.getElementById('clockOutModal').classList.add('active');
    }
    function hideClockOutModal() {
        document.getElementById('clockOutModal').classList.remove('active');
    }
    document.getElementById('clockOutModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideClockOutModal();
    });

    document.addEventListener("DOMContentLoaded", function () {
        const CIRCUMFERENCE = 427; // 2 * PI * 68
        let progressCircle = document.getElementById("progressCircle");
        let progressTime   = document.getElementById("progressTime");
        let clockInButton  = document.getElementById("clock_in");
        let clockIn2Button = document.getElementById("clock_in_2");
        let confirmClockOutBtn = document.getElementById("confirmClockOutBtn");
        let currentTimeEl  = document.getElementById("currentDateTime");

        function isNewDay() {
            const last = localStorage.getItem("lastClockOutDate");
            if (!last) return false;
            return last !== new Date().toLocaleDateString();
        }
        if (isNewDay()) {
            localStorage.removeItem("clockInTime");
            localStorage.removeItem("clockOutTime");
            localStorage.removeItem("isPunchedOut");
        }

        let clockInTime  = localStorage.getItem("clockInTime")  && !isNewDay() ? new Date(localStorage.getItem("clockInTime"))  : null;
        let clockOutTime = localStorage.getItem("clockOutTime") && !isNewDay() ? new Date(localStorage.getItem("clockOutTime")) : null;
        let isPunchedOut = localStorage.getItem("isPunchedOut") === "true" && !isNewDay();

        <?php if(isset($employeeAttendance) && $employeeAttendance->clock_in): ?>
            <?php
                $clockInDate = \Carbon\Carbon::parse(($employeeAttendance->date ?? date('Y-m-d')) . ' ' . $employeeAttendance->clock_in);
            ?>
            clockInTime = new Date(<?php echo e($clockInDate->year); ?>, <?php echo e($clockInDate->month - 1); ?>, <?php echo e($clockInDate->day); ?>, <?php echo e($clockInDate->hour); ?>, <?php echo e($clockInDate->minute); ?>, <?php echo e($clockInDate->second); ?>);
            localStorage.setItem("clockInTime", clockInTime.toISOString());
        <?php else: ?>
            clockInTime = null;
            localStorage.removeItem("clockInTime");
        <?php endif; ?>

        <?php if(isset($employeeAttendance) && $employeeAttendance->clock_out && $employeeAttendance->clock_out !== '00:00:00'): ?>
            <?php
                $clockOutDate = \Carbon\Carbon::parse(($employeeAttendance->date ?? date('Y-m-d')) . ' ' . $employeeAttendance->clock_out);
            ?>
            clockOutTime = new Date(<?php echo e($clockOutDate->year); ?>, <?php echo e($clockOutDate->month - 1); ?>, <?php echo e($clockOutDate->day); ?>, <?php echo e($clockOutDate->hour); ?>, <?php echo e($clockOutDate->minute); ?>, <?php echo e($clockOutDate->second); ?>);
            localStorage.setItem("clockOutTime", clockOutTime.toISOString());
            localStorage.setItem("isPunchedOut", "true");
            localStorage.setItem("lastClockOutDate", new Date().toLocaleDateString());
            isPunchedOut = true;
        <?php else: ?>
            clockOutTime = null;
            isPunchedOut = false;
            localStorage.removeItem("clockOutTime");
            localStorage.removeItem("isPunchedOut");
            localStorage.removeItem("lastClockOutDate");
        <?php endif; ?>

        function updateTimeDisplay() {
            if (!currentTimeEl) return;
            currentTimeEl.textContent = new Date().toLocaleString("en-US", {
                hour: "2-digit", minute: "2-digit", second: "2-digit",
                hour12: true, day: "2-digit", month: "short", year: "numeric"
            });
        }

        function updateProgress() {
            if (!clockInTime || !progressCircle) return;
            let elapsedSeconds;
            if (clockOutTime) {
                elapsedSeconds = Math.floor((clockOutTime - clockInTime) / 1000);
            } else {
                elapsedSeconds = Math.floor((new Date() - clockInTime) / 1000);
            }
            elapsedSeconds = Math.max(0, Math.min(elapsedSeconds, 10 * 60 * 60));
            let percentage = elapsedSeconds / (10 * 60 * 60);
            progressCircle.style.strokeDashoffset = CIRCUMFERENCE - (percentage * CIRCUMFERENCE);

            let h = Math.floor(elapsedSeconds / 3600);
            let m = Math.floor((elapsedSeconds % 3600) / 60);
            let s = elapsedSeconds % 60;
            if (progressTime) {
                progressTime.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            }
        }

        updateTimeDisplay();
        updateProgress();
        setInterval(updateTimeDisplay, 1000);
        if (!isPunchedOut) setInterval(updateProgress, 1000);

        // Geolocation
        let prefetchedLocation = null;
        let lastPrefetchTime = 0;

        async function startLocationPrefetching() {
            if (!navigator.geolocation) return;
            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    });
                });
                prefetchedLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
                lastPrefetchTime = Date.now();
                if(document.getElementById('latitude'))  document.getElementById('latitude').value  = position.coords.latitude;
                if(document.getElementById('longitude')) document.getElementById('longitude').value = position.coords.longitude;
            } catch (e) {
                console.warn("Pre-fetch failed:", e.message);
            }
        }
        startLocationPrefetching();

        function getLocation(options) {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error("Geolocation not supported"));
                    return;
                }
                const timeoutId = setTimeout(() => {
                    reject(new Error("Location request timed out"));
                }, (options.timeout || 10000) + 2000);

                navigator.geolocation.getCurrentPosition(
                    (pos) => { clearTimeout(timeoutId); resolve(pos); },
                    (err) => { clearTimeout(timeoutId); reject(err); },
                    options
                );
            });
        }

        async function captureGPSLocation() {
            if (prefetchedLocation && (Date.now() - lastPrefetchTime < 120000)) {
                return prefetchedLocation;
            }
            return new Promise(async (resolve, reject) => {
                try {
                    const position = await getLocation({ enableHighAccuracy: true, timeout: 8000, maximumAge: 30000 });
                    resolve({ latitude: position.coords.latitude, longitude: position.coords.longitude, accuracy: position.coords.accuracy || 100 });
                } catch (error) {
                    try {
                        const position = await getLocation({ enableHighAccuracy: false, timeout: 7000, maximumAge: 60000 });
                        resolve({ latitude: position.coords.latitude, longitude: position.coords.longitude, accuracy: position.coords.accuracy || 200 });
                    } catch (err2) {
                        reject(new Error('Location could not be captured. Please ensure GPS is enabled and permission is granted.'));
                    }
                }
            });
        }

        async function handleAttendanceAction(action, button) {
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Verifying Location...';

            try {
                const loc = await captureGPSLocation();
                
                document.getElementById('latitude').value  = loc.latitude;
                document.getElementById('longitude').value = loc.longitude;
                document.getElementById('location').value  = "GPS Fixed (" + loc.accuracy.toFixed(0) + "m)";
                
                if (action === 'punch_in') {
                    localStorage.setItem("clockInTime", new Date().toISOString());
                } else if (action === 'punch_out') {
                    localStorage.setItem("isPunchedOut", "true");
                    localStorage.setItem("lastClockOutDate", new Date().toLocaleDateString());
                }
                
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
                document.getElementById('attendanceForm').submit();
                
                // Add safety timeout to prevent stuck button if submit hangs
                setTimeout(() => {
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }
                }, 5000);
            } catch (error) {
                button.disabled = false;
                button.innerHTML = originalText;
                
                if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
                    if(confirm("Browser blocked location on localhost.\n\nUse test location (Pune)?")) {
                        document.getElementById('latitude').value  = 18.5204;
                        document.getElementById('longitude').value = 73.8567;
                        document.getElementById('location').value  = "GPS Fixed (Test)";
                        document.getElementById('attendanceForm').submit();
                        return;
                    }
                }
                alert(error.message || "Location error. Please allow location access and try again.");
            }
        }

        if (clockInButton)  clockInButton.addEventListener("click",  (e) => { e.preventDefault(); handleAttendanceAction('punch_in', clockInButton); });
        if (clockIn2Button) clockIn2Button.addEventListener("click",  (e) => { e.preventDefault(); handleAttendanceAction('site_in', clockIn2Button); });
        if (confirmClockOutBtn) confirmClockOutBtn.addEventListener("click", () => {
            hideClockOutModal();
            handleAttendanceAction('punch_out', confirmClockOutBtn);
        });
    });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/dashboard/dashboard.blade.php ENDPATH**/ ?>