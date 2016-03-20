<?php

namespace App;

use \SpaceWeb\Quest\QuestAbstract;

class App extends QuestAbstract
{
    private $_sourceOptions = [];

    private $_dateStart;
    private $_dateFinish;

    public function setSourceOptions(array $sourceOptions)
    {
        $this->_sourceOptions = $sourceOptions;
    }

    private function _checkdate($date)
    {
        $dateParsed = date_parse_from_format('Y-m-d', $date);
        if (!checkdate($dateParsed['month'], $dateParsed['day'], $dateParsed['year'])) {
            return false;
        }
        return true;
    }

    public function setDateStart($date)
    {
        return $this->_checkdate($date) && ($this->_dateStart = $date);
    }

    public function setDateFinish($date)
    {
        return $this->_checkdate($date) && ($this->_dateFinish = $date);
    }

    public function getPayments()
    {

        $arrPayments = [];

        foreach ($this->_sourceOptions as $sourceOption => $val) {
            switch ($sourceOption) {

                case 'with-documents': {
                    $sth = $this->getDb()->prepare('
                      SELECT COUNT(`payments`.`id`) AS `count`, SUM(`payments`.`amount`) AS `amount`
                      FROM `documents`
                      LEFT JOIN `payments` ON `payments`.`id` = `documents`.`entity_id`
                      WHERE `documents`.`id` IS NOT NULL
                      AND `payments`.`create_ts` >= :dateStart
                      AND `payments`.`finish_time` <= :dateFinish
                    ');
                    $sth->execute([':dateStart' => $this->_dateStart, ':dateFinish' => $this->_dateFinish]);
                    array_push($arrPayments, $sth->fetch(\PDO::FETCH_ASSOC));
                    unset($sth);
                    break;
                }

                case 'without-documents': {
                    $sth = $this->getDb()->prepare('
                      SELECT COUNT(`payments`.`id`) AS `count`, SUM(`payments`.`amount`) AS `amount`
                      FROM `payments`
                      LEFT JOIN `documents` ON `documents`.`entity_id` = `payments`.`id`
                      WHERE `documents`.`entity_id` IS NULL
                      AND `payments`.`create_ts` >= :dateStart
                      AND `payments`.`finish_time` <= :dateFinish
                    ');
                    $sth->execute([':dateStart' => $this->_dateStart, ':dateFinish' => $this->_dateFinish]);
                    array_push($arrPayments, $sth->fetch(\PDO::FETCH_ASSOC));
                    unset($sth);
                    break;
                }

                default: break;
            }

        }

        return $arrPayments;

    }

}