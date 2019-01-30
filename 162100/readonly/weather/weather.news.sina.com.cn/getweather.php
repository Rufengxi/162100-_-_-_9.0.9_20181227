<?php
@ ini_set('display_errors', false);

@ header("content-type: text/html; charset=gb2312");

function city_err_journal($city) {
  global $web;
  $city_err_file = $GLOBALS['WEATHER_DATA'].'readonly/weather/'.$web['weather_from'].'/city_err_journal.txt';
  if ($f = file_get_contents($city_err_file)) {
    if (preg_match("/".preg_quote($city, "/")."[\n\r]+/", $f)) {
      return false;
    }
  }
  if ($fp = @fopen($city_err_file, 'ab')) {
    @fwrite($fp, "".$city."\n");
    @fclose($fp);
  }
}

function get_w_img($img) {
  global $web;
  $img = preg_replace('/\?.*$/', '', $img);
  $img_ = basename($img);
  $img_file = 'readonly/weather/'.$web['weather_from'].'/img/'.$img_.'';
  if (file_exists($GLOBALS['WEATHER_DATA'].$img_file)) {
    return $img_file;
  } else {
    if ($img_file_ = read_file($img)) {
      write_file($GLOBALS['WEATHER_DATA'].$img_file, $img_file_);
    } else {
      return $img;
    }
  }
  return $img_file;
}


if (isset($_GET['type']) && $_GET['type'] == 2) :

$t = '2';
//�õ����� ���Ƶ�
function getWEATHER($city) {
  global $tmp;
  $weatherurl = 'http://php.weather.sina.com.cn/search.php?c=1&city='.$city.'&dpc=1';
  if ($W_FR = read_file($weatherurl)) {
    preg_match('/<\!--\s*�Ҳ�������\s*begin\s*-->(.*)<\!--\s*�Ҳ�������\s*end\s*-->/isU', $W_FR, $matches);
    unset($W_FR);
    if (!empty($matches[1])) {
      $matches[1] = preg_replace('/<ul\s+class="list_01">.*<\/script>/isU', '</div></div>', $matches[1]);
	  $weather = $matches[1];
    }
    unset($matches);
  }
  if (!empty($weather)) {
    $GLOBALS['WEATHER_BORN'] = 1;
    write_file($tmp, $weather);
  } else {
    @ setcookie('weathercity', '', -(time() + 365 * 24 * 60 * 60), '/'); //+8*3600
    @ setcookie('weathercity2', '', -(time() + 365 * 24 * 60 * 60), '/'); //+8*3600
    @unlink($tmp);
	$GLOBALS['WEATHER_BORN'] = 0;
    $weather .= '<span style="background-color:#FFFFFF;">����Ԥ����ȡʧ�ܣ����Ժ����ԡ�<br />
���ܲɼ�Դ��������������[<a href="weather.php?err='.urlencode($weatherurl).'" target="_blank" style="color:blue;">ȥ���</a>]</span>';
  }
  return $weather;
}

else :

