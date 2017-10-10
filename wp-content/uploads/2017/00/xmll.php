<?php 
header('Content-type: text/xml');
$xmlouyt = "<?xml version=\"1.0\" ?>\n";
$xmlout .= "<reserve>\n";

$db = new PDO('mysql:host=localhost;dbname=bitnami_wordpress','root','toor');
$stmt = $db->prepare("select * from reserve");
$stmt->execute();
while($row = $stmt->fetch()){
	$xmlout	.="<marker ";
	$xmlout .="name=\"".$row['name']. "\" ";
	$xmlout .="address=\"".$row['address']. "\" ";
	$xmlout .="lat=\"".$row['lat']. "\" ";
	$xmlout .="lng=\"".$row['lng']. "\" ";
	$xmlout	.="/>";
 }

$xmlout .= "</reserve>";
echo $xmlout;
?>


