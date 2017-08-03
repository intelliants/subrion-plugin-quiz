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

class iaQuestions extends abstractModuleAdmin
{
    protected static $_table = 'quizzes_questions';
    protected $_quizzesTable = 'quizzes';
    protected $_answersTable = 'quizzes_answers';

    protected $_itemName = 'quizzes_questions';


    public function getQuizzesTable()
    {
        return $this->_quizzesTable;
    }

    public function getAnswersTable()
    {
        return $this->_answersTable;
    }

    public function getAll($where = null)
    {
        $sql = <<<SQL
SELECT qs.`id`, qs.`title_:lang` `title`, qs.`date_added`, qs.`status`, qz.`title_:lang` `quiz`, 1 `update`, 1 `delete`
FROM `:prefix:table_questions` qs
LEFT JOIN `:prefix:table_quizzes` qz ON (qs.`quiz_id` = qz.`id`)
WHERE :where AND qs.status = 'active' AND qz.status = 'active'
SQL;

        $sql = iaDb::printf($sql, [
            'prefix' => $this->iaDb->prefix,
            'table_questions' => self::$_table,
            'table_quizzes' => $this->_quizzesTable,
            'lang' => $this->iaCore->language['iso'],
            'where' => $where ? $where : iaDb::EMPTY_CONDITION
        ]);

        $rows = $this->iaDb->getAll($sql);

        $this->_processValues($rows);

        return $rows;
    }
}
