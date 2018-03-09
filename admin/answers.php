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

class iaBackendController extends iaAbstractControllerModuleBackend
{
    protected $_name = 'answers';
    protected $_path = 'answers';
    protected $_itemName = 'quiz_answer';

    protected $_helperName = 'answers';

    protected $_tooltipsEnabled = true;

    protected $_activityLog = ['item' => 'answers'];

    protected $_iaQuestions;


    public function init()
    {
        $this->_iaQuestions = $this->_iaCore->factoryModule('questions', $this->getModuleName(), iaCore::ADMIN);

        if (iaView::REQUEST_HTML == $this->_iaCore->iaView->getRequestType()) {
            iaBreadcrumb::insert(iaLanguage::get('quizzes'), IA_ADMIN_URL . 'quiz/');
        }
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        if (!empty($params['text'])) {
            $langCode = $this->_iaCore->language['iso'];
            $conditions[] = "(a.`title_{$langCode}` LIKE :text)";
            $values['text'] = '%' . iaSanitize::sql($params['text']) . '%';
        }

        if (!empty($params['question'])) {
            $langCode = $this->_iaCore->language['iso'];
            $conditions[] = "(qs.`title_{$langCode}` LIKE :question OR qs.`body_{$langCode}` LIKE :question)";
            $values['question'] = '%' . iaSanitize::sql($params['question']) . '%';
        }

        if (!empty($params['status'])) {
            $conditions[] = "(a.`status` = :status)";
            $values['status'] = $params['status'];
        }
    }

    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {
        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS a.`id`, a.`title_:lang` `title`, a.`date_added`, a.`status`, qs.`title_:lang` `question`, 1 `update`, 1 `delete`, `correct_answer`
FROM `:prefix:table_answers` a
LEFT JOIN `:prefix:table_questions` qs ON (a.`question_id` = qs.`id`)
WHERE :where :order
LIMIT :start, :limit
SQL;

        $sql = iaDb::printf($sql, [
            'prefix' => $this->_iaDb->prefix,
            'table_answers' => $this->getTable(),
            'table_questions' => $this->getHelper()->getQuestionsTable(),
            'lang' => $this->_iaCore->language['iso'],
            'where' => $where ? $where : iaDb::EMPTY_CONDITION,
            'order' => $order,
            'start' => $start,
            'limit' => $limit
        ]);

        return $this->_iaDb->getAll($sql);
    }

    protected function _setDefaultValues(array &$entry)
    {
        $entry = [
            'member_id' => iaUsers::getIdentity()->id,
            'status' => iaCore::STATUS_ACTIVE,
        ];
    }

    protected function _preSaveEntry(array &$entry, array $data, $action)
    {
        parent::_preSaveEntry($entry, $data, $action);

        if (empty($data['question_id'])) {
            $this->addMessage('invalid_question');
        } else {
            $entry['quiz_id'] = $this->getHelper()->getAnswerByQuestionId((int)$data['question_id']);
            $entry['question_id'] = (int)$data['question_id'];
        }

        if ($data['correct_answer'] && !empty($data['question_id'])) {
            $this->_iaDb->update(['correct_answer' => 0], 'question_id = ' . (int)$data['question_id'], null, $this->getTable());
            $entry['correct_answer'] = (int)$data['correct_answer'];
        }

        return !$this->getMessages();
    }

    protected function _entryUpdate(array $entryData, $entryId)
    {
        $entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);

        return parent::_entryUpdate($entryData, $entryId);
    }

    protected function _entryAdd(array $entryData)
    {
        $entryData['date_added'] = date(iaDb::DATETIME_FORMAT);
        $entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);

        return parent::_entryAdd($entryData);
    }

    protected function _assignValues(&$iaView, array &$entryData)
    {
        $entryData['questions'] = $this->_iaQuestions->getAll();

        return parent::_assignValues($iaView, $entryData);
    }
}
