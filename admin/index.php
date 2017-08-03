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

class iaBackendController extends iaAbstractControllerModuleBackend
{
    protected $_name = 'quizzes';
    protected $_itemName = 'quizzes';

    protected $_helperName = 'quizzes';

    protected $_tooltipsEnabled = true;

    protected $_activityLog = ['item' => 'quizzes'];

    public function init()
    {
        $this->_path = IA_ADMIN_URL . 'quiz' . IA_URL_DELIMITER;
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        if (!empty($params['text'])) {
            $langCode = $this->_iaCore->language['iso'];
            $conditions[] = "(qz.`title_{$langCode}` LIKE :text OR qz.`body_{$langCode}` LIKE :text)";
            $values['text'] = '%' . iaSanitize::sql($params['text']) . '%';
        }

        if (!empty($params['status'])) {
            $conditions[] = "(qz.`status` = :status)";
            $values['status'] = $params['status'];
        }
    }

    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {
        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS qz.`id`, qz.`title_:lang` `title`, qz.`date_added`, qz.`order`, qz.`status`, 1 `update`, 1 `delete`,
  (SELECT COUNT(*) FROM `:prefix:table_questions` WHERE `quiz_id` = qz.`id`) `questions_num`,
  (SELECT COUNT(*) FROM `:prefix:table_answers` WHERE `quiz_id` = qz.`id`) `answers_num`
FROM `:prefix:table_quizzes` qz
WHERE :where :order
LIMIT :start, :limit
SQL;

        $sql = iaDb::printf($sql, [
            'prefix' => $this->_iaDb->prefix,
            'table_quizzes' => $this->getTable(),
            'table_questions' => $this->getHelper()->getQuestionsTable(),
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
            'slug' => '',
            'featured' => false,
            'status' => iaCore::STATUS_ACTIVE,
            'member_id' => iaUsers::getIdentity()->id,
        ];
    }

    protected function _preSaveEntry(array &$entry, array $data, $action)
    {
        parent::_preSaveEntry($entry, $data, $action);

        if (empty($data['title_slug'])) {
            $entry['slug'] = iaSanitize::slug($data['title'][iaLanguage::getMasterLanguage()->code]);
        } else {
            $entry['slug'] = $data['title_slug'];
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

    protected function _getJsonSlug(array $data)
    {
        $title = iaSanitize::slug(isset($data['title']) ? $data['title'] : '');

        $slug = $this->getHelper()->url('view', [
            'id' => empty($data['id']) ? $this->_iaDb->getNextId() : $data['id'],
            'slug' => $title,
        ]);

        return ['data' => $slug];
    }

    protected function _assignValues(&$iaView, array &$entryData)
    {
        $entryData['id'] = $this->getEntryId();

        return parent::_assignValues($iaView, $entryData);
    }
}
