<?php
namespace WebFiori\Framework\Ui\ServerErrPage;

if (!defined('WF_VERBOSE') || WF_VERBOSE === false) {
    return;
}
?>
<v-col cols=12>
    <v-card>
        <v-card-title>
            Exception Details
        </v-card-title>
        <v-card-text>
            <v-list>
                <v-list-item>
                    <v-list-item-content>
                        <v-list-item-title>
                            Exception Message
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            <?= $throwableOrErr->getMessage()?>
                        </v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item>
                    <v-list-item-content>
                        <v-list-item-title>
                            Exception Class
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            <?= get_class($throwableOrErr->getException())?>
                        </v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item>
                    <v-list-item-content>
                        <v-list-item-title>
                            At Class
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            <?= $throwableOrErr->getClass()?>
                        </v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item>
                    <v-list-item-content>
                        <v-list-item-title>
                            At Line
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            <?= $throwableOrErr->getLine()?>
                        </v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
            </v-list>
        </v-card-text>
    </v-card>
</v-col>