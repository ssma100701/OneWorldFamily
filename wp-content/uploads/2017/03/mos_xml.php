<?php 
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$radius = $_GET["radius"];
// header("Access-Control-Allow-Origin: *");
header('Content-type: text/xml');
$xmlouyt = "<?xml version=\"1.0\" ?>\n";
$xmlout .= "<mosques>\n";



$db = new PDO('mysql:host=localhost;dbname=bitnami_wordpress','root','toor');
$stmt = $db->prepare("SELECT name, address, lat, lng, ( 3959 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance FROM mosques HAVING distance < ? ORDER BY distance LIMIT 0 , 100");
$stmt->execute(array($center_lat,$center_lng,$center_lat,$radius));
while($row = $stmt->fetch()){
	$xmlout	.="<marker ";
	$xmlout .="name=\"".$row['name']. "\" ";
	$xmlout .="address=\"".$row['address']. "\" ";
	$xmlout .="phone=\"".$row['phone']. "\" ";
	$xmlout .="website=\"".$row['website']. "\" ";
	$xmlout .="lat=\"".$row['lat']. "\" ";
	$xmlout .="lng=\"".$row['lng']. "\" ";
	$xmlout .="distance=\"".$row['distance']. "\" ";
	$xmlout	.="/>";
 }

$xmlout .= "</mosques>";
echo $xmlout;
?>


