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

class iaQuestions extends abstractModuleFront
{
    protected static $_table = 'quizzes_questions';
    protected $_answersTable = 'quizzes_answers';

    protected $_itemName = 'quizzes_questions';

    protected $_iaAnswers;


    public function init()
    {
        parent::init();

        $this->_iaAnswers = $this->iaCore->factoryModule('answers', 'quiz', iaCore::FRONT);
    }

    public function get($where, $limit = null)
    {
        $where = !empty($where) ? $where . ' AND ' : '';
        $where.= 'qs.`status` = "active"';

        $sql = <<<SQL
SELECT qs.`id`, qs.`title_:lang`, qs.`body_:lang`, qs.`pictures`
FROM `:prefix:table_questions` qs
WHERE :where :order :limit
SQL;

        $sql = iaDb::printf($sql, [
            'lang' => $this->iaCore->language['iso'],
            'prefix' => $this->iaDb->prefix,
            'table_questions' => self::$_table,
            'where' => $where,
            'order' => 'ORDER BY `date_added` ' . iaDb::ORDER_ASC,
            'limit' => !empty($limit) ? "LIMIT {$limit}" : ''
        ]);

        $rows = $this->iaDb->getAll($sql);

        $this->_processValues($rows);

        return $rows;
    }

    protected function _processValues(&$rows, $singleRow = false, $fieldNames = [])
    {
        parent::_processValues($rows, $singleRow, $fieldNames);

        $singleRow && $rows = [$rows];

        foreach ($rows as &$row) {
            $row['answers'] = $this->_iaAnswers->get("a.`question_id` = {$row['id']}");
        }

        $singleRow && $rows = array_shift($rows);
    }
}
