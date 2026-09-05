<?php
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_favicon = \App\Models\Utility::getValByName('company_favicon');
    $company_logo = \App\Models\Utility::GetLogo();
    $SITE_RTL = \App\Models\Utility::getValByName('SITE_RTL');
    $setting = \App\Models\Utility::colorset();
    $color = !empty($setting['theme_color']) ? $setting['theme_color'] : 'theme-3';
    $pusher_setting = \App\Models\Utility::settings();
    $getseo = App\Models\Utility::getSeoSetting();
    $metatitle = isset($getseo['meta_title']) ? $getseo['meta_title'] : '';
    $metadesc = isset($getseo['meta_description']) ? $getseo['meta_description'] : '';
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');
    $meta_logo = isset($getseo['meta_image']) ? $getseo['meta_image'] : '';
    $enable_cookie = \App\Models\Utility::getCookieSetting('enable_cookie');

    if (isset($setting['color_flag']) && $setting['color_flag'] == 'true') {
        $themeColor = 'custom-color';
    } else {
        $themeColor = $color;
    }
?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e($SITE_RTL == 'on' ? 'rtl' : ''); ?>">

<head>

    <title>
        <?php echo e(\App\Models\Utility::getValByName('title_text') ? \App\Models\Utility::getValByName('title_text') : config('app.name', 'HRMGo SaaS')); ?>

        - <?php echo $__env->yieldContent('page-title'); ?></title>

    <!-- SEO META -->
    <meta name="title" content="<?php echo e($metatitle); ?>">
    <meta name="description" content="<?php echo e($metadesc); ?>">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(env('APP_URL')); ?>">
    <meta property="og:title" content="<?php echo e($metatitle); ?>">
    <meta property="og:description" content="<?php echo e($metadesc); ?>">
    <meta property="og:image"
        content="<?php echo e(isset($meta_logo) && !empty(asset('storage/uploads/meta/' . $meta_logo)) ? asset('storage/uploads/meta/' . $meta_logo) : 'hrmgo.png'); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo e(env('APP_URL')); ?>">
    <meta property="twitter:title" content="<?php echo e($metatitle); ?>">
    <meta property="twitter:description" content="<?php echo e($metadesc); ?>">
    <meta property="twitter:image"
        content="<?php echo e(isset($meta_logo) && !empty(asset('storage/uploads/meta/' . $meta_logo)) ? asset('storage/uploads/meta/' . $meta_logo) : 'hrmgo.png'); ?>">


    <meta charset="utf-8" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="csrf-token-url" content="<?php echo e(route('csrf.token')); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Dashboard Template Description" />
    <meta name="keywords" content="Dashboard Template" />
    <meta name="author" content="Rajodiya Infotech" />


    <!-- Favicon icon -->
    <link rel="icon"
        href="<?php echo e($logo . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon . '?' . time() : 'favicon.png' . '?' . time())); ?>"
        type="image/x-icon" />
    <!-- for calender-->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/main.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/datepicker-bs5.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/style.css')); ?>">
    <!-- font css -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/bootstrap-switch-button.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/tabler-icons.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/feather.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/fontawesome.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/material.css')); ?>">

    <!-- vendor css -->

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/customizer.css')); ?>">


    <?php if($SITE_RTL == 'on'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/css/style-rtl.css')); ?>">
    <?php endif; ?>

    <?php if($setting['cust_darklayout'] == 'on'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/css/style-dark.css')); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>" id="main-style-link">
    <?php endif; ?>

    <meta name="url" content="<?php echo e(url('') . '/' . config('chatify.routes.prefix')); ?>"
        data-user="<?php echo e(Auth::user()->id); ?>">

    <link rel='stylesheet' href='https://unpkg.com/nprogress@0.2.0/nprogress.css' />

    <?php if($setting['cust_darklayout'] == 'on'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/css/custom-dark.css')); ?>">
    <?php endif; ?>

    <style>
        :root {
            --color-customColor: <?=$color ?>;
        }
        .loader-bg {
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
    </style>
    <link rel="stylesheet" href="<?php echo e(asset('css/custom-color.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/custom.css')); ?>">

    <style>
        /* Immediate fix for mobile menu flicker and header responsiveness */
        @media screen and (max-width: 991px) {
            .dash-sidebar {
                left: -280px !important;
                width: 280px !important;
                height: 100vh !important;
                transition: none !important;
            }
            .dash-sidebar.active, .dash-sidebar.mob-sidebar-active {
                left: 0 !important;
                width: 280px !important;
                transition: left 0.3s ease !important;
            }
        }
        
        /* Hide quote on small mobiles to avoid crowding */
        @media screen and (max-width: 575px) {
            .quote-container {
                display: none !important;
            }
        }

        /* Fix for Datatable controls to prevent scrolling horizontally */
        .dataTable-top {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 15px 10px !important;
            position: sticky !important;
            left: 0 !important;
            width: 100% !important;
            background: #fff !important;
            z-index: 5 !important;
        }

        .dataTable-search {
            margin-left: auto !important;
        }

        .dataTable-dropdown label {
            font-size: 0 !important;
        }
        .dataTable-dropdown label select {
            font-size: 14px !important;
            margin-right: 5px !important;
        }

        .dataTable-info {
            display: none !important;
        }

        .dataTable-bottom {
            display: flex !important;
            justify-content: flex-end !important;
            padding: 15px 10px !important;
        }

        .dataTable-container {
            overflow-x: auto !important;
            width: 100% !important;
        }

        .table-responsive {
            overflow-x: visible !important;
        }

        /* --- GLOBAL TABLE REDESIGN (Matches Dashboard) --- */
        :root {
            --app-primary: #e8590c;
            --app-primary-hover-bg: rgba(232, 89, 12, 0.05);
            --app-table-header-bg: #f8f9fa;
            --app-table-text-muted: #8492a6;
            --app-table-border: #f1f3f5;
            --app-table-text-main: #3c4b64;
        }

        .table, .dataTable-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: none !important;
            margin-bottom: 0 !important;
        }
        
        .table thead th, .dataTable-table thead th {
            background-color: var(--app-table-header-bg) !important;
            color: var(--app-table-text-muted) !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.6px !important;
            padding: 12px 16px !important;
            border-bottom: 1px solid var(--app-table-border) !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            white-space: nowrap !important;
            vertical-align: middle !important;
        }
        
        .table tbody td, .dataTable-table tbody td {
            padding: 14px 16px !important;
            font-size: 13.5px !important;
            color: var(--app-table-text-main) !important;
            border-bottom: 1px solid var(--app-table-border) !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            vertical-align: middle !important;
        }

        /* Hide bottom border for the last row to keep it clean */
        .table tbody tr:last-child td, .dataTable-table tbody tr:last-child td {
            border-bottom: none !important;
        }
        
        /* Primary color hover effect on rows */
        .table tbody tr:hover td, .dataTable-table tbody tr:hover td {
            background-color: var(--app-primary-hover-bg) !important;
            color: var(--app-primary) !important;
        }

        /* Ensure links/actions inside hovered row stay clean */
        .table tbody tr:hover td a:not(.btn), .dataTable-table tbody tr:hover td a:not(.btn) {
            color: var(--app-primary) !important;
        }

        /* Clean up datatable controls */
        .dataTable-top {
            padding: 15px 20px !important;
            border-bottom: 1px solid var(--app-table-border) !important;
        }
        .dataTable-bottom {
            padding: 15px 20px !important;
            border-top: 1px solid var(--app-table-border) !important;
        }

        /* Hide all sorting icons from tables globally */
        .dataTable-sorter::before,
        .dataTable-sorter::after,
        th.sorting::before,
        th.sorting::after,
        th.sorting_asc::before,
        th.sorting_asc::after,
        th.sorting_desc::before,
        th.sorting_desc::after {
            display: none !important;
            content: none !important;
            background-image: none !important;
        }
    </style>

    <?php echo $__env->yieldPushContent('css-page'); ?>
</head>



<body class="<?php echo e($themeColor); ?>">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <script>
        (function () {
            function hideLoader() {
                var loader = document.querySelector('.loader-bg');
                if (loader) {
                    loader.remove();
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', hideLoader);
            } else {
                hideLoader();
            }
            window.addEventListener('load', hideLoader);
            setTimeout(hideLoader, 800);
        })();
    </script>
    <!-- [ Pre-loader ] End -->
    <!-- [ navigation menu ] start -->
    <?php echo $__env->make('partial.Admin.menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->

    <?php echo $__env->make('partial.Admin.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Modal -->
    <div class="modal notification-modal fade" id="notification-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <h6 class="mt-2">
                        <i data-feather="monitor" class="me-2"></i>Desktop settings
                    </h6>
                    <hr />
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting1" checked />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting1">Allow desktop
                            notification</label>
                    </div>
                    <p class="text-muted ms-5">
                        you get lettest content at a time when data will updated
                    </p>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting2" />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting2">Store Cookie</label>
                    </div>
                    <h6 class="mb-0 mt-5">
                        <i data-feather="save" class="me-2"></i>Application settings
                    </h6>
                    <hr />
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting3" />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting3">Backup Storage</label>
                    </div>
                    <p class="text-muted mb-4 ms-5">
                        Automaticaly take backup as par schedule
                    </p>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting4" />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting4">Allow guest to print
                            file</label>
                    </div>
                    <h6 class="mb-0 mt-5">
                        <i data-feather="cpu" class="me-2"></i>System settings
                    </h6>
                    <hr />
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting5" checked />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting5">View other user chat</label>
                    </div>
                    <p class="text-muted ms-5">Allow to show public user message</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-danger btn-sm" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-light-primary btn-sm">
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Header ] end -->


    <!-- [ Main Content ] start -->
    <section class="dash-container">
        <div class="dash-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="page-header-title">
                                <h4 class="m-b-10">
                                    <?php echo $__env->yieldContent('page-title'); ?>
                                </h4>
                            </div>
                            <ul class="breadcrumb">
                                <?php echo $__env->yieldContent('breadcrumb'); ?>
                            </ul>
                        </div>
                        <div class="col-sm-auto col-md">
                            <div class="float-end "
                                <?php if($SITE_RTL == 'on'): ?> style=" float: left !important;" <?php endif; ?>>
                                <?php echo $__env->yieldContent('action-button'); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <!-- [ basic-table ] start -->
            <?php echo $__env->yieldContent('content'); ?>
            <!-- [ basic-table ] end -->
            <!-- [ Main Content ] end -->
        </div>
    </section>
    <div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="commonModalOver" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="successModalBody">
                    <!-- Success message will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
        <div id="liveToast" class="toast text-white  fade" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
    <footer class="dash-footer">
        <div class="footer-wrapper">
            <div class="py-1">
                <span class="text-muted">
                    <?php if(empty(App\Models\Utility::getValByName('footer_text'))): ?>
                        &copy;<?php echo e(date(' Y')); ?>

                    <?php endif; ?>
                    <?php echo e(App\Models\Utility::getValByName('footer_text') ? App\Models\Utility::getValByName('footer_text') : config('app.name', 'HRMGo SaaS')); ?>


                    

                </span>
            </div>

        </div>
    </footer>
    <!-- Warning Section start -->
    <!-- Older IE warning message -->
    <!--[if lt IE 11]>
 
<![endif]-->
    <!-- Warning Section Ends -->
    <!-- Required Js -->
    <script src="<?php echo e(asset('assets/js/plugins/choices.min.js')); ?>"></script>
    <script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script>
    <script src="<?php echo e(asset('js/jquery.form.js')); ?>"></script>
    <script src="<?php echo e(asset('js/csrf-guard.js')); ?>"></script>

    
    <script src="<?php echo e(asset('assets/js/plugins/datepicker-full.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/popper.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/simplebar.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/perfect-scrollbar.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/bootstrap.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/feather.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/bootstrap-switch-button.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/dash.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/sweetalert2.all.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/simple-datatables.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/flatpickr.min.js')); ?>"></script>

    <script src="<?php echo e(asset('js/custom.js')); ?>"></script>

    <script src="<?php echo e(asset('js/chatify/autosize.js')); ?>"></script>
    <script src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>


    

    <script>
        document.querySelectorAll("#pc-dt-simple, .pc-dt-simple, .datatable, #tds-table, [id^='dataTable']").forEach(el => {
            if (el.dataset.dtInit === '1' || typeof simpleDatatables === 'undefined') {
                return;
            }
            el.dataset.dtInit = '1';
            new simpleDatatables.DataTable(el, {
                pagerDelta: 1
            });
        });
    </script>

    <script>
        feather.replace();
        var pctoggle = document.querySelector("#pct-toggler");
        if (pctoggle) {
            pctoggle.addEventListener("click", function() {
                if (
                    !document.querySelector(".pct-customizer").classList.contains("active")
                ) {
                    document.querySelector(".pct-customizer").classList.add("active");
                } else {
                    document.querySelector(".pct-customizer").classList.remove("active");
                }
            });
        }
        var themescolors = document.querySelectorAll(".themes-color > a");
        if (themescolors.length > 0) {
            for (var h = 0; h < themescolors.length; h++) {
                var c = themescolors[h];
                c.addEventListener("click", function(event) {
                    var targetElement = event.target;
                    if (targetElement.tagName == "SPAN") {
                        targetElement = targetElement.parentNode;
                    }
                    var temp = targetElement.getAttribute("data-value");
                    removeClassByPrefix(document.querySelector("body"), "theme-");
                    document.querySelector("body").classList.add(temp);
                });
            }
        }
        var custthemebg = document.querySelector("#cust_theme_bg");
        if (custthemebg) {
            custthemebg.addEventListener("click", function() {
                if (custthemebg.checked) {
                    document.querySelector(".dash-sidebar").classList.add("transprent-bg");
                    document
                        .querySelector(".dash-header:not(.dash-mob-header)")
                        .classList.add("transprent-bg");
                } else {
                    document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
                    document
                        .querySelector(".dash-header:not(.dash-mob-header)")
                        .classList.remove("transprent-bg");
                }
            });
        }
        var custdarklayout = document.querySelector("#cust_darklayout");
        if (custdarklayout) {
            custdarklayout.addEventListener("click", function() {
                if (custdarklayout.checked) {
                    document
                        .querySelector("#main-style-link")
                        .setAttribute("href", "<?php echo e(asset('assets/css/style-dark.css')); ?>");
                    document
                        .querySelector(".m-header > .b-brand > .logo-lg")
                        .setAttribute("src", "<?php echo e(asset('/storage/uploads/logo/logo-light.png')); ?>");
                } else {
                    document
                        .querySelector("#main-style-link")
                        .setAttribute("href", "<?php echo e(asset('assets/css/style.css')); ?>");
                    document
                        .querySelector(".m-header > .b-brand > .logo-lg")
                        .setAttribute("src", "<?php echo e(asset('/storage/uploads/logo/logo-dark.png')); ?>");
                }
            });
        }

        function removeClassByPrefix(node, prefix) {
            for (let i = 0; i < node.classList.length; i++) {
                let value = node.classList[i];
                if (value.startsWith(prefix)) {
                    node.classList.remove(value);
                }
            }
        }
    </script>

    <script>
        $(document).on('click', '.local_calender .fc-daygrid-event', function(e) {
            // if (!$(this).hasClass('project')) {
            e.preventDefault();
            var event = $(this);
            var title = $(this).find('.fc-event-title').html();

            var size = 'md';
            var url = $(this).attr('href');
            $("#commonModal .modal-title ").html(title);
            $("#commonModal .modal-dialog").addClass('modal-' + size);
            $.ajax({
                url: url,
                success: function(data) {
                    $('#commonModal .body').html(data);
                    $("#commonModal").modal('show');
                    if ($(".d_week").length > 0) {
                        $($(".d_week")).each(function(index, element) {
                            var id = $(element).attr('id');

                            // (function() {
                            //     const d_week = new Datepicker(document.querySelector('#' +
                            //         id), {
                            //         buttonClass: 'btn',
                            //         format: 'yyyy-mm-dd',
                            //     });
                            // })();

                        });
                    }

                },
                error: function(data) {
                    data = data.responseJSON;
                    toastrs('Error', data.error, 'error')
                }
            });
            // }
        });
    </script>

    <script src="https://js.pusher.com/5.0/pusher.min.js"></script>

    <?php if(\App\Models\Utility::getValByName('gdpr_cookie') == 'on'): ?>
        <script type="text/javascript">
            var defaults = {
                'messageLocales': {
                    /*'en': 'We use cookies to make sure you can have the best experience on our website. If you continue to use this site we assume that you will be happy with it.'*/
                    'en': "<?php echo e(\App\Models\Utility::getValByName('cookie_text')); ?>"
                },
                'buttonLocales': {
                    'en': 'Ok'
                },
                'cookieNoticePosition': 'bottom',
                'learnMoreLinkEnabled': false,
                'learnMoreLinkHref': '/cookie-banner-information.html',
                'learnMoreLinkText': {
                    'it': 'Saperne di più',
                    'en': 'Learn more',
                    'de': 'Mehr erfahren',
                    'fr': 'En savoir plus'
                },
                'buttonLocales': {
                    'en': 'Ok'
                },
                'expiresIn': 30,
                'buttonBgColor': '#d35400',
                'buttonTextColor': '#fff',
                'noticeBgColor': '#000',
                'noticeTextColor': '#fff',
                'linkColor': '#009fdd'
            };
        </script>
        <script src="<?php echo e(asset('js/cookie.notice.js')); ?>"></script>
    <?php endif; ?>

    <?php if(\Auth::user()->type != 'super admin'): ?>
        <script>
            $(document).ready(function() {
                pushNotification('<?php echo e(Auth::id()); ?>');
            });

            function pushNotification(id) {
                if (typeof Pusher === 'undefined') {
                    return;
                }

                Pusher.logToConsole = false;

                var pusher = new Pusher('<?php echo e($pusher_setting['pusher_app_key']); ?>', {
                    cluster: '<?php echo e($pusher_setting['pusher_app_cluster']); ?>',
                    forceTLS: true
                });

                // Pusher Notification
                var channel = pusher.subscribe('send_notification');
                channel.bind('notification', function(data) {
                    if (id == data.user_id) {
                        $(".notification-toggle").addClass('beep');
                        $(".notification-dropdown #notification-list").prepend(data.html);
                    }
                });

                // Pusher Message
                var msgChannel = pusher.subscribe('my-channel');
                msgChannel.bind('my-chat', function(data) {

                    if (id == data.to) {
                        getChat();
                    }
                });
            }

            // Get chat for top ox
        </script>
    <?php endif; ?>


    <?php if($message = Session::get('success')): ?>
        <script>
            show_toastr('Success', '<?php echo $message; ?>', 'success');
        </script>
    <?php endif; ?>
    <?php if($message = Session::get('error')): ?>
        <script>
            show_toastr('Error', '<?php echo $message; ?>', 'error');
        </script>
    <?php endif; ?>


    <?php echo $__env->yieldPushContent('script-page'); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    

    <?php echo $__env->yieldPushContent('custom-scripts'); ?>
    <?php if($enable_cookie['enable_cookie'] == 'on'): ?>
        <?php echo $__env->make('layouts.cookie_consent', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/layouts/admin.blade.php ENDPATH**/ ?>