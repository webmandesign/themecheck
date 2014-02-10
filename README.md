themecheck.org
==============

Themecheck.org is a free service that allows to test web themes or templates.
Dozens of tests are run to check best practice compliance : CMS standards, security, malware, coding style, use of deprecated functions, etc.

This service is compatible with Wordpress and Joomla themes. More CMS will be added later on.

This service was inspired by Simon Posser and Samuel Wood (Otto) work on Theme-CHeck plugin for wordpress (http://wordpress.org/plugins/theme-check/)

Please add your contribution to improve the checks !

Installation 
============

unzip in a folder called "themecheck"
create a sibbling folder (located in the same parent directory) called "themecheck_vault"
in themecheck_vault create three subfolders :
themecheck_vault/reports
themecheck_vault/unzip
themecheck_vault/upload

in folder themecheck, create a subfolder calld "dyn"

make sure all there directories have at least 660 rights

the final structure should be :
/*parent*
	/themecheck
		/dyn
	/themecheck_vault
		/reports
		/unzip
		/upload
