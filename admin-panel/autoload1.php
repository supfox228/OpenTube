<?php

$page = 'dashboard';
if (!empty($_GET['page'])) {
    $page = PT_Secure($_GET['page']);
}
if (!in_array($page, ['import-from-youtube','import-from-dailymotion','import-from-twitch'])) {
    cleanConfigData();
}
else{
    decryptConfigData();
}

$page_loaded = '';
$pages = array(
    'dashboard',
    'general-settings',
    'site-settings',
    'email-settings',
    'social-login',
    's3',
    'prosys-settings',
    'manage-payments',
    'payment-requests',
    'manage-users',
    'manage-videos',
    'import-from-youtube',
    'import-from-dailymotion',
    'import-from-twitch',
    'manage-video-ads',
    'create-video-ad',
    'edit-video-ad',
    'manage-website-ads',
    'manage-user-ads',
    'manage-themes',
    'change-site-desgin',
    'create-new-sitemap',
    'manage-pages',
    'manage-faqs',
    'changelog',
    'backup',
    'create-article',
    'edit-article',
    'manage-articles',
    'manage-profile-fields',
    'add-new-profile-field',
    'edit-profile-field',
    'payment-settings',
    'verification-requests',
    'manage-announcements',
    'ban-users',
    'custom-design',
    'api-settings',
    'manage-video-reports',
    'manage-languages',
    'add-language',
    'edit-lang',
    'manage_categories',
    'manage_sub_categories',
    'push-notifications-system',
    'sold_videos_analytics',
    'manage-movies',
    'manage-movies-category',
    'manage-comments',
    'manage-custom-pages',
    'add-new-custom-page',
    'edit-custom-page',
    'manage-currencies',
    'bank-receipts',
    'earnings',
    'copy_report',
    'monetization-requests',
    'mass-notifications',
    'manage-invitation-keys',
    'auto_subscribe',
    'auto-delete',
    'manage-activities',
    'live',
    'ffmpeg',
    'video_settings',
    'newsletters',
    'ads-settings',
    'clean-videos',
    'edit-terms-pages',
    'seo',
    'manage-invitation',
    'manage-permission',
    'upload-to-storage',
    'system_status',
    'cronjob_settings',
    'affiliates-settings',
);
if ($pt->user->admin != 1 && !CheckHavePermission($page) && $page != 'changelog') {
    $permission = json_decode($pt->user->permission,true);
    if (!empty($permission) && is_array($permission)) {
        foreach ($permission as $key => $value) {
            if(isset($permission[$key]) && $permission[$key] == "1") {
                header("Location: " . PT_LoadAdminLinkSettings($key));
                exit();
            }
        }
    }
    header("Location: " . PT_Link(''));
    exit();
}
if (in_array($page, $pages)) {
    $page_loaded = PT_LoadAdminPage("$page/content");
}

if (empty($page_loaded)) {
    header("Location: " . PT_Link('admincp'));
    exit();
}

if ($page == 'dashboard') {
    if ($pt->config->last_admin_collection < (time() - 18000)) {
        $update_information = PT_UpdateAdminDetails();
    }
}

