<?php

set_time_limit(0);

if(!isset($_POST['url']) && !isset($_POST['mode'])) {
    echo "No POST arguments";
    exit;
}

$url = $_POST['url']; //POST
$mode = $_POST['mode']; //POST


$date = date("YmdHi");

if($mode == 0) {
    $parsedUrl = parse_url($url);
    $dir =  "archive/$date/$mode/" . $parsedUrl['host'] . $parsedUrl['path'];

    archive($url);

    addWebsiteRecord($url, $dir, $date, $mode);

    echo "$dir" . "\n";
}
else if($mode == 1){
    $hrefUrls = getHrefUrls($url);
    $result = $hrefUrls;

    $result = array_unique($result);

    $count = count($result);
    $step = 1;


    foreach ($result as $res) {
        $log = $res . ' ' . $step. '/' . $count . "\n";
        error_log($log);

        $parsedUrl = parse_url($res);
        $dir =  "archive/$date/$mode/" . $parsedUrl['host'] . $parsedUrl['path'];

        archive($res);
        if($step == 1) {
            addWebsiteRecord($url, $dir, $date, $mode);

            echo "$dir" . "\n";
        }

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

    foreach ($result as $res) {
        $log = $res . ' ' . $step. '/' . $count . "\n";
        error_log($log);

        $parsedUrl = parse_url($res);
        $dir =  "archive/$date/$mode/" . $parsedUrl['host'] . $parsedUrl['path'];

        archive($res);

        if($step == 1) {
            addWebsiteRecord($url, $dir, $date, $mode);
            
            echo "$dir" . "\n";
        }

        $step = $step + 1;
        
    }

}
else {
    echo "Wrong mode!";
    exit;
}


echo "Website archiving completed!\n";


function getHrefUrls($url) {
    $htmlContent = file_get_contents($url);

    if ($htmlContent === false) {
        echo "Failed to fetch website content.";
        return;
    }

    $hrefUrls = [];
    $hrefUrls[] = $url;

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


        if(isset($parsedUrl['host']) && isset($parsedUrl2['host']) && ($parsedUrl['host'] == $parsedUrl2['host'])){
            $save = 'https://' . $parsedUrl2['host'] . $parsedUrl2['path'];
            $hrefUrls[] = $save;
        }

    }, $htmlContent);

    return array_unique($hrefUrls);
}
////

function archive($url) {
    global $dir;
    global $date;
    global $mode;


    // Fetch the HTML content of the requested URL
    $htmlContent = file_get_contents_2($url);

    $parsedUrl = parse_url($url);
    $url= 'https://' . $parsedUrl['host'];

    if (!is_dir('archive')) {
        echo "Directory archive not created!";
        exit;
    }


    mkdir($dir,0777,true);

    $htmlContent = str_replace('src="//', 'src="https://', $htmlContent);
    $htmlContent = str_replace('href="//', 'href="https://', $htmlContent);
    $htmlContent = str_replace('src="/', 'src="' . $url . '/', $htmlContent);
    $htmlContent = str_replace("src='/", "src='" . $url . '/', $htmlContent);
    $htmlContent = str_replace('href="/', 'href="' . $url . '/', $htmlContent);
    $htmlContent = str_replace("href='/", "href='" . $url . '/', $htmlContent);


    // Download linked CSS, JS, and images
    preg_match_all('/<link[^>]+href=[\'"]([^\'"]+)[\'"][^>]*>/', $htmlContent, $matches);
    $cssUrls = $matches[1];

    foreach ($cssUrls as $cssUrl) {

        global $dir;

        $cssContent = file_get_contents_2($cssUrl);

        $cssContent = extractUrlsFromCSS($cssContent, $cssUrl, $url);

        $cssUrl = strtok($cssUrl, '?'); // Remove query parameters
        $cssFilename = $dir . '/' . basename($cssUrl);
        file_put_contents($cssFilename, $cssContent);
    }

    preg_match_all('/<script[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/', $htmlContent, $matches);
    $jsUrls = $matches[1];

    foreach ($jsUrls as $jsUrl) {
        global $dir;

        $jsContent = file_get_contents_2($jsUrl);
        $jsUrl = strtok($jsUrl, '?'); // Remove query parameters
        $jsFilename = $dir . '/' . basename($jsUrl);
        file_put_contents($jsFilename, $jsContent);
    }

    preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/', $htmlContent, $matches);
    $imageUrls = $matches[1];

    foreach ($imageUrls as $imageUrl) {
        global $dir;

        $imageContent = file_get_contents_2($imageUrl);
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
        $importContent = file_get_contents_2($importUrl);
            
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

    $htmlContent = str_replace('<a href="' . $url, '<a href="' . "/archive/$date/$mode/" . $parsedUrl['host'], $htmlContent);

    $htmlContent = preg_replace_callback('/<a([^>]*href=[\'"])([^\'"]+)([^\'"]*[\'"][^>]*>)/', function($matches) {
        $url = $matches[2];

        $url= $url . '/index.html';

        return "<a{$matches[1]}{$url}{$matches[3]}";
    }, $htmlContent);

    $htmlContent = preg_replace_callback('/<a([^>]*href=[\'"])(http[^\'"]+)([^\'"]*[\'"][^>]*>)/', function($matches) {
        $url = $matches[2];

        return "<a{$matches[1]}{$matches[3]}";
    }, $htmlContent);

    
    $navigationDiv = '
    <div id="navigationWebsites">
    Other versions: <select id="websiteDropdown"></select><button id="webbutton" onclick="redirect()">Go</button>
    </div>
    ';

    $scriptDiv ='
    <link rel="stylesheet" type="text/css" href="/styles/navigation.css">
    <script type="text/javascript" src="/scripts/navigation.js" defer></script>
    ';

    $htmlContent = str_replace('</body>', $navigationDiv . '</body>', $htmlContent);

    $htmlContent = str_replace('</head>', $scriptDiv . '</head>', $htmlContent);

    // Save the HTML content as index.html in the archive directory
    $filename = $dir . '/index.html';
    file_put_contents($filename, $htmlContent);
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
    $pattern = '/url\((["\']?)(.*?)\1\)/i';
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

        $content = file_get_contents_2($contentUrl);

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


function addWebsiteRecord($url, $dir, $date, $mode) {
    $host = 'localhost';
    $dbname = 'webarchiver';
    $username = 'webuser';
    $password = 'pass@webuser';
    $port = '3306';

    try {
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        return;
    }

    try {
        $stmt = $conn->prepare('INSERT INTO websites (url, dir, date, mode) VALUES (?, ?, ?, ?)');
        $stmt->execute([$url, $dir, $date, $mode]);
    } catch(PDOException $e) {
        echo 'Error adding record: ' . $e->getMessage();
    }

    $conn = null;
}

function file_get_contents_2($url) {
    $headers = get_headers($url);
    if(strpos($headers[0], '200') !== false) {
        return file_get_contents($url);
    } else {
        return '';
    }
}

?>