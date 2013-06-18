<!-- Results -->
<div id='results'>
  <?php
    if (isset($resultsMsg)) {
      var_dump($resultsMsg);
    }
  ?>
</div>

<!-- Template Output -->
<div id='view'>

  <article>
    <header>
      <h1><?=$article['articleName'];?></h1>
    </header>
    <section>
      <p><?php
      if (!empty($articleBody)) {
        foreach ($articleBody as $body) {
          if ($body['bodyContentActive'] != 1) {
            continue;
          }
          echo $body['bodyContentText'];
        }
      }?></p>
      <hr />
    </section>
    
    <section>
      <h2>Comments</h2>
      <?php
        if (empty($articleComments)) {
          echo "<p>Be the first to <a href='".BRANDURL."comments/write/".$article['articleID']."'>add a comment!</a></p>";
        } else {
          echo "<p><a href='".BRANDURL."comments/write/".$article['articleID']."'>Add a comment!</a></p>
            <ul>";
          $ci = 0;
          $clvl = 0;
          foreach ($articleComments as $comment) {
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
                <cite><a name='".$comment['commentID']."'>#</a>".$comment['commentID']." ".$comment['commentDatePublished']."</cite> <a href='".BRANDURL."comments/write/".$article['articleID']."/".$comment['commentID']."'>reply</a>
                <br />".$comment['commentBodyText']."
              </li>";
          }
          echo '</ul>';
        }
      ?>
    </section>
  </article>
  
  <aside>
    <cite>Published: <?=$article['articleDatePublished'];?> UTC</cite>
    <br />
    <cite>Author: anonymous</cite>
    <br />
    <a href='<?=BRANDURL;?>articles/edit/<?=$article['articleID'];?>'>Edit Article</a>
    
    <?php
    // Article Long Description?
    if (isset($article['articleLongDescription'])
      && $article['articleLongDescription'] != '') {
    ?>
    <h3>Description</h3>
    <p><?=$article['articleLongDescription'];?></p>
    <?php
    }
    ?>
  </aside>

</div>