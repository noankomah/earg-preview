<?php

declare(strict_types=1);

/**
 * Public survey page.
 *
 * Shows the approved survey questions and submits the answers to
 * api/survey.php with JavaScript (see assets/js/survey.js). The wording of
 * every question lives in includes/survey_questions.php.
 */

require_once __DIR__ . '/includes/survey_questions.php';
require_once __DIR__ . '/includes/survey_helpers.php';

// Remember when the page was opened (used to screen out instant bot submits).
survey_mark_started();

$meta = survey_meta();
$questions = survey_questions();
$csrfToken = survey_csrf_token();
$alreadySubmitted = !empty($_SESSION['survey_submitted']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Mentorship &amp; Career Readiness Survey | EA Research Group</title>
    <meta name="description" content="A short voluntary survey for final-year students and recent graduates in Northern Ghana about mentorship, postgraduate education and job-market readiness." />

    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/about.css?v=2">
    <link rel="stylesheet" href="assets/css/survey.css?v=1">

    <script defer src="assets/vendor/alpinejs/alpine.min.js"></script>
</head>

<script>
    // Opens/closes the mobile navigation dropdowns (same behaviour as other pages).
    document.addEventListener("DOMContentLoaded", function () {
        const dropdowns = document.querySelectorAll(".nav-dropdown");

        dropdowns.forEach(function (dropdown) {
            const parent = dropdown.querySelector(".nav-parent");

            if (!parent) return;

            parent.addEventListener("click", function (event) {
                if (window.innerWidth > 1024) return;

                event.preventDefault();

                dropdowns.forEach(function (otherDropdown) {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove("is-open");
                    }
                });

                dropdown.classList.toggle("is-open");
            });
        });
    });
</script>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="survey-main">
        <div class="container">

            <header class="survey-header">
                <p class="survey-eyebrow"><?= htmlspecialchars($meta['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
                <h1><?= htmlspecialchars($meta['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="survey-intro"><?= htmlspecialchars($meta['description'], ENT_QUOTES, 'UTF-8') ?></p>
            </header>

            <!-- Thank-you panel, revealed by JavaScript after a successful submit. -->
            <section id="surveyThankYou" class="survey-thankyou" hidden>
                <h2>Thank you!</h2>
                <p>Your response has been recorded. We really appreciate you taking the time to help.</p>
            </section>

            <!-- Already-submitted notice (soft duplicate protection). -->
            <?php if ($alreadySubmitted): ?>
                <section class="survey-notice">
                    <p>It looks like you have already submitted a response from this browser. Thank you!</p>
                </section>
            <?php endif; ?>

            <form id="surveyForm" class="survey-form" novalidate <?= $alreadySubmitted ? 'hidden' : '' ?>>

                <!-- CSRF token and honeypot field, checked by api/survey.php. -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="survey-honeypot" aria-hidden="true">
                    <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <p class="survey-required-note"><span class="survey-req">*</span> Required</p>

                <div id="surveyGeneralError" class="survey-alert" hidden></div>

                <?php foreach ($questions as $key => $question): ?>
                    <?php
                    $type = $question['type'];
                    $required = !empty($question['required']);
                    $inputName = $type === 'checkbox' ? $key . '[]' : $key;
                    ?>
                    <fieldset class="survey-question" data-question="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                        <legend class="survey-legend">
                            <?= (int) $question['number'] ?>. <?= htmlspecialchars($question['title'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($required): ?><span class="survey-req" aria-hidden="true">*</span><?php endif; ?>
                        </legend>

                        <?php if ($question['help'] !== ''): ?>
                            <p class="survey-help"><?= htmlspecialchars($question['help'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>

                        <?php if ($type === 'radio'): ?>
                            <div class="survey-options">
                                <?php foreach ($question['options'] as $index => $option): ?>
                                    <?php $optionId = $key . '-' . $index; ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="<?= htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8') ?>"
                                               id="<?= htmlspecialchars($optionId, ENT_QUOTES, 'UTF-8') ?>"
                                               value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>">
                                        <label class="form-check-label" for="<?= htmlspecialchars($optionId, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($type === 'checkbox'): ?>
                            <div class="survey-options">
                                <?php foreach ($question['options'] as $index => $option): ?>
                                    <?php $optionId = $key . '-' . $index; ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="<?= htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8') ?>"
                                               id="<?= htmlspecialchars($optionId, ENT_QUOTES, 'UTF-8') ?>"
                                               value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>">
                                        <label class="form-check-label" for="<?= htmlspecialchars($optionId, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (!empty($question['other'])): ?>
                                    <?php $otherId = $key . '-other'; ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="<?= htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8') ?>"
                                               id="<?= htmlspecialchars($otherId, ENT_QUOTES, 'UTF-8') ?>"
                                               value="Other" data-other-toggle="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                                        <label class="form-check-label" for="<?= htmlspecialchars($otherId, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($question['other_label'] ?? 'Other', ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                    <input type="text"
                                           class="form-control survey-other-text"
                                           name="<?= htmlspecialchars($key . '_other', ENT_QUOTES, 'UTF-8') ?>"
                                           id="<?= htmlspecialchars($key . '_other', ENT_QUOTES, 'UTF-8') ?>"
                                           data-other-input="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                           maxlength="<?= (int) ($question['other_maxlength'] ?? 300) ?>"
                                           placeholder="Please specify"
                                           hidden>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($type === 'text'): ?>
                            <input type="text"
                                   class="form-control"
                                   name="<?= htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8') ?>"
                                   id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                   maxlength="<?= (int) ($question['maxlength'] ?? 200) ?>">

                        <?php elseif ($type === 'textarea'): ?>
                            <textarea class="form-control"
                                      name="<?= htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8') ?>"
                                      id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                      rows="4"
                                      maxlength="<?= (int) ($question['maxlength'] ?? 2000) ?>"></textarea>

                        <?php elseif ($type === 'scale'): ?>
                            <div class="survey-scale">
                                <span class="survey-scale-label"><?= htmlspecialchars($question['min_label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="survey-scale-row">
                                    <?php for ($value = (int) $question['min']; $value <= (int) $question['max']; $value++): ?>
                                        <?php $optionId = $key . '-' . $value; ?>
                                        <div class="survey-scale-item">
                                            <input class="form-check-input" type="radio"
                                                   name="<?= htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8') ?>"
                                                   id="<?= htmlspecialchars($optionId, ENT_QUOTES, 'UTF-8') ?>"
                                                   value="<?= $value ?>">
                                            <label for="<?= htmlspecialchars($optionId, ENT_QUOTES, 'UTF-8') ?>"><?= $value ?></label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <span class="survey-scale-label"><?= htmlspecialchars($question['max_label'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endif; ?>

                        <p class="survey-error" data-error-for="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" hidden></p>
                    </fieldset>
                <?php endforeach; ?>

                <div class="survey-actions">
                    <button type="submit" class="btn btn-primary" id="surveySubmit">Submit response</button>
                    <span class="survey-status" id="surveyStatus" aria-live="polite"></span>
                </div>
            </form>

            <noscript>
                <p class="survey-notice">Please enable JavaScript to complete and submit this survey.</p>
            </noscript>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/survey.js"></script>
    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
