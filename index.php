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

$iaQuizzes = $iaCore->factoryModule('quizzes', 'quiz');

if (iaView::REQUEST_JSON == $iaView->getRequestType()) {
    if (isset($_GET['action']) && !empty($_GET['action'])) {
        switch ($_GET['action']) {
            case 'update-clicks-num':
                $id = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : 0;
                $question_id = 0;

                $id && $question_id = $iaDb->one('`question_id`', iaDb::convertIds($id), $iaQuizzes->getAnswersTable());

                $sql = <<<SQL
UPDATE `:prefix:table` SET `clicks_num` = `clicks_num` + 1 WHERE `id` = :id
SQL;
                $sql = iaDb::printf($sql, [
                    'prefix' => $iaDb->prefix,
                    'table' => $iaQuizzes->getAnswersTable(),
                    'id' => $id
                ]);

                $iaDb->query($sql);

                $sql = <<<SQL
SELECT `id`, `clicks_num`,
  (SELECT SUM(`clicks_num`) FROM `:prefix:table` WHERE `question_id` = :question_id AND `status` = "active") `sum_clicks_num`
FROM `:prefix:table` WHERE `question_id` = :question_id AND `status` = "active"
SQL;
                $sql = iaDb::printf($sql, [
                    'prefix' => $iaDb->prefix,
                    'table' => $iaQuizzes->getAnswersTable(),
                    'question_id' => $question_id
                ]);

                $rows = $iaDb->getAll($sql);

                if (!empty($rows)) {
                    foreach ($rows as &$row) {
                        $row['stats'] = $row['clicks_num'] ? round($row['clicks_num'] / $row['sum_clicks_num'] * 100) : 0;
                        unset($row['clicks_num'], $row['sum_clicks_num']);
                    }
                }

                $iaView->assign($rows);

                break;

            case 'load-question':
                $iaView->loadSmarty(true);
                $iaSmarty = &$iaView->iaSmarty;

                $id = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : 0;

                $iaQuestions = $iaCore->factoryModule('questions', 'quiz');

                if ($entry['question'] = $iaQuestions->getById($id)) {
                    $next_question_id = $iaDb->one(iaDb::ID_COLUMN_SELECTION, "`id` > {$entry['question']['id']} AND `quiz_id` = {$entry['id']}", $iaQuizzes->getQuestionsTable());
                    $next_question_id || $next_question_id = 0;

                    $entry['id'] = $entry['question']['quiz_id'];
                    $entry['question']['next_id'] = $next_question_id ? $next_question_id : 0;

                    $iaSmarty->assign('entry', $entry);
                    $output = $iaSmarty->fetch('extra:quiz/question');

                    $iaView->assign([
                        'html' => $output
                    ]);
                }
        }
    }
}

if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    $iaDb->setTable('quizzes');

    if (isset($iaCore->requestPath[0]) && (int)$iaCore->requestPath[0]) {
        $id = $iaCore->requestPath[0];

        if (!$id) {
            return iaView::errorPage(iaView::ERROR_NOT_FOUND);
        }

        $iaUsers = $this->factory('users');

        $entry = $iaQuizzes->getById($id, true);
        $member = $iaUsers->getById($entry['member_id']);

        $entry['fullname'] = $member['fullname'];

        if (empty($entry)) {
            return iaView::errorPage(iaView::ERROR_NOT_FOUND);
        }

        iaBreadcrumb::toEnd($entry['title'], IA_SELF);

        $openGraph = [
            'title' => $entry['title'],
            'url' => IA_SELF,
            'description' => iaSanitize::tags($entry['body'])
        ];

        $entry['pictures'] && $openGraph['image'] = IA_CLEAR_URL . 'uploads/' . $entry['pictures'][0]['path'] . 'large/' . $entry['pictures'][0]['file'];

        $iaView->set('og', $openGraph);

        if ($question = $iaQuizzes->getQuestionsByQuizId($entry['id'], 1)) {
            $entry['question'] = array_shift($question);

            $next_question_id = $iaDb->one(iaDb::ID_COLUMN_SELECTION, "`id` > {$entry['question']['id']} AND `quiz_id` = {$entry['id']}", $iaQuizzes->getQuestionsTable());
            $next_question_id || $next_question_id = 0;

            $entry['question']['next_id'] = $next_question_id ? $next_question_id : 0;
        }

        $iaView->assign('entry', $entry);

        $iaView->title(iaSanitize::tags($entry['title']));
    } elseif (isset($iaCore->requestPath[0]) && $iaCore->requestPath[0] == 'finish' && isset($iaCore->requestPath[1]) && (int)$iaCore->requestPath[1]) {
        $id = $iaCore->requestPath[1];

        $entry = $iaQuizzes->getById($id);

        if (!isset($_COOKIE['quiz_id']) || $_COOKIE['quiz_id'] !== $id) {
            return iaView::errorPage(iaView::ERROR_NOT_FOUND);
        }

        $openGraph = [
            'title' => $entry['title'],
            'description' => iaSanitize::tags($entry['quiz_completion_text'])
        ];

        $entry['pictures'] && $openGraph['image'] = IA_CLEAR_URL . 'uploads/' . $entry['pictures'][0]['path'] . 'large/' . $entry['pictures'][0]['file'];

        $iaView->set('og', $openGraph);
        $iaView->title($entry['title']);

        $total = $iaDb->one('COUNT(*)', "`quiz_id` = {$entry['id']}", $iaQuizzes->getQuestionsTable());
        $total || $total = 0;

        $iaUsers = $this->factory('users');
        $member = $iaUsers->getById($entry['member_id']);

        $entry['evaluation'] = "{$_COOKIE['correct_answers_num']}/{$total}";
        $entry['fullname'] = $member['fullname'];

        $iaView->assign('result', $entry);
    } else {
        $pagination = [
            'total' => 0,
            'limit' => (int)$iaCore->get('quizzes_number'),
            'url' => $iaCore->factory('page', iaCore::FRONT)->getUrlByName('quizzes') . '?page={page}'
        ];

        $page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
        $start = ($page - 1) * $pagination['limit'];

        $where = iaDb::EMPTY_CONDITION;
        $direction = iaDb::ORDER_DESC;

        $iaCore->get('quizzes_direction') == 'Ascending' && $direction = iaDb::ORDER_ASC;

        switch ($iaCore->get('quizzes_order')) {
            case 'Alphabetic':
                $order = "`title_{$iaCore->language['iso']}` " . $direction;
                break;

            case 'Order':
                $order = '`order` ' . $direction;
                break;

            default:
                $order = '`date_added` ' . $direction;
        }

        $rows = $iaQuizzes->get($where, $start, $pagination['limit'], $order);

        $pagination['total'] = $iaQuizzes->getFoundRows();

        $iaView->assign('page', $page);
        $iaView->assign('entries', $rows);
        $iaView->assign('pagination', $pagination);
    }

    $iaView->display('index');

    $iaDb->resetTable();
}