$t = '';
//�õ����� �򵥵�
function getWEATHER($city) {
  global $tmp;
  $weatherurl = 'http://php.weather.sina.com.cn/search.php?c=1&city='.$city.'&dpc=1';
  $weather = '';
  if ($W_FR = read_file($weatherurl)) {
    $weather .= '
<span class="weather">
  <span id="city_where" onmouseover="ct=window.setInterval(function(){showWD($(\'city_where\'), \'weather.php?area=china\', \'720px\', \'auto\');}, 100);" onmouseout="window.clearInterval(ct);">
    <a href="weather.php?area=china" id="weather_where" title="�л�����"><img src="readonly/images/po.png" /></a>
    <a href="weather.php" id="weather_city">'.$city.'</a>
  </span>
  <a href="weather.php" id="weather_show">
';

    if (preg_match('/<\/span>ʵʱ��������<\/h4>[\s\n\r]*<table class="tb_air">.+<p>([^<>]*)<\/p>/isU', $W_FR, $matches_kq)) {
      if ($matches_kq[1]) {
        switch ($matches_kq[1]) {
          case '������Ⱦ':
          $w_kq = 'w_kq_yzwr';
          break;
          case '�ض���Ⱦ':
          $w_kq = 'w_kq_yzwr';
          break;
          case '�ж���Ⱦ':
          $w_kq = 'w_kq_zdwr';
          break;
          case (strstr($matches_kq[1], '��Ⱦ') == true):
          $w_kq = 'w_kq_qdwr';
          break;
          case '��':
          $w_kq = 'w_kq_you';
          break;
          case '��':
          $w_kq = 'w_kq_liang';
          break;
          default :
          $w_kq = 'w_kq';
          break;
        }
        $weather .= ' <span id="w_kq">'.(strstr($matches_kq[1], '��Ⱦ') ? '' : '����').'<b class="'.$w_kq.'">'.$matches_kq[1].'</b></span>';
      }
    }
    if (preg_match('/<h5>�������<\/h5>[\s\r\n]*<div title=\'([^\'\"\>]+)\' [^\>]+url\(([^\)]*)\).*<span class="fs_30 tpte">([\-\d]+��)<\/span>.*<h5>����ҹ��<\/h5>[\s\r\n]*<div title=\'([^\'\"\>]+)\' [^\>]+url\(([^\)]*)\).*<span class="fs_30 tpte">([\-\d]+��)<\/span>/isU', $W_FR, $matches)) {
      $weather .= '';
	  $weather .= $today = '<span id="w_today" title="����"><span class="w_img"><img src="'.get_w_img($matches[2]).'" style="width:34px;height:34px;" /></span><span class="w_qingkuang">'.$matches[1].'</span><span class="w_wendu'.(floatval($matches[3])>0 ? ' w_wendu_ls':' w_wendu_lx').'">'.$matches[3].'</span>';
      $weather .= '</span>';

      $weather .= ' ��';
	  $weather .= $night = '<span id="w_moday" title="ҹ��"><span class="w_img"><img src="'.get_w_img($matches[5]).'" style="width:34px;height:34px;" /></span><span class="w_qingkuang">'.$matches[4].'</span><span class="w_wendu'.(floatval($matches[6])>0 ? ' w_wendu_ls':' w_wendu_lx').'">'.$matches[6].'</span>';
      $weather .= '</span>';
      $weather .= '<!--span class="w_xiangqing" title="δ������">��</span-->';
      $weather .= '</a>
</span>';
      unset($matches, $matches_kq);
	  $GLOBALS['WEATHER_BORN'] = 1;
      write_file($tmp, $weather);
	} else {
      if (preg_match('/<h5>�������<\/h5>(.*)<h5>����ҹ��<\/h5>(.*<\/ul>)/isU', $W_FR, $matches)) {
        $weather .= '<span id="w_today">'.trim(strip_tags($matches[1])).' ';
	    $weather .= 'ҹ�� '.trim(strip_tags($matches[2])).'</span>';
        $weather .= '<!--span class="w_xiangqing" title="δ������">��</span-->';
        $weather .= '</a>
</span>';
        unset($matches);
	    $GLOBALS['WEATHER_BORN'] = 1;
        write_file($tmp, $weather);
      } else {
        @ setcookie('weathercity', '', -(time() + 365 * 24 * 60 * 60), '/'); //+8*3600
        @ setcookie('weathercity2', '', -(time() + 365 * 24 * 60 * 60), '/'); //+8*3600
        @unlink($tmp);
  	    $GLOBALS['WEATHER_BORN'] = 0;
        $weather .= '����Ԥ����ȡʧ�ܣ�</a>[<a href="weather.php?err='.urlencode($weatherurl).'" target="_blank" style="color:blue;">ȥ���</a>]</span>';
      }
	}
    unset($W_FR);
  }
  return $weather;
}

endif;


//�õ�����
function getCITY($str) {
  if (!empty($str)) {
    $str = preg_replace('/^.*(�½�|����|���ɹ�|����|����)(.*������)?/', '', $str);
    $str = preg_replace('/^.*(̨��|�㶫|����|�㽭|ɽ��|����|����|������|�ӱ�|����|����|����|����|����|�Ĵ�|����|ɽ��|����|����|����|����|�ຣ|����)(ʡ)?/', '', $str);
    //��
    $str = preg_replace('/^(.*��)?(.+)��.*$/', '$2', $str);
    $str = preg_replace('/^(��|��|��|��|�|�ߺ�|��|��|��|��|��|ɳ|��|��|��|�|��|��|��|��|��|÷|��|��|��|��|��|�е�|��|��|�|��|��|��|κ|��|��|��|��|ε|��|��|��|��|�|ۣ|Ҷ|��|�|��|��|��|��|Ϣ|��|�|��|��|��|��|��|����|����|�|��|ͨ��|��|��|����|��|��Ϫ|����|��|��|��|��|ݷ|��|��|��ͬ|��|��|Ӧ|��|��|��|��|��|��|��|�|��|�|��|��|��|¤|ü|��|��|��|��|��|��|Ǭ|ۯ|��|��|��|��|�˱�|��|��|ï|��|��|����|��)$/', '$1��', $str);
    //��
    $str = preg_replace('/^.*(��|��|����|��|��|��)��.*$/', '$1��', $str);
    //��
    $str = preg_replace('/^(.*��)?(.+)��.*$/', '$2', $str);
    $str = preg_replace('/^(������|�˰�|���ֹ���|��)$/', '$1��', $str);
    //��
    $str = preg_replace('/^(.*��)?(.+)��.*$/', '$2��', $str);
    //��
    $str = preg_replace('/^(.*��)?(.+)��.*$/', '$2', $str);
    $str = preg_replace('/^(����)$/', '$1��', $str);

  }
  $city = (!empty($str) && !strstr($str,'������ַ') && !strstr($str,'������') && !strstr($str,'IANA������ַ')) ? $str : '����';
  //@ setcookie('weathercity', '', -(time() + 365 * 24 * 60 * 60), '/'); //+8*3600
  //@ setcookie('weathercity', $city, time() + 365 * 24 * 60 * 60, '/'); //+8*3600
  //unset($str);
  return $city;
}


