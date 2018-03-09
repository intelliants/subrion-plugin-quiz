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
    protected $_name = 'questions';
    protected $_path = 'questions';
    protected $_itemName = 'quiz_question';

    protected $_helperName = 'questions';

    protected $_tooltipsEnabled = true;

    protected $_activityLog = ['item' => 'questions'];

    protected $_iaQuizzes;

    public function init()
    {
        $this->_iaQuizzes = $this->_iaCore->factoryModule('quizzes', $this->getModuleName(), iaCore::ADMIN);

        if (iaView::REQUEST_HTML == $this->_iaCore->iaView->getRequestType()) {
            iaBreadcrumb::insert(iaLanguage::get('quizzes'), IA_ADMIN_URL . 'quiz/', iaBreadcrumb::POSITION_FIRST + 1);
        }
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        if (!empty($params['text'])) {
            $langCode = $this->_iaCore->language['iso'];
            $conditions[] = "(qs.`title_{$langCode}` LIKE :text)";
            $values['text'] = '%' . iaSanitize::sql($params['text']) . '%';
        }

        if (!empty($params['quiz'])) {
            $langCode = $this->_iaCore->language['iso'];
            $conditions[] = "(qz.`title_{$langCode}` LIKE :quiz OR qz.`body_{$langCode}` LIKE :quiz)";
            $values['quiz'] = '%' . iaSanitize::sql($params['quiz']) . '%';
        }

        if (!empty($params['status'])) {
            $conditions[] = "(qs.`status` = :status)";
            $values['status'] = $params['status'];
        }
    }

    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {
        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS qs.`id`, qs.`title_:lang` `title`, qs.`date_added`, qs.`status`, qz.`title_:lang` `quiz`, 1 `update`, 1 `delete`,
  (SELECT COUNT(*) FROM `:prefix:table_answers` WHERE `question_id` = qs.`id`) `answers_num`
FROM `:prefix:table_questions` qs
LEFT JOIN `:prefix:table_quizzes` qz ON (qs.`quiz_id` = qz.`id`)
WHERE :where :order
LIMIT :start, :limit
SQL;

        $sql = iaDb::printf($sql, [
            'prefix' => $this->_iaDb->prefix,
            'table_questions' => $this->getTable(),
            'table_quizzes' => $this->getHelper()->getQuizzesTable(),
            'table_answers' => $this->getHelper()->getAnswersTable(),
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

        if (empty($data['quiz_id'])) {
            $this->addMessage('invalid_quiz');
        } else {
            $entry['quiz_id'] = (int)$data['quiz_id'];
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
        $entryData['quizzes'] = $this->_iaQuizzes->getAll();

        return parent::_assignValues($iaView, $entryData);
    }
}
