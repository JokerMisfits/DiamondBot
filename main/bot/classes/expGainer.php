<?php

use \Discord\Discord;
use React\Promise\Deferred;

class expGainer extends DiamondBot
{

    private $deferred;

    public function __construct(){
        $this->deferred = new Deferred();
        return $this->deferred->promise();
    }

    public function dbExpGain(Discord $discord){
        $dbExpGain = function () use ($discord){
            $this->dbGetUsersExp($discord)->then(function ($users){
                $mysqli = DiamondBot::mysqlConnection();
                $size = sizeof($users[0]);
                if($size > 0){
                    for($i = 0;$i < $size;$i++){
                        $exp = $users[0][$i];
                        $id = $users[1][$i];
                        $query = "UPDATE users SET account_exp = \"$exp\" WHERE discord_id = $id";
                        if($mysqli->query($query) == false){
                            echo $id . ' ' .$exp . PHP_EOL;
                            echo $mysqli->errno;
                        }
                    }
                }
                return $this->deferred->resolve($users);
            });
        };
        $loop = $discord->__get('loop');
        $loop->addPeriodicTimer(5, $dbExpGain);
        $loop->run();
        return $this->deferred->promise();
    }//Начисление опыта в бд

    private function dbGetUsersExp(Discord $discord){
        return DiamondBot::getOnlineUsers($discord)->then(function ($users) {
            $expBonusRates = $this->dbGetUsersBonusRate($users);
                $size = sizeof($users);
                $exp = [];
                $result = [];
                for($i = 0;$i < $size;$i++){
                    $query = "SELECT account_exp FROM users WHERE discord_id = $users[$i]";
                    $exp[$i] = DiamondBot::prepareQuery($query,'account_exp');
                    $result[$i] = $exp[$i] + ($GLOBALS['expPerMin'] * $expBonusRates[$i]);
                }
                $this->dbCheckLevelUp($result,$users);
                return [$result,$users];
            });
    }//Получает exp онлайн пользователей

    private function dbGetUsersBonusRate($users){
        $size = sizeof($users);
        $expBonusRates = [];
        for($i = 0;$i < $size;$i++){
            $query = "SELECT account_bonus_rate FROM users WHERE discord_id = $users[$i]";
            $expBonusRates[$i] = DiamondBot::prepareQuery($query,'account_bonus_rate');
        }
        return $expBonusRates;
    }//Получает множитель опыта онлайн пользователей

    private function dbCheckLevelUp($exp,$users){
        $size = sizeof($exp);
        $mysqli = DiamondBot::mysqlConnection();
        $maxLevel = $this->getMaxLevel();
        for($i = 0;$i < $size;$i++){
            $query = "SELECT account_level FROM users WHERE discord_id = $users[$i]";
            $level = (int)$this->prepareQuery($query,'account_level');
            if($level < $maxLevel){
                $level++;
                $query = "SELECT exp FROM users_level WHERE level = $level";
                $needExp = $this->prepareQuery($query,'exp');
                if($exp[$i] > $needExp){
                    $query = "UPDATE users SET account_level = $level WHERE discord_id = $users[$i]";
                    if($mysqli->query($query) == false){
                        echo 'Ошибка поднятия уровня пользователя ' . $users[$i] . PHP_EOL;
                    }
                }//Поднятие уровня пользователя
            }
            else{
                $query = "SELECT exp FROM users_level WHERE level = $maxLevel";
                $maxExp = $this->prepareQuery($query,'exp');
                $query = "UPDATE users SET account_exp = $maxExp WHERE discord_id = $users[$i]";
                if($mysqli->query($query) == false){
                    echo 'Ошибка сброса опыта пользователя ' . $users[$i] . PHP_EOL;
                }
            }//Сброс опыта до максимального
        }
    }//Проверка уровней пользователей

    private function getMaxLevel(){
        return (int)DiamondBot::dbGetLastId('users_level') - 1;
    }//Получение максимального уровня
}