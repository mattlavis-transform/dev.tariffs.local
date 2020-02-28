    <script>
        document.body.className = ((document.body.className) ? document.body.className + ' js-enabled' : 'js-enabled');
    </script>

    <a href="#main-content" class="govuk-skip-link">Skip to main content</a>

    <?php
    $accepted = get_querystring("accepted");
    if (($application->session->cookies_accepted != 1) || ($accepted == "yes")) {
    ?>
        <!-- Begin cookie message //-->
        <div id="global-cookie-message" class="gem-c-cookie-banner govuk-clearfix" data-module="cookie-banner" role="region" aria-label="cookie banner" data-nosnippet>
            <?php
            if ($application->session->cookies_accepted != 1) {
            ?>
                <div class="gem-c-cookie-banner__wrapper govuk-width-container">
                    <div class="govuk-grid-row">
                        <div class=" govuk-grid-column-two-thirds">
                            <div>
                                <span class="govuk-heading-m">Tell us whether you accept cookies</span>
                                <p class="govuk-body">We use <a class="govuk-link" href="/content/cookies.html">cookies to collect information</a> about how you use GOV.UK. We use this information to make the website work as well as possible and improve government services.</p>
                            </div>
                            <div class="gem-c-cookie-banner__buttons">
                                <form method="post" action="/session/cookies.html">
                                    <button value="yes" name="accept-all-cookies" id="accept-all-cookies" class="gem-c-button govuk-button gem-c-button--inline" type="submit" data-module="track-click" data-accept-cookies="true" data-track-category="cookieBanner" data-track-action="Cookie banner accepted">Accept all cookies</button>
                                    <a class="gem-c-button govuk-button gem-c-button--inline" role="button" data-module="track-click" data-track-category="cookieBanner" data-track-action="Cookie banner settings clicked" href="/content/cookies.html">Set cookie preferences</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <?php
            if ($accepted == "yes") {
            ?>
                <div class="gem-c-cookie-banner__confirmation govuk-width-container" tabindex="-1">
                    <p class="govuk-body">
                        You’ve accepted all cookies. You can <a class="govuk-link" href="/content/cookies.html" data-module="track-click" data-track-category="cookieBanner" data-track-action="Cookie banner settings clicked from confirmation">change your cookie settings</a> at any time.
                    </p>
                    <button name="hide_cookie_message" id="hide_cookie_message" class="govuk-button" data-hide-cookie-banner="true" data-module="track-click" data-track-category="cookieBanner" data-track-action="Hide cookie banner">Hide</button>
                </div>
            <?php
            }
            ?>
        </div>
        <!-- End cookie message //-->
    <?php
    }
    ?>
    <header class="govuk-header" role="banner" data-module="govuk-header">
        <div class="govuk-header__container govuk-width-container">
            <div class="govuk-header__logo">
                <a href="/" class="govuk-header__link govuk-header__link--homepage">
                    <span class="govuk-header__logotype">
                        <svg role="presentation" focusable="false" class="govuk-header__logotype-crown" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 132 97" height="30" width="36">
                            <path fill="currentColor" fill-rule="evenodd" d="M25 30.2c3.5 1.5 7.7-.2 9.1-3.7 1.5-3.6-.2-7.8-3.9-9.2-3.6-1.4-7.6.3-9.1 3.9-1.4 3.5.3 7.5 3.9 9zM9 39.5c3.6 1.5 7.8-.2 9.2-3.7 1.5-3.6-.2-7.8-3.9-9.1-3.6-1.5-7.6.2-9.1 3.8-1.4 3.5.3 7.5 3.8 9zM4.4 57.2c3.5 1.5 7.7-.2 9.1-3.8 1.5-3.6-.2-7.7-3.9-9.1-3.5-1.5-7.6.3-9.1 3.8-1.4 3.5.3 7.6 3.9 9.1zm38.3-21.4c3.5 1.5 7.7-.2 9.1-3.8 1.5-3.6-.2-7.7-3.9-9.1-3.6-1.5-7.6.3-9.1 3.8-1.3 3.6.4 7.7 3.9 9.1zm64.4-5.6c-3.6 1.5-7.8-.2-9.1-3.7-1.5-3.6.2-7.8 3.8-9.2 3.6-1.4 7.7.3 9.2 3.9 1.3 3.5-.4 7.5-3.9 9zm15.9 9.3c-3.6 1.5-7.7-.2-9.1-3.7-1.5-3.6.2-7.8 3.7-9.1 3.6-1.5 7.7.2 9.2 3.8 1.5 3.5-.3 7.5-3.8 9zm4.7 17.7c-3.6 1.5-7.8-.2-9.2-3.8-1.5-3.6.2-7.7 3.9-9.1 3.6-1.5 7.7.3 9.2 3.8 1.3 3.5-.4 7.6-3.9 9.1zM89.3 35.8c-3.6 1.5-7.8-.2-9.2-3.8-1.4-3.6.2-7.7 3.9-9.1 3.6-1.5 7.7.3 9.2 3.8 1.4 3.6-.3 7.7-3.9 9.1zM69.7 17.7l8.9 4.7V9.3l-8.9 2.8c-.2-.3-.5-.6-.9-.9L72.4 0H59.6l3.5 11.2c-.3.3-.6.5-.9.9l-8.8-2.8v13.1l8.8-4.7c.3.3.6.7.9.9l-5 15.4v.1c-.2.8-.4 1.6-.4 2.4 0 4.1 3.1 7.5 7 8.1h.2c.3 0 .7.1 1 .1.4 0 .7 0 1-.1h.2c4-.6 7.1-4.1 7.1-8.1 0-.8-.1-1.7-.4-2.4V34l-5.1-15.4c.4-.2.7-.6 1-.9zM66 92.8c16.9 0 32.8 1.1 47.1 3.2 4-16.9 8.9-26.7 14-33.5l-9.6-3.4c1 4.9 1.1 7.2 0 10.2-1.5-1.4-3-4.3-4.2-8.7L108.6 76c2.8-2 5-3.2 7.5-3.3-4.4 9.4-10 11.9-13.6 11.2-4.3-.8-6.3-4.6-5.6-7.9 1-4.7 5.7-5.9 8-.5 4.3-8.7-3-11.4-7.6-8.8 7.1-7.2 7.9-13.5 2.1-21.1-8 6.1-8.1 12.3-4.5 20.8-4.7-5.4-12.1-2.5-9.5 6.2 3.4-5.2 7.9-2 7.2 3.1-.6 4.3-6.4 7.8-13.5 7.2-10.3-.9-10.9-8-11.2-13.8 2.5-.5 7.1 1.8 11 7.3L80.2 60c-4.1 4.4-8 5.3-12.3 5.4 1.4-4.4 8-11.6 8-11.6H55.5s6.4 7.2 7.9 11.6c-4.2-.1-8-1-12.3-5.4l1.4 16.4c3.9-5.5 8.5-7.7 10.9-7.3-.3 5.8-.9 12.8-11.1 13.8-7.2.6-12.9-2.9-13.5-7.2-.7-5 3.8-8.3 7.1-3.1 2.7-8.7-4.6-11.6-9.4-6.2 3.7-8.5 3.6-14.7-4.6-20.8-5.8 7.6-5 13.9 2.2 21.1-4.7-2.6-11.9.1-7.7 8.8 2.3-5.5 7.1-4.2 8.1.5.7 3.3-1.3 7.1-5.7 7.9-3.5.7-9-1.8-13.5-11.2 2.5.1 4.7 1.3 7.5 3.3l-4.7-15.4c-1.2 4.4-2.7 7.2-4.3 8.7-1.1-3-.9-5.3 0-10.2l-9.5 3.4c5 6.9 9.9 16.7 14 33.5 14.8-2.1 30.8-3.2 47.7-3.2z"></path>
                            <image src="/assets/images/govuk-logotype-crown.png" xlink:href="" class="govuk-header__logotype-crown-fallback-image" width="36" height="32"></image>
                        </svg>
                        <span class="govuk-header__logotype-text"><?= $application->name ?></span><span class="env">Development</span>
                    </span>
                </a>
            </div>
            <div class="govuk-header__controls">
                <?php
                //$application->session->workbasket = null;
                if ($application->session->user_id != null) {
                    if ($application->session->workbasket != null) {
                        echo ('Current workbasket: <a href="/workbaskets/view.html">' . $application->session->workbasket->title . '</a>&nbsp;&nbsp;');
                    } else {
                        echo ('<a href="/workbaskets/create_or_open_workbasket.html">Create or open workbasket</a>&nbsp;&nbsp;');
                    }
                    echo ('<a href="/session/sign_out.html">Sign out</a>');
                }
                ?>

            </div>
        </div>
    </header>
