<ul>
<?php
for ($i=0; $i<count($pages); $i++)
{
    $pageDate = str_replace('-','/',substr($pages[$i]['pageDatePublished'], 0, 10));
    $link = BRAND_URL.'pages/update/'.$pages[$i]['pageID'].'/'.$pageDate.'/'.str_replace(' ', '-', $pages[$i]['pageName']);
?>
    <li>
      <a href='<?=$link;?>'>
        <?= $pages[$i]['pageName']; ?>
      </a>&nbsp;<small><cite><?= $pages[$i]['pageDatePublished']; ?></cite></small>
      <small><?=$pages[$i]['pageShortDescription']?></small>
    </li>
<?php
}
?>
</ul>
