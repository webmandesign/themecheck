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
            $validationDate = 0;
            if (is_array($themeInfo))
            {
                    $score = $themeInfo["score"];
                    $criticalCount = $themeInfo["criticalCount"];
                    $warningsCount = $themeInfo["warningsCount"];
                    $validationDate = $themeInfo["validationDate"];
            } else {
                    $score = $themeInfo->score;
                    $criticalCount = $themeInfo->criticalCount;
                    $warningsCount = $themeInfo->warningsCount;
                    $validationDate = $themeInfo->validationDate;
            }
            
            // ICONS SITE WEB
            if($size == null)
            {
		
                $sprite_result = 'result_100';
		$color = 'cbd715';
		$text = sprintf(__("Themecheck.org validation score : 100%%, %s.", $lang), date("Y-m-d", $validationDate));
		if ($score<100.0)
		{
			if ($score > 95)
			{
                            $sprite_result = 'result_green';
                            $color = 'cbd715';
			} 
                        else if ($score > 80)
			{
                            $sprite_result = 'result_orange';
                            $color = 'ff8214';
			} 
                        else 
                        {
                            $sprite_result = 'result_red';
                            $color = 'F04E2E';
			}
			
			if ($criticalCount > 0)
			{
				$text = sprintf(__("Themecheck.org validation score : %s%% (%s severe failures, %s warnings, %s).", $lang), intval($score), $criticalCount, $warningsCount, date("Y-m-d", $validationDate));
			} else {
				$text = sprintf(__("Themecheck.org validation score : %s%% (%s warnings, %s).", $lang), intval($score), $warningsCount, date("Y-m-d", $validationDate));
			}
		}
                
                $html .= '<a href="'.$href.'"  target="_parent" title="'.$text.'">';
                $html .= '<span class="sprite '.$sprite_result.'">';	
                $scoreClass = '';
                if(strlen(intval($score)) == 1) $scoreClass = 'addLeft';
		if ($score<100.0) $html .= '<strong><span class="score_verif '.$scoreClass.'" style="color: #'.$color.'">'.intval($score).'</span></strong>';
		$html .= '</a>';
            } // ICONS IFRAME
            else
            {
                $img = "pictoperfect$size.svg";
		$color = 'cbd715';
		$text = sprintf(__("Themecheck.org validation score : 100%%, %s.", $lang), date("Y-m-d", $validationDate));
		if ($score<100.0)
		{
			if ($score > 95)
			{
				$img = "pictogreen$size.svg";
				$color = 'cbd715';
			} else if ($score > 80)
			{
				$img = "pictoorange$size.svg";
				$color = 'ff8214';
			} else {
				$img = "pictored$size.svg";
				$color = 'ff1427';
			}
			
			if ($criticalCount > 0)
			{
				$text = sprintf(__("Themecheck.org validation score : %s%% (%s severe failures, %s warnings, %s).", $lang), intval($score), $criticalCount, $warningsCount, date("Y-m-d", $validationDate));
			} else {
				$text = sprintf(__("Themecheck.org validation score : %s%% (%s warnings, %s).", $lang), intval($score), $warningsCount, date("Y-m-d", $validationDate));
			}
		}
		
		$html .= '<a href="'.$href.'" style="background-image:url('.$rootdir.'img/'.$img.');" target="_parent" title="'.$text.'">';
		$html .= '<div class="div'.$size.'" style="color:#'.$color.';">';			
		if ($score<100.0) $html .= intval($score);
		$html .= '</div></a>';
              
            }
	} 
        else $html = __("Error : non existant id.", $lang);
	
	return $html;
}

function displayShield($themeInfo, $lang, $size, $href , $rootdir)
{
	echo getShield($themeInfo, $lang, $size, $href , $rootdir);
}