<div class="bg-popup" id="reactivate-subscription-form">
	<div class="formular-frame" style="padding-top: 40px;">
        <table width="80%" align="center" cellspacing="0">
            <tr valign="baseline">
                <td colspan="6" align="center" valign="middle">
                    <img src="/images/sys-img/card-revoked.png" alt="Card Revoked" width="80px" height="80px">
                </td>
            </tr>
            <tr valign="baseline">
                <td colspan="6" align="center" valign="middle">
                    <h2><?= $t['non_payment_message'] ?></h2>
                </td>
            </tr>
            <tr valign="baseline">
                <td colspan="6" align="center" valign="middle">
                    <p>
                        <?= $t['non_payment_message_reactivate'] ?>
                    </p>
                </td>
            </tr>
            <tr valign="baseline">
                <td width="50%" align="center" valign="middle">
                    <button type="button" class="cancel-btn logout-button"><?= $t['header_logout'] ?></button>
                </td>
                <td width="50%" align="center" valign="middle">
                    <button type="button" class="button-style-agree" id="reactivate-subscription"><?= $t['reactivate_subscription'] ?></button>
                </td>
            </tr>
        </table>
    </div>
</div>