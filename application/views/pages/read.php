<!-- Template Output -->
<div id='view'>

  <article>
    <header>
      <h1><?=$page['pageName'];?></h1>
    </header>
    <section>
    <?php
        echo html_entity_decode($page['pageBody']['bodyContentText'], ENT_NOQUOTES);
    ?>
    </section>
    
<?php
/*
 * @TODO configurable per page
 * 
    <section>
      <h2>Comments</h2>
      <?php
        if (empty($page['comments'])) {
          echo "<p>Be the first to <a href='".BRAND_URL."comments/create/".$page['pageID']."'>add a comment!</a></p>";
        } else {
          echo "<p><a href='".BRAND_URL."comments/create/".$page['pageID']."'>Add a comment!</a></p>
            <ul>";
          $ci = 0;
          $clvl = 0;
          foreach ($page['comments'] as $comment) {
            $ci++;
            // What level of comment are we on
            if ($comment['level'] > $clvl) {
              $clvl = $comment['level'];
              echo '<ul>';
            } elseif ($comment['level'] < $clvl) {
              for ($u=0; $u<($clvl-$comment['level']); $u++) {
                echo "</ul>";
              }
              echo '</ul><ul>';
              $clvl = 1;
            }
            echo "
              <li>
                <cite><a name='".$comment['commentID']."'>#</a>".$comment['commentID']." ".$comment['commentDatePublished']."</cite> <a href='".BRAND_URL."comments/create/".$page['pageID']."/".$comment['commentID']."'>reply</a>
                <br />".$comment['commentBodyText']."
              </li>";
          }
          echo '</ul>';
        }
      ?>
    </section>
  </article>
  
  <aside>
    <cite>Published: <?=$page['pageDatePublished'];?> UTC</cite>
    <br />
    <cite>Author: anonymous</cite>
    <br />
    <a href='<?=BRAND_URL;?>pages/update/<?=$page['pageID'];?>'>Update Page</a>
    
    <?php
    // Page Long Description?
    if (isset($page['pageLongDescription'])
      && $page['pageLongDescription'] != '') {
    ?>
    <h3>Description</h3>
    <p><?=$page['pageLongDescription'];?></p>
    <?php
    }
    ?>
  </aside>

    */
?>
    
</div>