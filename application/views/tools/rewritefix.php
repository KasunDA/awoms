<?php
$Domain = new Domain();

echo "<hr /><h1>Current map.txt:</h1>";
$map = ROOT . DS . "url-alias-map.txt";
$handle = @fopen($map, "r");
if ($handle) {
    echo "<table>";
    while (($line = fgets($handle, 4096)) !== false) {
        $rule = explode(' ', $line);
        $alias = trim($rule[0]);
        $actual = trim($rule[1]);
        if ($alias[0] == "/")
        {
            $domainName = "Global";
        }
        else
        {
            // Domain alias beings with "domain."
            $d = explode('/', $alias);
            $domainName = $d[0];
        }
        echo "<tr><td><strong>Domain:</strong></td><td>".$domainName."</td><td><strong>Alias:</strong></td><td>".$alias."</td><td><strong>Actual:</strong></td><td>".$actual."</td></tr>";
        
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
    echo "</table>";
}

echo "<hr /><h1>Current db.map:</h1>";
echo "<table>";
$RewriteMapping = new RewriteMapping();
foreach ($RewriteMapping->getWhere() as $rewriteRule)
{
    if (!empty($rewriteRule['domainID']))
    {
        $d = $Domain->getSingle(array('domainID' => $rewriteRule['domainID']));
        $domainName = $d['domainName'];
    } else {
        $domainName = "";
    }
    $alias = $rewriteRule['aliasURL'];
    $actual = $rewriteRule['actualURL'];
    
    echo "<tr><td><strong>Domain:</strong></td><td>".$domainName."</td><td><strong>Alias:</strong></td><td>".$alias."</td><td><strong>Actual:</strong></td><td>".$actual."</td>";
    
    $found = $RewriteMapping->rewriteRuleExists($domainName.$rewriteRule['aliasURL']);
    
    echo "<td><strong>Found in map.txt:</strong></td>";
    if (empty($found))
    {
        echo "<td>NO, creating...</td></tr>";
        $data = array('rewriteMappingID' => $rewriteRule['rewriteMappingID'],
            'aliasURL' => $rewriteRule['aliasURL'],
            'actualURL' => $rewriteRule['actualURL'],
            'sortOrder' => $rewriteRule['sortOrder'],
            'domainID' => $rewriteRule['domainID']);
        $RewriteMapping->update($data);
        
    } else {
        echo "<td>Yes</td></tr>";
    }
}

echo "</table>";