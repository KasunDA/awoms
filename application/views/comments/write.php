<!-- Results -->
<div id='results'>
  <?php
  if (isset($commentID)
    && $commentID != 'DEFAULT') {
    echo "
      Comment (#".$commentID.") updated! (Copy #".$bodyContentID.") <a href='".DOMAINURL."comments/view/".$commentID."'>View Comment</a> or <a href='".DOMAINURL."articles/view/".$articleID."#".$commentID."'>View Article</a><hr />";
  }
  ?>
</div>

<!-- Template Output -->
<div id='view'>

  <form method='POST'>
    <input type='hidden' name='step' value='2' />
    <?php
    if (!isset($commentID)) {
      $commentID = 'DEFAULT';
    }?>
    
    <?php
    if (isset($articleID)) {
      echo "ArticleID: ".$articleID;
    }
    
    if (isset($parentCommentID)) {
      echo "parentCommentID: ".$parentCommentID;
    }
    ?>
    <input type='hidden' name='inp_commentID' value='<?php echo $commentID; ?>' />
    <input type='hidden' name='inp_parentItemID' value='<?php echo $parentItemID; ?>' />
    
    
    <table cellpadding='2' cellspacing='0'>

      <tr>
        <td>
          <!-- Body -->
          Body
        </td>
        <td>
          <textarea name='inp_commentBody' cols='60' rows='8'><?php
            if (isset($inp_commentBody)) {
              echo $inp_commentBody; // @todo [] NL to BR
            }
          ?></textarea>
        </td>
      </tr>

    <!-- Adv Options -->
    <div id='advancedOptions'>

      <tr>
        <td>
            Active
        </td>
        <td>
            <select name='inp_commentActive'>
              <option value='1'<?php
                if (!isset($inp_commentActive)
                  || $inp_commentActive == 1) {
                  echo ' selected';
                }
              ?>>Active</option>
              <option value='0'<?php
                if (isset($inp_commentActive)
                  && $inp_commentActive == 0) {
                  echo ' selected';
                }
              ?>>Inactive</option>
            </select>
        </td>
      </tr>
      
    </div>
    
      <tr>
        <td>
          <center>
            <!-- Submit -->
            <button type='submit'>Go</button>
          </center>
        </td>
        <td>
          &nbsp;
        </td>
      </tr>
    
    </table>

  </form>

</div>