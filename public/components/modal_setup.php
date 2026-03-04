<div class="bg-popup" id="setup-form">
	<div class="formular-frame">
        <table width="80%" align="center" cellspacing="0">
            <tr valign="baseline" class="form_height">
                <td colspan="6" align="center" valign="middle">
                    <h2>Welcome to AllStockControl</h2>
                </td>
            </tr>
            <tr valign="baseline" class="form_height">
                <td colspan="6" align="center" valign="middle">
                    <p>To get started, please fill in your company information.</p>
                </td>
            </tr>
            <tr valign="baseline" class="form_height">
                <td style="font-size: 12px;" colspan="6" align="center" valign="middle">
                    <table width="90%" align="center" cellspacing="0">
                        <tr valign="baseline" height="60px">
                            <td colspan="3" align="center" valign="middle">
                                <input type="checkbox" id="terms-check" name="acepto" value="1">
                            </td>
                            <td colspan="5" align="left" valign="middle">
                                I accept the <a href="<?= htmlspecialchars($lang) ?>/terms" target="_blank">terms and conditions</a> of use of AllStockControl
                            </td>
                        </tr>
                        <tr valign="baseline" height="60px">
                            <td colspan="3" align="center" valign="middle">
                                <input type="checkbox" id="privacy-check" name="acepto" value="1">
                            </td>
                            <td colspan="5" align="left" valign="middle">
                                I accept the processing of my personal data in accordance with the
                                <a href="<?= htmlspecialchars($lang) ?>/gdpr" target="_blank">Privacy Policy</a> and in compliance with the applicable laws:</br>
                                GDPR (Sweden/EU), CCPA/CPRA (USA), LGPD (Brazil), LFPDPPP (Mexico), and other relevant regulations.
                            </td>
                        </tr>
                    </table>
                </td>      
			</tr>
            <tr valign="baseline" height="60px">
                <td colspan="6" align="center" valign="middle">
                    <button type="button" class="button-style-agree button-ghost" id="submit-company-info">I Agree</button>
                </td>
            </tr>
        </table>
    </div>
</div>