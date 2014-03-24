# themecheck.org

Themecheck.org is a free service that allows to test web themes or templates.
Dozens of tests are run to check best practice compliance : CMS standards, security, malware, coding style, use of deprecated functions, etc.

This service is compatible with Wordpress and Joomla themes. More CMS will be added later on.

It is as a fork of the theme-check plugin for wordpress (http://wordpress.org/plugins/theme-check/).

Please add your contribution to improve the checks !

## License 

MIT


## Installation 

 * unzip in a folder called `themecheck` or clone the repository `git clone git@github.com:themecheck/themecheck.git`
 * create a sibbling folder (located in the same parent directory) called `themecheck_vault`
 * in `themecheck_vault` create three subfolders:

    themecheck_vault/reports
    themecheck_vault/unzip
    themecheck_vault/upload

 * in folder themecheck, create a subfolder called `dyn`

 * make sure all there directories have at least `770` rights

 * the final structure should be:

    /*parent*
    /themecheck
        /dyn
    /themecheck_vault
        /reports
        /unzip
        /upload
