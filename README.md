# Medalhas Proitec #

TO-DO Describe the plugin shortly here.

TO-DO Provide more detailed description here.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/medalhasproitec

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2025 DEAD/ZL/IFRN <dead.zl@ifrn.edu.br>, Kelson da Costa Medeiros <kelsoncm@gmail.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.


## Regras

| Medalha                        | Critério                                                                                    | Popup |
| ------------------------------ | ------------------------------------------------------------------------------------------- | ---------- | 
| 1. Sentinela do Codex          | Onde `c.jornada == true and c.completion_percentage > 0`                                    | ... a "Sentinela do Codex" por ter acessado este Codex .... |
| 2. Maratonista do Conhecimento | Onde todos os módulos `interactivevideo` de todos os curso desta categoria, esteja completo | ... assistir a todas as video aulas do curso ...  |
| 3. Busca pelo Saber            | Onde ao menos um `h5p`, do tipo book, foi lido em qualquer dos cursos desta categoria       | ... leu ao menos 1 livro ... |
| 4. Mestre do Portal            | Onde acertou ao menos 50% de em todos os questionários  dos cursos desta categoria          | ... tu é o cara ... |
| 5. Amante dos Números          | Onde a completude do curso `FIC.1196` seja `== 100%`                                        | ... NERD!!! ... |
| 6. Amante das Palavras         | Onde a completude do curso `FIC.1195` seja `== 100%`                                        | ... Achamos um Camões!!! ... |
| 7. Orgulho da Comunidade       | Onde a completude do curso `FIC.1197` seja `== 100%`                                        | ... Tá se achando o Sócrates!!! ... |
| 8. Entusiasta do IFRN          | Onde a completude do curso `FIC.1198` seja `== 100%`                                        | ... Bom começo. ... |

- diarios - Diários
  - ZL - Campus
    - 527 - Curso
        - 527.2024.2 - Período da oferta
          - 20242.1.527.1E - Turma
            - **20242.1.527.1E**.`FIC.1198`#*654325* - Seminário de Integração
            - **20242.1.527.1E**.`FIC.1197`#*654321* - Ética e Cidadania
            - **20242.1.527.1E**.`FIC.1196`#*654323* - Matemática
            - **20242.1.527.1E**.`FIC.1195`#*564322* - Língua Portuguesa