if ($pt->config->live_video == 1) {

    if ($pt->config->live_video_save == 0) {
        try {
            $posts = $db->where('live_time','0','!=')->where('live_time',time() - 11,'<=')->get(T_VIDEOS);
            foreach ($posts as $key => $post) {
                if ($pt->config->live_video == 1 && !empty($pt->config->agora_app_id) && !empty($pt->config->agora_customer_id) && !empty($pt->config->agora_customer_certificate) && $pt->config->live_video_save == 1) {
                    StopCloudRecording(array('resourceId' => $post->agora_resource_id,
                                             'sid' => $post->agora_sid,
                                             'cname' => $post->stream_name,
                                             'post_id' => $post->id,
                                             'uid' => explode('_', $post->stream_name)[2]));
                }
                PT_DeleteVideo(PT_Secure($post->id));
            }
        } catch (Exception $e) {

        }

    }
    else{
        if ($pt->config->live_video == 1 && $pt->config->amazone_s3_2 != 1) {
            try {
            $posts = $db->where('live_time','0','!=')->where('live_time',time() - 11,'<=')->get(T_VIDEOS);
            foreach ($posts as $key => $post) {
                PT_DeleteVideo(PT_Secure($post->id));
            }
        } catch (Exception $e) {

        }
        }
    }
}
$notify_count = $db->where('recipient_id',0)->where('admin',1)->where('seen',0)->getValue(T_NOTIFICATIONS,'COUNT(*)');
$notifications = $db->where('recipient_id',0)->where('admin',1)->where('seen',0)->orderBy('id','DESC')->get(T_NOTIFICATIONS);
$old_notifications = $db->where('recipient_id',0)->where('admin',1)->where('seen',0,'!=')->orderBy('id','DESC')->get(T_NOTIFICATIONS,5);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Админ панель | <?php echo $pt->config->title; ?></title>
    <link rel="icon" href="<?php echo $pt->config->theme_url ?>/img/icon.png" type="image/png">

    <!-- Main css -->
    <link rel="stylesheet" href="<?php echo(PT_LoadAdminLink('vendors/bundle.css')) ?>" type="text/css">

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Daterangepicker -->
    <link rel="stylesheet" href="<?php echo(PT_LoadAdminLink('vendors/datepicker/daterangepicker.css')) ?>" type="text/css">

    <!-- DataTable -->
    <link rel="stylesheet" href="<?php echo(PT_LoadAdminLink('vendors/dataTable/datatables.min.css')) ?>" type="text/css">

    <!-- App css -->
    <link rel="stylesheet" href="<?php echo(PT_LoadAdminLink('assets/css/app.css')) ?>" type="text/css">
    <!-- Main scripts -->
<script src="<?php echo(PT_LoadAdminLink('vendors/bundle.js')) ?>"></script>

    <!-- Apex chart -->
    <script src="<?php echo(PT_LoadAdminLink('vendors/charts/apex/apexcharts.min.js')) ?>"></script>

    <!-- Daterangepicker -->
    <script src="<?php echo(PT_LoadAdminLink('vendors/datepicker/daterangepicker.js')) ?>"></script>

    <!-- DataTable -->
    <script src="<?php echo(PT_LoadAdminLink('vendors/dataTable/datatables.min.js')) ?>"></script>

    <!-- Dashboard scripts -->
    <script src="<?php echo(PT_LoadAdminLink('assets/js/examples/pages/dashboard.js')) ?>"></script>
    <script src="<?php echo PT_LoadAdminLink('vendors/charts/chartjs/chart.min.js'); ?>"></script>

    <!-- App scripts -->

<script type="text/javascript" src="<?php echo $pt->config->theme_url; ?>/js/jquery.form.min.js"></script>
<link href="<?php echo PT_LoadAdminLink('vendors/sweetalert/sweetalert.css'); ?>" rel="stylesheet" />
<script src="<?php echo PT_LoadAdminLink('assets/js/admin.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo(PT_LoadAdminLink('vendors/select2/css/select2.min.css')) ?>" type="text/css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
<?php if ($page == 'create-article' || $page == 'edit-article' || $page == 'manage-announcements' || $page == 'newsletters') { ?>
<script src="<?php echo PT_LoadAdminLink('vendors/tinymce/js/tinymce/tinymce.min.js'); ?>"></script>
<script src="<?php echo PT_LoadAdminLink('vendors/bootstrap-tagsinput/src/bootstrap-tagsinput.js'); ?>"></script>
<link href="<?php echo PT_LoadAdminLink('vendors/bootstrap-tagsinput/src/bootstrap-tagsinput.css'); ?>" rel="stylesheet" />
<?php } ?>
<?php if ($page == 'custom-design') { ?>
<script src="<?php echo PT_LoadAdminLink('vendors/codemirror-5.30.0/lib/codemirror.js'); ?>"></script>
<script src="<?php echo PT_LoadAdminLink('vendors/codemirror-5.30.0/mode/css/css.js'); ?>"></script>
<script src="<?php echo PT_LoadAdminLink('vendors/codemirror-5.30.0/mode/javascript/javascript.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo PT_LoadAdminLink('vendors/codemirror-5.30.0/lib/codemirror.css'); ?>">
<?php } ?>


    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php if ($page == 'bank-receipts' || $page == 'verification-requests' || $page == 'monetization-requests' || $page == 'manage-user-ads') { ?>
        <!-- Css -->
        <link rel="stylesheet" href="<?php echo(PT_LoadAdminLink('vendors/lightbox/magnific-popup.css')) ?>" type="text/css">

        <!-- Javascript -->
        <script src="<?php echo(PT_LoadAdminLink('vendors/lightbox/jquery.magnific-popup.min.js')) ?>"></script>
        <script src="<?php echo(PT_LoadAdminLink('vendors/charts/justgage/raphael-2.1.4.min.js')) ?>"></script>
        <script src="<?php echo(PT_LoadAdminLink('vendors/charts/justgage/justgage.js')) ?>"></script>
    <?php } ?>
