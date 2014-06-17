<?php

class RewriteMapping extends Model
{
    protected static function getRewriteMappingColumns()
    {
        $cols = array('rewriteMappingID', 'aliasURL', 'actualURL', 'sortOrder', 'domainID');
        return $cols;
    }
    
    public function update($data, $table = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        
        // Remove Alias Map Rewrite Rule if already Exists
        $Domain = new Domain();
        $matchDomain = $Domain->getSingle(array('domainID' => $data['domainID']));
        $r = self::rewriteRuleExists($matchDomain['domainName'].$data['aliasURL']);
        if (!empty($r))
        {
            // Delete existing
            self::removeRewriteRule($data['aliasURL'], $matchDomain['domainName'], $matchDomain['domainID']);
        }
        
        Errors::debugLogger(__METHOD__.' *** Updating rewrite rule in DB: ');
        Errors::debugLogger($data);
        
        // DB Changes
        parent::update($data);
        
        // Create new rewrite rule
        $r = self::rewriteRuleExists($matchDomain['domainName'].$data['aliasURL']);
        if (!empty($r))
        {
            if (!empty($matchDomain))
            {
                $alias = $matchDomain['domainName'].$data['aliasURL'];
            } else {
                $alias = $data['aliasURL'];
            }
            self::createRewriteRule($alias, $data['actualURL']);
        }
    }
    
    private function createRewriteRule($alias, $actual)
    {
        $map = ROOT . DS . "url-alias-map.txt";
        $current = file_get_contents($map);
        $new = $alias." ".$actual."\n";
        $current .= $new;
        Errors::debugLogger(__METHOD__.' *** Adding rewrite rule to map: '.$alias." => ".$actual);
        file_put_contents($map, $current);
    }
    
    public static function removeRewriteRule($alias, $domainName, $domainID)
    {
        Errors::debugLogger(__METHOD__.' *** Removing rewrite rule from DB and rewrite map: aliasURL:'.$alias.' domainID:'.$domainID.' ('.$domainName.') ...');
        
        // Remove from DB
        $RewriteMapping = new RewriteMapping();
        $RewriteMapping->delete(array('domainID' => $domainID,
            'aliasURL' => $alias));
        $alias = $domainName.$alias;
        
        // Remove from txt map file
        $map = ROOT . DS . "url-alias-map.txt";
        $handle = @fopen($map, "rw");
        if ($handle) {
            $new = "";
            while (($line = fgets($handle, 4096)) !== false) {
                $rule = explode(' ', $line);
                $fileAlias = trim($rule[0]);
                if ($fileAlias == $alias)
                {
                    #Errors::debugLogger(__METHOD__.' *** Removing: '.$fileAlias);
                    continue;
                }
                $new .= $line;
            }
#            if (!feof($handle)) {
#                echo "Error: unexpected fgets() fail\n";
#            }
            fclose($handle);
        }
        return file_put_contents($map, $new);
    }
    
    /**
     * Checks if rewrite rule exists
     * 
     * @param string $alias dev.test.com/myalias
     * @param int $domainID
     * 
     * @return boolean
     */
    private function rewriteRuleExists($alias)
    {
        // Matching terms
        $matchAliasURL = $alias;
        
        // Alias Map Rewrite
        $map = ROOT . DS . "url-alias-map.txt";
        $handle = @fopen($map, "r");
        $found = FALSE;
        
        // Domain lookup
        $Domain = new Domain();
        if ($handle) {
            while (($line = fgets($handle, 4096)) !== false) {
                $rule = explode(' ', $line);
                $alias = trim($rule[0]);
                $actual = trim($rule[1]);
                #Errors::debugLogger(__METHOD__.': *** looking for alias: '.$alias.' => actual: '.$actual);
                if ($alias[0] == "/")
                {
                    // Global alias begins with "/" - ignoring domain name
                    if (preg_match('/'.str_replace('/', '\/', $matchAliasURL).'/', $alias))
                    {
                        #Errors::debugLogger(__METHOD__.': *** Found Global Match');
                        $found = TRUE;
                        break;
                    }
                }
                else
                {
                    // Domain alias beings with "domain."
                    $d = explode('/', $alias);
                    $matchDomain = $Domain->getSingle(array('domainName' => $d[0]));
                    if (preg_match('/'.str_replace('/', '\/', $matchAliasURL).'/', $alias))
                    {
                        #Errors::debugLogger(__METHOD__.': *** Found Domain Match');
                        $found = TRUE;
                        $res['domainID'] = $matchDomain['domainID'];
                        break;
                    }
                }
            }
#            if (!feof($handle)) {
#                echo "Error: unexpected fgets() fail\n";
#            }
            fclose($handle);
        }
        
        $res['found'] = $found;
        $res['actual'] = $actual;
        if ($found === TRUE)
        {
            return $res;
        }
        return false;
    }
}