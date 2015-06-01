stupidCMS
=========

stupidCMS is a flat file stupid CMS with nice templating and caching engines.

Install
-------
- Drop all files (including `.htaccess`) at the site root
- Create the file `./config.php` and override default config (cf _Overridable Configuration_)
	

Stupid Pages (SP)
-----------------
- _Stupid Pages_ are located at the site root `./`
- _Stupid Pages_ files extensions should be `.html`
- In _Stupid Pages_, paths are relative to the site root
- To link from a page to another page, use `http://site.com/other-page`
- They use the _Stupid Micro Templating Engine_ (SMTE) allowing administrable content declaration

Stupid Micro Templating Engine (SMTE)
-------------------------------------
- Administrable contents : `{{CNT:content-name}}`
- Administrable files : `<img src="{{FILE:file-name.jpg}}"/>` or `<a href="{{FILE:file-name.pdf}}">the file</a>`
- Include another page in a page : `{{INC:page-name-without-html-extension}}`
- Definitions : `{{DEF:CONSTANT_NAME}}` (for example `SITE_URL`)
- If : `{{IF:php expression}}content{{EIF}}`

Studid Dynamic Page (SDP)
-------------------------
- _Stupid Pages_ files extensions can be `.php`
- In this case the page is a _Stupid Dynamic Page (SDP)_
- Custom php scripting can be addded
- `<?php global $stupid;?>` must be called to make the stupid engine available
- Sutpid API available :
- `$stupid->__inc("page-name-without-html-extension")`
- `$stupid->__cnt("content-name")`
- `$stupid->__file("file-name")`
- No cache is applied (it is applied only for included contents)

Compiled Stupid Pages Caching (CSPC)
------------------------------------
- Compiled `Stupid Pages` are _cached_ for performance optimization
- The cache engine is selectable in `/config.php`
- Three engines are available : 
    - _None_ cache engine : no cache, all pages and contents are loaded from original files (not recommended)
    - _File_ cache engine : cache files are located in `/__cache`
    - _Redis_ cache engine : redis port is configurable in `___stupid/libs/stupidDefinitions.php`
- Tip : If new contents are not visible, run `Clear` from the `Stupid Backend`
- Tip : In `DEVELOPMENT_MODE`, the cache is disabled

Stupid Backend (SB)
-------------------
- stupidCMS comes with a stupid backend
- Open `http://site.com/admin` in browser
- Content editing tips : 
- Contents are also SMTE compatible, which means you can use the SMTE tags
- Contents can also be written in markdown format : 
- Prefix your content with *** to enable Markdown
- [Markdown documentation](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)
- Tip : If SMTE administrable content are not visible, run `Scan Contents`
- Tip : If SMTE new contents are not visible, run `Clear Cache`

Overridable configuration
-------------------------
- Defined in `___stupid/libs/stupidDefinitions.php`
- `DEBUG_MODE (true)`: displays hints
- `DEVELOPMENT_MODE (false)`: no cache for easier development
- `ADMIN_PASSWORD ("password")`: has to be changed
- `SITE_URL ("http://localhost")`: has to be set to the site root url
- `PAGE_404 (false)`: `false` or the name of the 404 page (without extension)
- `NO_SCAN_FOLDERS (none)`: folders not to scan when finding contents and files
- `SMTE_CACHE_ENGINE ("file")`: `file` | `redis` | `none`

Dev
---
- Install `composer` : `curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer`
- Install dependencies : `cd ___stupid && composer install`
- Underlying cotents in `./__contents`
- Underlying files in `./__files`

TODO
----
- sub folder index
- todo