<title><?php
    // Admins use different title
    if (!defined(BRAND_TITLE)
            || (!empty($_SESSION['user_logged_in'])
                && $_SESSION['user']['usergroup']['usergroupName'] == "Administrators"))
        {
        $metaTitle = $this->title;
    } else {
        if (empty($metaTitle)) {
            // Default BRAND_TITLE is set in Brand Meta Title setting
            $metaTitle = BRAND_TITLE;
        }
    }
    echo $metaTitle;
    ?></title>
<meta name="description" content="<?php echo !empty($metaDescription) ? $metaDescription : $metaTitle; ?>">
<meta name="keywords" content="<?php echo !empty($metaKeywords) ? $metaKeywords : $metaTitle; ?>">