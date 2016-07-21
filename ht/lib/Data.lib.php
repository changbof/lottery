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

    // K3三同号单选
    public function k33dx($betData, $kjData){
        $kjData = str_replace(',','',$kjData);
        return !!stripos($betData, $kjData) ? 1 : 0;

    }

    // K3三同号通选
    public function k33ttx($betData, $kjData){
        return self::k33dx($betData, $kjData);
    }

    // K3三连号通选
    public function k33ltx($betData, $kjData){

        return self::k33dx($betData, $kjData);
    }

    // K3三不同号
    public function k33bt($betData, $kjData){
        return self::zx($betData, $kjData);
    }

    // K3二不同号
    public function k32bt($betData, $kjData){
        return self::k33bt($betData, $kjData);
    }

    // K3二同号复选
    public function k32tfx($betData, $kjData){
        $strkj = 'k32tfx';
        $betData = preg_replace('/\*\s?/','',$betData);

        $data = explode(',',$kjData);
        sort($data);
        $r = preg_match("/(?<k1>[\d])\k<k1>{1}/",join('',$data),$match);
        if($r)
            $strkj = $match[0];

        return !!stripos($betData,$strkj) ? 1 : 0;
    }

    // K3二同号单选 todo
    public function k32tdx($betData, $kjData){
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

    /**
     * 常用算法
     */



    /** 组合
     * @param $array            备选数组
     * @param $num              选取的个数
     * @return array            组合
     */

    function combination($a, $m) {
        $r = array();

        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i=0; $i<$n; $i++) {
            $t = array($a[$i]);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i+1);
                $c = combination($b, $m-1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }

    public function combina($array, $num){
        $c = count($ar);
        if ($n>$c) return false; // parameter wrong
        if ($c>50) return false; // too big array :)

        $r = array();

        $code = "";
        $list = array();
        for($i=1;$i<=$n;$i++){
            $list[] = '$v'.$i;
            $code .= 'foreach($ar as $k'.$i.'=>$v'.$i.'){';
            if($i!=$n) $code .= 'unset($ar[$k'.$i.']);';
        }
        $code .= '$t = array('.join(',',$list).');';
        $code .= 'sort($t);';
        $code .= '$r[] = join(",",$t);';
        for($i=$n-1;$i>0;$i--){
            $code .= '}$ar[$k'.$i.']=$v'.$i.';';
        }
        $code .= '}';
        eval($code);
        return array_values(array_unique($r));
    }

    /**
     * 组合算法
     * @param array $elements    备选数组
     * @param $chosen            选取的个数
     * @return array             组合
     */
    function array_combination(array $elements, $chosen)
    {
        $result = array();

        for ($i = 0; $i < $chosen;   $i++) { $vecm[$i] = $i; }
        for ($i = 0; $i < $chosen-1; $i++) { $vecb[$i] = $i; }
        $vecb[$chosen - 1] = count($elements) - 1;
        $result[] = $vecm;

        $mark = $chosen - 1;
        while (true) {
            if ($mark == 0) {
                $vecm[0]++;
                $result[] = $vecm;
                if ($vecm[0] == $vecb[0]) {
                    for ($i = 1; $i < $chosen; $i++) {
                        if ($vecm[$i] < $vecb[$i]) {
                            $mark = $i;
                            break;
                        }
                    }
                    if (($i == $chosen) && ($vecm[$chosen - 1] == $vecb[$chosen - 1])) { break; }
                }
            } else {
                $vecm[$mark]++;
                $mark--;
                for ($i = 0; $i <= $mark; $i++) {
                    $vecb[$i] = $vecm[$i] = $i;
                }
                $vecb[$mark] = $vecm[$mark + 1] - 1;
                $result[] = $vecm;
            }
        }

        return $result;
    }
    /**
     * 常用算法：zx
     *
     * @params bet		投注列表：1 2 3 4 5 6
     * @params data		开奖所需的号码：4,5,2
     *
     * @return 			返回中奖注数
     */
    public function zx($bet,$data){
        $bet = explode(' ',$bet);
        $data = explode(',',$data);
        sort($data);
        $strDate = implode(',',$data);
        return count(array_filter( combine($bet,count($data)),
            funcion($var) use ($strDate){
                    return !!($v == $strDate);
        }));
    }
}