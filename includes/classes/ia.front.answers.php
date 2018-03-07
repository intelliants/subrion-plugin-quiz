<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2018 Intelliants, LLC <https://intelliants.com>
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

class iaAnswers extends abstractModuleFront
{
    protected static $_table = 'quizzes_answers';

    protected $_itemName = 'quiz_answer';


    public function get($where)
    {
        $where = !empty($where) ? $where . ' AND ' : '';
        $where.= 'a.`status` = "active"';

        $sql = <<<SQL
SELECT a.`id`, a.`title_:lang`, a.`body_:lang`, a.`clicks_num`, a.`correct_answer`,
  (SELECT SUM(a.`clicks_num`) FROM `:prefix:table_answers` a WHERE :where) `sum_clicks_num`
FROM `:prefix:table_answers` a
WHERE :where :order
SQL;

        $sql = iaDb::printf($sql, [
            'lang' => $this->iaCore->language['iso'],
            'prefix' => $this->iaDb->prefix,
            'table_answers' => self::$_table,
            'where' => $where,
            'order' => 'ORDER BY `date_added` ' . iaDb::ORDER_ASC,
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
            $row['stats'] = $row['clicks_num'] ? round($row['clicks_num'] / $row['sum_clicks_num'] * 100) : 0;
        }

        $singleRow && $rows = array_shift($rows);
    }
}
