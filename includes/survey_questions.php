<?php

declare(strict_types=1);

/**
 * Single source of truth for the survey.
 *
 * The public form (survey.php), the submission validator (api/survey.php),
 * the CSV export and the admin dashboard all read the questions from here,
 * so the wording only ever needs to be changed in ONE place.
 *
 * This mirrors the approved Google Form titled
 * "Mentorship, Higher Education and Career Readiness Survey — Northern Ghana".
 */

/**
 * Small pieces of text shown around the form (title, intro, subtitle).
 */
function survey_meta(): array
{
    return [
        'title' => 'Mentorship, Higher Education and Career Readiness Survey — Northern Ghana',
        'subtitle' => 'University for Development Studies, Tamale - Ghana',
        'description' => 'This short survey is for final-year students and recent graduates '
            . 'from Northern Ghana. It aims to understand access to mentorship and how '
            . 'mentorship may relate to motivation, readiness for postgraduate education, '
            . 'and job-market preparation. Participation is voluntary. Please do not '
            . 'include your name or other identifying details. Responses will be used to '
            . 'understand needs and plan possible support. Most people should complete '
            . 'this in about 3 minutes.',
    ];
}

/**
 * The full list of questions, keyed by "q1" ... "q13".
 *
 * Each question has:
 *   number    - the number shown in front of the title.
 *   title     - the question text.
 *   help      - optional helper line under the question.
 *   type      - radio | checkbox | text | textarea | scale.
 *   required  - whether an answer is compulsory.
 *   options   - the choices for radio/checkbox questions.
 *   other     - adds an "Other" choice with a free-text box (checkbox only).
 *   maxlength - longest allowed answer for text/textarea questions.
 *   min/max   - range for scale questions.
 */
function survey_questions(): array
{
    return [
        'q1' => [
            'number' => 1,
            'title' => 'What is your age group?',
            'help' => '',
            'type' => 'radio',
            'required' => true,
            'options' => [
                'Under 20',
                '20–24',
                '25–29',
                '30 or older',
                'Prefer not to say',
            ],
        ],

        'q2' => [
            'number' => 2,
            'title' => 'Which part of Northern Ghana are you mainly from or based in?',
            'help' => '',
            'type' => 'radio',
            'required' => true,
            'options' => [
                'Northern Region',
                'Savannah Region',
                'North East Region',
                'Upper East Region',
                'Upper West Region',
                'Other / currently based elsewhere',
                'Prefer not to say',
            ],
        ],

        'q3' => [
            'number' => 3,
            'title' => 'Which school or tertiary institution do/did you attend?',
            'help' => 'Please enter the name of your current or most recently attended institution.',
            'type' => 'text',
            'required' => true,
            'maxlength' => 200,
        ],

        'q4' => [
            'number' => 4,
            'title' => 'Which best describes your current status?',
            'help' => '',
            'type' => 'radio',
            'required' => true,
            'options' => [
                'Final-year student',
                'Recent graduate (graduated within the last 3 years)',
                'Other student status',
            ],
        ],

        'q5' => [
            'number' => 5,
            'title' => 'What is your current or most recently completed level of education?',
            'help' => '',
            'type' => 'radio',
            'required' => true,
            'options' => [
                'Certificate',
                'Diploma / HND',
                'Bachelor’s degree',
                'Master’s degree',
                'Other',
            ],
        ],

        'q6' => [
            'number' => 6,
            'title' => 'If you are currently doing a final-year or major research project, how difficult is it?',
            'help' => 'Rate from 1 to 10, where 1 = not difficult at all and 10 = extremely difficult. Leave blank if this does not apply to you.',
            'type' => 'scale',
            'required' => false,
            'min' => 1,
            'max' => 10,
            'min_label' => 'Not difficult',
            'max_label' => 'Extremely difficult',
        ],

        'q7' => [
            'number' => 7,
            'title' => 'How much access have you had to useful mentorship for your education or career?',
            'help' => '',
            'type' => 'radio',
            'required' => true,
            'options' => [
                'No access to a mentor',
                'I tried to find a mentor but could not',
                'Very limited — one or two conversations',
                'Occasional guidance when needed',
                'Regular guidance from a mentor',
                'Structured mentorship programme',
            ],
        ],

        'q8' => [
            'number' => 8,
            'title' => 'To what extent do you agree that mentorship can help people from Northern Ghana progress to postgraduate education and become job-market ready?',
            'help' => 'Choose one response.',
            'type' => 'radio',
            'required' => true,
            'options' => [
                'Strongly disagree',
                'Disagree',
                'Neither agree nor disagree',
                'Agree',
                'Strongly agree',
            ],
        ],

        'q9' => [
            'number' => 9,
            'title' => 'Which best describes your postgraduate or further-education situation?',
            'help' => '',
            'type' => 'radio',
            'required' => true,
            'options' => [
                'Currently enrolled in postgraduate / further education',
                'Applied and awaiting a decision',
                'Plan or hope to apply, but have not yet applied',
                'Would like to continue, but face barriers',
                'Not currently interested in further education',
            ],
        ],

        'q10' => [
            'number' => 10,
            'title' => 'What kind of mentorship or support would be most useful to you?',
            'help' => 'Select all that apply.',
            'type' => 'checkbox',
            'required' => true,
            'options' => [
                'Choosing a postgraduate programme or career path',
                'Scholarship and funding opportunities',
                'Preparing applications, CVs or personal statements',
                'Research skills and final-year project support',
                'Interview skills and job-search preparation',
                'Networking and connecting with professionals',
                'Confidence, motivation and goal setting',
                'Other',
            ],
        ],

        'q11' => [
            'number' => 11,
            'title' => 'What are the main barriers you face in accessing postgraduate education or becoming job-ready?',
            'help' => 'Select all that apply.',
            'type' => 'checkbox',
            'required' => false,
            'other' => true,
            'other_label' => 'Other (please specify)',
            'other_maxlength' => 300,
            'options' => [
                'Limited information or guidance',
                'Cost or lack of funding',
                'Limited professional networks or contacts',
                'Lack of confidence or motivation',
                'Limited relevant experience or skills',
                'Family, location or time constraints',
                'No major barrier at present',
            ],
        ],

        'q12' => [
            'number' => 12,
            'title' => 'Is there one specific kind of mentorship or opportunity you wish you had access to?',
            'help' => 'Optional.',
            'type' => 'textarea',
            'required' => false,
            'maxlength' => 2000,
        ],

        'q13' => [
            'number' => 13,
            'title' => 'If an organisation offered voluntary mentorship and guidance to help people access postgraduate education and prepare for the job market, how would you feel about taking part?',
            'help' => 'There is no right or wrong answer. Please choose the response that best reflects your current view.',
            'type' => 'radio',
            'required' => true,
            'options' => [
                'I would definitely be interested',
                'I would probably be interested',
                'I am unsure and would need more information',
                'I would probably not be interested',
                'I would not be interested',
                'It would depend on the type of support offered',
            ],
        ],
    ];
}
