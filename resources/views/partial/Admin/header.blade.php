
@php
    use App\Models\Utility;

    $users = \Auth::user();
    $currantLang = $users->currentLanguage();
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    $unseenCounter = App\Models\ChMessage::where('to_id', Auth::user()->id)
        ->where('seen', 0)
        ->count();
    $unreadNotifications = $users->unreadNotifications;
    $notificationCount = $unreadNotifications->count();
    $recentNotifications = $users->notifications()->orderBy('created_at', 'desc')->take(5)->get();
@endphp


@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
    <header class="dash-header transprent-bg" style="background: linear-gradient(to right, #fff, #fff); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);" >
    @else
        <header class="dash-header" style="background: linear-gradient(to right, #0a3772, #008ecc);">
@endif
{{-- <header class="dash-header  {{ isset($setting['is_sidebar_transperent']) && $setting['is_sidebar_transperent'] == 'on' ? 'transprent-bg' : '' }}"> --}}

<div class="header-wrapper" style="display: flex; justify-content: space-between; align-items: center; width: 100%; ">
    <div class="me-auto dash-mob-drp">
        <ul class="list-unstyled" style="display: flex; align-items: center;">
            <li class="dash-h-item mob-hamburger">
                <a href="#!" class="dash-head-link" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
            </li>
            <li class="dropdown dash-h-item drp-company">
                <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                   role="button" aria-haspopup="false" aria-expanded="false" style="background-color: white;">
                    <span class="theme-avtar" style="background-color: white;">
                        <img alt="#"
                             src="{{ !empty($users->avatar) ? $profile . $users->avatar : $profile . 'avatar.png' }}"
                             class="header-avtar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; background-color: white;">
                    </span>
                    <span class="hide-mob ms-2" style="background-color: white;">{{ 'Hi, ' . Auth::user()->name . '!' }}
                        <i class="ti ti-chevron-down drp-arrow nocolor hide-mob" style="background-color: white;"></i>
                    </span>
                </a>
                <div class="dropdown-menu dash-h-dropdown" style="background-color: white;">
                    <a href="{{ route('profile') }}" class="dropdown-item" style="background-color: white;">
                        <i class="ti ti-user"></i>
                        <span>{{ __('My Profile') }}</span>
                    </a>
                    <a href="{{ route('logout') }}" class="dropdown-item"
                       onclick="event.preventDefault();document.getElementById('logout-form').submit();"
                       style="background-color: white;">
                        <i class="ti ti-power"></i>
                        <span>{{ __('Logout') }}</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </div>
    
    <!-- Marquee Section for Daily Quote -->
    <!-- Marquee Section for Daily Quote -->
    <div class="quote-container dash-h-item" style="display: flex; justify-content: center; align-items: center; flex-grow: 1; min-width: 0;">
        <marquee behavior="scroll" direction="left" scrollamount="6" style="color: #fd7523; font-size: 16px; font-weight: bold; width: 100%; margin: 0 10px;">
            " {{ $quote->quote ?? 'Welcome to the DTG! No quote for today.' }} "
        </marquee>
    </div>



   


    <div class="ms-auto" style="display: flex; justify-content: flex-end; align-items: center;">
        <ul class="list-unstyled" style="display: flex; align-items: center;">
            @if (\Auth::user()->type != 'super admin')
                <li class="dropdown dash-h-item drp-notification">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false" id="msg-btn"
                       style="background-color: white;">
                        <i class="ti ti-message-2"></i>
                        <span class="bg-danger dash-h-badge message-counter custom_messanger_counter"
                              style="background-color: white; {{ ($unseenCounter + $notificationCount) == 0 ? 'display: none;' : '' }}">{{ $unseenCounter + $notificationCount }}</span>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" style="background-color: white; width: 360px;">
                        <div class="noti-header d-flex justify-content-between align-items-center" style="background-color: white; border-bottom: 1px solid #f1f1f1; padding: 10px 15px;">
                            <h5 class="m-0" style="background-color: white;">{{ __('Notifications') }}</h5>
                            @if(!$recentNotifications->isEmpty())
                                <a href="#" class="dash-head-link clear_all_notifications text-xs text-primary"
                                   style="background-color: white; text-decoration: none; font-size: 11px;">{{ __('Clear Notifications') }}</a>
                            @endif
                        </div>
                        
                        <!-- Leave Notifications Section -->
                        <div class="notifications-list-wrapper" style="background-color: white; max-height: 180px; overflow-y: auto;">
                            @if(!$recentNotifications->isEmpty())
                                <ul class="list-group list-group-flush" style="background-color: white; padding: 0; margin: 0;">
                                    @foreach($recentNotifications as $notification)
                                        @php
                                            $notiData = $notification->data;
                                            $action = $notiData['action'] ?? '';
                                            $bgColor = 'bg-primary';
                                            if ($action === 'Approved') {
                                                $bgColor = 'bg-success';
                                            } elseif ($action === 'Rejected') {
                                                $bgColor = 'bg-danger';
                                            } elseif ($action === 'created') {
                                                $bgColor = 'bg-warning';
                                            }
                                        @endphp
                                        <a href="{{ route('leave.index') }}" data-id="{{ $notification->id }}" class="list-group-item list-group-item-action d-flex align-items-start notification-item-click {{ $notification->read_at ? 'opacity-75' : 'fw-bold' }}" style="background-color: white; border-bottom: 1px solid #f8f9fa; padding: 10px 15px; text-decoration: none; border-left: none; border-right: none;">
                                            <span class="theme-avtar me-3 d-flex align-items-center justify-content-center text-white rounded-circle {{ $bgColor }}" style="width: 28px; height: 28px; min-width: 28px;">
                                                @if($action === 'Approved')
                                                    <i class="ti ti-check" style="font-size: 14px;"></i>
                                                @elseif($action === 'Rejected')
                                                    <i class="ti ti-x" style="font-size: 14px;"></i>
                                                @else
                                                    <i class="ti ti-file-text" style="font-size: 14px;"></i>
                                                @endif
                                            </span>
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <p class="m-0 text-sm" style="font-size: 12px; line-height: 1.4; color: #333; word-wrap: break-word; font-weight: inherit;">
                                                    {{ $notiData['message'] ?? '' }}
                                                </p>
                                                <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </a>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                    </div>
                </li>
            @endif
        </ul>
    </div>
