<?php
/*

if (count($argv) !== 2) {
    echo "Usage: php crawl.php [URL]";
    exit(1);
}

$url = $argv[1];

*/
//$parsedUrl = parse_url($url);
//$dir =  'archive/' . $parsedUrl['host'] . $parsedUrl['path'];

//$rootContent = file_get_contents($argv[1]);

//archive($argv[1]);

////


//$url = $_POST['url']; //POST
//$mode = $_POST['mode']; //POST

$url = $argv[1];
$mode = $argv[2];

if($mode == 0) {
    $parsedUrl = parse_url($url);
    $dir =  'archive/' . $parsedUrl['host'] . $parsedUrl['path'];

    echo $url;
    //exit;

    archive($url);
}
else if($mode == 1){
    $hrefUrls = getHrefUrls($url);
    $result = $hrefUrls;

    $result = array_unique($result);

    $count = count($result);
    $step = 1;


    foreach ($result as $res) {
        echo $res . ' ' . $step. '/' . $count . "\n";

        $parsedUrl = parse_url($res);
        $dir =  'archive/' . $parsedUrl['host'] . $parsedUrl['path'];
        
        //exit;

        archive($res); //IMPORTANT!!!

        $step = $step + 1;

    }
}
else if($mode == 2) {
    $hrefUrls = getHrefUrls($url);
    $result = [];

    foreach ($hrefUrls as $hrefUrl) {
        $secondIter = getHrefUrls($hrefUrl);
        if ($secondIter == null) {
            continue;
        }
        $result = array_merge($result,$secondIter);
    }

    $result = array_unique($result);

    $count = count($result);
    $step = 1;

    //print_r($result);

    foreach ($result as $res) {
        echo $res . ' ' . $step. '/' . $count . "\n";

        $parsedUrl = parse_url($res);
        $dir =  'archive/' . $parsedUrl['host'] . $parsedUrl['path'];

        //exit;

        archive($res); //IMPORTANT!!!

        $step = $step + 1;
        
    }

}
else {
    echo "Wrong mode!";
    exit;
}

/*
$hrefUrls = getHrefUrls($url);

/*$result = [];

foreach ($hrefUrls as $hrefUrl) {
    //echo $hrefUrl . "\n";
    $secondIter = getHrefUrls($hrefUrl);
    if ($secondIter == null) {
        continue;
    }
    $result = array_merge($result,$secondIter);
    //$secondIter = array_unique($secondIter);
    //foreach ($secondIter as $secondUrl) {
    //    echo $secondUrl . "\n";
    //}
}

*/ //IMPORTANT!!! IN DEPTH SEARCH

/*


$result = $hrefUrls;
$count = count($result);
$step = 1;

$result = array_unique($result);

foreach ($result as $res) {
    echo $res . ' ' . $step. '/' . $count . "\n";

    $parsedUrl = parse_url($res);
    $dir =  'archive/' . $parsedUrl['host'] . $parsedUrl['path'];

    //$rootContent = file_get_contents($argv[1]);

    //echo $dir . "\n";

    archive($res); //IMPORTANT!!!

    $step = $step + 1;
    //exit;
    //$secondIter[] = getHrefUrls($hrefUrl);
}

*/

function getHrefUrls($url) {
    $htmlContent = file_get_contents($url);

    if ($htmlContent === false) {
        echo "Failed to fetch website content.";
        return;
    }

    $hrefUrls = [];

    $parsedUrl = parse_url($url);

    $url= 'https://' . $parsedUrl['host'];

    $htmlContent = str_replace('href="/', 'href="' . $url . '/', $htmlContent);
    $htmlContent = str_replace("href='/", "href='" . $url . '/', $htmlContent);

    // Extract href URLs using regular expressions
    $pattern = '/<a[^>]+href=["\'](.*?)["\'][^>]*>/i';
    preg_replace_callback($pattern, function($matches) use (&$hrefUrls) {
        global $url;
        $parsedUrl = parse_url($url);

        $u = $matches[1];
        $parsedUrl2 = parse_url($u);

        if($parsedUrl['host'] == $parsedUrl2['host']){
            $save = 'https://' . $parsedUrl2['host'] . $parsedUrl2['path'];
            $hrefUrls[] = $save;
        }

    }, $htmlContent);

    return array_unique($hrefUrls);
}
////

