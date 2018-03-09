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

class iaQuizzes extends abstractModuleAdmin
{
    protected static $_table = 'quizzes';
    protected $_questionsTable = 'quizzes_questions';
    protected $_answersTable = 'quizzes_answers';

    protected $_itemName = 'quiz';

    protected $_activityLog = ['item' => 'quiz'];

    public $dashboardStatistics = ['_format' => 'small', 'icon' => 'folder', 'url' => 'quizzes/quizzes/'];

    private $_urlPatterns = [
        'view' => ':basequizzes/:id-:slug.html',
    ];


    public function getQuestionsTable()
    {
        return $this->_questionsTable;
    }

    public function getAnswersTable()
    {
        return $this->_answersTable;
    }

    public function url($action, $params)
    {
        $params['base'] = IA_URL_DELIMITER != $this->getInfo('url') ? $this->getInfo('url') : '';

        return iaDb::printf($this->_urlPatterns[$action], $params);
    }

    public function getAll($where = null, $fields = null, $start = null, $limit = null)
    {
        $where = $where ? $where . ' AND ' : '' . 'status = "active"';
        $rows = $this->iaDb->all(iaDb::ALL_COLUMNS_SELECTION, $where, 0, null, self::$_table);

        $this->_processValues($rows);

        return $rows;
    }
}
