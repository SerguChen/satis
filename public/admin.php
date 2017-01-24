<center>
<br/>
<div style="width:400px;">
<form action="/admin.php" method="GET">
<input name="q" type="text" style="width:300px;height:35px;line-height:35px;font-size:25px;"/>
<input type="submit" value="search" style=";height:35px;line-height:35px;font-size:25px;" />
</form>
</div>
</center>
<style>
#list label {font-size:20px;line-height:25px;height:25px;}
.span { margin-left:10px;}
</style>
<script>
	function merge()
	{
		document.getElementById("merge").onclick=function(){


		}
	}
</script>
<?php

$q = isset($_GET['q'])? $_GET['q']:"";
$p = isset($_GET['p'])?intval($_GET['p']):1;

echo "<center><div id='list' style='width:400px;text-align:left'>";

if ($q) {
	$url = "https://packagist.org/search.json?q=" . $q . "&page=" . $p;
	$result = curl_text($url);
	$json = @json_decode($result,true);
	$next = $json['next'];
	$p++;
	if ($json) {
		echo "<h1>search packages</h1>";
		foreach ($json['results'] as $result) {
			echo "<label><input type='checkbox' name='".$result['name']."' value='".$result['name']."'/>" . $result['name'] . "</label><span class='span'><a id='merge' href='admin.php?q=" . $q . "&page=" . $p."&name=".$result['name']."&repository=".base64_encode($result['repository'])."'>at</a></span>
<br/>";
		}
		if ($next) {
			echo "<a href='/admin.php?q=".$q."&p=".$p."'>next</a>";
		}

		$repository = isset($_GET['repository']) ? $_GET['repository']:'';
		$name = isset($_GET['name']) ? $_GET['name']:'';
		if(!empty($repository) && !empty($name)){
			repository($name,$repository);
		}




		echo "<pre>";
		//print_r($json);
		//print_r($json);

		
		echo "</pre>";
	}
}





$curr = get_curr();
echo "<h1>local packages</h1>";
foreach ($curr['require'] as $pack_name=>$pack_value) {
	echo "<label><input type='checkbox' name='".$pack_name."' value='".$pack_name."'/>" . $pack_name . "</label><br/>";
}


echo "</div></center>";


function get_curr() {
	$s = file_get_contents(__DIR__ . "/../satis.json");
	$json = @json_decode($s,true);
	return $json;
}


function curl_text($url) {
	set_time_limit(60);
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	$result = curl_exec($ch);

	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($httpCode !== 200 || false === $result) {
		$err = curl_error($ch);
		curl_close($ch);
		throw new \Exception('网络异常:' . $err);
	}

	curl_close($ch);
	return $result;
}

function repository($name,$repository){
	$fileGet = file_get_contents(__DIR__ . "/../satis.json");

	$fileData = json_decode($fileGet,true);
	$requireName = array($name => '*');


		$merge = array_merge($fileData['require'], $requireName);

	$fileData['require'] = $merge;

	$repositoriesData[] = array(
		'type' => 'git',
		'url'  => base64_decode($repository)
	);
	$url=array();
	foreach($fileData['repositories'] as $k=>$v) {
		$url[] = $v['url'];
	}

	if (!in_array(base64_decode($repository), $url)) {

		$newData = array_merge($fileData['repositories'], $repositoriesData);
		$fileData['repositories'] = $newData;
	}
	echo '<pre>';
	print_r($fileData['repositories']);
	echo '</pre>';
		file_put_contents(__DIR__ . "/../satis.json",json_encode($fileData));


}