function archive($url) {
    global $dir;

    //$url = $argv[1];

    //mkdir('archive');
    // Perform URL validation or sanitization if needed

    // Create the archive directory if it doesn't exist
    //if (!is_dir('archive')) {
    //    mkdir('archive');
    //}

    // Fetch the HTML content of the requested URL
    $htmlContent = file_get_contents($url);

    //echo $url . ' '; //testing

    $parsedUrl = parse_url($url);
    //$url2= $url;
    $url= 'https://' . $parsedUrl['host'];

    //$dir =  'archive/' . $parsedUrl['host'] . $parsedUrl['path'];

    if (!is_dir('archive')) {
        mkdir('archive');
    }

    if (is_dir($dir)) {
        //removeDirectory($dir);
    }

    mkdir($dir,0777,true);

    //return;

    //echo $url . ' '; //testing

    // Replace '//' in src with 'https://'
    $htmlContent = str_replace('src="//', 'src="https://', $htmlContent);
    $htmlContent = str_replace('href="//', 'href="https://', $htmlContent);
    $htmlContent = str_replace('src="/', 'src="' . $url . '/', $htmlContent);
    $htmlContent = str_replace("src='/", "src='" . $url . '/', $htmlContent);
    $htmlContent = str_replace('href="/', 'href="' . $url . '/', $htmlContent);
    $htmlContent = str_replace("href='/", "href='" . $url . '/', $htmlContent);

    //$filename = 'archive/index.html'; //testing
    //file_put_contents($filename, $htmlContent); //testing
    //exit; //testing

    // Download linked CSS, JS, and images
    preg_match_all('/<link[^>]+href=[\'"]([^\'"]+)[\'"][^>]*>/', $htmlContent, $matches);
    $cssUrls = $matches[1];

    foreach ($cssUrls as $cssUrl) {
        //$cssUrl = rtrim($url, '/') . $cssUrl;

        global $dir;

        $cssContent = file_get_contents($cssUrl);

        $cssContent = extractUrlsFromCSS($cssContent, $cssUrl, $url);

        $cssUrl = strtok($cssUrl, '?'); // Remove query parameters
        $cssFilename = $dir . '/' . basename($cssUrl);
        file_put_contents($cssFilename, $cssContent);
    }

    preg_match_all('/<script[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/', $htmlContent, $matches);
    $jsUrls = $matches[1];

    foreach ($jsUrls as $jsUrl) {
        //$jsUrl = rtrim($url, '/') . $jsUrl;
        global $dir;

        $jsContent = file_get_contents($jsUrl);
        //echo $jsUrl . ' ';
        $jsUrl = strtok($jsUrl, '?'); // Remove query parameters
        //echo $jsUrl . ' ';
        $jsFilename = $dir . '/' . basename($jsUrl);
        file_put_contents($jsFilename, $jsContent);
    }

    preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/', $htmlContent, $matches);
    $imageUrls = $matches[1];

    foreach ($imageUrls as $imageUrl) {
        //$imageUrl = rtrim($url, '/') . $imageUrl;
        global $dir;

        $imageContent = file_get_contents($imageUrl);
        $imageUrl = strtok($imageUrl, '?'); // Remove query parameters
        $imageFilename = $dir . '/' . basename($imageUrl);
        file_put_contents($imageFilename, $imageContent);
    }


    preg_match_all('/@import\s+url\((.*?)\);/', $htmlContent, $matches);
    $importUrls = $matches[1];

    foreach ($importUrls as $importUrl) {
        global $dir;

        $importUrl = trim($importUrl, '\'"'); // Remove surrounding quotes
        $importUrl = rtrim($url, '/') . $importUrl;
        $importContent = file_get_contents($importUrl);
            
        $importContent = extractUrlsFromCSS($importContent, $importUrl, $url);

        $importUrl = strtok($importUrl, '?'); // Remove query parameters
        $importFilename = $dir . '/' . basename($importUrl);
        file_put_contents($importFilename, $importContent);
    }

    //Remove path and leave only local filename

    $htmlContent = preg_replace_callback('/<script([^>]*src=[\'"])([^\'"]+)([^\'"]*[\'"][^>]*>)/', function($matches) {
        $url = $matches[2];
        
        $url = strtok($url, '?'); // remove query parameters

        $fileName = basename(parse_url($url, PHP_URL_PATH));
        return "<script{$matches[1]}{$fileName}{$matches[3]}";
    }, $htmlContent);

    $htmlContent = preg_replace_callback('/<link([^>]*href=[\'"])([^\'"]+)([^\'"]*[\'"][^>]*>)/', function($matches) {
        $url = $matches[2];
        
        $url = strtok($url, '?'); // remove query parameters

        $fileName = basename(parse_url($url, PHP_URL_PATH));
        return "<link{$matches[1]}{$fileName}{$matches[3]}";
    }, $htmlContent);

    $htmlContent = preg_replace_callback('/<img([^>]*src=[\'"])([^\'"]+)([^\'"]*[\'"][^>]*>)/', function($matches) {
        $url = $matches[2];
        
        $url = strtok($url, '?'); // remove query parameters

        $fileName = basename(parse_url($url, PHP_URL_PATH));
        return "<img{$matches[1]}{$fileName}{$matches[3]}";
    }, $htmlContent);

    $htmlContent = preg_replace_callback('/@import\s+url\(\'(.*?)\'\);/', function ($matches) {
        $url = $matches[1];
        
        $url = strtok($url, '?'); // remove query parameters

        $fileName = basename(parse_url($url, PHP_URL_PATH));
        return "@import url('" . $fileName . "');";
    }, $htmlContent);

    $htmlContent = preg_replace_callback('/@import\s+url\("(.*?)"\);/', function ($matches) {
        $url = $matches[1];
        
        $url = strtok($url, '?'); // remove query parameters

        $fileName = basename(parse_url($url, PHP_URL_PATH));
        return "@import url(\"" . $fileName . "\");";
    }, $htmlContent);

    $htmlContent = preg_replace_callback('/@import\s+url\((.*?)\);/', function ($matches) {
        $url = $matches[1];
        
        $url = strtok($url, '?'); // remove query parameters

        $fileName = basename(parse_url($url, PHP_URL_PATH));
        return "@import url(\"" . $fileName . "\");";
    }, $htmlContent);

    $htmlContent = str_replace('<a href="' . $url, '<a href="/archive/' . $parsedUrl['host'], $htmlContent);

    $htmlContent = preg_replace_callback('/<a([^>]*href=[\'"])([^\'"]+)([^\'"]*[\'"][^>]*>)/', function($matches) {
        $url = $matches[2];
        
        //$parsedUrl = parse_url($url);

        //if ($parsedUrl['host'] == 'URL') {
        //    $url= '.' . $parsedUrl['path'] . '/index.html';
        //}
        //else {
        //    $url= $parsedUrl['host'] . $parsedUrl['path'];
        //    return "<a{$matches[1]}{$url}{$matches[3]}";
        //}

        $url= $url . '/index.html';

        //$url = strtok($url, '?'); // remove query parameters

        //$fileName = basename(parse_url($url, PHP_URL_PATH));

        //$url1 = str_replace('<a href="' . $url, "<a href=.", $url1);

        return "<a{$matches[1]}{$url}{$matches[3]}";
    }, $htmlContent);

    $htmlContent = preg_replace_callback('/<a([^>]*href=[\'"])(http[^\'"]+)([^\'"]*[\'"][^>]*>)/', function($matches) {
        $url = $matches[2];
        
        //$parsedUrl = parse_url($url);

        //if ($parsedUrl['host'] == 'URL') {
        //    $url= '.' . $parsedUrl['path'] . '/index.html';
        //}
        //else {
        //    $url= $parsedUrl['host'] . $parsedUrl['path'];
        //    return "<a{$matches[1]}{$url}{$matches[3]}";
        //}

        //$url= $url . '/index.html';

        //$url = strtok($url, '?'); // remove query parameters

        //$fileName = basename(parse_url($url, PHP_URL_PATH));

        //$url1 = str_replace('<a href="' . $url, "<a href=.", $url1);

        return "<a{$matches[1]}{$matches[3]}";
    }, $htmlContent);

    // Save the HTML content as index.html in the archive directory
    $filename = $dir . '/index.html';
    file_put_contents($filename, $htmlContent);

    echo "Website archiving completed!\n";
}


