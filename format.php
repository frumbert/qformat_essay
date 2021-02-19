<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * essay format question importer.
 *
 * @package    qformat_essay
 * @copyright  2021 Tim St. Clair <tim.stclair@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * essay format - a simple format for creating questions from a text file
 *
 * Each line in the text file becomes a new short-answer question.
 * The format looks like this:
 *
 * One. Explain how a curtain rod works.
 * Two. In 100 words or less explain the purpose of a door
 *
 * That is,
 *  + question text all one one line. \n will be converted to <br>
 *    the question name precedes the first period (.)
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_essay extends qformat_default {

    public function provide_import() {
        return true;
    }

    public function provide_export() {
        return true;
    }

    public function readquestions($lines) {
        $questions = array();

        foreach ($lines as $line) {

            // no period? can't process this line.
            if (!strpos($line,'.')) {
                continue;
            }

            // split line at first period.
            $split = explode('.', $line, 2);


            // behind period: name; after period: question
            $name = $split[0];
            $prompt = $split[1];

            // set up required question characteristics
            $question = $this->defaultquestion();
            $question->qtype = 'essay';

            $question->responseformat = 'editor';
            $question->responserequired = 1;
            $question->responsefieldlines = 15;
            $question->attachments = 0;
            $question->attachmentsrequired = 0;
            $question->graderinfo = $this->text_field('');
            $question->responsetemplate = $this->text_field('');
            $question->name = $this->create_default_question_name($name, get_string('questionname', 'question'));
            $question->questiontext = htmlspecialchars(trim($prompt), ENT_NOQUOTES);
            $question->questiontextformat = FORMAT_HTML;
            $question->generalfeedback = '';
            $question->generalfeedbackformat = FORMAT_HTML;
            $question->answer = array();
            $question->fraction = array();
            $question->feedback = array();
            $question->correctfeedback = $this->text_field('');
            $question->partiallycorrectfeedback = $this->text_field('');
            $question->incorrectfeedback = $this->text_field('');

            // good to go
            $questions[] = $question;
        }

        return $questions;
    }

    protected function text_field($text) {
        return array(
            'text' => htmlspecialchars(trim($text), ENT_NOQUOTES),
            'format' => FORMAT_HTML,
            'files' => array(),
        );
    }

    public function readquestion($lines) {
        // This is no longer needed but might still be called by default.php.
        return;
    }

    public function exportpreprocess() {
        // This format is not able to export categories.
        $this->setCattofile(false);
        return true;
    }

    public function writequestion($question) {
        $endchar = "\n";

        // Only export essay questions.
        if ($question->qtype != 'essay') {
            return null;
        }

        // Export the question.
        $expout = $question->name . ". ";
        $expout .= str_replace("\n", '', question_utils::to_plain_text($question->questiontext,
                $question->questiontextformat, array('para' => false, 'newlines' => false))) . $endchar;

        return $expout;
    }
}


