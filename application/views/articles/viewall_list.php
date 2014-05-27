<ul>
<?php
for ($i=0; $i<count($articles); $i++)
{
    $articleDate = str_replace('-','/',substr($articles[$i]['articleDatePublished'], 0, 10));
    $articleLink = BRANDURL.'articles/edit/'.$articles[$i]['articleID'].'/'.$articleDate.'/'.str_replace(' ', '-', $articles[$i]['articleName']);
?>
    <li>
      <a href='<?=$articleLink;?>'>
        <?= $articles[$i]['articleName']; ?>
      </a>&nbsp;<small><cite><?= $articles[$i]['articleDatePublished']; ?></cite></small>
      <small><?=$articles[$i]['articleShortDescription']?></small>
    </li>
<?php
}
?>
</ul>
