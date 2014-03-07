<?php
namespace ThemeCheck;
require_once 'Bootstrap.php';

function getShield($themeInfo, $lang, $size, $href , $rootdir)
{
	$html = '';
	if (!empty($themeInfo))
	{
		$score = 0;
		$failuresCount = 0;
		$warningsCount = 0;
		if (is_array($themeInfo))
		{
			$score = $themeInfo["score"];
			$failuresCount = $themeInfo["failuresCount"];
			$warningsCount = $themeInfo["warningsCount"];
		} else {
			$score = $themeInfo->score;
			$failuresCount = $themeInfo->failuresCount;
			$warningsCount = $themeInfo->warningsCount;
		}
		
		$img = "pictogreen$size.png";
		$color = 'cbd715';
		$text = __("Themecheck.org validation score : 100%", $lang);
		if ($score<100.0)
		{
			if ($failuresCount > 0)
			{
				$img = "pictored$size.png";
				$color = 'ff1427';
				$text = sprintf(__("Themecheck.org validation score : %s%% (%s severe failures, %s warnings)", $lang), intval($score), $failuresCount, $warningsCount);
			} else {
				$img = "pictoorange$size.png";
				$color = 'ff8214';
				$text = sprintf(__("Themecheck.org validation score : %s%% (%s warnings)", $lang), intval($score), $warningsCount);
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