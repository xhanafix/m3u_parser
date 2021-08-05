<?php

class m3u_parser {
	private $content;
	private $parsed;

	// M3U Parser constructor
	function __construct ($path = '') {
		if (preg_match("#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#iS", $path)) {
			$this->load_url($path);
		} else {
			$this->load_file($path);
		}
	}

	// Load from URL
	function load_url ($url) {
		$this->content = preg_split('/\r\n|\r|\n/', file_get_contents($url));
	}

	// Load from filename
	function load_file ($filename) {
		if (file_exists($filename)) {
			$this->content = preg_split('/\r\n|\r|\n/', file_get_contents($filename));
		} else {
			$this->content = '';
		}
	}

	// Convert M3U to array
	function parse () {
		$output = [];
		$entry  = [];
		$group  = '';
		foreach ($this->content as $line) {
			if (preg_match('/\#EXTM3U/i', $line)) {
				continue;
			}
			if (preg_match('/\#EXTINF/i', $line)) {
				$group = '';
				if (preg_match('/\#EXTINF:(?P<play_length>-?\d*\.?\d+)/i', $line, $result)) {
					$entry['play_length'] = $result['play_length'];
				}
				if (preg_match('/(?<=channel-id=")(?P<channel_id>.*?)(?=")/i', $line, $result)) {
					$entry['channel_id'] = $result['channel_id'];
				}
				if (preg_match('/(?<=radio=")(?P<radio>.*?)(?=")/i', $line, $result)) {
					$entry['radio'] = json_decode($result['radio']) == true;
				}
				if (preg_match('/(?<=tvg-id=")(?P<tvg_id>.*?)(?=")/i', $line, $result)) {
					$entry['tvg_id'] = $result['tvg_id'];
				}
				if (preg_match('/(?<=tvg-name=")(?P<tvg_name>.*?)(?=")/i', $line, $result)) {
					$entry['tvg_name'] = $result['tvg_name'];
				}
				if (preg_match('/(?<=tvg-logo=")(?P<tvg_logo>.*?)(?=")/i', $line, $result)) {
					$entry['tvg_logo'] = $result['tvg_logo'];
				}
				if (preg_match('/(?<=tvg-shift=")(?P<tvg_shift>.*?)(?=")/i', $line, $result)) {
					$entry['tvg_shift'] = $result['tvg_shift'];
				}
				if (preg_match('/(?<=tvg-chno=")(?P<tvg_chno>.*?)(?=")/i', $line, $result)) {
					$entry['tvg_chno'] = $result['tvg_chno'];
				}
				if (preg_match('/(?<=group-title=")(?P<group_title>.*?)(?=")/i', $line, $result)) {
					$group = $result['group_title'];
				}
				if (preg_match('/(?<=parent-code=")(?P<parent_code>.*?)(?=")/i', $line, $result)) {
					$entry['parent_code'] = $result['parent_code'];
				}
				if (preg_match('/(?<=audio-track=")(?P<audio_track>.*?)(?=")/i', $line, $result)) {
					$entry['audio_track'] = $result['audio_track'];
				}
				if (preg_match('/(?<=,)(?P<name>.*?)$/i', $line, $result)) {
					$entry['name'] = $result['name'];
				} else {
					$entry['name'] = '';
				}
			} elseif (preg_match("#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#iS", $line)) {
				$entry['url'] = $line;
				$entry['type'] = 'Live';
				if (preg_match('/(?P<ext>[0-9a-z]+)(?:[\?#]|$)/i', $line, $result)) {
					$entry['ext'] = $result['ext'];
				} 
				if (preg_match('/.*\/movie\//i', $line) || preg_match('/.*\/series\//i', $line) || preg_match('/.*\/play\/vod\//', $line)) {
					$entry['type'] = 'VOD';
				}
				if (array_key_exists($group, $output)) {
					array_push($output[$group], $entry);
				} else {
					$output[$group] = [$entry];
				}
				$entry = [];
			}
		}
		return $output;
	}
}
