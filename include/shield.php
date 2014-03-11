<?php
namespace ThemeCheck;
require_once 'Bootstrap.php';

function getShield($themeInfo, $lang, $size, $href , $rootdir)
{
	$html = '';
	if (!empty($themeInfo))
	{
		$score = 0;
		$criticalCount = 0;
		$warningsCount = 0;
		if (is_array($themeInfo))
		{
			$score = $themeInfo["score"];
			$criticalCount = $themeInfo["criticalCount"];
			$warningsCount = $themeInfo["warningsCount"];
		} else {
			$score = $themeInfo->score;
			$criticalCount = $themeInfo->criticalCount;
			$warningsCount = $themeInfo->warningsCount;
		}
		
		$img = "pictogreen$size.png";
		$color = 'cbd715';
		$text = sprintf(__("Themecheck.org validation score : 100%%, %s.", $lang), date("Y-m-d", $themeInfo->validationDate));
		if ($score<100.0)
		{
			if ($criticalCount > 0)
			{
				$img = "pictored$size.png";
				$color = 'ff1427';
				$text = sprintf(__("Themecheck.org validation score : %s%% (%s severe failures, %s warnings, %s).", $lang), intval($score), $criticalCount, $warningsCount, date("Y-m-d", $themeInfo->validationDate));
			} else {
				$img = "pictoorange$size.png";
				$color = 'ff8214';
				$text = sprintf(__("Themecheck.org validation score : %s%% (%s warnings, %s).", $lang), intval($score), $warningsCount, date("Y-m-d", $themeInfo->validationDate));
			}
		}
		
		$html .= '<a href="'.$href.'" style="background-image:url('.$rootdir.'img/'.$img.');" title="'.$text.'">';
		$html .= '<div class="div'.$size.'" style="color:#'.$color.';">';			
		if ($score<100.0) $html .= intval($score);
		$html .= '</div></a>';

	} else $html = __("Error : non existant id.", $lang);
	
	return $html;
}

function displayShield($themeInfo, $lang, $size, $href , $rootdir)
{
	echo getShield($themeInfo, $lang, $size, $href , $rootdir);
}