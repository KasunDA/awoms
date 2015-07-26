<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_cartID' value='<?php echo $cartID; ?>' />

  <h1>Cart Information</h1>
  <table class="bordered">

<?php
    // Brand List - Non-Global-Admins (BrandID=1, Group=Admin) == limited by brand
    if (!empty($brandChoiceList))
    {
        $class='';
        if (empty($_SESSION['user'])
                || $_SESSION['user']['usergroup']['usergroupName'] != "Administrators"
                || $_SESSION['user']['usergroup']['brandID'] != 1)
        {
            $class='hidden';
        }
?>
    <tr class='<?php echo $class; ?>'>
      <td>
        <!-- Brand -->
        Brand
      </td>
      <td>
        <select name='inp_brandID'>
          <?=$brandChoiceList;?>
        </select>
      </td>
    </tr>
<?php
    }
    
    // Store List - Non-Global-Admins (BrandID=1, Group=Admin) == limited by brand
    if (!empty($storeChoiceList))
    {
        $class='';
        if (empty($_SESSION['user'])
                || $_SESSION['user']['usergroup']['usergroupName'] != "Administrators"
                || $_SESSION['user']['usergroup']['brandID'] != 1)
        {
            $class='hidden';
        }
?>
    <tr class='<?php echo $class; ?>'>
      <td>
        Store
        <p class='muted'>You can assign a domain name to a specific store to be brought to that store when visited</p>
      </td>
      <td>
        <select name='inp_storeID'>
          <?=$storeChoiceList;?>
        </select>
      </td>
    </tr>
<?php
    }
?>
    
    <tr>
      <td>
        Cart Name
      </td>
      <td>
        <input type='text' id='inp_cartName' name='inp_cartName' value='<?php
          if (isset($inp_cartName)) {
            echo $inp_cartName;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
          Active Theme
      </td>
      <td>
        <input type='text' id='inp_cartTheme' name='inp_cartTheme' value='<?php
          if (isset($inp_cartTheme)) {
            echo $inp_cartTheme;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
        Orders Email
      </td>
      <td>
        <input type='text' id='inp_emailOrders' name='inp_emailOrders' value='<?php
          if (isset($inp_emailOrders)) {
            echo $inp_emailOrders;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
        Contact Email
      </td>
      <td>
        <input type='text' id='inp_emailContact' name='inp_emailContact' value='<?php
          if (isset($inp_emailContact)) {
            echo $inp_emailContact;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
        Errors Email
      </td>
      <td>
        <input type='text' id='inp_emailErrors' name='inp_emailErrors' value='<?php
          if (isset($inp_emailErrors)) {
            echo $inp_emailErrors;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
        Address
      </td>
      <td>

          <?php
          $formattedAddress = $inp_address['firstName'].' '.$inp_address['middleName'].' '.$inp_address['lastName'];
          if (!empty(trim($formattedAddress)))
          {
              $formattedAddress .= "<br />";
          }

          if (!empty($inp_address['line1']))
          {
            $formattedAddress .= "<br />".$inp_address['line1'];
          }

          if (!empty($inp_address['line2']))
          {
            $formattedAddress .= "<br />".$inp_address['line2'];
          }

          if (!empty($inp_address['line3']))
          {
            $formattedAddress .= "<br />".$inp_address['line3'];
          }

          if (!empty($inp_address['city']))
          {
            $formattedAddress .= "<br />".$inp_address['city'];
          }

          if (!empty($inp_address['stateProvinceCounty']))
          {
            $formattedAddress .= ", ".$inp_address['stateProvinceCounty'];
          }

          if (!empty($inp_address['zipPostcode']))
          {
            $formattedAddress .= ", ".$inp_address['zipPostcode'];
          }

          if (!empty($inp_address['country']))
          {
            $formattedAddress .= ", ".$inp_address['country'];
          }

          if (!empty($inp_address['phone']))
          {
            $formattedAddress .= "<br />".$inp_address['phone'];
          }

          if (!empty($inp_address['email']))
          {
            $formattedAddress .= "<br />".$inp_address['email'];
          }

          echo $formattedAddress;
          ?>
      </td>
    </tr>

    <tr>
      <td>
        Terms of Service
      </td>
      <td>
          <textarea id='inp_termsOfService' name='inp_termsOfService' cols='55' rows='10'><?php
          if (isset($inp_termsOfService)) {
            echo $inp_termsOfService;
          }
          ?></textarea>
      </td>
    </tr>

    <tr>
      <td>
        Storefront Carousel
      </td>
      <td>
        <input type='text' id='inp_storefrontCarousel' name='inp_storefrontCarousel' value='<?php
          if (isset($inp_storefrontCarousel)) {
            echo $inp_storefrontCarousel;
          }
        ?>' size='60' />
      </td>
    </tr>
    
    <tr>
      <td>
        Storefront Categories
      </td>
      <td>
        <input type='text' id='inp_storefrontCategories' name='inp_storefrontCategories' value='<?php
          if (isset($inp_storefrontCategories)) {
            echo $inp_storefrontCategories;
          }
        ?>' size='60' />
      </td>
    </tr>
    
    <tr>
      <td>
        Storefront Description
      </td>
      <td>
        <input type='text' id='inp_storefrontDescription' name='inp_storefrontDescription' value='<?php
          if (isset($inp_storefrontDescription)) {
            echo $inp_storefrontDescription;
          }
        ?>' size='60' />
      </td>
    </tr>

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
                <button type="button" class="callAPI button_save" name="<?=$this->controller;?>" value="<?=$this->action;?>">
                    Save
                </button>
            </td>
        </tr>
    </table>

</form>