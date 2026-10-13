-- Survey tables for the "Mentorship, Higher Education and Career Readiness
-- Survey (Northern Ghana)".
--
-- Run this ONCE on each environment (local XAMPP and the cPanel database)
-- before opening the survey for responses. Creating the tables again is
-- safe because of "IF NOT EXISTS".
--
-- Design notes:
--   * survey_responses holds one row per submission (no personal details
--     are stored, only a one-way hash of the visitor IP for spam checks).
--   * survey_answers holds one row per answered question so that option
--     counts, averages and CSV export are simple SQL queries.

CREATE TABLE IF NOT EXISTS survey_responses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_hash CHAR(64) NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_submitted_at (submitted_at),
    KEY idx_ip_hash (ip_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS survey_answers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    response_id INT UNSIGNED NOT NULL,
    question_key VARCHAR(16) NOT NULL,
    value TEXT NOT NULL,
    PRIMARY KEY (id),
    KEY idx_question_value (question_key, value(191)),
    KEY idx_response (response_id),
    CONSTRAINT fk_survey_answers_response
        FOREIGN KEY (response_id) REFERENCES survey_responses (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
