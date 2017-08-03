<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2017 Intelliants, LLC <https://intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link https://subrion.org/
 *
 ******************************************************************************/

class iaAnswers extends abstractModuleAdmin
{
    protected static $_table = 'quizzes_answers';
    protected $_quizzesTable = 'quizzes';
    protected $_questionsTable = 'quizzes_questions';

    protected $_itemName = 'quizzes_answers';


    public function getQuizzesTable()
    {
        return $this->_quizzesTable;
    }

    public function getQuestionsTable()
    {
        return $this->_questionsTable;
    }

    public function getAnswerByQuestionId($id)
    {
        if (!$id) {
            return false;
        }

        return $this->iaDb->one('quiz_id', iaDb::convertIds($id), $this->_questionsTable);
    }
}
