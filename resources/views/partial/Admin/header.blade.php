
@php
    use App\Models\Utility;

    $users = \Auth::user();
    $currantLang = $users->currentLanguage();
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    $unseenCounter = App\Models\ChMessage::where('to_id', Auth::user()->id)
        ->where('seen', 0)
        ->count();
    $unseen_count = DB::select('SELECT from_id, COUNT(*) AS totalmasseges FROM ch_messages WHERE seen = 0 GROUP BY from_id');

    
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
                             src="{{ !empty($users->avatar) ? $profile . $users->avatar : $profile . '/avatar.png' }}"
                             class="header-avtar" style="width: 100%; border-radius: 50%; background-color: white;">
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
    <div class="quote-container" style="display: flex; justify-content: center; align-items: center; flex-grow: 1;">
        <marquee behavior="scroll" direction="left" scrollamount="6" style="color: #fd7523; font-size: 18px; font-weight: bold; width: 100%;margin: left 11px;">
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
                              style="background-color: white;">{{ $unseenCounter }}</span>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" style="background-color: white;">
                        <div class="noti-header" style="background-color: white;">
                            <h5 class="m-0" style="background-color: white;">{{ __('Messages') }}</h5>
                            <a href="#" class="dash-head-link mark_all_as_read_message"
                               style="background-color: white;">{{ __('Clear All') }}</a>
                        </div>
                        <div class="noti-body dropdown-list-message-msg" style="background-color: white;">
                            <div style="display: flex; background-color: white;">
                                <a href="#" class="show-listView" style="background-color: white;"></a>
                                <div class="count-listOfContacts" style="background-color: white;"></div>
                            </div>
                        </div>
                        <div class="noti-footer" style="background-color: white;">
                            <div class="d-grid">
                                {{-- <a href="{{ route('chats') }}"
                                   class="btn dash-head-link justify-content-center text-primary mx-0"
                                   style="background-color: white;">View all</a> --}}
                                <a href="#"
                                   class="btn dash-head-link justify-content-center text-primary mx-0"
                                   style="background-color: white;">View all</a>
                            </div>
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
    </script>
@endpush
