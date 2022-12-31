<?php
if(!defined("doc")) define("doc", $_SERVER['DOCUMENT_ROOT']);

function vite()
{
	$url = config('app_url');

	if(config('app_mode') != 'production')
	{
		echo <<<EOD
		<script type="module" src="${url}:5173/@vite/client"></script>
        <script type="module" src="${url}:5173/src/main.js"></script>
EOD;
	}
	else
	{
		$src = rootDir()."/dist/manifest.json";
		
		if(file_exists($src))
		{
			$content = file_get_contents($src);
			
			if($content)
			{
				$manifest = json_decode($content, true);
			    $js = $manifest["src/main.js"]['file'];
			    $css = $manifest["src/main.css"]['file'];
	        }
		
		    echo <<<EOD
		    <link rel="stylesheet" href="${url}/dist/${css}" />
		    <script type="module" src="${url}/dist/${js}"></script>
EOD;
        }
    }
}

function config($key)
{
	$config = include rootDir().'/config/app.php';

	return isset($config[$key]) ? $config[$key]: null;
}
?>