</head>
<script type="text/javascript">

    $(function() {

        $(document).on('click', 'a[data-ajax]', function(e) {
            $(document).off('click', '.ranges ul li');
            $(document).off('click', '.applyBtn');
            e.preventDefault();
            if (($(this)[0].hasAttribute("data-sent") && $(this).attr('data-sent') == '0') || !$(this)[0].hasAttribute("data-sent")) {
                if (!$(this)[0].hasAttribute("data-sent") && !$(this).hasClass('waves-effect')) {
                    $('.navigation-menu-body').find('a').removeClass('active');
                    $(this).addClass('active');
                }
                window.history.pushState({state:'new'},'', $(this).attr('href'));
                $(".barloading").css("display","block");
                if ($(this)[0].hasAttribute("data-sent")) {
                    $(this).attr('data-sent', "1");
                }
                var url = $(this).attr('data-ajax');
                $.post("<?php echo $pt->config->site_url.'/admin_load.php';?>" + url, {url:url}, function (data) {
                    $(".barloading").css("display","none");
                    if ($('#redirect_link')[0].hasAttribute("data-sent")) {
                        $('#redirect_link').attr('data-sent', "0");
                    }
                    json_data = JSON.parse($(data).filter('#json-data').val());
                    $('.content').html(data);
                    setTimeout(function () {
                      $(".content").getNiceScroll().resize()
                    }, 500);
                    $(".content").animate({ scrollTop: 0 }, "slow");
                    showEncryptedAlert();
                });
            }
        });
        $(window).on("popstate", function (e) {
            location.reload();
        });
    });
</script>
<body <?php echo ($pt->mode == 'night' || $pt->config->night_mode == 'night' ? 'class="dark"' : ''); ?>>
    <div class="barloading" style="display: none;"></div>
    <a id="redirect_link" href="" data-ajax="" data-sent="0"></a>
    <div class="colors"> <!-- To use theme colors with Javascript -->
        <div class="bg-primary"></div>
        <div class="bg-primary-bright"></div>
        <div class="bg-secondary"></div>
        <div class="bg-secondary-bright"></div>
        <div class="bg-info"></div>
        <div class="bg-info-bright"></div>
        <div class="bg-success"></div>
        <div class="bg-success-bright"></div>
        <div class="bg-danger"></div>
        <div class="bg-danger-bright"></div>
        <div class="bg-warning"></div>
        <div class="bg-warning-bright"></div>
    </div>
<!-- Preloader -->
<div class="preloader">
    <div class="preloader-icon"></div>
    <span>Загрузка...</span>
</div>
<!-- ./ Preloader -->

<!-- Sidebar group -->
<div class="sidebar-group">

</div>
<!-- ./ Sidebar group -->