if ($_GET['city']) {
  if ($city_arr_file = file_get_contents($GLOBALS['WEATHER_DATA'].'readonly/weather/'.$web['weather_from'].'/getweather_seek.php')) {
    if (preg_match('/\''.preg_quote($_GET['city'], '/').'\'/', $city_arr_file, $m)) {
      @ setcookie('weathercity', $_GET['city'], time() + 365 * 24 * 60 * 60, '/'); //+8*3600
      $city = $_GET['city'];
      unset($m);
    }
  }
}
if (empty($city)) {
  if ($_COOKIE['weathercity']) {
    $city = $_COOKIE['weathercity'];
  } else {
    require($GLOBALS['WEATHER_DATA'].'readonly/weather/getip.php');
    $myobj = new ipLocation();
    $ip = $myobj->getIP();
    $address = $myobj->getaddress($ip);
    $myobj = NULL;
    $city_tmp = getCITY($address["area1"]);
    if ($city_arr_file = file_get_contents($GLOBALS['WEATHER_DATA'].'readonly/weather/'.$web['weather_from'].'/getweather_seek.php')) {
      if (preg_match('/\''.preg_quote($city_tmp, '/').'\'/', $city_arr_file, $m)) {
        @ setcookie('weathercity', $city_tmp, time() + 365 * 24 * 60 * 60, '/'); //+8*3600
        $city = $city_tmp;
        unset($m);
      } else {
        city_err_journal($city_tmp);
      }
    }
  }
}
if (empty($city)) {
  $city = '����';
}



$weather = '';
$tmp = $GLOBALS['WEATHER_DATA'].'writable/__temp__/weather/'.$web['weather_from'].'/'.urlencode($city).''.$t.'.txt';
$time = time();
$filemtime = @file_exists($tmp) ? @filemtime($tmp) : 0;
/*
$time = time();
$pass = date('G') * 3600 + date('i') * 60 + date('s');
$time_0000 = $time - $pass;
$step_1710 = 17 * 3600 + 30 * 60;
$step_0810 = 8 * 3600 + 20 * 60;

if ($pass >= $step_1710) {
  if ($filemtime != 0 && $filemtime >= $time_0000 + $step_1710) {
    $weather = @file_get_contents($tmp);
  }
  //���浽�ڶ���08:10
  $diff_s = $step_0810 - $pass + 86400;
} else {
  if ($pass >= $step_0810) {
    if ($filemtime != 0 && $filemtime >= $time_0000 + $step_0810) {
      $weather = @file_get_contents($tmp);
    }
    //���浽17:10 ����17:00����һ��
    $diff_s = $step_1710 - $pass;
  } else {
    if ($filemtime != 0 && $filemtime >= $time_0000 + $step_1710 - 86400) {
      $weather = @file_get_contents($tmp);
    }
    //���浽08:10 ����8:00����һ��
    $diff_s = $step_0810 - $pass;
  }
}
*/

$step = (isset($web['weather_step']) && is_numeric($web['weather_step']) && $web['weather_step'] > 0) ? $web['weather_step'] * 3600 : 7200; //����2Сʱ

//��Ч�ڽ�ֹʱ��
$ekey = $filemtime - (gmdate('i', $filemtime + floatval($web['time_pos']) * 3600) * 60 + gmdate('s', $filemtime + floatval($web['time_pos']) * 3600)) + $step;
//��һ����Ч�ڽ�ֹʱ��
$next = $time - (gmdate('i', $time + floatval($web['time_pos']) * 3600) * 60 + gmdate('s', $time + floatval($web['time_pos']) * 3600)) + $step;


if ($time >= $ekey) {
  $weather = getWEATHER($city);
  if ($GLOBALS['WEATHER_BORN'] == 1) {
    header("Cache-Control: max-age = ".($next - $time)."");
    $expires = gmdate("D, d M Y H:i:s", $next + floatval($web['time_pos']) * 3600).' GMT';
    header('Expires: '.$expires.'');
  }
} else {
  header("Cache-Control: max-age = ".($ekey - $time)."");
  $expires = gmdate("D, d M Y H:i:s", $ekey + floatval($web['time_pos']) * 3600).' GMT';
  header('Expires: '.$expires.'');
  //include($tmp);
  $weather = @file_get_contents($tmp);
}

//ob_end_flush();

/*
  echo $weather;
*/
if (isset($_GET['char']) && $_GET['char'] == 'utf-8') {
  if (function_exists('iconv')) {
    @ header("content-type: text/html; charset=utf-8");
    echo iconv("gbk", "utf-8", $weather);
  } else {
    echo $weather;
  }
} else {
  echo $weather;
}
//echo eval('return '.$w.';');




















?>