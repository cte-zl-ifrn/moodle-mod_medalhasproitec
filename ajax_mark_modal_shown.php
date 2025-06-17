<?php
// ajax_mark_modal_shown.php

require(__DIR__ . '/../../config.php');
require_login();

require_once($CFG->libdir . '/moodlelib.php');

// Define o tipo da resposta como JSON.
header('Content-Type: application/json');

$medalhaid = required_param('medalhaid', PARAM_ALPHANUMEXT); // Ex: 'sentinela_do_codex'
$userid = $USER->id;

// Validação simples
if (!$medalhaid) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetro medalhaid ausente']);
    exit;
}

// Verifica se já foi registrado
global $DB;
$exists = $DB->record_exists('medalhasproitec_shown_modal', [
    'userid' => $userid,
    'medalhaid' => $medalhaid,
]);

if (!$exists) {
    $DB->insert_record('medalhasproitec_shown_modal', (object)[
        'userid' => $userid,
        'medalhaid' => $medalhaid,
        'timecreated' => time(),
    ]);
}

echo json_encode(['success' => true]);
