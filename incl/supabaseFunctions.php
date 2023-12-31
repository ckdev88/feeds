<?php
require('supabase.php');
if (isset($_POST['updatefeeds'])) listFeedsUpdate();

function addFeed()
{
	if (isset($_POST['addfeed'])) {
		global $service;
		if (boolval($_POST['addfeed']) === true) {
			$db = $service->initializeDatabase('feeds', 'id');

			if (
				$_POST['addfeed-name'] != ''
				&& $_POST['addfeed-url'] != ''
			) {
				$newFeed = [
					'name' => $_POST['addfeed-name'],
					'url'       => $_POST['addfeed-url'],
					'interval'  => (int)$_POST['addfeed-interval'],
					'hidden'  => (isset($_POST['hidden']) ? true : false),
				];

				try {
					$data = $db->insert($newFeed);
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}
		}
	}
}
function ListFeeds()
{
	global $service;
	$db = $service->initializeDatabase('feeds', 'id');

	try {
		$listFeeds = $db->fetchAll()->getResult(); // fetch all feeds
		return $listFeeds;
	} catch (Exception $e) {
		echo $e->getMessage();
	}
}

function ListFeedsUpdate()
{
	if (isset($_POST['updatefeeds'])) {
		global $service;
		$db = $service->initializeDatabase('feeds', 'id');

		$updatedata = $_POST['updatefield'];
		if (is_array($updatedata)) {
			try {
				foreach ($updatedata as $key => $feed) {
					$updateFeed = [
						'name' => $feed[0],
						'url' => $feed[1],
						'interval' => (int)$feed[2],
						'hidden' => (!isset($feed[3]) ? false : true)
					];
					$data = $db->update((string)$key, $updateFeed);
				}
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
	}
}
