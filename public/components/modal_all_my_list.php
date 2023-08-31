<div class="formular_songs_list" id="formular_songs_list">
    <table class="music-list" cellspacing="0">
    <h4>My lists</h4>
    <?php
    foreach($all_my_lists as $all_list) {
    ?>
        <tr class="playlistContainer" data-playListId="<?= $all_list['listingsId']; ?>">
            <td>
                <div class="list-mini-cover">
                    <img src="images/profile/<?php ?>perfil.png" alt="">
                </div>
            </td>
            <td>
                <?php echo $all_list['listName'] .' - '. $all_list['listingsId'];?>
            </td>
        </tr>
    <?php
    }
    ?>
    </table>
</div>