<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_medalhasproitec
 * @copyright   2025 DEAD/ZL/IFRN <dead.zl@ifrn.edu.br>, Kelson da Costa Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CODIGO_DISCIPLINA_JORNADA', 'FIC.1198');
define('CODIGO_DISCIPLINA_ETICA', 'FIC.1197');
define('CODIGO_DISCIPLINA_MATEMATICA', 'FIC.1196');
define('CODIGO_DISCIPLINA_PORTUGUES', 'FIC.1195');


/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function medalhasproitec_supports($feature)
{
    return match ($feature) {
        FEATURE_MOD_ARCHETYPE => MOD_ARCHETYPE_RESOURCE,
        FEATURE_GROUPS => false,
        FEATURE_GROUPINGS => false,
        FEATURE_MOD_INTRO => false,
        FEATURE_COMPLETION => false,
        FEATURE_COMPLETION_TRACKS_VIEWS => false,
        FEATURE_GRADE_HAS_GRADE => false,
        FEATURE_GRADE_OUTCOMES => false,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_SHOW_DESCRIPTION => false,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_CONTENT,
        FEATURE_MODEDIT_DEFAULT_COMPLETION => false,
        FEATURE_QUICKCREATE => true,
        default => null,
    };
}

/**
 * Saves a new instance of the mod_medalhasproitec into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_medalhasproitec_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function medalhasproitec_add_instance($moduleinstance, $mform = null)
{
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('medalhasproitec', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_medalhasproitec in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_medalhasproitec_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function medalhasproitec_update_instance($moduleinstance, $mform = null)
{
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('medalhasproitec', $moduleinstance);
}

/**
 * Removes an instance of the mod_medalhasproitec from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function medalhasproitec_delete_instance($id)
{
    global $DB;

    $exists = $DB->get_record('medalhasproitec', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('medalhasproitec', ['id' => $id]);

    return true;
}

/**
 * Is a given scale used by the instance of mod_medalhasproitec?
 *
 * This function returns if a scale is being used by one mod_medalhasproitec
 * if it has support for grading and scales.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_medalhasproitec instance.
 */
