<h1>Tests</h1>

<?php
$raw = "Goin' Postal & Co. test' test\" test";
echo "Raw:";
var_dump($raw);
echo "<hr />";

$results = array();
$results[] = $raw;
$results[] = htmlspecialchars($raw, ENT_COMPAT | ENT_HTML401, "UTF-8", TRUE);
$results[] = htmlspecialchars($raw, ENT_COMPAT | ENT_HTML401, "UTF-8", FALSE);
$results[] = htmlspecialchars($raw, ENT_QUOTES, "UTF-8", TRUE);
$results[] = htmlspecialchars($raw, ENT_QUOTES, "UTF-8", FALSE); // Win with quotes
$results[] = htmlspecialchars($raw, ENT_NOQUOTES, "UTF-8", TRUE);
$results[] = htmlspecialchars($raw, ENT_NOQUOTES, "UTF-8", FALSE);
var_dump($results);
foreach ($results as $r)
{
    echo "<br/>r: ".$r." <input type='text' value='".$r."'/>";
}

$encoded = "Test&amp;#039; test&amp;quot; test &amp;amp; Co. &gt;&lt; face";
echo "<hr />Encoded:";
var_dump($encoded);
echo "<hr />";

$results = array();
$results[] = $encoded;
$results[] = htmlspecialchars_decode($encoded, ENT_COMPAT | ENT_HTML401);
$results[] = htmlspecialchars_decode($encoded, ENT_QUOTES);
$results[] = htmlspecialchars_decode($encoded, ENT_NOQUOTES); // Win with quotes

var_dump($results);
foreach ($results as $r)
{
    echo "<br/>r: ".$r." <input type='text' value='".$r."' size='60'/>";
}