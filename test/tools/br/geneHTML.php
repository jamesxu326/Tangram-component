<?php
/**
 * 使用注意事项：一般情况下不会所产生的测试结果表格内容不会有问题，
 * 问题的引入是没有对每次添加的数据做浏览器判断，在正常情况下浏览器的顺序恒定不变的
 * 当不同浏览器运行的测试内容不同的情况下，如ie8下采用filter=baidu.fx，
 * 而chrome下采用filter=baidu.fx.collaplse
 * 在添加浏览器的时候按照顺序会先添加chrome，再添加ie8
 * 那么当chrome下用例只有baidu.fx.collapse的时候，
 * 由于他会默认先找到的浏览器为chrome，那么与它相邻的ie8的baidu.fx.current的内容会左移到chrome下。
 * 这个跟存储数据的格式有关系：caseList
 * 							/         \
 *               baidu.fx.collapse    baidu.fx.current
 *              /           \             /            \
 *          chrome          ie8         null           ie8
 *         /  |  \         / |  \    (supposed       /   |  \
 *    fail  total hostInfo          to be chrome)  fail total hostInfo
 *
 *
 *
 * 不直接使用<style type ="text/css">来设置css是因为有的邮件客户端会过滤这样的信息
 *
 * ***/
function geneHTML($caseList, $name=''){
	date_default_timezone_set('PRC');
	//	$url = (isset ($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $_SERVER['PHP_SELF'];
	$url = "http://{$_SERVER['HTTP_HOST']}/report/ui/$name";
	$thead = getThBrowser($caseList);
	$tcase = getTrCase($caseList);
	$style_table = "border: 1px solid black;
		 color: #fff; background-color: #0d3349;
		 text-shadow: rgba(0, 0, 0, 0.5) 2px 2px 1px; text-align: center; ";
	//<style>td, th {border: 1px solid white;}</style>
	$html = "<!DOCTYPE><html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		</head><body><div>
		<h2 align='center'>测试结果".date('Y-m-d H:i:s')."</h2>
		<a href='http://$url' style='font:normal bolder 12pt Arial; ' title='效果应该比邮件好'>网页版</a>
		<table cellspacing='0' style='$style_table'>
			<thead><tr><th rowspan='2'>用例名称</th><th rowspan='2'>总覆盖率</th>$thead</tr></thead>
			$tcase
		</table></div></body></html>";
			return $html;
}

/**
 *
 * 根据实际浏览器书目确认生成表头
 * @param unknown_type $caseList
 */
function getThBrowser($caseList){//创建浏览器相关单元格
	$thBrowser = '';
	$count = 0;
	foreach ($caseList as $casename => $casedetail) { //每一个用例
		foreach ($casedetail as $b => $info) {
			$thBrowser .= "<th colspan='3'>$b</th>";
			$count++;
		}
		$thBrowser .="</tr><tr>";
		break;//遍历一次就知道所有浏览器的信息
	}
	for($index = 0; $index < $count; $index++) {
		$thBrowser .= "<td>cov</td><td>fail</td><td>total</td>";
	}

	return $thBrowser;
}

/**
 *
 * 根据执行结果生成单元格信息
 * @param unknown_type $caseList
 */
function getTrCase($caseList){//创建case名对应的单元格
	$trCase = '';
	require_once 'config.php';
	$style_case = "";
	$numBro = count(Config::$BROWSERS);
	foreach ($caseList as $casename => $caseDetail) { //每一个用例
		$cnurl = implode('.', explode('_', $casename));
		$trCase .= "<tr><td><a href='http://{$_SERVER['HTTP_HOST']}/{$_SERVER['PHP_SELF']}/../run.php?case=$cnurl'>
			$casename</a></td>";
		$totalCov = calTotalCov($caseDetail,$numBro);
		$trCase .= "<td title='所有覆盖率的均值'>$totalCov</td>";
		foreach ($caseDetail as $br => $infos) { //$b为browser名字,$info为详细信息
			$fail = $infos['fail'];
			$total = $infos['total'];
			$cov = $infos['cov'];
			$color = $fail == 0 ? '#5E740B' : '#710909';
			$trCase .= "<td style='background-color:$color'>$cov%</td>
			<td style='background-color:$color; '>$fail</td>
			<td style='background-color:$color; '>$total</td>";
		}

		$trCase .= "</tr>";
	}
	return $trCase;
}

/**
 *
 * 计算总覆盖率信息
 * @param unknown_type $caseDetail
 * @param unknown_type $brcount
 */
function calTotalCov($caseDetail,$brcount){
	$totalCov = 0;
	foreach ($caseDetail as $infos){
		$totalCov += $infos['cov'];
	}
	$aveCov = ceil($totalCov/$brcount);/*php默认原函数ceil，小数取整*/
	return $aveCov.'%';
}

?>