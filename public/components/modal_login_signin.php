<div class="formular_front" id="formular_front">
    <form action="discover.php" method="post" name="formsignin" id="formsignin">
        <table width="80%" align="center" cellspacing="0">
            <tr valign="baseline">
                <td style="font-size: 12px;" colspan="6" align="center" valign="middle">
                    
                </td>      
            </tr>
            <tr valign="baseline" class="form_height">
                <td colspan="6" align="center" valign="middle">
                    <input class="text_input_form1" type="email" name="email" id="email" placeholder="Enter your E-Mail..." title="Enter a valid email" required/>
                </td>
            </tr>
            <tr valign="baseline" class="form_height">
                <td colspan="6" align="center" valign="middle">
                    <input class="text_input_form1" type="password" name="password" id="password" placeholder="Enter your Password..." required/>
                </td>
            </tr>
            <tr valign="baseline" class="form_height">
                <td nowrap="nowrap" align="center" valign="middle">
                    <input type="submit" class="button_form1" value="Log in" />
                </td>
            </tr>
            <tr valign="baseline" height="40">
                <td style="color: #999;" nowrap="nowrap" align="center" valign="middle">
                    <p>- or -</p>
                </td>
            </tr>
            <tr valign="baseline" class="form_height">
                <td nowrap="nowrap" align="center" valign="middle">
                    <button class="button_form1" onclick="location.href='index.php?signup=1'" type="button">Sign up</button>
                </td>
            </tr>
        </table>
        <input type="hidden" name="status" id="status" value="1"/>
        <input type="hidden" name="MM_insert" id="MM_insert" value="formsignin" />
    </form>
</div>