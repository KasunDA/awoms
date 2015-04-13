<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />

    <table style="margin-left: 50px;">
        <tr>
            <td>
                <h1><?= $_SESSION['brand']['brandName']; ?> Store Locator</h1>
                <table width="85%">
                    <tr>
                        <td colspan="2">
                            <p><strong>Find a store in your area or search by state</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td>Zip Code:</td>
                        <td><input type="text" name="inp_zipcode" size="5"/></td>
                    </tr>
                    <tr>
                        <td>Search Radius:</td>
                        <td>
                            <select name="inp_searchRadius">
                                <option value="25">25 Miles</option>
                                <option value="50">50 Miles</option>
                                <option value="100" selected>100 Miles</option>
                                <option value="200">200 Miles</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="submit" name="stores" value="FIND"/>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <!-- <img src="/css/<?= $_SESSION['brand']['brandLabel']; ?>/images/items/store_locator_map.png" alt="" width="632" height="424" border="0" usemap="#MapStore" class="no-border" /> -->
                <!-- Map -->
            </td>
        </tr>
    </table>
</form>