<!-- Layout wrapper -->
<div class="layout-wrapper">

    <!-- Header -->
    <div class="header d-print-none">
        <div class="header-container">
            <div class="header-left">
                <div class="navigation-toggler">
                    <a href="#" data-action="navigation-toggler">
                        <i data-feather="menu"></i>
                    </a>
                </div>

                <div class="header-logo">
                    <a href="<?php echo $pt->config->site_url ?>">
                        <img class="logo" src="<?php echo $pt->config->theme_url ?>/img/logo-light.png?cache=<?php echo($pt->config->logo_cache) ?>" alt="логотип">
                    </a>
                </div>
            </div>

            <div class="header-body">
                <div class="header-body-left">
                    <ul class="navbar-nav">
                        <li class="nav-item mr-3">
                            <div class="header-search-form">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <button class="btn">
                                            <i data-feather="search"></i>
                                        </button>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Поиск"  onkeyup="searchInFiles($(this).val())">
                                    <div class="pt_admin_hdr_srch_reslts" id="search_for_bar"></div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="header-body-right">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link <?php if ($notify_count > 0) { ?> nav-link-notify<?php } ?>" title="Уведомления" data-toggle="dropdown">
                                <i data-feather="bell"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-big">
                                <div
                                    class="border-bottom px-3 py-3 text-center d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Уведомления</h5>
                                    <?php if ($notify_count > 0) { ?>
                                    <a class="btn btn-sm btn-success" href="javascript:void(0)" onclick="ReadNotify()"><svg xmlns="http://www.w3.org/2000/svg" class="mr-2" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M0.41,13.41L6,19L7.41,17.58L1.83,12M22.24,5.58L11.66,16.17L7.5,12L6.07,13.41L11.66,19L23.66,7M18,7L16.59,5.58L10.24,11.93L11.66,13.34L18,7Z" /></svg> Пометить все как прочитанное</a>
                                    <?php } ?>
                                </div>
                                <div class="dropdown-scroll">
                                    <ul class="list-group list-group-flush">
                                        <?php if ($notify_count > 0) { ?>
                                            <li class="px-4 py-2 text-center small text-muted bg-light"><?php echo $notify_count; ?> Непрочитанные уведомления</li>
                                            <?php if (!empty($notifications)) {
                                                    foreach ($notifications as $key => $notify) {
                                                        $page_ = '';
                                                        $text = '';
                                                        if ($notify->type == 'bank') {
                                                            $page_ = 'bank-receipts';
                                                            $text = 'У вас новый банковский платеж, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'verify') {
                                                            $page_ = 'verification-requests';
                                                            $text = 'У вас новый запрос на верификацию, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'mon') {
                                                            $page_ = 'monetization-requests';
                                                            $text = 'У вас новый запрос на монетизацию, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'with') {
                                                            $page_ = 'payment-requests';
                                                            $text = 'У вас новый запрос на вывод средств, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'report') {
                                                            $page_ = 'manage-video-reports';
                                                            $text = 'У вас новый отчет о видео, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'copy') {
                                                            $page_ = 'copy_report';
                                                            $text = 'У вас новый отчет о нарушении авторских прав, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'approve') {
                                                            $page_ = 'manage-videos?type=review';
                                                            $text = 'У вас новое видео, ожидающее вашего одобрения';
                                                        }
                                                ?>
                                            <li class="px-3 py-3 list-group-item">
                                                <a href="<?php echo PT_LoadAdminLinkSettings($page_); ?>" class="d-flex align-items-center hide-show-toggler">
                                                    <div class="flex-shrink-0">
                                                        <figure class="avatar mr-3">
                                                            <span
                                                                class="avatar-title bg-info-bright text-info rounded-circle">
                                                                <?php if ($notify->type == 'bank') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                                                <?php }elseif ($notify->type == 'verify') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#2196f3" d="M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2M10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z"></path></svg>
                                                                <?php }elseif ($notify->type == 'mon') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                                                <?php }elseif ($notify->type == 'with') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                                                <?php }elseif ($notify->type == 'report') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-flag"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                                                <?php }elseif ($notify->type == 'copy') { ?>
																	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-flag"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                                                <?php }elseif ($notify->type == 'approve') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 426.667 426.667" height="20" width="20" fill="#04abf2"> <g> <g> <g> <path d="M42.667,85.333H0V384c0,23.573,19.093,42.667,42.667,42.667h298.667V384H42.667V85.333z"></path> <path d="M384,0H128c-23.573,0-42.667,19.093-42.667,42.667v256c0,23.573,19.093,42.667,42.667,42.667h256 c23.573,0,42.667-19.093,42.667-42.667v-256C426.667,19.093,407.573,0,384,0z M213.333,266.667v-192l128,96L213.333,266.667z"></path> </g> </g> </g></svg>
                                                                <?php } ?>

                                                            </span>
                                                        </figure>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 line-height-20 d-flex justify-content-between">
                                                            <?php echo $text; ?>
                                                        </p>
                                                        <span class="text-muted small"><?php echo PT_Time_Elapsed_String($notify->time); ?></span>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php } } ?>
                                        <?php } ?>
                                        <?php if ($notify_count == 0 && !empty($old_notifications)) { ?>
                                            <li class="px-4 py-2 text-center small text-muted bg-light">Старые уведомления</li>
                                            <?php
                                                    foreach ($old_notifications as $key => $notify) {
                                                        $page_ = '';
                                                        $text = '';
                                                        if ($notify->type == 'bank') {
                                                            $page_ = 'bank-receipts';
                                                            $text = 'У вас новый банковский платеж, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'verify') {
                                                            $page_ = 'verification-requests';
                                                            $text = 'У вас новый запрос на верификацию, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'mon') {
                                                            $page_ = 'monetization-requests';
                                                            $text = 'У вас новый запрос на монетизацию, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'with') {
                                                            $page_ = 'payment-requests';
                                                            $text = 'У вас новый запрос на вывод средств, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'report') {
                                                            $page_ = 'manage-video-reports';
                                                            $text = 'У вас новый отчет о видео, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'copy') {
                                                            $page_ = 'copy_report';
                                                            $text = 'У вас новый отчет о нарушении авторских прав, ожидающий вашего одобрения';
                                                        }
                                                        elseif ($notify->type == 'approve') {
                                                            $page_ = 'manage-videos?type=review';
                                                            $text = 'У вас новое видео, ожидающее вашего одобрения';
                                                        }
                                                ?>
                                            <li class="px-4 py-3 list-group-item">
                                                <a href="<?php echo PT_LoadAdminLinkSettings($page_); ?>" class="d-flex align-items-center hide-show-toggler">
                                                    <div class="flex-shrink-0">
                                                        <figure class="avatar mr-3">
                                                            <span class="avatar-title bg-secondary-bright text-secondary rounded-circle">
                                                                <?php if ($notify->type == 'bank') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                                                <?php }elseif ($notify->type == 'verify') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#2196f3" d="M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2M10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z"></path></svg>
                                                                <?php }elseif ($notify->type == 'mon') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                                                <?php }elseif ($notify->type == 'with') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                                                <?php }elseif ($notify->type == 'report') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-flag"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                                                <?php }elseif ($notify->type == 'copy') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-flag"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                                                <?php }elseif ($notify->type == 'approve') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 426.667 426.667" height="20" width="20" fill="#04abf2"> <g> <g> <g> <path d="M42.667,85.333H0V384c0,23.573,19.093,42.667,42.667,42.667h298.667V384H42.667V85.333z"></path> <path d="M384,0H128c-23.573,0-42.667,19.093-42.667,42.667v256c0,23.573,19.093,42.667,42.667,42.667h256 c23.573,0,42.667-19.093,42.667-42.667v-256C426.667,19.093,407.573,0,384,0z M213.333,266.667v-192l128,96L213.333,266.667z"></path> </g> </g> </g></svg>
                                                                <?php } ?>

                                                            </span>
                                                        </figure>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 line-height-20 d-flex justify-content-between">
                                                            <?php echo $text; ?>
                                                        </p>
                                                        <span class="text-muted small"><?php echo PT_Time_Elapsed_String($notify->time); ?></span>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php } }else{ ?>
                                            <li class="px-4 py-3 list-group-item">
                                                <a href="javascript:void(0)" class="d-flex align-items-center hide-show-toggler">
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 line-height-20 d-flex justify-content-between">
                                                            Уведомления не найдены
                                                        </p>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php }  ?>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" title="Меню пользователя" data-toggle="dropdown">
                                <figure class="avatar avatar-sm">
                                    <img src="<?php echo $pt->user->avatar; ?>"
                                         class="rounded-circle"
                                         alt="аватар">
                                </figure>
                                <span class="ml-2 d-sm-inline d-none"><?php echo $pt->user->name; ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-big">
                                <div class="text-center py-4">
                                    <figure class="avatar avatar-lg mb-3 border-0">
                                        <img src="<?php echo $pt->user->avatar; ?>"
                                             class="rounded-circle" alt="изображение">
                                    </figure>
                                    <h5 class="text-center"><?php echo $pt->user->name; ?></h5>
                                    <div class="mb-3 small text-center text-muted"><?php echo $pt->user->email; ?></div>
                                    <a href="<?php echo $pt->user->url; ?>" class="btn btn-outline-light btn-rounded">Просмотреть профиль</a>
                                </div>
                                <div class="list-group">
                                    <a href="<?php echo(PT_Link('logout')) ?>" class="list-group-item text-danger">Выйти!</a>
                                    <?php if ($pt->config->night_mode == 'both' || $pt->config->night_mode == 'night_default'){ ?>
                                    <?php if ($pt->mode == 'night') { ?>
                                        <a href="javascript:void(0)" class="list-group-item admin_mode" onclick="ChangeMode('day')">
                                            <span id="night-mode-text">Дневной режим </span>
                                            <svg class="feather feather-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                                        </a>
                                    <?php }else{ ?>
                                        <a href="javascript:void(0)" class="list-group-item admin_mode" onclick="ChangeMode('night')">
                                            <span id="night-mode-text">Ночной режим </span>
                                            <svg class="feather feather-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                                        </a>
                                    <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item header-toggler">
                    <a href="#" class="nav-link">
                        <i data-feather="arrow-down"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- ./ Header -->

    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- begin::navigation -->
        <div class="navigation">
            <div class="navigation-header">
                <span>Навигация</span>
                <a href="#">
                    <i class="ti-close"></i>
                </a>
            </div>
            <div class="navigation-menu-body">
                <ul>
                    <?php if ($pt->user->admin == 1 || CheckHavePermission('dashboard')) { ?>
                    <li>
                        <a <?php echo ($page == 'dashboard') ? 'class="active"' : ''; ?>  href="<?php echo PT_LoadAdminLinkSettings(''); ?>" data-ajax="?path=dashboard">
                            <span class="nav-link-icon">
                                <i class="material-icons">dashboard</i>
                            </span>
                            <span>Панель управления</span>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if ($pt->user->admin == 1 || CheckHaveMultiPermission(['general-settings','site-settings','ffmpeg','video_settings','email-settings','social-login','live', 'upload-to-storage', 'cronjob_settings'])) { ?>
                    <li <?php echo ($page == 'general-settings' || $page == 'site-settings' || $page == 'upload-to-storage' || $page == 'email-settings' || $page == 'social-login' || $page == 's3' || $page == 'live' || $page == 'ffmpeg' || $page == 'video_settings' || $page == 'cronjob_settings') ? 'class="open"' : ''; ?>>
                        <a href="#">
                            <span class="nav-link-icon">
                                <i class="material-icons">settings</i>
                            </span>
                            <span>Настройки</span>
                        </a>
                        <ul <?php echo ($page == 'general-settings' || $page == 'site-settings' || $page == 'email-settings' || $page == 'social-login' || $page == 's3' || $page == 'live') ? 'style="display: block;"' : ''; ?>>
                            <?php if ($pt->user->admin == 1 || CheckHavePermission('general-settings')) { ?>
                            <li>
                                <a <?php echo ($page == 'general-settings') ? 'class="active"' : ''; ?> href="<?php echo PT_LoadAdminLinkSettings('general-settings'); ?>" data-ajax="?path=general-settings">Общая конфигурация</a>
                            </li>
                            <?php } ?>
                            <?php if ($pt->user->admin == 1 || CheckHavePermission('site-settings')) { ?>
                            <li>
                                <a <?php echo ($page == 'site-settings') ? 'class="active"' : ''; ?> href="<?php echo PT_LoadAdminLinkSettings('site-settings'); ?>" data-ajax="?path=site-settings">Информация о сайте</a>
                            </li>
                            <?php } ?>
                            <?php if ($pt->user->admin == 1 || CheckHavePermission('ffmpeg')) { ?>
                            <li>
                                <a <?php echo ($page == 'ffmpeg' || $page == 'upload-to-storage') ? 'class="active"' : ''; ?> href="<?php echo PT_LoadAdminLinkSettings('ffmpeg'); ?>" data-ajax="?path=ffmpeg">Импорт и загрузка конфигурации</a>
                            </li>
                            <?php } ?>
                            <?php if ($pt->user->admin == 1 || CheckHavePermission('video_settings')) { ?>
                            <li>
                                <a <?php echo ($page == 'video_settings') ? 'class="active"' : ''; ?> href="<?php echo PT_LoadAdminLinkSettings('video_settings'); ?>" data-ajax="?path=video_settings">Настройки видео</a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <!-- ./navigation -->
    </div>
    <!-- ./Content wrapper -->
</body>
</html>