<?php
namespace webfiori\framework\ui\serverErrPage;

?>
<v-col cols=12>
    <v-alert prominent type=error>
        <v-row align=center>
            <v-col>
                <?php
                if (defined('WF_VERBOSE') && WF_VERBOSE === true) {
                    echo '500 - Server Error: Uncaught Exception.';
                } else {
                    echo 'General Server Error';
                }
?>
            </v-col>
        </v-row>
    </v-alert>
</v-col>
