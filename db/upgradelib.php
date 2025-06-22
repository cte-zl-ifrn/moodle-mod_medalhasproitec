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
 * Plugin upgrade helper functions are defined here.
 *
 * @package     mod_medalhasproitec
 * @category    upgrade
 * @copyright   2025 DEAD/ZL/IFRN <dead.zl@ifrn.edu.br>, Kelson da Costa Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Helper function used by the upgrade.php file.
 */
function mod_medalhasproitec_install_and_upgrade_tables($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.

    $dbman = $DB->get_manager();
    if (!$dbman->table_exists('medalhasproitec')) {

        // Define a nova tabela medalhasproitec.
        $table = new xmldb_table('medalhasproitec');

        // Adiciona os campos.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('intro', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NULL, null, null);
        $table->add_field('introformat', XMLDB_TYPE_INTEGER, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Define a chave primária.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Cria a tabela se ainda não existir.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Marca o upgrade como completo.
        upgrade_mod_savepoint(true, 2025_06_21_12, 'medalhasproitec');
    }

    if (!$dbman->table_exists('medalhasproitec_shown_modal')) {

        // Define a nova tabela medalhasproitec_shown_modal.
        $table = new xmldb_table('medalhasproitec_shown_modal');

        // Adiciona os campos.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('medalhaid', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Define a chave primária.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Define chave única para evitar duplicação.
        $table->add_key('usermedalha_unique', XMLDB_KEY_UNIQUE, ['userid', 'medalhaid']);

        // Cria a tabela se ainda não existir.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Marca o upgrade como completo.
        upgrade_mod_savepoint(true, 2025_06_21_12, 'medalhasproitec');
    }

    return true;
}