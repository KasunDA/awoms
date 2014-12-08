<?php
// Update form (Owners):
?>
<div class="owners">
<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />
    <input type='hidden' name='inp_userID' value='<?php echo $userID; ?>' />
    <input type='hidden' name='inp_usergroupID' value='<?php echo $inp_usergroupID; ?>' />
    <input type='hidden' name='inp_userActive' value='<?php echo $inp_userActive; ?>' />
    <input type='hidden' name='inp_userName' value='<?php echo $inp_userName; ?>' />
    
    <h1>User Login</h1>
    <table>

        <tr>
            <td>
                <!-- User -->
                Username
            </td>
            <td>
                <?=$inp_userName;?>
            </td>
        </tr>

        <tr>
            <td>
                New Passphrase<p>
                <small class='muted'>
                    Please choose a <a href="http://www.passwordmeter.com/" target="_blank">complex password</a>:
                    <ul>
                        <li>8 characters minimum</li>
                        <li>Uppercase, Lowercase</li>
                        <li>Numbers</li>
                        <li>Symbols, Spaces</li>
                    </ul>
                </small></p>
            </td>
            <td>
                <input type='password' id='inp_passphrase' name='inp_passphrase' size='30' />
                <br />
                <small class="muted"><p>Suggestions:
                    <ul>
                        <?php
                        for ($i=0; $i<2; $i++)
                        {
                            echo "<li>".htmlspecialchars(Utility::randomPassphrase())."</li>";
                        }
                        for ($i=0; $i<2; $i++)
                        {
                            echo "<li>".htmlspecialchars(Utility::randomPassphrase(NULL, TRUE))."</li>";
                        }
                        ?>
                    </ul></p>
                </small>
                
            </td>
        </tr>        

        <tr>
            <td>
                <!-- Email -->
                Email
            </td>
            <td>
                <input type='text' id='inp_userEmail' name='inp_userEmail' value='<?php
                if (isset($inp_userEmail)) {
                    echo $inp_userEmail;
                }
                ?>' size='30' />
            </td>
        </tr>


    </table>
    
    <h1>Contact Information</h1>
    <table class="bordered">

        <tr>
            <td>
                Name
            </td>
            <td>
                <table>
                    <tr>
                        <td>First</td>
                        <td>Middle</td>
                        <td>Last</td>
                    </tr>
                    <tr>
                        <td>
                            <input type='text' id='inp_firstName' name='inp_firstName' value='<?php
                            if (isset($inp_firstName)) {
                                echo $inp_firstName;
                            }
                            ?>' size='15' />
                        </td>
                        <td>
                            <input type='text' id='inp_middleName' name='inp_middleName' value='<?php
                            if (isset($inp_middleName)) {
                                echo $inp_middleName;
                            }
                            ?>' size='15' />
                        </td>
                        <td>
                            <input type='text' id='inp_lastName' name='inp_lastName' value='<?php
                            if (isset($inp_lastName)) {
                                echo $inp_lastName;
                            }
                            ?>' size='15' />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td>
                Phone
            </td>
            <td>
                <input type='text' id='inp_phone' name='inp_phone' value='<?php
                if (isset($inp_phone)) {
                    echo $inp_phone;
                }
                ?>' size='60' />
            </td>
        </tr>

        <tr>
            <td>
                Cell Phone
            </td>
            <td>
                <input type='text' id='inp_cellPhone' name='inp_cellPhone' value='<?php
                if (isset($inp_cellPhone)) {
                    echo $inp_cellPhone;
                }
                ?>' size='60' />
            </td>
        </tr>

    </table>
    
    <h1>Address</h1>
    <table class="bordered">

        <tr>
            <td>
                Line 1
            </td>
            <td>
                <?php
                // Address ID
                if (!empty($inp_address['addressID']))
                {
                    echo "<input type='hidden' name='inp_addressID' value='".$inp_address['addressID']."'/>";
                } else {
                    echo "<input type='hidden' name='inp_addressID' value='DEFAULT'/>";
                }
                ?>
                
                <input type='text' id='inp_addressLine1' name='inp_addressLine1' value='<?php
                if (isset($inp_address)) {
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
                if (isset($inp_address)) {
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
                if (isset($inp_address)) {
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
                if (isset($inp_address)) {
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
                    <?=$stateChoiceList;?>
                </select>
            </td>
        </tr>

        <tr>
            <td>
                Zipcode
            </td>
            <td>
                <input type='text' id='inp_addressZipcode' name='inp_addressZipcode' value='<?php
                       if (isset($inp_address)) {
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
                    <?=$countryChoiceList;?>
                </select>
            </td>
        </tr>
        
        <tr>
            <td>
                Address Notes
            </td>
            <td>
                <textarea id='inp_addressNotes' name='inp_addressNotes' cols="40" rows="3"><?php
                      if (isset($inp_address)) {
                    echo $inp_address['addressNotes'];
                }
                       ?></textarea>
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
</div>