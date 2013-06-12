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
      <!-- <h2>Article section h2</h2> -->
      <p><?php
      foreach ($articleBody as $body) {
        if ($body['bodyContentActive'] != 1) {
          continue;
        }
        echo $body['bodyContentText'];
      }?></p>
    </section>
  </article>
  
  <aside>
    <cite>Published: <?=$article['articleDatePublished'];?></cite>
    <h3>Description</h3>
    <p><?=$article['articleLongDescription'];?></p>
  </aside>

</div>