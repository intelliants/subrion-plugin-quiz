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

class iaQuizzes extends abstractModuleFront
{
    protected static $_table = 'quizzes';
    protected $_questionsTable = 'quizzes_questions';
    protected $_answersTable = 'quizzes_answers';

    protected $_itemName = 'quizzes';

    protected $_iaQuestions;

    private $_foundRows = 0;

    public $coreSearchEnabled = false;


    public function init()
    {
        parent::init();

        $this->_iaQuestions = $this->iaCore->factoryModule('questions', 'quiz', iaCore::FRONT);
    }

    public function getQuestionsTable()
    {
        return $this->_questionsTable;
    }

    public function getAnswersTable()
    {
        return $this->_answersTable;
    }

    public function get($where, $start = null, $limit = null, $order = null)
    {
        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS qz.*, m.`fullname`
FROM `:table_quizzes` qz
LEFT JOIN `:table_members` m ON (qz.`member_id` = m.`id`)
WHERE :where :order :limit
SQL;

        $sql = iaDb::printf($sql, [
            'table_quizzes' => self::getTable(true),
            'table_members' => iaUsers::getTable(true),
            'where' => ($where ? $where . ' AND' : '') . " qz.`status` = 'active' ",
            'order' => !empty($order) ? 'ORDER BY ' . $order : '',
            'limit' => $start || $limit ? "LIMIT {$start}, {$limit}" : ''
        ]);

        $rows = $this->iaDb->getAll($sql);

        $this->_foundRows = $this->iaDb->foundRows();
        $this->_processValues($rows);

        return $rows;
    }

    public function getQuestionsByQuizId($id, $limit = null)
    {
        if ($question = $this->_iaQuestions->get("quiz_id = {$id}", $limit)) {
            return $question;
        }

        return false;
    }

    public function getFoundRows()
    {
        return $this->_foundRows;
    }

    protected function _processValues(&$rows, $singleRow = false, $fieldNames = [])
    {
        parent::_processValues($rows, $singleRow, $fieldNames);

        $singleRow && $rows = [$rows];

        foreach ($rows as &$row) {
            $row['questions_num'] = $this->_getQuestionsNumByQuizId($row['id']);
            $row['questions_page'] = (int)$row['questions_num'] ? 1 : 0;
        }

        $singleRow && $rows = array_shift($rows);
    }

    protected function _getQuestionsNumByQuizId($id)
    {
        if (!$id) {
            return false;
        }

        return $this->iaDb->one(iaDb::STMT_COUNT_ROWS, "`quiz_id` = {$id} AND `status` = 'active'",
            $this->_questionsTable);
    }
}
