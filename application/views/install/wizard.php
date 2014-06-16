<?php
if (empty($_POST))
{
?>

<form method='POST'>
  <input type='hidden' name='step' value='2' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        Brand Name
      </td>
      <td>
        <input type='text' id='inp_brandName' name='inp_brandName' size='60' placeholder="My Company"/>
      </td>
    </tr>
    
    <tr>
      <td>
        Brand Label
      </td>
      <td>
        <input type='text' id='inp_brandLabel' name='inp_brandLabel' size='60' placeholder="MyCo"/>
      </td>
    </tr>
    
    <tr>
      <td>
        Active Theme
      </td>
      <td>
        <input type='text' id='inp_activeTheme' name='inp_activeTheme' value='<?php
              echo "default";
        ?>' size='60' placeholder="default" />
      </td>
    </tr>
    
    <tr>
      <td>
        Domain Name
      </td>
      <td>
        <input type='text' id='inp_domainName' name='inp_domainName' value='<?php
            echo $_SERVER['HTTP_HOST'];
        ?>' size='60' placeholder="<?php echo $_SERVER['HTTP_HOST']; ?>"/>
      </td>
    </tr>
    
    <tr>
        <td colspan='2'>&nbsp;</td>
    </tr>
    
    <tr>
      <td>
        Administrator Username
      </td>
      <td>
        <input type='text' id='inp_adminUsername' name='inp_adminUsername' placeholder="GlobalAdmin" size='60' />
      </td>
    </tr>
    
    <tr>
      <td>
        Administrator Passphrase
      </td>
      <td>
        <input type='password' id='inp_adminPassphrase' name='inp_adminPassphrase' size='60' />
      </td>
    </tr>
    
    <tr>
      <td>
        Administrator Email
      </td>
      <td>
        <input type='text' id='inp_adminEmail' name='inp_adminEmail' placeholder="admin@myco.com" size='60' />
      </td>
    </tr>
    
    <tr>
        <td colspan='2' align='center'>
            <button type='submit'>Install</button>
        </td>
    </tr>

  </table>
</form>

<?php
} else {
?>
    <h1>Success!</h1>
    
    Click <a href='/admin/home'>here</a> if you are not redirected!

    <META http-equiv='refresh' content='2;URL=/admin/home'>      
<?php
}
?>