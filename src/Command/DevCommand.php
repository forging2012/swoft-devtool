<?php

namespace Swoft\Devtool\Command;

use Swoft\App;
use Swoft\Console\Bean\Annotation\Command;
use Swoft\Console\Helper\ConsoleUtil;
use Swoft\Console\Input\Input;
use Swoft\Helper\ProcessHelper;

/**
 * Some commands for application dev[<cyan>built-in</cyan>]
 * @package Swoft\Devtool\Command
 * @Command(coroutine=false)
 */
class DevCommand
{
    /**
     * @return array
     */
    public function internalConfig(): array
    {
        return [
            'swoft/devtool' => [
                '@devtool/web/dist/devtool',
                '@root/public'
            ],
        ];
    }

    /**
     * Used to publish the internal resources of the module to the 'public' directory
     * @Arguments
     *  srcDir   The source assets directory path. eg. `@vendor/some/lib/assets`
     *  dstDir   The defined component name.(default is `@root/public`)
     * @Options
     *   -y, --yes BOOL      Whether to ask when writing a file. default is: <info>True</info>
     *   -f, --force BOOL    Force override all exists file.(default: <info>False</info>)
     * @Example
     *   {fullCommand} swoft/devtool
     * @param Input $input
     * @return int
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function publish(Input $input): int
    {
        $assetDir = $input->getArg(0);
        $targetDir = $input->getArg(1);

        if (!$assetDir && !$targetDir) {
            \output()->colored('arguments is required!', 'warning');

            return -1;
        }

        // first arg is internal component name
        if ($assetDir && !$targetDir) {
            $config = $this->internalConfig();

            if (!isset($config[$assetDir])) {
                \output()->colored('missing arguments!', 'warning');
            }

            list($assetDir, $targetDir) = $config[$assetDir];
        }

        $assetDir = App::getAlias($assetDir);
        $targetDir = App::getAlias($targetDir);

        $yes = \input()->sameOpt(['y', 'yes'], false);
        $command = "cp -Rf $assetDir $targetDir";

        \output()->writeln("Will run shell command:\n $command");

        if (!$yes && !ConsoleUtil::confirm('Ensure continue?', true)) {
            \output()->writeln(' Quit, Bye!');

            return 0;
        }

        list($code, , $error) = ProcessHelper::run($command, App::getAlias('@root'));

        if ($code !== 0) {
            \output()->colored("Publish assets to $targetDir is failed!", 'error');
            \output()->writeln($error);

            return -2;
        }

        \output()->colored("\nPublish assets to $targetDir is OK!", 'success');

        return 0;
    }

    public function test(): int
    {
        return 0;
    }
}