function medalhasproitec_scale_used($moduleinstanceid, $scaleid)
{
    global $DB;

    if ($scaleid && $DB->record_exists('medalhasproitec', ['id' => $moduleinstanceid, 'grade' => -$scaleid])) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of mod_medalhasproitec.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_medalhasproitec instance.
 */
function medalhasproitec_scale_used_anywhere($scaleid)
{
    global $DB;

    if ($scaleid && $DB->record_exists('medalhasproitec', ['grade' => -$scaleid])) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given mod_medalhasproitec instance.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function medalhasproitec_grade_item_update($moduleinstance, $reset = false)
{
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $item = [];
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $moduleinstance->grade;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('/mod/medalhasproitec', $moduleinstance->course, 'mod', 'mod_medalhasproitec', $moduleinstance->id, 0, null, $item);
}

/**
 * Delete grade item for given mod_medalhasproitec instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function medalhasproitec_grade_item_delete($moduleinstance)
{
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update(
        '/mod/medalhasproitec',
        $moduleinstance->course,
        'mod',
        'medalhasproitec',
        $moduleinstance->id,
        0,
        null,
        ['deleted' => 1]
    );
}

/**
 * Update mod_medalhasproitec grades in the gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function medalhasproitec_update_grades($moduleinstance, $userid = 0)
{
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = [];
    grade_update('/mod/medalhasproitec', $moduleinstance->course, 'mod', 'mod_medalhasproitec', $moduleinstance->id, 0, $grades);
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_medalhasproitec
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[].
 */
function medalhasproitec_get_file_areas($course, $cm, $context)
{
    return [];
}

/**
 * File browsing support for mod_medalhasproitec file areas.
 *
 * @package     mod_medalhasproitec
 * @category    files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info Instance or null if not found.
 */
function medalhasproitec_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename)
{
    return null;
}

/**
 * Serves the files from the mod_medalhasproitec file areas.
 *
 * @package     mod_medalhasproitec
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_medalhasproitec's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function medalhasproitec_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = [])
{
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    send_file_not_found();
}

/**
 * Extends the global navigation tree by adding mod_medalhasproitec nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $medalhasproitecnode An object representing the navigation tree node.
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function medalhasproitec_extend_navigation($medalhasproitecnode, $course, $module, $cm) {}

/**
 * Extends the settings navigation with the mod_medalhasproitec settings.
 *
 * This function is called when the context for the page is a mod_medalhasproitec module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@see settings_navigation}
 * @param navigation_node $medalhasproitecnode {@see navigation_node}
 */
function medalhasproitec_extend_settings_navigation($settingsnav, $medalhasproitecnode = null) {}





function medalhasproitec_cm_info_view(cm_info $cm)
{
    global $PAGE, $OUTPUT, $COURSE;

    $data = get_insignias();
    $content = $OUTPUT->render_from_template('mod_medalhasproitec/activitycard', $data);
    $cm->set_content($content);
}


/**
 * Extracts the disciplina from the idnumber.
 *
 * @param string $idnumber The idnumber to extract the disciplina from.
 * @return string|null The extracted disciplina or null if not found.
 */
function get_disciplina_from_idnumber($idnumber)
{
    // Extrai o valor 'FIC.1197' do idnumber usando regex
    $disciplina = null;
    if (preg_match('/.*\.(FIC.\\d*)#.*/', $idnumber, $matches)) {
        $disciplina = $matches[1];
    }
    return $disciplina;
}


/**
 * Get the status of the courses based on the matrix curricular.
 *
 * Exemplo:
 * [
 *    (object)[
 *      'course_id' => 0,
 *      'course_idnumber' => '...',
 *      'course_fullname' => '...',
 *      'course_shortname' => '...',
 *      'course_alias' => '...',
 *      'course_subtitle' => '...',
 *      'course_image_url' => '...',
 *      'total_modules' => 0,
 *      'completed_modules' => 0,
 *      'completion_percentage' => 0,
 *      'disciplina' => 'FIC.1198',
 *      'iniciada' => 0,
 *      'concluida' => 0,
 *    ],
 *    (object)[
 *      'course_id' => 0,
 *      'course_idnumber' => '...',
 *      'course_fullname' => '...',
 *      'course_shortname' => '...',
 *      'course_alias' => '...',
 *      'course_subtitle' => '...',
 *      'course_image_url' => '...',
 *      'total_modules' => 0,
 *      'completed_modules' => 0,
 *      'completion_percentage' => 0,
 *      'disciplina' => 'FIC.1197',
 *      'iniciada' => 0,
 *      'concluida' => 0,
 *    ],
 *    (object)[
 *      'course_id' => 0,
 *      'course_idnumber' => '...',
 *      'course_fullname' => '...',
 *      'course_shortname' => '...',
 *      'course_alias' => '...',
 *      'course_subtitle' => '...',
 *      'course_image_url' => '...',
 *      'total_modules' => 0,
 *      'completed_modules' => 0,
 *      'completion_percentage' => 0,
 *      'disciplina' => 'FIC.1196',
 *      'iniciada' => 0,
 *      'concluida' => 0,
 *    ],
 *    (object)[
 *      'course_id' => 0,
 *      'course_idnumber' => '...',
 *      'course_fullname' => '...',
 *      'course_shortname' => '...',
 *      'course_alias' => '...',
 *      'course_subtitle' => '...',
 *      'course_image_url' => '...',
 *      'total_modules' => 0,
 *      'completed_modules' => 0,
 *      'completion_percentage' => 0,
 *      'disciplina' => 'FIC.1195',
 *      'iniciada' => 0,
 *      'concluida' => 0,
 *    ],
 * ]
 * 
 * @return array An array containing the status of each course.
 */
function get_courses_progress_as_list()
{
    global $DB, $CFG, $COURSE, $USER;
    // Título do curso
    // Subtítulo do curso
    // URL da pedra do curso
    // Progresso do curso
    $courses = $DB->get_records_sql(
        "
            SELECT c.id                                       AS course_id
            , c.idnumber                                      AS course_idnumber
            , c.fullname                                      AS course_fullname
            , c.shortname                                      AS course_shortname
            , (SELECT cd.value
                FROM mdl_customfield_data                cd
                        INNER JOIN mdl_customfield_field cf ON
                            (cd.fieldid = cf.id AND cf.shortname = 'multiprogress_course_alias')
                WHERE cd.instanceid = c.id)                 AS course_alias
            , (SELECT cd.value
                FROM mdl_customfield_data                cd
                        INNER JOIN mdl_customfield_field cf ON
                            (cd.fieldid = cf.id AND cf.shortname = 'multiprogress_course_subtitle')
                WHERE cd.instanceid = c.id)                 AS course_subtitle
            , (SELECT cd.value
                FROM mdl_customfield_data                cd
                        INNER JOIN mdl_customfield_field cf ON
                            (cd.fieldid = cf.id AND cf.shortname = 'multiprogress_course_image_url')
                WHERE cd.instanceid = c.id)                 AS course_image_url
            , COUNT(cm.id)                                    AS total_modules
            , COUNT(mc.id)                                    AS completed_modules
            , TRUNC((COUNT(mc.id) * 100.0 / COUNT(cm.id)), 0) AS completion_percentage
        FROM mdl_course                                   c
                INNER JOIN mdl_course_modules            cm ON (c.id = cm.course)
                LEFT JOIN  mdl_course_modules_completion mc ON (cm.id = mc.coursemoduleid)
        WHERE c.category = $COURSE->category
        AND (mc.userid = $USER->id OR mc.userid IS NULL)
        GROUP BY c.id, c.fullname, c.shortname, c.idnumber
        ORDER BY c.idnumber DESC
        "
    );
    $traducao = [
        'FIC.1195' => [
            'course_alias' => 'PEDRA DA LÓGICA',
            'course_subtitle' => 'Viaje ate o Oeste Potiguar para obtê-la',
            'stone_color' => '62, 193, 52'
        ],
        'FIC.1196' => [
            'course_alias' => 'PEDRA DA COMUNICAÇÃO',
            'course_subtitle' => 'Viaje ate a Central Potiguar para obtê-la',
            'stone_color' => '253, 35, 217'
        ],
        'FIC.1197' => [
            'course_alias' => 'PEDRA DA HARMONIA',
            'course_subtitle' => 'Viaje ate o Agreste Potiguar para obtê-la',
            'stone_color' => '242, 183, 34'
        ],
        'FIC.1198' => [
            'course_alias' => 'PEDRA DA UNIDADE',
            'course_subtitle' => 'Viaje ate o Leste Potiguar para obtê-la',
            'stone_color' => '47, 109, 246'
        ]
    ];

    foreach ($courses as $course) {
        // Extrai o valor 'FIC.1197' do idnumber usando regex
        $course->disciplina = get_disciplina_from_idnumber($course->course_idnumber);

        // If the course alias is not set, use the course fullname.
        if (empty($course->course_alias)) {
            $course->course_alias = $course->course_fullname;
        }
        // If the course subtitle is not set, use an empty string.
        if (empty($course->course_subtitle)) {
            $course->course_subtitle = $course->course_shortname;
        }
        // If the course image URL is not set, use a default image.
        if (empty($course->course_image_url)) {
            $course->course_image_url = "$CFG->wwwroot/mod/medalhasproitec/pix/pedra.{$course->disciplina}.png";
        }
        $course->iniciada = TRUE;
        $course->concluida = $course->completion_percentage >= 100;
        $course->jornada = $course->disciplina === 'FIC.1198';

        if (array_key_exists($course->disciplina, $traducao)) {
            $course->stone_color =  $traducao[$course->disciplina]['stone_color'];
            $course->course_alias = $traducao[$course->disciplina]['course_alias'];
            $course->course_subtitle = $traducao[$course->disciplina]['course_subtitle'];
        } else {
            $course->stone_color = '0, 255, 255';
        }
        $course->isactive = ($course->course_id == $COURSE->id) ? 'd-flex' : 'hidden';
    }
    return array_values($courses);
}

/**
 * Get the status of the courses based on the matrix curricular.
 *
 * Exemplo:
 * {
 *    "jornada": {
 *      "course_id": 0,
 *      "course_idnumber": "...",
 *      "course_fullname": "...",
 *      "course_shortname": "...",
 *      "course_alias": "...",
 *      "course_subtitle": "...",
 *      "course_image_url": "...",
 *      "total_modules": 0,
 *      "completed_modules": 0,
 *      "completion_percentage": 0,
 *      "disciplina": "FIC.1198",
 *      "iniciada": 0,
 *      "concluida": 0,
 *    },
 *    "etica": {
 *      "course_id": 0,
 *      "course_idnumber": "...",
 *      "course_fullname": "...",
 *      "course_shortname": "...",
 *      "course_alias": "...",
 *      "course_subtitle": "...",
 *      "course_image_url": "...",
 *      "total_modules": 0,
 *      "completed_modules": 0,
 *      "completion_percentage": 0,
 *      "disciplina": "FIC.1197",
 *      "iniciada": 0,
 *      "concluida": 0,
 *    },
 *    "matematica": {
 *      "course_id": 0,
 *      "course_idnumber": "...",
 *      "course_fullname": "...",
 *      "course_shortname": "...",
 *      "course_alias": "...",
 *      "course_subtitle": "...",
 *      "course_image_url": "...",
 *      "total_modules": 0,
 *      "completed_modules": 0,
 *      "completion_percentage": 0,
 *      "disciplina": "FIC.1196",
 *      "iniciada": 0,
 *      "concluida": 0,
 *    },
 *    "portugues": {
 *      "course_id": 0,
 *      "course_idnumber": "...",
 *      "course_fullname": "...",
 *      "course_shortname": "...",
 *      "course_alias": "...",
 *      "course_subtitle": "...",
 *      "course_image_url": "...",
 *      "total_modules": 0,
 *      "completed_modules": 0,
 *      "completion_percentage": 0,
 *      "disciplina": "FIC.1195",
 *      "iniciada": 0,
 *      "concluida": 0,
 *    },
 * }
 * 
 * @return array An array containing the status of each course.
 */
function get_courses_progress_as_dict()
{
    global $DB, $CFG, $COURSE, $USER;
    $matrix_curricular = [
        "FIC.1198" => ["curso" => "Seminário de Integração", "key" => "jornada"],
        "FIC.1197" => ["curso" => "Ética e Cidadania", "key" => "etica"],
        "FIC.1196" => ["curso" => "Matemática", "key" => "matematica"],
        "FIC.1195" => ["curso" => "Língua Portuguesa", "key" => "portugues"],
    ];

    $courses = get_courses_progress_as_list();

    $courses_statuses = [
        'jornada' => null,
        'etica' => null,
        'matematica' => null,
        'portugues' => null,
    ];

    foreach ($courses as $course) {
        if (isset($matrix_curricular[$course->disciplina])) {
            $courses_statuses[$matrix_curricular[$course->disciplina]['key']] = $course;
        }
    }
    return $courses_statuses;
}

/**
 * Get the status of the courses based on the matrix curricular.
 *
 * Exemplo:
 * [
 *    'sentinela_do_codex' => (object)[
 *      'tem' => false,
 *      'title' => 'Sentinela do Codex',
 *      'description' => '...',
 *      'popup' => '...',
 *    ],
 *    'maratonista_do_conhecimento' => (object)[
 *      'tem' => false,
 *      'title' => 'Maratonista do Conhecimento',
 *      'description' => '...',
 *      'popup' => '...',
 *    ],
 *    'busca_pelo_saber' => (object)[
 *      'tem' => false,
 *      'title' => 'Busca pelo Saber',
 *      'description' => '...',
 *      'popup' => '...',
 *    ],
 *    'mestre_do_portal' => (object)[
 *      'tem' => false,
 *      'title' => 'Mestre do Portal',
 *      'description' => '...',
 *      'popup' => '...',
 *    ],
 *    'amante_dos_numeros' => (object)[
 *      'tem' => false,
 *      'title' => 'Amante dos Números',
 *      'description' => '...',
 *      'popup' => '...',
 *    ],
 *    'amante_das_palavras' => (object)[
 *      'tem' => false,
 *      'title' => 'Amante das Palavras',
 *      'description' => '...',
 *      'popup' => '...',
 *    ],
 *    'orgulho_da_comunidade' => (object)[
 *      'tem' => false,
 *      'title' => 'Orgulho da Comunidade',
 *      'description' => '...',
 *      'popup' => '...',
 *    ],
 *    'entusiasta_do_ifrn' => (object)[
 *      'tem' => false,
 *      'title' => 'Entusiasta do IFRN',
 *      'description' => '...',
 *      'popup' => '...',
 *    ],
 * ]
 * 
 * @return array An array containing the status of each course.
 */
function get_insignias()
{
    global $DB, $CFG, $COURSE, $USER;
    $courses = get_courses_progress_as_list();

    $insignias = [
        'sentinela_do_codex' => (object)[
            'tem' => false,
            'ja_mostrou' => false,
            'title' => 'Sentinela do Codex',
            'description' => '...',
            'popup' => '...',
        ],
        'maratonista_do_conhecimento' => (object)[
            'tem' => false,
            'mostrar_popup' => false,
            'title' => 'Maratonista do Conhecimento',
            'description' => '...',
            'popup' => '...',
        ],
        'busca_pelo_saber' => (object)[
            'tem' => false,
            'mostrar_popup' => false,
            'title' => 'Busca pelo Saber',
            'description' => '...',
            'popup' => '...',
        ],
        'mestre_do_portal' => (object)[
            'tem' => false,
            'mostrar_popup' => false,
            'title' => 'Mestre do Portal',
            'description' => '...',
            'popup' => '...',
        ],
        'amante_dos_numeros' => (object)[
            'tem' => false,
            'mostrar_popup' => false,
            'title' => 'Amante dos Números',
            'description' => '...',
            'popup' => '...',
        ],
        'amante_das_palavras' => (object)[
            'tem' => false,
            'mostrar_popup' => false,
            'title' => 'Amante das Palavras',
            'description' => '...',
            'popup' => '...',
        ],
        'orgulho_da_comunidade' => (object)[
            'tem' => false,
            'mostrar_popup' => false,
            'title' => 'Orgulho da Comunidade',
            'description' => '...',
            'popup' => '...',
        ],
        'entusiasta_do_ifrn' => (object)[
            'tem' => false,
            'mostrar_popup' => false,
            'title' => 'Entusiasta do IFRN',
            'description' => '...',
            'popup' => '...',
        ],
    ];
    return $insignias;
}