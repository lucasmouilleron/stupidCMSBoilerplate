<?php

/////////////////////////////////////////////////////////////////////////////
require_once __DIR__."/config.php";
require_once __DIR__."/vendors/Michelf/Markdown.inc.php"; use \Michelf\Markdown;

/////////////////////////////////////////////////////////////////////////////
// BASIC
/////////////////////////////////////////////////////////////////////////////
session_start();
date_default_timezone_set("Europe/Paris");
$debugInfos = array();

/////////////////////////////////////////////////////////////////////////////
// PATHS
/////////////////////////////////////////////////////////////////////////////
define("CONTENTS_PATH",truepath(__DIR__."/../_contents"));
define("IMAGES_PATH",truepath(__DIR__."/../_images"));
define("PAGES_PATH",truepath(__DIR__."/../../"));
define("PAGES_EXTENSION",".html");
define("SMTE_CACHE_PATH",truepath(__DIR__."/../_cache"));
define("CONTENT_TAG","CNT:");
define("IMAGE_TAG","IMG:");
define("DEFINITION_TAG","DEF:");
define("INCLUDE_TAG","INC:");
define("CONTENTS_FILE",CONTENTS_PATH."/__index.json");
define("IMAGES_FILE",IMAGES_PATH."/__index.json");
define("IMG_URL","./__stupid/_images/");
define("README_FILE",__DIR__."/../../README.md");
define("CONTENT_MARKDOWN_PREFIX","***");

///////////////////////////////////////////////////////////////////////////////
// SMTE Engine
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
function clearSMTECache() {
    @deleteDirectory(SMTE_CACHE_PATH);
    @mkdir(SMTE_CACHE_PATH);

    $pages = listPages();
    foreach ($pages as $page) {
        @mkdir(dirname(SMTE_CACHE_PATH."/".$page),0777, true);
        file_put_contents(SMTE_CACHE_PATH."/".$page.PAGES_EXTENSION, renderPage($page, true));
        setDegubInfo("cacheGenerated",$page);
    }
    return $pages;
}

///////////////////////////////////////////////////////////////////////////////
function processPage($page) {
    if(isPageDynamic($page)) {
        include(PAGES_PATH."/".$page.".php");
    }
    else if(isPageRenderable($page)){ 
        echo @renderPage($page);
    }
}

///////////////////////////////////////////////////////////////////////////////
function isPageDynamic($page) {
    return file_exists(PAGES_PATH."/".$page.".php");
}

///////////////////////////////////////////////////////////////////////////////
function isPageRenderable($page) {
    if(!isset($page) || $page == "") {
        return false;
    }
    else {
        return true;
    }
}

///////////////////////////////////////////////////////////////////////////////
function renderPage($page, $noCache=false) {
    $page = $page.PAGES_EXTENSION;
    if($noCache == false && file_exists(SMTE_CACHE_PATH."/".$page)) {
        setDegubInfo("loadedFromCache","true");
        return file_get_contents(SMTE_CACHE_PATH."/".$page);
    }
    else {
        setDegubInfo("loadedFromCache",false);
        if(SMTE_CACHE_AUTO_GENERATE && $noCache == false) {
            clearSMTECache();
        }
        ob_start(); 
        $content = @file_get_contents(PAGES_PATH."/".$page);
        if($content == "") {
            echo "404 !";
            exit();
        }
        return renderSMTETemplate($content);
    }
}

///////////////////////////////////////////////////////////////////////////////
function renderSMTETemplate($content) {
    return preg_replace_callback("/\{\{(.*)\}\}/U", function($matches) {
        $result = $matches[1];
        if(startsWith($result,INCLUDE_TAG)) {
            $result = renderInclusion(substr($result, strlen(DEFINITION_TAG)));
        }
        if(startsWith($result,DEFINITION_TAG)) {
            $result = renderDefinition(substr($result, strlen(DEFINITION_TAG)));
        }
        if(startsWith($result,CONTENT_TAG)) {
            $result = renderContent(substr($result, strlen(CONTENT_TAG)));
        }
        if(startsWith($result,IMAGE_TAG)) {
            $result = renderImage(substr($result, strlen(IMAGE_TAG)));
        }
        return $result;
    }, $content);
}

///////////////////////////////////////////////////////////////////////////////
function renderDefinition($def) {
    return @constant($def);
}

///////////////////////////////////////////////////////////////////////////////
function renderInclusion($inclusionName) {
    return renderSMTETemplate(@file_get_contents(PAGES_PATH."/".$inclusionName.PAGES_EXTENSION));
}

///////////////////////////////////////////////////////////////////////////////
function renderContent($sectionName) {
	$content = @file_get_contents(getMDFilePath(clearSectionName($sectionName)));
	if(startsWith($content,CONTENT_MARKDOWN_PREFIX)) {
		$content = markdownToHTML(substr($content, strlen(CONTENT_MARKDOWN_PREFIX)));
	}
    return renderSMTETemplate(replaceWithDefines($content));
}

