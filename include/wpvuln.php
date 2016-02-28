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
		foreach ($jsonObject as $j) // a bit dirty, buut could not find another way to extract first object without knowing property name (relying on $name is not safe)
		{
			$obj = $j; 
			break;
		}
		$instance->latest_version = $obj->latest_version;
		if ($instance->latest_version === null) $instance->latest_version = '';
		$instance->last_updated = $obj->last_updated;
		if ($instance->last_updated === null) $instance->last_updated = '';
		$instance->popular = $obj->popular;
		
		foreach ($obj->vulnerabilities as $v)
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