function removeDirectory($directory) {
    $files = glob($directory . '/*');
    foreach ($files as $file) {
        if (is_dir($file)) {
            removeDirectory($file);
        } else {
            unlink($file);
        }
    }
    rmdir($directory);
}

function extractUrlsFromCSS($cssContent, $cssFile, $htmlUrl) {
    global $dir;


    // Match all occurrences of url()
    //$pattern = '/url\((["\']?)(\.\.\/|http.*?)\1\)/i';
    $pattern = '/url\((["\']?)(.*?)\1\)/i';
    //$pattern = '/url\((["\']?)(\.\.\/.*?)\1\)/i'; //works
    preg_match_all($pattern, $cssContent, $matches);

    // Extract URLs from the matches
    $urls = $matches[2];

    foreach ($urls as $url) {
        if (strpos($url, 'http') === 0) {
            $contentUrl = $url;

        }
        else if (strpos($url, '/') === 0) {
            $contentUrl = $htmlUrl . $url;
        }
        else if (strpos($url, 'data') === 0) {
            continue;
        }
        else {
            $contentUrl = dirname($cssFile) . '/' . $url;

        }

        $content = file_get_contents($contentUrl);

        $contentUrl = strtok($contentUrl, '?'); // remove query parameters    

        $fileName = $dir . '/' . basename($contentUrl);
        file_put_contents($fileName, $content);
    }

    $cssContent = preg_replace_callback('/url\((["\']?)(.*?)\1\)/i', function($matches) {
        $url = $matches[2];

        $url = strtok($url, '?'); // remove query parameters    

        $basename = basename($url);
        return 'url(' . $matches[1] . $basename . $matches[1] . ')';
    }, $cssContent);

    return $cssContent;
}


?>