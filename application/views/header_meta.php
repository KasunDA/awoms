<title><?php
    if (!empty($page['pageName'])) {
        echo $page['pageName'];
    } elseif (!empty($article['articleName'])) {
        echo $article['articleName'];
    } else {
        if (empty($title)) {
            $title = BRAND_DOMAIN;
        }
        echo $title;
    }
    ?></title>

<meta name="description" content="<?php
    if (!empty($page['pageLongDescription'])) {
        echo $page['pageLongDescription'];
    } elseif (!empty($page['pageShortDescription'])) {
        echo $page['pageShortDescription'];
    } elseif (!empty($article['articleLongDescription'])) {
        echo $article['articleLongDescription'];
    } elseif (!empty($article['articleShortDescription'])) {
        echo $article['articleShortDescription'];
    } else {
        echo $title;
    }
?>">

<meta name="keywords" content="<?php
if (!empty($article['articleName'])) {
    echo $article['articleName'];
} elseif (!empty($page['pageName'])) {
    echo $page['pageName'];
} else {
    echo $title;
}
?>">