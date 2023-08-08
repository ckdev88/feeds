<?php
function getFeeds($groupby = 'datum', $interval)
{
	$feeds = getFeedsArr();
	$html = '';
	$nu = strtotime('now');
	$interval = $nu - $interval;
	$count = 0;

	// platslaan en gewoon een goede array
	$newArray = array();
	$rnCount = 2000;
	foreach ($feeds as $feedKey => $feedVal) {
		$xml = simplexml_load_file($feedVal['url'] . $feedVal['rss_suffix'], "SimpleXMLElement", LIBXML_NOERROR |  LIBXML_ERR_NONE);
		$xmlPath = $xml->xpath('//item');
		if (empty($xmlPath)) { // exception 1, in case of hidde.blog & front-end.social
			$xmlPath = $xml;
			foreach ($xmlPath as $xmll) {
				if (!isset($xmll->id)) continue;
				$newArray[$feedKey][$rnCount]['title'] = (string)$xmll->title;
				if (!isset($xmll->title)) $newArray[$feedKey][$rnCount]['title'] = (string)$xmll->description;
				$newArray[$feedKey][$rnCount]['description'] =  '';
				$newArray[$feedKey][$rnCount]['link'] = (string)$xmll->id;
				$newArray[$feedKey][$rnCount]['pubDate'] = (string)$xmll->updated;
				$newArray[$feedKey][$rnCount]['name'] = $feedVal['name'];
				$newArray[$feedKey][$rnCount]['new_window'] = $feedVal['new_window'];
				$rnCount++;
			}
		} else {
			foreach ($xmlPath as $itemKey => $itemVal) {
				$newArray[$feedKey][$itemKey]['title'] = (string)$itemVal->title;
				if ($newArray[$feedKey][$itemKey]['title'] == '') {
					$newArray[$feedKey][$itemKey]['title'] = (string)$itemVal->description;
					$newArray[$feedKey][$itemKey]['description'] = ''; // TODO: not functioning properly
				} else {
					$newArray[$feedKey][$itemKey]['description'] = (string)$itemVal->description; // TODO: not functioning properly
				}
				$newArray[$feedKey][$itemKey]['link'] = (string)$itemVal->link;
				$newArray[$feedKey][$itemKey]['pubDate'] = (string)$itemVal->pubDate;
				$newArray[$feedKey][$itemKey]['name'] = $feedVal['name'];
				$newArray[$feedKey][$itemKey]['new_window'] = $feedVal['new_window'];
			}
		}
	}

	$entries = array(); // voor sort op datum, onafhankelijk van blog
	foreach ($newArray as $item) {
		$entries = array_merge($entries, $item);
	}
	if ($groupby == 'datum') {
		usort($entries, function ($feed1, $feed2) {
			return strtotime($feed2['pubDate']) - strtotime($feed1['pubDate']);
		});
	}
	if (empty($entries)) $html .= 'no entries';
	$html .= '<ul>';
	$tmpBlogname = '';
	foreach ($entries as $entry) {
		if (strtotime($entry['pubDate']) < $interval) continue;

		$pubDate = strftime('%m/%d/%Y %I:%M %p', strtotime($entry['pubDate']));
		$pubDate2 = strftime('%H:%M', strtotime($entry['pubDate']));
		$count++;

		if ($groupby == 'blog') {
			if ($entry['name'] != $tmpBlogname) {
				$html .= '<li class="msg" style="font-weight:bold;color:lime;"><h3>' . $entry['name'] . '</h3></li>';
			}
			$tmpBlogname = $entry['name'];
		}

		$html .= '<li class="msg">';
		// $html .= expandButton($count);
		$html .= msgLink(
			(string)$entry['link'],
			$pubDate2,
			(string)$entry['title'],
			(isset($entry['name']) && $groupby == 'datum' ? $entry['name'] : ''),
			$entry['new_window']
		);
		$html .= msgDescription(
			$count,
			$pubDate,
			(isset(parse_url($entry['link'])['host']) ? str_replace('www.', '', parse_url($entry['link'])['host']) : ''),
			$entry['title'],
			$entry['description'],
			$entry['link']
		);
		$html .= '</li>';
	}
	$html .= '</ul>';

	return $html;
}