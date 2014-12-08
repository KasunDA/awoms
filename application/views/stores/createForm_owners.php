<?php
// Update form (Owners):
?>
<div class="owners">
    <form id='<?php echo $formID; ?>'  method='POST'>
        <input type='hidden' name='step' value='2' />
        <input type='hidden' name='inp_storeID' value='<?php echo $storeID; ?>' />
        <input type='hidden' name='inp_brandID' value='<?php echo $inp_brandID; ?>' />

        <h1>Store Information</h1>
        <p>Make sure what you type here is correct as the store info is what will dynamically show up on the website.
            Only relevant store information will show up on the public website. Your personal information will be kept strictly confidential.</p>

        <table>

            <tr>
                <td>

                    <table>

                        <tr>
                            <td style="width:200px">
                                Store Number
                            </td>
                            <td>
                                <?= $inp_storeNumber; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Store Email
                            </td>
                            <td>
                                <?= $inp_email; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Store Phone
                            </td>
                            <td>
                                <input type='text' id='inp_phone' name='inp_phone' value='<?php
                                if (isset($inp_phone))
                                {
                                    echo $inp_phone;
                                }
                                ?>' size='30' />
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Store Toll Free
                            </td>
                            <td>
                                <input type='text' id='inp_tollFree' name='inp_tollFree' value='<?php
                                if (isset($inp_tollFree))
                                {
                                    echo $inp_tollFree;
                                }
                                ?>' size='30' />
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Store Fax
                            </td>
                            <td>
                                <input type='text' id='inp_fax' name='inp_fax' value='<?php
                                if (isset($inp_fax))
                                {
                                    echo $inp_fax;
                                }
                                ?>' size='30' />
                            </td>
                        </tr>

                        <tr>
                            <td>
                                Store Facebook URL
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
                    </table>

                </td>

                <td>

                    <table>
                        <tr>
                            <td>
                                Company Legal Name
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
                                <textarea id='inp_bio' name='inp_bio' cols="32" rows="5"><?php
                                    if (isset($inp_bio))
                                    {
                                        echo $inp_bio;
                                    }
                                    ?></textarea>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>

        </table>

        <table>
            <tr>
                <td>


                    <h1>Store Address</h1>
                    <table>

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
                                ?>' size='15' />

                                &nbsp;State
                                <?php
                                $selectedState = FALSE;
                                if (!empty($inp_address['stateProvinceCounty']))
                                {
                                    $selectedState = $inp_address['stateProvinceCounty'];
                                }
                                $stateChoiceList = Utility::GetStateChoiceList($selectedState);
                                ?>
                                <select id='inp_addressState' name='inp_addressState' style="width:100px">
                                    <?= $stateChoiceList; ?>
                                </select>

                                &nbsp;Zipcode
                                <input type='text' id='inp_addressZipcode' name='inp_addressZipcode' value='<?php
                                if (isset($inp_address))
                                {
                                    echo $inp_address['zipPostcode'];
                                }
                                ?>' size='10' />

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
                                <select id='inp_addressCountry' name='inp_addressCountry' style="width:200px">
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

                </td>
                <td>

                    <h1>Hours</h1>
                    <table>
                        <?= $hoursChoiceList; ?>
                    </table>


                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td>


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


                </td>
                <td>



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

    <p>&nbsp;</p>

    <p>
    <h1>Your store's protected territory</h1>
</p>
<p class='center'>
    <img src='' width='500' height='500'/>
</p>


</div>