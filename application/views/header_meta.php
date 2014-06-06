
        <title><?php
        //@TODO
          if (!empty($article['articleName'])) {
            echo $article['articleName'];
          } else {
            if (empty($title)) {
              $title = BRAND_DOMAIN;
            }
            echo $title;
          }?></title>
        
        <meta name="description" content="<?php
          if (empty($article['articleLongDescription'])) {
            if (empty($article['articleShortDescription'])) {
              // Use default description
              echo BRAND_DOMAIN;
            } else {
              // Use Short Description
              echo $article['articleShortDescription'];
            }
          } else {
            // Use Long Description
            echo $article['articleLongDescription'];
          }
          ?>">
        <meta name="keywords" content="<?php
          if (!empty($article['articleName'])) {
            echo $article['articleName'];
          } else {
            echo BRAND_DOMAIN;
          }?>">
