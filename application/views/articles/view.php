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
  
<?php
// Can be used for multi-step 1.2..3..4.....5 inner-templates
/*
// Loads appropriate template file if exists otherwise goes straight to default template
$tplPath = ROOT.DS.'application/views'.DS.$this->controller.DS.$this->action.'.step'.$step.'.php';
if (is_file($tplPath)) {
  include($tplPath);
}
 * 
 */
?>