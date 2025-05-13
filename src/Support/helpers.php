<?php

if (! function_exists('plugin_path')) {
	function plugin_path(string $name, string $path = ''): string
	{
		$plugin = app('plugins.repository')->find($name);

		return $plugin->getPath().($path ? DIRECTORY_SEPARATOR.$path : $path);
	}
}

if (! function_exists('plugin_native')) {
	function plugin_native(string $name): bool
	{
		$plugin = app('plugins.repository')->find($name);

		return $plugin ? true : false;
	}
}

if (! function_exists('plugin_enabled')) {
	function plugin_enabled(string $name): string
	{
		$plugin = app('plugins.repository')->find($name);

		return $plugin && $plugin->isEnabled();
	}
}

if (! function_exists('plugin_logo')) {
	if (! function_exists('plugin_logo')) {
		function plugin_logo(string $name, bool $returnSvg = true): string
		{
			$len = strlen($name);
			// 传入字符串为中文
			if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $name)) {
				$name = mb_substr($name, 0, 1);
			}
			// 传入字符串为纯英文
			elseif (preg_match('/^[A-Za-z]+$/', $name)) {
				$name = strtoupper(substr($name, 0, 2));
			}
			// 传入字符串是其他字符（非中文、非英文）
			else {
				$name = substr($name, 0, 2);
			}

			$text = mb_substr(strtoupper($name), 0, 4);
			$total = unpack('L', hash('adler32', $text, true))[1];
			$hue = $total % 360;
			list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

			$background = "rgb({$r},{$g},{$b})";

			$svg = <<<EOT
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100px" height="100px" viewBox="0 0 512 512" xml:space="preserve">
    <circle cx="250" cy="250" r="230" style="fill:{$background};"/>
    <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"
     font-size="160" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white">{$text}</text>
</svg>
EOT;

			if ($returnSvg) {
				return $svg; // 返回原始 SVG 字符串
			}

			// 返回 base64 编码的 SVG Data URL，适用于 img src
			return 'data:image/svg+xml;base64,' . base64_encode($svg);
		}
	}

}

if (! function_exists('hsv2rgb')) {

	function hsv2rgb($h, $s, $v)
	{
		$r = $g = $b = 0;

		$i = floor($h * 6);
		$f = $h * 6 - $i;
		$p = $v * (1 - $s);
		$q = $v * (1 - $f * $s);
		$t = $v * (1 - (1 - $f) * $s);

		switch ($i % 6) {
			case 0:
				$r = $v;
				$g = $t;
				$b = $p;
				break;
			case 1:
				$r = $q;
				$g = $v;
				$b = $p;
				break;
			case 2:
				$r = $p;
				$g = $v;
				$b = $t;
				break;
			case 3:
				$r = $p;
				$g = $q;
				$b = $v;
				break;
			case 4:
				$r = $t;
				$g = $p;
				$b = $v;
				break;
			case 5:
				$r = $v;
				$g = $p;
				$b = $q;
				break;
		}

		return [
			floor($r * 255),
			floor($g * 255),
			floor($b * 255)
		];
	}
}