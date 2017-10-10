<?php 
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$radius = $_GET["radius"];
header("Access-Control-Allow-Origin: *");
header('Content-type: text/xml');
$xmlouyt = "<?xml version=\"1.0\" ?>\n";
$xmlout .= "<halals>\n";



$db = new PDO('mysql:host=localhost;dbname=bitnami_wordpress','root','toor');
$stmt = $db->prepare("SELECT Name, Address, lat, lng, ( 3959 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance FROM halal_restaurants HAVING distance < ? ORDER BY distance LIMIT 0 , 100");
$stmt->execute(array($center_lat,$center_lng,$center_lat,$radius));
while($row = $stmt->fetch()){
	$xmlout	.="<marker ";
	$xmlout .="name=\"".$row['Name']. "\" ";
	$xmlout .="address=\"".$row['Address']. "\" ";
	$xmlout .="phone=\"".$row['Phone']. "\" ";
	$xmlout .="website=\"".$row['Website']. "\" ";
	$xmlout .="lat=\"".$row['lat']. "\" ";
	$xmlout .="lng=\"".$row['lng']. "\" ";
	$xmlout .="distance=\"".$row['distance']. "\" ";
	$xmlout	.="/>";
 }

$xmlout .= "</halals>";
echo $xmlout;
?>


