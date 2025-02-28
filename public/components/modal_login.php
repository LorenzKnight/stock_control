<div class="formular-front-frame">
    <div class="formular_front hidden" id="formular_front">
        <form action="stock.php" method="post" name="formlogin" id="formlogin">
            <table width="80%" align="center" cellspacing="0">
                <tr valign="baseline">
                    <td style="font-size: 12px;" colspan="6" align="center" valign="middle">
                        
                    </td>      
                </tr>
                <tr valign="baseline" class="form_height">
                    <td colspan="6" align="center" valign="middle">
                        <input class="form-input-style" type="email" name="login_email" id="login_email" placeholder="Enter your E-Mail..." title="Enter a valid email" required/>
                    </td>
                </tr>
                <tr valign="baseline" class="form_height">
                    <td colspan="6" align="center" valign="middle">
                        <input class="form-input-style" type="password" name="login_password" id="login_password" placeholder="Enter your Password..." required/>
                    </td>
                </tr>
                <tr valign="baseline" class="form_height">
                    <td nowrap="nowrap" align="center" valign="middle">
                        <input type="submit" class="button-style-agree" value="Log in" />
                    </td>
                </tr>
                <tr valign="baseline">
                    <td style="color: #999;" nowrap="nowrap" align="center" valign="middle">
                        - or -
                    </td>
                </tr>
                <tr valign="baseline" class="form_height">
                    <td nowrap="nowrap" align="center" valign="middle">
                        <button class="button-style-agree" onclick="location.href='index.php?signup=1'" type="button">Sign up</button>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="login_status" id="login_status" value="1"/>
            <!-- <input type="hidden" name="MM_insert" id="MM_insert" value="formlogin" /> -->
        </form>
    </div>
</div>