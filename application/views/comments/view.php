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

  <comment>
    <section>
      <p><?php
      foreach ($commentBody as $body) {
        if ($body['bodyContentActive'] != 1) {
          continue;
        }
        echo $body['bodyContentText'];
      }?></p>
      <hr />
    </section>
    
    <section>
      <h2>Comments</h2>
      <p><a href='<?=BRANDURL;?>comments/write/<?=$comment['parentItemID'];?>/<?=$comment['commentID'];?>'>add a comment!</a></p>
    </section>
  </comment>
  
  <aside>
    <cite>Published: <?=$comment['commentDatePublished'];?> UTC</cite>
    <br />
    <cite>Author: anonymous</cite>
    <br />
    <a href='<?=BRANDURL;?>comments/edit/<?=$comment['commentID'];?>'>Edit Comment</a>
  </aside>

</div>