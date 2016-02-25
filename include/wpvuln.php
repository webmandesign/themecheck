<?php
namespace ThemeCheck;

// Simple unit of check
class WpVuln
{
	public $name;
	public $latest_version;
	public $last_updated;
	public $popular;
	public $vulnerabilities = array();
	
	public static function fromJson($jsonObject, $name)
	{
		$instance = new self();
		$instance->name = $name;
		$instance->latest_version = $jsonObject->$name->latest_version;
		if ($instance->latest_version === null) $instance->latest_version = '';
		$instance->last_updated = $jsonObject->$name->last_updated;
		if ($instance->last_updated === null) $instance->last_updated = '';
		$instance->popular = $jsonObject->$name->popular;
		
		foreach ($jsonObject->$name->vulnerabilities as $v)
		{
			$vul = new WpVulnVulnerability();
			$vul->id = $v->id;
			$vul->title = $v->title;
			$vul->created_at = $v->created_at;
			$vul->updated_at = $v->updated_at;
			$vul->published_date = $v->published_date;
			$vul->vuln_type = $v->vuln_type;
			$vul->fixed_in = $v->fixed_in;
			
			foreach ($v->references as $r)
			{
				$vul->references[] = $r[0];
			}
			
			$instance->vulnerabilities[] = $vul; 
		}
		
		return $instance;
	}
}

class WpVulnVulnerability
{
	public $id;
	public $title;
	public $created_at;
	public $updated_at;
	public $published_date;
	public $vuln_type;
	public $fixed_in;
	public $references = array();
}