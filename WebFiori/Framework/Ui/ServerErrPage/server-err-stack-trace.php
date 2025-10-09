<?php
namespace WebFiori\Framework\Ui\ServerErrPage;

if (!defined('WF_VERBOSE') || WF_VERBOSE === false) {
    return;
}
?>
<v-col cols=12>
    <v-card>
        <v-card-title>
            Stack Trace
        </v-card-title>
        <v-card-text>
            <v-list dense>
                <?php
                $index = 0;

foreach ($throwableOrErr->getTrace() as $traceEntry) {
    ?>
                <v-list-item>
                    <v-list-item-title>
                        <?= '#'.$index.' '.$traceEntry?>
                    </v-list-item-title>
                </v-list-item>
                <?php
    $index++;
}
?>
            </v-list>
        </v-card-text>
    </v-card>
</v-col>