/////////////////////////////////////////////////////////////////////////////
function renderImage($image) {
    return IMG_URL."/".clearImageName($image);
}

///////////////////////////////////////////////////////////////////////////////
// SMTE backend
///////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
function listPagesFull() {
    $files = getDirContents(PAGES_PATH);
    $pages = array();
    foreach ($files as $file) {
        if(endsWith($file, PAGES_EXTENSION) && !startsWith($file,SMTE_CACHE_PATH)) {
            array_push($pages, str_replace(PAGES_EXTENSION, "", $file));
        }
    }
    return $pages;
}

/////////////////////////////////////////////////////////////////////////////
function listPages() {
    $files = getDirContents(PAGES_PATH);
    $pages = array();
    foreach ($files as $file) {
        if(endsWith($file, PAGES_EXTENSION) && !startsWith($file,SMTE_CACHE_PATH)) {
            array_push($pages, str_replace(PAGES_PATH, "", str_replace(PAGES_EXTENSION, "", $file)));
        }
    }
    return $pages;
}

/////////////////////////////////////////////////////////////////////////////
function getContentsList() {
    $contents = @json_decode(file_get_contents(CONTENTS_FILE), true);
    if($contents === null) {
        return array();
    }
    else {
        return $contents;
    }
}

/////////////////////////////////////////////////////////////////////////////
function getImagesList() {
    $images = @json_decode(file_get_contents(IMAGES_FILE), true);
    if($images === null) {
        return array();
    }
    else {
        return $images;
    }
}

/////////////////////////////////////////////////////////////////////////////
function clearSectionName($sectionName) {
   return preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $sectionName);
}

/////////////////////////////////////////////////////////////////////////////
function clearImageName($imageName) {
   return preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $imageName);
}

/////////////////////////////////////////////////////////////////////////////
function replaceWithDefines($str) {
    return preg_replace_callback("/\%\%(.*)\%\%/si", function($matches) {return constant($matches[1]);}, $str);
}

/////////////////////////////////////////////////////////////////////////////
function getMDFilePath($section) {
    return CONTENTS_PATH."/".clearSectionName($section).".md";
}

/////////////////////////////////////////////////////////////////////////////
function getImagePath($image) {
    return IMAGES_PATH."/".clearImageName($image);
}

///////////////////////////////////////////////////////////////////////////////
function isAuthentified() {
    if(!isset($_SESSION["authentified"])) {
        return false;
    }
    else {
        return $_SESSION["authentified"];
    }
}

///////////////////////////////////////////////////////////////////////////////
function lockPage() {
    if(!isAuthentified()) {
        header("Location: login");
    }
}

///////////////////////////////////////////////////////////////////////////////
function login($password) {
    if($password == ADMIN_PASSWORD) {
        $_SESSION["authentified"] = true;
        header("Location: .");   
    }
}

/////////////////////////////////////////////////////////////////////////////
// UTILS
/////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if(is_dir($path) && $value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

/////////////////////////////////////////////////////////////////////////////
function createFileIfNotExists($filePath) {
    if (!file_exists($filePath)) {
        file_put_contents($filePath, '');
    }
}

/////////////////////////////////////////////////////////////////////////////
function isCurrentPage($pageName) {
    return endsWith(basename($_SERVER["PHP_SELF"]),$pageName);
}

///////////////////////////////////////////////////////////////////////////////
function setDegubInfo($label, $value) {
    global $debugInfos;
    $label .= uniqid();
    $debugInfos[$label] = $value;
}

///////////////////////////////////////////////////////////////////////////////
function getDebugInfos() {  
    global $debugInfos;
    return var_export($debugInfos,true);
}

/////////////////////////////////////////////////////////////////////////////
function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

/////////////////////////////////////////////////////////////////////////////
function endsWith($haystack, $needle) {
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

///////////////////////////////////////////////////////////////////////////////
function markdownToHTML($content) {
    return Markdown::defaultTransform($content);
}

///////////////////////////////////////////////////////////////////////////////
function truepath($path){
    // whether $path is unix or not
    $unipath=strlen($path)==0 || $path{0}!='/';
    // attempts to detect if path is relative in which case, add cwd
    if(strpos($path,':')===false && $unipath)
        $path=getcwd().DIRECTORY_SEPARATOR.$path;
    // resolve path parts (single dot, double dot and double delimiters)
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.'  == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    $path=implode(DIRECTORY_SEPARATOR, $absolutes);
    // resolve any symlinks
    if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
    // put initial separator that could have been lost
    $path=!$unipath ? '/'.$path : $path;
    return $path;
}

///////////////////////////////////////////////////////////////////////////////
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

?>