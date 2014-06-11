<div class="main_readall">
    <div id='divResults'></div>
    <div class="main_output">
        
        <!-- Output -->
        <ul>
<?php
    $c = $this->controller;
    $items = $$c;
    $lbl = trim(strtolower($this->controller), "s");
    for ($i=0; $i<count($items); $i++) {
      $updateLink = BRAND_URL.$this->controller.'/update/'.$items[$i][$lbl.'ID'].'/'.str_replace(' ', '-', $items[$i][$lbl.'Name']);
?>
            <li>
              <a href="<?=$updateLink;?>">
                <?= $items[$i][$lbl.'Name']; ?>
              </a>
            </li>
<?php
    }
?>
        </ul>
        
    </div>
</div>