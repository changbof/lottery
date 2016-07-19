<?php

/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2016/7/19 0019
 * Time: 23:07
 */
class Data
{
    /**
     * 功能:获得投注数据中开奖号码的中奖注数
     * 算法模型
     *　function func(betData, kjData, betWeiShu)
     */


    /** K3和值
     * @param $bets
     * @param $kj
     * @return int
     */
    public function k3hz($betData, $kjData){
        $kjData = explode(',',$kjData);
        $hz = array_sum($kjData);
        $reg = '|('.$hz.')|';
        preg_match($reg,$betData,$r);
        return count($r);
    }

    // K3三同号单选 todo
    public function k33dx($betData, $kjData){
        $betData = str_replace('*','',$betData);
        $betData = explode(',',$betData);
        $kjData = explode(',',$kjData);

        $kj1 = intval($kjData[0]) + intval($kjData[1]);
        $kj2 = intval($kjData[2]);
        $kj = $kj1.','.$kj2;
        return strpos($betData, $kjData)!=false ? 1 : 0;


    }

    // K3三同号通选
    public function k33ttx($betData, $kjData){
        $kjData = str_replace(',','',$kjData);
        return strpos($betData, $kjData)!=false ? 1 : 0;
    }

    // K3三连号通选
    public function k33ltx($betData, $kjData){

        return self::k33ttx($betData, $kjData);
    }

    // K3三不同号
    public function k33bt($bet){
        $check=array('1','2','3','4','5','6');
        $exp_num = '';
        foreach ($check as $v) $exp_num .= $v.'|';
        $exp_num = substr($exp_num, 0, -1);
        $exp = '/^\((('.$exp_num.') ){0,}('.$exp_num.')\)(('.$exp_num.') ){0,}('.$exp_num.')$/';
        if (preg_match($exp, $bet)) { // 胆拖模式
            $pos = strpos($bet, ')');
            $bet_prefix = substr($bet, 1, $pos - 1);
            $bet_prefix = explode(' ', $bet_prefix);
            $new_bet = substr($bet, $pos + 1);
            foreach ($bet_prefix as $v) {
                if (strpos($new_bet, $v)) return 0;
            }
            if (count($bet_prefix) === 1) { // 一胆
                return $this->rx($new_bet, 2);
            } else { // 两胆
                return $this->rx($new_bet, 1);
            }
        } else {
            $bet1=explode(' ', $bet);$a=array_unique($bet1);
            if(count($bet1)!=count($a) || count($bet1)<3 || count($bet1)>6) return 0;
            foreach($bet1 as $bets){
                if(!in_array($bets,$check)) return 0;
            }
            return $this->C(count($bet1), 3);
        }
    }

    // K3二不同号
    public function k32bt($bet){
        $check=array('1','2','3','4','5','6');
        $exp_num = '';
        foreach ($check as $v) $exp_num .= $v.'|';
        $exp_num = substr($exp_num, 0, -1);
        $exp = '/^\(('.$exp_num.')\)(('.$exp_num.') ){0,}('.$exp_num.')$/';
        if (preg_match($exp, $bet)) {  // 胆拖模式
            $bet_prefix = substr($bet, 1, 2);
            $new_bet = substr($bet, 4);
            if (strpos($new_bet, $bet_prefix)) return 0;
            $total = count(explode(' ', $new_bet));
            return $total > 5 ? 0 : $total;
        } else {
            $bet1=explode(' ', $bet);$a=array_unique($bet1);
            if(count($bet1)!=count($a) || count($bet1)<2 || count($bet1)>6) return 0;
            foreach($bet1 as $bets){
                if(!in_array($bets,$check)) return 0;
            }
            return $this->C(count($bet1), 2);
        }
    }

    // K3二同号复选
    public function k32tfx($bet){
        $check=array('11*','22*','33*','44*','55*','66*');
        $bet1=explode(' ', $bet);$a=array_unique($bet1);
        if(count($bet1)!=count($a) || count($bet1)>6 || count($bet1)<1) return 0;
        foreach($bet1 as $bets){
            if(!in_array($bets,$check)) return 0;
        }
        return count($bet1);
    }

    // K3二同号复选
    public function k32tdx($bet){
        $check=array('11','22','33','44','55','66');
        $check2=array('1','2','3','4','5','6');
        $bet=explode(',', $bet);$bet[0]=explode(' ',$bet[0]);$bet[1]=explode(' ',$bet[1]);$a=array_unique($bet[0]);$b=array_unique($bet[1]);
        if(count($bet[0])!=count($a) || count($bet[1])!=count($b) || count($bet[0])>6 || count($bet[0])<1 || count($bet[1])>6 || count($bet[1])<1) return 0;
        foreach($bet[0] as $x){
            if(!in_array($x,$check)) return 0;
        }
        foreach($bet[1] as $y){
            if(!in_array($y,$check2)) return 0;
        }
        return count($bet[0])*count($bet[1]);
    }
}