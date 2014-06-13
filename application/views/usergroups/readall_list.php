<ul>
    <?php
    for ($i = 0; $i < count($usergroups); $i++) {
        $link = BRAND_URL . 'usergroups/update/' . $usergroups[$i]['usergroupID'] . '/' . str_replace(' ', '-',
                                                                                                      $usergroups[$i]['usergroupName']);
        ?>
        <li>
            <small><cite><?= $usergroups[$i]['brand']['brandName']; ?></cite></small>
            &nbsp;
            <a href='<?= $link; ?>'>
                <?= $usergroups[$i]['usergroupName']; ?>
            </a>
        </li>
        <?php
    }
    ?>
</ul>
