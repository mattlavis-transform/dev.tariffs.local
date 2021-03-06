<?php
require(dirname(__FILE__) . "../../includes/db.php");
$error_handler = new error_handler();
$application = new application;
$additional_code = new additional_code();
$additional_code->additional_code_sid = get_querystring("additional_code_sid");
$additional_code->additional_code_type_id = get_querystring("additional_code_type_id");
$additional_code->additional_code = get_querystring("additional_code");

if ($application->mode == "insert") {
    $verb = "created";
    $crumb_text = "Create a new description";
} else {
    $verb = "updated";
    $crumb_text = "Edit additional code description";
}
?>

<!DOCTYPE html>
<html lang="en" class="govuk-template">
<?php
require("../includes/metadata.php");
?>

<body class="govuk-template__body">
    <?php
    require("../includes/header.php");
    ?>
    <div class="govuk-width-container">
        <?php
        require("../includes/phase_banner.php");
        ?>

        <!-- Start breadcrumbs //-->
        <div class="govuk-breadcrumbs">
            <ol class="govuk-breadcrumbs__list">
                <li class="govuk-breadcrumbs__list-item">
                    <a class="govuk-breadcrumbs__link" href="/">Home</a>
                </li>
                <li class="govuk-breadcrumbs__list-item">
                    <a class="govuk-breadcrumbs__link" href="/additional_codes">Additional codes</a>
                </li>
                <li class="govuk-breadcrumbs__list-item">
                    <a class="govuk-breadcrumbs__link" href="<?= $additional_code->view_url() ?>">Additional code <?= $additional_code->additional_code_type_id ?><?= $additional_code->additional_code ?></a>
                </li>
                <li class="govuk-breadcrumbs__list-item" aria-current="page"><?= $crumb_text ?></li>
            </ol>
        </div>
        <!-- End breadcrumbs //-->


        <main class="govuk-main-wrapper" id="main-content" role="main">
            <div class="govuk-grid-row">
                <div class="govuk-grid-column-full">
                    <!-- Start panel //-->
                    <div class="govuk-panel govuk-panel--confirmation">
                        <h1 class="govuk-panel__title">
                            The description for additional code <?= $additional_code->additional_code_type_id ?><?= $additional_code->additional_code ?> has been <?= $verb ?>.
                        </h1>
                        <div class="govuk-panel__body">
                            This change has been added to your workbasket<br /><br />&quot;<?= $application->session->workbasket->title ?>&quot;
                        </div>

                    </div>
                    <!-- End panel //-->
                    <h2 class="govuk-heading-m">Next steps</h2>
                    <ul class="govuk-list">
                        <li><a class="govuk-link" href="<?= $additional_code->view_url() ?>">View additional code <?= $additional_code->additional_code_type_id ?><?= $additional_code->additional_code ?></a></li>
                        <li><a class="govuk-link" href="/additional_codes">Manage more additional codes</a></li>
                        <li><a class="govuk-link" href="/workbaskets/view.html">View content of your workbasket</a></li>
                        <li><a class="govuk-link" href="/">Return to main menu</a></li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    <?php
    require("../includes/footer.php");
    ?>

</body>

</html>