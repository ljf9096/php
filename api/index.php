<?php
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: inline; filename="playlist.txt"');

if (!isset($_GET['url']) || empty(trim($_GET['url']))) die("错误：请提供m3u文件的URL参数，例如：?url=http://example.com/file.m3u\n");
$m3uUrl = trim($_GET['url']);
if (!filter_var($m3uUrl, FILTER_VALIDATE_URL)) die("错误：提供的URL格式无效\n");
$m3uContent = @file_get_contents($m3uUrl);
if ($m3uContent === false) die("错误：无法从提供的URL获取m3u文件内容\n");

$output = "";
$currentGroup = null;
$groupSet = [];
$programs = [];

$lines = explode("\n", $m3uContent);
$i = 0;
while ($i < count($lines)) {
    $line = trim($lines[$i]);
    if (empty($line)) {
        $i++;
        continue;
    }
    if (strpos($line, '#EXTINF:') === 0) {
        $groupTitle = "";
        $programName = "";
        preg_match('/group-title="([^"]+)"/', $line, $matches) && $groupTitle = $matches[1];
        preg_match('/,(.*)$/', $line, $matches) && $programName = trim($matches[1]);
        $i++;
        $programUrl = $i < count($lines) && !empty(trim($lines[$i])) ? trim($lines[$i]) : null;
        if (!empty($programName) && !empty($programUrl)) $programs[] = ['group' => $groupTitle, 'name' => $programName, 'url' => $programUrl];
        $i++;
    } else {
        $i++;
    }
}

$currentGroup = null;
foreach ($programs as $program) {
    if ($program['group'] !== $currentGroup) {
        if (!in_array($program['group'], $groupSet)) {
            $output .= "$program[group],#genre#\n";
            $groupSet[] = $program['group'];
        }
        $currentGroup = $program['group'];
    }
    $output .= "$program[name],$program[url]\n";
}

echo $output;
?>
