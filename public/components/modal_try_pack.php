<div class="bg-popup" id="activate-pack-form">
	<div class="formular-frame" style="padding-top: 40px;">
        <table width="80%" align="center" cellspacing="0">
            <tr valign="baseline">
                <td colspan="6" align="center" valign="middle">
                    <img src="/images/sys-img/expired.png" alt="Expired" width="80px" height="80px">
                </td>
            </tr>
            <tr valign="baseline">
                <td colspan="6" align="center">
                    <h2><?= $t['your_free_trial_has_expired'] ?></h2>
                </td>
            </tr>
            <tr valign="baseline">
                <td colspan="6" align="center">
                    <p>
                        <?= $t['trial_expired_desc'] ?>
                    </p>
                    <p>
                        <?= $t['trial_expired_warning'] ?>
                    </p>
                </td>
            </tr>
            <tr valign="baseline" height="60px">
                <td width="50%" align="center">
                    <button type="button" class="cancel-btn logout-button"><?= $t['header_logout'] ?></button>
                </td>
                <td width="50%" align="center">
                    <button type="button" class="button-style-agree" id="upgrade-package"><?= $t['upgrade_package'] ?></button>
                </td>
            </tr>
        </table>
    </div>
</div>