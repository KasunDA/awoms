<?php

class InstallController extends Controller
{
    public function wizard()
    {
        // @TODO No wizard if already setup (current work around in database class, if error contains missing table, redirects to wizard)
        // Need to query if table exists, otherwise get error when trying to query for brands list when table not there yet
        $Brand = new Brand();
        $found = $Brand->getWhere(array('brandActive' => 1));
        if (!empty($found)) {
            header('Location: /admin/home');
            exit(0);
        }

        // Install default data
        if (!empty($_POST['step'])) {
            $Install = new Install();
            $Install->InstallDefaultData($_POST['inp_brandName'], $_POST['inp_brandLabel'], $_POST['inp_adminUsername'],
                                         $_POST['inp_adminPassphrase'], $_POST['inp_adminEmail'], $_POST['inp_domainName']);
        }
    }
    
    public function sample()
    {
        $Install = new Install();
        $resultsMsg = $Install->PopulateSampleData();
        $this->set('resultsMsg', $resultsMsg);
    }

}
