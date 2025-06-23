<?php

/** LICENSE
 * 
 * This file is part of Moodle - https://moodle.org/
 * 
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
 */

/** DESCRIPTION
 * 
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
    $cm->set_content($content, true);

    // 1) Estatísticas iniciais
    $totalMedalhas    = count($data);
    $countTem         = 0;
    $pendingInsignias = [];      // vai guardar as medalhas que mostram agora

    foreach ($data as $id => $insignia) {
        if ($insignia->tem) {
            $countTem++;
            if (!$insignia->ja_mostrou_popup) {
                $pendingInsignias[$id] = $insignia;
            }
        }
    }

    // 2) Enfileira os modais de cada pendente
    foreach ($pendingInsignias as $id => $insignia) {
        $ajaxurl = (new moodle_url('/mod/medalhasproitec/ajax_mark_modal_shown.php', [
            'medalhaid' => $id
        ]))->out();
        $imgurl = (new moodle_url("/mod/medalhasproitec/pix/{$id}.png"))->out();

        $PAGE->requires->js_call_amd(
            'mod_medalhasproitec/modal',
            'show',
            [
                $insignia->title,
                $insignia->popup,
                null,
                false,
                null,
                $ajaxurl,
                $imgurl
            ]
        );
    }

    // 3) Se agora o usuário já tem todas e temos pelo menos uma pendente,
    //    enfileira o modal de conclusão **depois de todos os anteriores**.
    if ($countTem === $totalMedalhas && count($pendingInsignias) > 0) {
        $imgurlFinal = (new moodle_url('/mod/medalhasproitec/pix/ISA-VITORIOSA.png'))->out();

        $ajaxurlFinal = (new moodle_url('/mod/medalhasproitec/ajax_mark_modal_shown.php', [
            'medalhaid' => 'conquista_final'
        ]))->out();

        $PAGE->requires->js_call_amd(
            'mod_medalhasproitec/modal',
            'show',
            [
                'Conquista Completa!',
                'Você conquistou todas as medalhas. Sua jornada foi incrível!',
                null,
                false,
                null,
                $ajaxurlFinal,
                $imgurlFinal
            ]
        );
    }
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
 *      {
 *        "jornada": {
 *          "course_id": 0,
 *          "course_idnumber": "20242.1.527.1E.FIC.1197#564322",
 *          "course_fullname": "Seminário de Integração	",
 *          "course_shortname": "20242.1.527.1E.FIC.1197#564322"
 *          "course_alias": "...",
 *          "course_subtitle": "...",
 *          "course_image_url": "...",
 *          "total_modules": 0,
 *          "completed_modules": 0,
 *          "completion_percentage": 0,
 *          "disciplina": "FIC.1198",
 *          "iniciada": 0,
 *          "concluida": 0,
 *        },
 *        "etica": {
 *          "course_id": 0,
 *          "course_idnumber": "20242.1.527.1E.FIC.1197#564322",
 *          "course_fullname": "Ética e Cidadania",
 *          "course_shortname": "20242.1.527.1E.FIC.1197#564322"
 *          "course_alias": "...",
 *          "course_subtitle": "...",
 *          "course_image_url": "...",
 *          "total_modules": 0,
 *          "completed_modules": 0,
 *          "completion_percentage": 0,
 *          "disciplina": "FIC.1197",
 *          "iniciada": 0,
 *          "concluida": 0,
 *        },
 *        "matematica": {
 *          "course_id": 0,
 *          "course_idnumber": "20242.1.527.1E.FIC.1196#564322",
 *          "course_fullname": "Matemática",
 *          "course_shortname": "20242.1.527.1E.FIC.1196#564322"
 *          "course_alias": "...",
 *          "course_subtitle": "...",
 *          "course_image_url": "...",
 *          "total_modules": 0,
 *          "completed_modules": 0,
 *          "completion_percentage": 0,
 *          "disciplina": "FIC.1196",
 *          "iniciada": 0,
 *          "concluida": 0,
 *        },
 *        "portugues": {
 *          "course_id": 0,
 *          "course_idnumber": "20242.1.527.1E.FIC.1195#564322",
 *          "course_fullname": "Língua Portuguesa",
 *          "course_shortname": "20242.1.527.1E.FIC.1195#564322"
 *          "course_alias": "...",
 *          "course_subtitle": "...",
 *          "course_image_url": "...",
 *          "total_modules": 0,
 *          "completed_modules": 0,
 *          "completion_percentage": 0,
 *          "disciplina": "FIC.1195",
 *          "iniciada": 0,
 *          "concluida": 0,
 *        },
 *     }
 *  
 * @return array An array containing the status of each course.
 */