</div>


</header>
@push('scripts')
    {{-- @include('Chatify::layouts.modals') --}}
    <script>
        $('#msg-btn').click(function() {
            let contactsPage = 1;
            let contactsLoading = false;
            let noMoreContacts = false;
            $.ajax({
                url: url + "/getContacts",
                method: "GET",
                data: {
                    _token: "{{ csrf_token() }}",
                    page: contactsPage,
                    type: 'custom',
                },
                dataType: "JSON",
                success: (data) => {

                    if (contactsPage < 2) {
                        $(".count-listOfContacts").html(data.contacts);

                    } else {
                        $(".count-listOfContacts").append(data.contacts);
                    }
                    $('.count-listOfContacts').find('.messenger-list-item').each(function(e) {
                        $('.noti-body .activeStatus').remove()
                        $('.noti-body .avatar').remove()
                        $(this).find('span').remove()
                        $(this).find('p').addClass("d-inline")
                        // $(this).find('b').addClass('position-absolute')
                        // $(this).find('b').css({position: "absolute"});
                        $(this).find('b').css({
                            "position": "absolute",
                            "right": "50px"
                        });
                        $(this).find('tr').remove('td')

                    })
                },
                error: (error) => {
                    setContactsLoading(false);
                    console.error(error);
                },
            });
        })
        $(document).on('click', '.clear_all_notifications', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: "{{ route('notifications.clear-all') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                dataType: "JSON",
                success: (data) => {
                    if (data.success) {
                        $(".notifications-list-wrapper").fadeOut(300, function() {
                            $(this).html('').show();
                        });
                        $(".clear_all_notifications").fadeOut();
                        let unseenMessages = parseInt("{{ $unseenCounter }}") || 0;
                        if (unseenMessages > 0) {
                            $(".custom_messanger_counter").text(unseenMessages).show();
                        } else {
                            $(".custom_messanger_counter").text(0).hide();
                        }
                    }
                },
                error: (error) => {
                    console.error(error);
                }
            });
        });
        $(document).on('click', '.notification-item-click', function(e) {
            let notificationId = $(this).data('id');
            let url = "{{ route('notifications.mark-as-read', ['id' => ':id']) }}".replace(':id', notificationId);
            $.ajax({
                url: url,
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                async: false
            });
        });
    </script>
@endpush
