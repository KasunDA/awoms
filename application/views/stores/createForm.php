<?php
// Owners get special locked down edit form
if ($_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
{
    require('createForm_owners.php');
    return;
}

// NEW store form (limited fields)
if ($storeID == 'DEFAULT')
{
    ?>

    <form id='<?php echo $formID; ?>'  method='POST'>
        <input type='hidden' name='step' value='2' />
        <input type='hidden' name='inp_storeID' value='<?php echo $storeID; ?>' />
        <input type='hidden' name='inp_storeTheme' value='default'/>
        <input type='hidden' name='inp_addressID' value='NULL'/>

        <h1>Store Information</h1>
        <table class="bordered">
            <!-- Brand -->
            <tr class='<?php echo $brandChoiceListClass; ?>'>
                <td>
                    Brand
                </td>
                <td>
                    <select name='inp_brandID'>
                        <?= $brandChoiceList; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    Store Number
                </td>
                <td>
                    <input type='text' id='inp_storeNumber' name='inp_storeNumber' value='<?php
                    if (isset($inp_storeNumber))
                    {
                        echo $inp_storeNumber;
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
                    if ($this->action != "create" && ACL::IsUserAuthorized($this->controller, "delete"))
                    {
                        ?>
                        <button type="button" class="callAPI button_delete" name="<?= $this->controller; ?>" value="delete">
                            Delete
                        </button>
                        <?php
                    }
                    ?>
                    <button type="button" class="callAPI button_cancel" name="<?= $this->controller; ?>" value="cancel">
                        Cancel
                    </button>
                    <button type="button" class="callAPI button_save" name="<?= $this->controller; ?>" value="<?= $this->action; ?>">
                        Save
                    </button>
                </td>
            </tr>
        </table>

    </form>
    <?php
    return;
}

// Update form:
?>
<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />
    <input type='hidden' name='inp_storeID' value='<?php echo $storeID; ?>' />

    <div id="accordion">

        <h1>Store Information</h1>
        <table class="bordered">
            <!-- Brand -->
            <tr class='<?php echo $brandChoiceListClass; ?>'>
                <td>
                    Brand
                </td>
                <td>
                    <select name='inp_brandID'>
                        <?= $brandChoiceList; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    Store Number
                </td>
                <td>
                    <input type='text' id='inp_storeNumber' name='inp_storeNumber' value='<?php
                    if (isset($inp_storeNumber))
                    {
                        echo $inp_storeNumber;
                    }
                    ?>' size='10' />
                </td>
            </tr>

            <tr>
                <td>
                    Coding
                    <p class="muted">Only Open stores will appear to public</p>
                </td>
                <td>
                    <select name='inp_coding'>
                        <?= $codingChoiceList; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    Email
                </td>
                <td>
                    <input type='text' id='inp_email' name='inp_email' value='<?php
                    if (isset($inp_email))
                    {
                        echo $inp_email;
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    Phone
                </td>
                <td>
                    <input type='text' id='inp_phone' name='inp_phone' value='<?php
                    if (isset($inp_phone))
                    {
                        echo $inp_phone;
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    Toll Free
                </td>
                <td>
                    <input type='text' id='inp_tollFree' name='inp_tollFree' value='<?php
                    if (isset($inp_tollFree))
                    {
                        echo $inp_tollFree;
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    Fax
                </td>
                <td>
                    <input type='text' id='inp_fax' name='inp_fax' value='<?php
                    if (isset($inp_fax))
                    {
                        echo $inp_fax;
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    Facebook URL
                    <p class="muted">Enter the entire facebook URL including http://www.</p>
                </td>
                <td>
                    <input type='text' id='inp_facebookURL' name='inp_facebookURL' value='<?php
                    if (isset($inp_facebookURL))
                    {
                        echo $inp_facebookURL;
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    Store Website URL
                    <p class="muted">If a store has their own website URL, enter it here including http://www. and it will be displayed on the store location page</p>
                </td>
                <td>
                    <input type='text' id='inp_website' name='inp_website' value='<?php
                    if (isset($inp_website))
                    {
                        echo $inp_website;
                    }
                    ?>' size='30' />
                </td>
            </tr>

            <tr>
                <td>
                    Store-to-Store Sales
                    <p class="muted">Private use only</p>
                </td>
                <td>
                    <?php
                    $checked = "";
                    if (!empty($inp_storeToStoreSales))
                    {
                        $checked = " checked";
                    }
                    ?>
                    <input type='hidden' name='inp_storeToStoreSales' value='0'/>
                    <input type='checkbox' name='inp_storeToStoreSales' value='1'<?= $checked; ?>/>
                </td>
            </tr>

            <tr>
                <td>
                    Company Legal Name
                    <p class="muted">Private use only</p>
                </td>
                <td>
                    <input type='text' id='inp_legalName' name='inp_legalName' value='<?php
                    if (isset($inp_legalName))
                    {
                        echo $inp_legalName;
                    }
                    ?>' size='30' />
                </td>
            </tr>

            <tr>
                <td>
                    Company EIN
                    <p class="muted">Private use only</p>
                </td>
                <td>
                    <input type='text' id='inp_ein' name='inp_ein' value='<?php
                    if (isset($inp_ein))
                    {
                        echo $inp_ein;
                    }
                    ?>' size='30' />
                </td>
            </tr>

            <tr>
                <td>
                    Store Bio
                </td>
                <td>
                    <textarea id='inp_bio' name='inp_bio' cols="60" rows="5"><?php
                        if (isset($inp_bio))
                        {
                            echo $inp_bio;
                        }
                        ?></textarea>
                </td>
            </tr>
        </table>

        <h1>Store Address</h1>
        <table class="bordered">

            <tr>
                <td>
                    Line 1
                    <?php
                    if (!empty($inp_address['addressID']))
                    {
                        echo "<input type='hidden' name='inp_addressID' value='" . $inp_address['addressID'] . "'/>";
                    }
                    else
                    {
                        echo "<input type='hidden' name='inp_addressID' value='DEFAULT'/>";
                    }
                    ?>
                </td>
                <td>
                    <input type='text' id='inp_addressLine1' name='inp_addressLine1' value='<?php
                    if (isset($inp_address))
                    {
                        echo $inp_address['line1'];
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    Line 2
                </td>
                <td>
                    <input type='text' id='inp_addressLine2' name='inp_addressLine2' value='<?php
                    if (isset($inp_address))
                    {
                        echo $inp_address['line2'];
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    Line 3
                </td>
                <td>
                    <input type='text' id='inp_addressLine3' name='inp_addressLine3' value='<?php
                    if (isset($inp_address))
                    {
                        echo $inp_address['line3'];
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    City
                </td>
                <td>
                    <input type='text' id='inp_addressCity' name='inp_addressCity' value='<?php
                    if (isset($inp_address))
                    {
                        echo $inp_address['city'];
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    State
                </td>
                <td>
                    <?php
                    $selectedState = FALSE;
                    if (!empty($inp_address['stateProvinceCounty']))
                    {
                        $selectedState = $inp_address['stateProvinceCounty'];
                    }
                    $stateChoiceList = Utility::GetStateChoiceList($selectedState);
                    ?>
                    <select id='inp_addressState' name='inp_addressState'>
                        <?= $stateChoiceList; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    Zipcode
                </td>
                <td>
                    <input type='text' id='inp_addressZipcode' name='inp_addressZipcode' value='<?php
                    if (isset($inp_address))
                    {
                        echo $inp_address['zipPostcode'];
                    }
                    ?>' size='60' />
                </td>
            </tr>

            <tr>
                <td>
                    Country
                </td>
                <td>
                    <?php
                    $selectedCountry = FALSE;
                    if (isset($inp_address))
                    {
                        $selectedCountry = $inp_address['country'];
                    }
                    $countryChoiceList = Utility::GetCountryChoiceList($selectedCountry);
                    ?>
                    <select id='inp_addressCountry' name='inp_addressCountry'>
                        <?= $countryChoiceList; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    Address Notes
                </td>
                <td>
                    <textarea id='inp_addressNotes' name='inp_addressNotes' cols="40" rows="3"><?php
                        if (isset($inp_address))
                        {
                            echo $inp_address['addressNotes'];
                        }
                        ?></textarea>
                </td>
            </tr>

        </table>

        <h1>Contacts</h1>
        <table class="bordered">
            <tr>
                <td>
                    Owner
                    <p class="muted">Store Owner is granted access to edit the store information that is displayed on the store location page</p>
                </td>
                <td>
                    <select name='inp_ownerID'>
                        <?= $contactsChoiceList; ?>
                    </select>
                </td>
            </tr>
        </table>

        <h1>Services</h1>
        <table class="bordered">
            <tr>
                <td>
                    <ul class="menu menu-horizontal">
                        <?= $servicesChoiceList; ?>
                    </ul>
                </td>
            </tr>
        </table>

        <h1>Hours</h1>
        <table class="bordered">
            <?= $hoursChoiceList; ?>
        </table>

    </div>

    <!-- Form Action Buttons -->
    <table class="form_actions">
        <tr>
            <td>
                <?php
                if ($this->action != "create" && ACL::IsUserAuthorized($this->controller, "delete"))
                {
                    ?>
                    <button type="button" class="callAPI button_delete" name="<?= $this->controller; ?>" value="delete">
                        Delete
                    </button>
                    <?php
                }
                ?>
                <button type="button" class="callAPI button_cancel" name="<?= $this->controller; ?>" value="cancel">
                    Cancel
                </button>
                <button type="button" class="callAPI button_save" name="<?= $this->controller; ?>" value="<?= $this->action; ?>">
                    Save
                </button>
            </td>
        </tr>
    </table>

</form>