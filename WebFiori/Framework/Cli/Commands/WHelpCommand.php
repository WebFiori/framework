<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Commands\HelpCommand;
/**
 * Description of WHelpCommand
 *
 * @author Ibrahim
 */
class WHelpCommand extends HelpCommand {
    public function exec() : int {
        $argV = $this->getOwner()->getArgsVector();

        if (count($argV) == 0) {
            $this->printLogo();
        }
        $formattingOptions = [
            'color' => 'light-blue',
            'bold' => true
        ];
        $this->prints('WebFiori Framework ', $formattingOptions);
        $this->prints(' (c) Version ');
        $this->println(WF_VERSION." ".WF_VERSION_TYPE."\n\n", $formattingOptions);

        return parent::exec();
    }
    private function printLogo() {
        $this->println('|\                /|');
        $this->println('| \      /\      / |              |  / \  |');
        $this->println('\  \    /  \    /  / __________   |\/   \/|');
        $this->println(' \  \  /    \  /  / /  /______ /  | \/ \/ |');
        $this->println('  \  \/  /\  \/  / /  /           |  \ /  |');
        $this->println('   \    /  \    / /  /______      |\  |  /|');
        $this->println('    \  /    \  / /  /______ /       \ | /  ');
        $this->println('     \/  /\  \/ /  /                  |    ');
        $this->println('      \ /  \ / /  /                   |    ');
        $this->println('       ______ /__/                    |    ');
    }
}
