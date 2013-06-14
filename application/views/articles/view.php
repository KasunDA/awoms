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
      <p>Be the first to <a href='<?=BRANDURL;?>comments/write/<?=$article['articleID'];?>'>add a comment!</a></p>
      <?php var_dump($articleComments); ?>
      
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