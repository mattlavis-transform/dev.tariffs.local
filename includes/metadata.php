<?php
// Get the page title
//h1 ("MODE = " . $application->mode . " on " . $application->data[$application->tariff_object]["config"]["object_name"]);
$title = "";
if ($application->tariff_object != "") {
    $config = $application->data[$application->tariff_object]["config"];
    switch ($application->mode) {
        case "insert":
            if (isset($config["title_create"])) {
                $title = $config["title_create"] . " : " . $application->name;
            }
            break;
        case "update":
            if (isset($config["title_edit"])) {
                $title = $config["title_edit"] . " : " . $application->name;
            }
            break;
        case "view":
            if (isset($config["title_view"])) {
                $title = $config["title_view"] . " : " . $application->name;
            }
            break;
        default:
            if (isset($config["title"])) {
                $title = $config["title"] . " : " . $application->name;
            } else {
                $title = $application->name;
            }
    }
} else {
    $title = $application->name;
}
$title = parse_placeholders($title);
?>

<head>
    <meta charset="utf-8" />
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b0c0c" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" sizes="16x16 32x32 48x48" href="/assets/images/favicon.ico" type="image/x-icon" />
    <link rel="mask-icon" href="/assets/images/govuk-mask-icon.svg" color="#0b0c0c">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/govuk-apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/assets/images/govuk-apple-touch-icon-167x167.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/assets/images/govuk-apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" href="/assets/images/govuk-apple-touch-icon.png">
    <!--[if !IE 8]><!-->
    <link href="/css/govuk-frontend-3.4.0.min.css" rel="stylesheet" />
    <link href="/css/application.css" rel="stylesheet" />
    <link href="/css/modaal.css" rel="stylesheet" />
    <!--<![endif]-->
    <script src="/js/jquery-3.4.1.js"></script>
    <!--
    <script src="/js/jquery-3.4.1.min.js"></script>
    //-->
    <script src="/js/jquery-ui.min.js"></script>
    <script src="/js/js.cookie.js"></script>
    <script src="/js/typeahead.bundle.js"></script>
    <link href="/css/select2.css" rel="stylesheet" />
    <script src="/js/select2.js"></script>
    <script src="/js/application.js"></script>
    <script src="/js/date.format.js"></script>
    <script src="/js/cursor.js"></script>
    <script src="/js/moment.js"></script>
    <script src="/js/modaal.min.js"></script>
    <script src="/js/notify.min.js"></script>


    <!--[if IE 8]>
<link href="/css/govuk-frontend-ie8-3.4.0.min.css" rel="stylesheet" />
<link href="/css/govuk-frontend-ie8-3.4.0.min.css" rel="stylesheet" />
<![endif]-->

    <!--[if lt IE 9]>
<script src="/html5-shiv/html5shiv.js"></script>
<![endif]-->

    <meta property="og:image" content="/assets/images/govuk-opengraph-image.png">
    <?= $application->notification_text ?>
</head>