function get_courses_progress_as_list()
{
    global $DB, $CFG, $COURSE, $USER;

    $courses = $DB->get_records_sql(
        "
            SELECT c.id                                                                                 AS course_id
            , c.idnumber                                                                                AS course_idnumber
            , c.fullname                                                                                AS course_fullname
            , c.shortname                                                                               AS course_shortname
            , (SELECT cd.value
                FROM mdl_customfield_data cd
                        INNER JOIN mdl_customfield_field cf ON
                            (cd.fieldid = cf.id AND cf.shortname = 'multiprogress_course_alias')
                WHERE cd.instanceid = c.id)                                                             AS course_alias
            , (SELECT cd.value
                FROM mdl_customfield_data cd
                        INNER JOIN mdl_customfield_field cf ON
                            (cd.fieldid = cf.id AND cf.shortname = 'multiprogress_course_subtitle')
                WHERE cd.instanceid = c.id)                                                             AS course_subtitle
            , (SELECT cd.value
                FROM mdl_customfield_data cd
                        INNER JOIN mdl_customfield_field cf ON
                            (cd.fieldid = cf.id AND cf.shortname = 'multiprogress_course_image_url')
                WHERE cd.instanceid = c.id)                                                             AS course_image_url
            , COUNT(cm.id)                                                                              AS total_modules
            , COUNT(mc.id)                                                                              AS completed_modules
            , 0                                                                                         AS course_grade
        FROM mdl_course                                         AS c
                LEFT JOIN mdl_course_modules                    AS cm ON (c.id = cm.course AND cm.completion > 0)
                    LEFT JOIN  mdl_course_modules_completion    AS mc ON (cm.id = mc.coursemoduleid AND mc.userid = $USER->id)
        WHERE c.category = $COURSE->category
        GROUP BY c.id, c.fullname, c.shortname, c.idnumber
        ORDER BY c.idnumber DESC
        "
    );
    $traducao = [
        'FIC.1198' => [
            'course_alias' => 'PEDRA DA UNIDADE',
            'course_subtitle' => 'Viaje até o Leste Potiguar para obtê-la',
            'stone_color' => '47, 109, 246'
        ],
        'FIC.1197' => [
            'course_alias' => 'PEDRA DA HARMONIA',
            'course_subtitle' => 'Viaje até o Agreste Potiguar para obtê-la',
            'stone_color' => '242, 183, 34'
        ],
        'FIC.1196' => [
            'course_alias' => 'PEDRA DA LÓGICA',
            'course_subtitle' => 'Viaje até o Oeste Potiguar para obtê-la',
            'stone_color' => '62, 193, 52'
        ],
        'FIC.1195' => [
            'course_alias' => 'PEDRA DA COMUNICAÇÃO',
            'course_subtitle' => 'Viaje até a Central Potiguar para obtê-la',
            'stone_color' => '253, 35, 217'
        ],
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
        $course->jornada = $course->disciplina === 'FIC.1198';
        $course->completion_percentage = 0;
        if ($course->total_modules > 0 && $course->completed_modules > 0) {
            $course->completion_percentage = intval(floatval($course->completed_modules) / floatval($course->total_modules) * 100);
        }
        $course->iniciada = $course->completion_percentage > 0;
        // $course->concluida = $course->course_grade >= 60 && $course->completion_percentage == 100;
        $course->concluida = $course->completion_percentage == 100;

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
 *        "jornada": {
 *          "course_id": 0,
 *          "course_idnumber": "20242.1.527.1E.FIC.1197#564322",
 *          "course_fullname": "Seminário de Integração	",
 *          "course_shortname": "20242.1.527.1E.FIC.1197#564322"
 *          "course_alias": "...",
 *          "course_subtitle": "...",
 *          "course_image_url": "...",
 *          "total_modules": 0,
 *          "completed_modules": 0,
 *          "completion_percentage": 0,
 *          "disciplina": "FIC.1198",
 *          "iniciada": 0,
 *          "concluida": 0,
 *        },
 *        "etica": {
 *          "course_id": 0,
 *          "course_idnumber": "20242.1.527.1E.FIC.1197#564322",
 *          "course_fullname": "Ética e Cidadania",
 *          "course_shortname": "20242.1.527.1E.FIC.1197#564322"
 *          "course_alias": "...",
 *          "course_subtitle": "...",
 *          "course_image_url": "...",
 *          "total_modules": 0,
 *          "completed_modules": 0,
 *          "completion_percentage": 0,
 *          "disciplina": "FIC.1197",
 *          "iniciada": 0,
 *          "concluida": 0,
 *        },
 *        "matematica": {
 *          "course_id": 0,
 *          "course_idnumber": "20242.1.527.1E.FIC.1196#564322",
 *          "course_fullname": "Matemática",
 *          "course_shortname": "20242.1.527.1E.FIC.1196#564322"
 *          "course_alias": "...",
 *          "course_subtitle": "...",
 *          "course_image_url": "...",
 *          "total_modules": 0,
 *          "completed_modules": 0,
 *          "completion_percentage": 0,
 *          "disciplina": "FIC.1196",
 *          "iniciada": 0,
 *          "concluida": 0,
 *        },
 *        "portugues": {
 *          "course_id": 0,
 *          "course_idnumber": "20242.1.527.1E.FIC.1195#564322",
 *          "course_fullname": "Língua Portuguesa",
 *          "course_shortname": "20242.1.527.1E.FIC.1195#564322"
 *          "course_alias": "...",
 *          "course_subtitle": "...",
 *          "course_image_url": "...",
 *          "total_modules": 0,
 *          "completed_modules": 0,
 *          "completion_percentage": 0,
 *          "disciplina": "FIC.1195",
 *          "iniciada": 0,
 *          "concluida": 0,
 *        },
 *     }
 * 
 * @return array An array containing the status of each course.
 */
function get_courses_progress_as_dict()
{
    global $CFG, $COURSE;

    $matrix_curricular = [
        "FIC.1198" => ["curso" => "Seminário de Integração", "key" => "jornada"],
        "FIC.1197" => ["curso" => "Ética e Cidadania", "key" => "etica"],
        "FIC.1196" => ["curso" => "Matemática", "key" => "matematica"],
        "FIC.1195" => ["curso" => "Língua Portuguesa", "key" => "portugues"],
    ];

    $courses = get_courses_progress_as_list();

    $CURSO_NULO = (object)[
        "course_id" => 0,
        "course_idnumber" => "Curso não encontrado",
        "course_fullname" => "Curso não encontrado",
        "course_shortname" => "Curso não encontrado",
        "course_alias" => "Curso não encontrado",
        "course_subtitle" => "Curso não encontrado",
        "course_image_url" => "{$CFG->wwwroot}/mod/medalhasproitec/pix/curso_nao_encontrado.png",
        "total_modules" => 0,
        "completed_modules" => 0,
        "completion_percentage" => 0,
        "nota_curso" => 0,
        "concluida" => false,
        "jornada" => false,
    ];

    $courses_statuses = ['jornada' => $CURSO_NULO, 'etica' => $CURSO_NULO, 'matematica' => $CURSO_NULO, 'portugues' => $CURSO_NULO];

    foreach ($courses as $course) {
        if (isset($matrix_curricular[$course->disciplina])) {
            $courses_statuses[$matrix_curricular[$course->disciplina]['key']] = $course;
        }
    }

    $courses_statuses["courses"] = $courses;
    $courses_statuses["isjourney"] = get_disciplina_from_idnumber($COURSE->idnumber) == CODIGO_DISCIPLINA_JORNADA;

    return $courses_statuses;
}


/**
 * Obtém o total de atividades de um tipo específico em um curso e quantas delas foram concluídas por um usuário.
 *
 * @param int $courseid ID do curso.
 * @param string $modulename Nome do módulo (como definido em mdl_modules.name).
 * @param int $userid ID do usuário.
 * @return stdClass Objeto contendo as propriedades 'total' (total de módulos com conclusão)
 *                  e 'completed' (quantos foram concluídos pelo usuário).
 */
function get_course_module_type_completion(int $courseid, string $modulename, int $userid): stdClass
{
    global $DB;

    $sql = "
        SELECT COUNT(cm.id) AS total,
               COUNT(mc.id) FILTER (WHERE mc.completionstate = 1) AS completed
          FROM {course_modules} cm
          JOIN {modules} m ON m.id = cm.module
     LEFT JOIN {course_modules_completion} mc ON mc.coursemoduleid = cm.id AND mc.userid = :userid
         WHERE cm.course = :courseid
           AND m.name = :modulename
           AND cm.completion > 0
    ";

    return $DB->get_record_sql($sql, [
        'courseid'   => $courseid,
        'modulename' => $modulename,
        'userid'     => $userid
    ]);
}


/**
 * Verifica se o usuário atual concluiu todos os módulos do tipo passado por parâmetro em todos os cursos.
 *
 * Retorna false assim que encontrar um curso com pelo menos um vídeo interativo não concluído.
 * Caso contrário, retorna true, indicando que todos foram concluídos.
 *
 * @global moodle_database $DB Instância global do banco de dados.
 * @global stdClass $USER Objeto global do usuário logado.
 * @return bool True se todos os módulos foram concluídos; false caso contrário.
 */
function has_completed_all_modules_type(string $modulename): bool
{
    global $USER;

    $courses = get_courses_progress_as_list();

    foreach ($courses as $course) {
        $result = get_course_module_type_completion(
            $course->course_id,
            $modulename,
            $USER->id
        );

        if ($result && $result->total > 0 && $result->completed < $result->total) {
            return false;
        }
    }

    return true;
}


/**
 * Verifica se o usuário acertou pelo menos 50% em todos os questionários de todos os curso.
 *
 * @param int $courseid ID do curso.
 * @param int|null $userid ID do usuário (opcional, padrão: usuário logado).
 * @return bool True se acertou pelo menos 50%, false caso contrário.
 */
function check_all_quizzes_minimum_score($minscore = 50): bool
{
    global $DB, $USER;

    if (!has_completed_all_modules_type('quiz')) {
        return false;
    }

    $courses = get_courses_progress_as_list();

    foreach ($courses as $course) {
        // Busca todos os quizzes do curso
        $quizzes = $DB->get_records_sql("
            SELECT q.id, q.grade
            FROM {quiz} q
            JOIN {course_modules} cm ON cm.instance = q.id
            JOIN {modules} m ON m.id = cm.module
            WHERE q.course = :courseid
              AND m.name = 'quiz'
        ", ['courseid' => $course->course_id]);

        foreach ($quizzes as $quiz) {
            $grade = $DB->get_record('quiz_grades', [
                'quiz' => $quiz->id,
                'userid' => $USER->id
            ]);

            if (!$grade || $quiz->grade == 0) {
                return false;
            }

            $percent = ($grade->grade / $quiz->grade) * 100;

            if ($percent < $minscore) {
                return false;
            }
        }
    }

    return true;
}


/**
 * Verifica se o usuário atual concluiu ao menos uma atividade H5P
 * em todos os cursos nos quais está matriculado.
 *
 * A função percorre os cursos e verifica se há ao menos um módulo 
 * do tipo 'h5pactivity' ou 'hvp' concluído.
 *
 * @return bool True se o usuário concluiu ao menos uma atividade H5P, false caso contrário.
 */
function at_least_read_one_book(): bool
{
    global $USER;

    $courses = get_courses_progress_as_list();

    foreach ($courses as $course) {
        // Se concluiu ao menos um módulo do tipo 'h5pactivity'
        $h5pactivity = get_course_module_type_completion(
            $course->course_id,
            'h5pactivity',
            $USER->id
        );

        if ($h5pactivity && isset($h5pactivity->completed) && (int) $h5pactivity->completed > 0) {
            return true;
        }

        // Se concluiu ao menos um módulo do tipo 'hvp'
        $hvp = get_course_module_type_completion(
            $course->course_id,
            'hvp',
            $USER->id
        );

        if ($hvp && isset($hvp->completed) && (int) $hvp->completed > 0) {
            return true;
        }
    }
    return false;
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
    $courses = get_courses_progress_as_dict();

    // Pega todos os slugs de medalha já mostrados para o usuário
    $shownrecords = $DB->get_records_menu('medalhasproitec_shown_modal', ['userid' => $USER->id], '', 'medalhaid, medalhaid');

    $insignias = [
        'sentinela_do_codex' => (object)[
            'tem' => $courses['jornada']->completion_percentage > 0,
            'ja_mostrou_popup' => array_key_exists('sentinela_do_codex', $shownrecords),
            'title' => 'Sentinela do Codex',
            'description' => '...',
            'popup' => 'Você acabou de obter o Codex. Sua jornada começa agora.',
        ],
        'maratonista_do_conhecimento' => (object)[
            'tem' => has_completed_all_modules_type('interactivevideo'),
            'ja_mostrou_popup' => array_key_exists('maratonista_do_conhecimento', $shownrecords),
            'title' => 'Maratonista do Conhecimento',
            'description' => '...',
            'popup' => 'Parabéns, você assistiu a todas as videoaulas!',
        ],
        'busca_pelo_saber' => (object)[
            'tem' => at_least_read_one_book(),
            'ja_mostrou_popup' => array_key_exists('busca_pelo_saber', $shownrecords),
            'title' => 'Busca pelo Saber',
            'description' => '...',
            'popup' => 'Seu primeiro livro foi concluído. Que venham os próximos...',
        ],
        'mestre_do_portal' => (object)[
            'tem' => check_all_quizzes_minimum_score(),
            'ja_mostrou_popup' => array_key_exists('mestre_do_portal', $shownrecords),
            'title' => 'Mestre do Portal',
            'description' => '...',
            'popup' => 'Missão cumprida. Chegou a hora de abrir o portal!',
        ],
        'amante_dos_numeros' => (object)[
            'tem' => $courses['matematica']->concluida,
            'ja_mostrou_popup' => array_key_exists('amante_dos_numeros', $shownrecords),
            'title' => 'Amante dos Números',
            'description' => '...',
            'popup' => 'Você concluiu o módulo de matemática. Pitágoras estaria orgulhoso.',
        ],
        'amante_das_palavras' => (object)[
            'tem' => $courses['portugues']->concluida,
            'ja_mostrou_popup' => array_key_exists('amante_das_palavras', $shownrecords),
            'title' => 'Amante das Palavras',
            'description' => '...',
            'popup' => 'Seu português afiado vai te levar longe.',
        ],
        'orgulho_da_comunidade' => (object)[
            'tem' => $courses['etica']->concluida,
            'ja_mostrou_popup' => array_key_exists('orgulho_da_comunidade', $shownrecords),
            'title' => 'Orgulho da Comunidade',
            'description' => '...',
            'popup' => 'Você é uma pessoa exemplar. Continue assim.',
        ],
        'entusiasta_do_ifrn' => (object)[
            'tem' => $courses['jornada']->concluida,
            'ja_mostrou_popup' => array_key_exists('entusiasta_do_ifrn', $shownrecords),
            'title' => 'Entusiasta do IFRN',
            'description' => '...',
            'popup' => 'Você já está com um pé dentro do IFRN.',
        ],
        'isstudent' => user_has_role_assignment($USER->id, 5, context_course::instance($COURSE->id)->id),
    ];

    return array_merge($insignias, $courses);
}