<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />
    <input type='text' class='botrequired' name='botrequired' value='' />

    <?php
    if (!isset($commentID)) {
        $commentID = 'DEFAULT';
    }
    ?>

    <?php
    if (isset($articleID)) {
        echo "ArticleID: " . $articleID;
    }

    if (isset($parentCommentID)) {
        echo "parentCommentID: " . $parentCommentID;
    }
    ?>
    <input type='hidden' name='inp_commentID' value='<?php echo $commentID; ?>' />
    <input type='hidden' name='inp_parentItemID' value='<?php echo $parentItemID; ?>' />


    <h1>Comment</h1>
    <table class="bordered">

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
                        if (!isset($inp_commentActive) || $inp_commentActive == 1) {
                            echo ' selected';
                        }
                        ?>>Active</option>
                        <option value='0'<?php
                        if (isset($inp_commentActive) && $inp_commentActive == 0) {
                            echo ' selected';
                        }
                        ?>>Inactive</option>
                    </select>
                </td>
            </tr>

        </div>

    </table>

    <!-- Form Action Buttons -->
    <table class="form_actions">
        <tr>
            <td>
                <?php
                if ($this->action != "create"
                        && ACL::IsUserAuthorized($this->controller, "delete")) {
                ?>
                <button type="button" class="callAPI button_delete" name="<?=$this->controller;?>" value="delete">
                    Delete
                </button>
                <?php
                }
                ?>
                <button type="button" class="callAPI button_cancel" name="<?=$this->controller;?>" value="cancel">
                    Cancel
                </button>
                <button type="submit" class="callAPI button_save" name="<?=$this->controller;?>" value="update">
                    Save
                </button>
            </td>
        </tr>
    </table>

</form>