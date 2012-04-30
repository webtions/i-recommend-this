<?php
require_once '../../../wp-config.php';

global $wpdb;
$post_ID = $_POST['id'];
$ip = $_SERVER['REMOTE_ADDR'];
$recommend = get_post_meta($post_ID, '_recommended', true);

if($post_ID != '') {
	$voteStatusByIp = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."irecommendthis_votes WHERE post_id = '$post_ID' AND ip = '$ip'");
	
    if (!isset($_COOKIE['recommended-'.$post_ID]) && $voteStatusByIp == 0) {
		$recommendNew = $recommend + 1;
		update_post_meta($post_ID, '_recommended', $recommendNew);

		setcookie('liked-'.$post_ID, time(), time()+3600*24*365, '/');
		$wpdb->query("INSERT INTO ".$wpdb->prefix."irecommendthis_votes VALUES ('', NOW(), '$post_ID', '$ip')");

		echo $recommendNew.' recommends'; // .' lights' is added new
	}
	else {
		echo $recommend.' recommends'; // .' lights' is added new
	}
}
?>